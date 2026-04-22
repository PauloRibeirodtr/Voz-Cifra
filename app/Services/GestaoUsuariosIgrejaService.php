<?php

namespace App\Services;

use App\Enums\PapelIgreja;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GestaoUsuariosIgrejaService
{
    public function __construct(
        private readonly NotificacaoSegurancaService $notificacaoSegurancaService,
        private readonly NotificacaoAcessoInicialService $notificacaoAcessoInicialService
    ) {
    }

    public function criarOuAtualizarContaOperacional(
        array $dados,
        Igreja $igreja,
        array $papeis,
        ?Usuario $ator = null,
        ?Usuario $usuarioBase = null,
        string $origem = 'gestao_usuario_igreja'
    ): Usuario {
        $papeisNormalizados = $this->normalizarPapeis($papeis);

        if ($papeisNormalizados->isEmpty()) {
            throw new \InvalidArgumentException('Informe ao menos um papel operacional para a conta.');
        }

        $usuario = $this->resolverUsuarioAlvo(
            cpf: (string) ($dados['cpf'] ?? ''),
            email: (string) ($dados['email'] ?? ''),
            usuarioBase: $usuarioBase
        );

        $papeisConcedidos = collect();
        $senhaInformada = trim((string) ($dados['password'] ?? ''));
        $deveLiberarPrimeiroAcesso = $senhaInformada !== ''
            || !$usuario
            || ($usuario && $this->ehEmailTecnicoSemLogin((string) $usuario->email));

        $usuario = DB::transaction(function () use (
            $dados,
            $igreja,
            $papeisNormalizados,
            $ator,
            $origem,
            $usuario,
            $senhaInformada,
            $deveLiberarPrimeiroAcesso,
            &$papeisConcedidos
        ): Usuario {
            $conta = $usuario ?? new Usuario();

            $conta->fill([
                'nome' => trim((string) $dados['nome']),
                'cpf' => trim((string) $dados['cpf']),
                'email' => trim((string) $dados['email']),
                'telefone' => $this->normalizarCampoTexto($dados['telefone'] ?? null),
                'ativo' => array_key_exists('ativo', $dados) ? (bool) $dados['ativo'] : true,
                'eh_padre' => (bool) ($conta->eh_padre ?? false) || (bool) ($dados['eh_padre'] ?? false),
            ]);

            if (!$conta->exists) {
                $conta->perfil_global = 'usuario';
                $conta->nivel_global = $this->nivelLegadoPorPapeis($papeisNormalizados, null);
                $conta->password = $senhaInformada !== ''
                    ? $senhaInformada
                    : $this->senhaPadraoPorCpf((string) $dados['cpf']);
                $conta->primeiro_acesso = true;
            } else {
                if (!$conta->ehAdminMaster()) {
                    $conta->perfil_global = 'usuario';
                    $conta->nivel_global = $this->nivelLegadoPorPapeis($papeisNormalizados, $conta->nivelGlobal());
                }

                if ($deveLiberarPrimeiroAcesso) {
                    $conta->password = $senhaInformada !== ''
                        ? $senhaInformada
                        : $this->senhaPadraoPorCpf((string) $dados['cpf']);
                    $conta->primeiro_acesso = true;
                }
            }

            $conta->save();
            $conta->garantirVinculoNaIgreja($igreja);

            foreach ($papeisNormalizados as $papel) {
                $jaPossuia = $conta->temPapelNaIgreja($papel, $igreja->id);
                $conta->adicionarPapel($papel, $igreja, $ator, $origem);

                if (!$jaPossuia) {
                    $papeisConcedidos->push($papel);
                }
            }

            return $conta->fresh();
        });

        $this->notificarPapeisConcedidos($usuario, $igreja, $papeisConcedidos, $ator, $origem);

        if ($usuario->primeiro_acesso && filter_var((string) $usuario->email, FILTER_VALIDATE_EMAIL)) {
            $this->notificacaoAcessoInicialService->enviarConvite(
                alvo: $usuario,
                ator: $ator,
                contexto: [
                    'origem' => $origem,
                    'origem_id' => $usuario->id,
                    'igreja_id' => $igreja->id,
                    'igreja_nome' => $igreja->nome,
                    'papeis_labels' => $papeisNormalizados->map(fn (PapelIgreja $papel) => $papel->label())->values()->all(),
                    'senha_inicial' => $senhaInformada !== '' ? 'definida_manual' : 'cpf_sem_pontuacao',
                ]
            );
        }

        return $usuario;
    }

    public function criarOuAtualizarContaBase(
        array $dados,
        ?Usuario $ator = null,
        ?Usuario $usuarioBase = null,
        string $origem = 'gestao_usuario_base'
    ): Usuario {
        $perfilGlobal = (string) ($dados['perfil_global'] ?? 'usuario');
        $ehAdminMaster = $perfilGlobal === 'admin_master';
        $ehPadre = (bool) ($dados['eh_padre'] ?? false);
        $emailInformado = $this->normalizarCampoTexto($dados['email'] ?? null);
        $senhaInformada = trim((string) ($dados['password'] ?? ''));

        $usuario = $this->resolverUsuarioAlvo(
            cpf: (string) ($dados['cpf'] ?? ''),
            email: $emailInformado,
            usuarioBase: $usuarioBase
        );

        $eraAdminMaster = $usuario?->ehAdminMaster() ?? false;
        $nivelAnterior = $usuario?->nivelGlobal();
        $senhaFoiDefinida = false;

        $usuario = DB::transaction(function () use (
            $dados,
            $perfilGlobal,
            $ehAdminMaster,
            $ehPadre,
            $emailInformado,
            $senhaInformada,
            $usuario,
            &$senhaFoiDefinida
        ): Usuario {
            $conta = $usuario ?? new Usuario();
            $emailAtual = $this->normalizarCampoTexto($conta->email ?? null);
            $emailTecnicoAtual = $emailAtual !== null && $this->ehEmailTecnicoSemLogin($emailAtual);
            $emailFinal = $emailInformado;

            if ($emailFinal === null && $ehPadre && (!$conta->exists || $emailTecnicoAtual)) {
                $emailFinal = $this->gerarEmailTecnicoPadre((string) $dados['cpf']);
            }

            if ($emailFinal === null) {
                $emailFinal = $emailAtual;
            }

            $conta->fill([
                'nome' => trim((string) $dados['nome']),
                'cpf' => trim((string) $dados['cpf']),
                'email' => $emailFinal,
                'telefone' => $this->normalizarCampoTexto($dados['telefone'] ?? null),
                'perfil_global' => $perfilGlobal,
                'nivel_global' => $ehAdminMaster
                    ? (int) ($dados['nivel_global'] ?? 6)
                    : 1,
                'eh_padre' => $ehPadre || (bool) ($conta->eh_padre ?? false),
                'ativo' => array_key_exists('ativo', $dados) ? (bool) $dados['ativo'] : true,
            ]);

            if (!$conta->exists) {
                if ($emailFinal !== null && !$this->ehEmailTecnicoSemLogin($emailFinal)) {
                    $conta->password = $senhaInformada !== ''
                        ? $senhaInformada
                        : $this->senhaPadraoPorCpf((string) $dados['cpf']);
                    $conta->primeiro_acesso = true;
                    $senhaFoiDefinida = true;
                } else {
                    $conta->password = Str::password(24);
                    $conta->primeiro_acesso = false;
                }
            } else {
                $mudouEmailTecnicoParaLogin = $emailInformado !== null && $emailTecnicoAtual;

                if ($senhaInformada !== '' || $mudouEmailTecnicoParaLogin) {
                    $conta->password = $senhaInformada !== ''
                        ? $senhaInformada
                        : $this->senhaPadraoPorCpf((string) $dados['cpf']);
                    $conta->primeiro_acesso = true;
                    $senhaFoiDefinida = true;
                }
            }

            $conta->save();

            return $conta->fresh();
        });

        if ($usuario->ehAdminMaster()) {
            $nivelNovo = $usuario->nivelGlobal();
            $foiPromovido = !$eraAdminMaster || $nivelAnterior !== $nivelNovo;

            if ($foiPromovido) {
                $this->notificacaoSegurancaService->enviarEventoConta(
                    alvo: $usuario,
                    evento: 'troca_nivel_global',
                    ator: $ator,
                    contexto: [
                        'origem' => $origem,
                        'nivel_anterior' => $nivelAnterior,
                        'nivel_novo' => $nivelNovo,
                    ]
                );
            }
        }

        if (!$usuario->ehAdminMaster() && $senhaFoiDefinida) {
            $this->notificacaoSegurancaService->enviarEventoConta(
                alvo: $usuario,
                evento: 'reset_senha',
                ator: $ator,
                contexto: [
                    'origem' => $origem,
                    'senha_inicial' => $senhaInformada !== '' ? 'definida_manual' : 'cpf_sem_pontuacao',
                ]
            );
        }

        if ($usuario->primeiro_acesso && $senhaFoiDefinida && filter_var((string) $usuario->email, FILTER_VALIDATE_EMAIL)) {
            $this->notificacaoAcessoInicialService->enviarConvite(
                alvo: $usuario,
                ator: $ator,
                contexto: [
                    'origem' => $origem,
                    'origem_id' => $usuario->id,
                    'senha_inicial' => $senhaInformada !== '' ? 'definida_manual' : 'cpf_sem_pontuacao',
                ]
            );
        }

        return $usuario;
    }

    public function atribuirPapeisAoUsuarioExistente(
        Usuario $usuario,
        Igreja $igreja,
        array $papeis,
        ?Usuario $ator = null,
        string $origem = 'gestao_usuario_igreja_vinculo'
    ): Usuario {
        $papeisNormalizados = $this->normalizarPapeis($papeis);

        if ($papeisNormalizados->isEmpty()) {
            throw new \InvalidArgumentException('Informe ao menos um papel operacional para vincular.');
        }

        $papeisConcedidos = collect();

        $usuario = DB::transaction(function () use ($usuario, $igreja, $papeisNormalizados, $ator, $origem, &$papeisConcedidos): Usuario {
            $conta = $usuario->fresh() ?? $usuario;
            $conta->garantirVinculoNaIgreja($igreja);

            foreach ($papeisNormalizados as $papel) {
                $jaPossuia = $conta->temPapelNaIgreja($papel, $igreja->id);
                $conta->adicionarPapel($papel, $igreja, $ator, $origem);

                if (!$jaPossuia) {
                    $papeisConcedidos->push($papel);
                }
            }

            return $conta->fresh();
        });

        $this->notificarPapeisConcedidos($usuario, $igreja, $papeisConcedidos, $ator, $origem);

        return $usuario;
    }

    public function vincularUsuarioExistente(
        array $dados,
        Igreja $igreja,
        array $papeis,
        ?Usuario $ator = null,
        string $origem = 'gestao_usuario_igreja_vinculo'
    ): Usuario {
        $usuarioBase = isset($dados['usuario_id']) && is_numeric($dados['usuario_id'])
            ? Usuario::query()->find((int) $dados['usuario_id'])
            : null;

        $usuario = $this->resolverUsuarioAlvo(
            cpf: (string) ($dados['cpf'] ?? ''),
            email: (string) ($dados['email'] ?? ''),
            usuarioBase: $usuarioBase
        );

        if (!$usuario) {
            throw ValidationException::withMessages([
                'usuario' => 'Nenhum usuario existente foi encontrado com os dados informados.',
            ]);
        }

        return $this->atribuirPapeisAoUsuarioExistente(
            usuario: $usuario,
            igreja: $igreja,
            papeis: $papeis,
            ator: $ator,
            origem: $origem
        );
    }

    public function criarOuAtualizarPadre(
        array $dados,
        ?Usuario $ator = null,
        ?Usuario $usuarioBase = null,
        string $origem = 'gestao_padre'
    ): Usuario {
        $usuario = $this->resolverUsuarioAlvo(
            cpf: (string) ($dados['cpf'] ?? ''),
            email: null,
            usuarioBase: $usuarioBase
        );

        $igrejaId = isset($dados['igreja_id']) && is_numeric($dados['igreja_id'])
            ? (int) $dados['igreja_id']
            : null;

        return DB::transaction(function () use ($dados, $usuario, $igrejaId): Usuario {
            $conta = $usuario ?? new Usuario();
            $cpf = trim((string) $dados['cpf']);

            $conta->fill([
                'nome' => trim((string) $dados['nome']),
                'cpf' => $cpf,
                'ativo' => array_key_exists('ativo', $dados) ? (bool) $dados['ativo'] : true,
                'eh_padre' => true,
            ]);

            if (!$conta->exists) {
                $conta->email = $this->gerarEmailTecnicoPadre($cpf);
                $conta->password = Str::password(24);
                $conta->perfil_global = 'usuario';
                $conta->nivel_global = 1;
                $conta->primeiro_acesso = false;
            } elseif ($this->normalizarCampoTexto($conta->email) === null) {
                $conta->email = $this->gerarEmailTecnicoPadre($cpf);
            }

            $conta->save();

            if ($igrejaId !== null) {
                $conta->garantirVinculoNaIgreja($igrejaId);
            }

            return $conta->fresh();
        });
    }

    public function alterarStatusConta(
        Usuario $usuario,
        bool $ativo,
        ?Usuario $ator = null,
        array $contexto = []
    ): Usuario {
        if ((bool) $usuario->ativo === $ativo) {
            return $usuario;
        }

        $usuario->forceFill([
            'ativo' => $ativo,
        ])->save();

        $evento = $ativo ? 'conta_reativada' : 'conta_inativada';
        $this->notificacaoSegurancaService->enviarEventoConta(
            alvo: $usuario,
            evento: $evento,
            ator: $ator,
            contexto: $contexto
        );

        return $usuario->fresh();
    }

    public function redefinirSenhaProvisoria(
        Usuario $usuario,
        ?string $senha,
        ?Usuario $ator = null,
        array $contexto = []
    ): Usuario {
        $senhaNormalizada = trim((string) $senha);

        $usuario->forceFill([
            'password' => $senhaNormalizada !== '' ? $senhaNormalizada : $this->senhaPadraoPorCpf((string) $usuario->cpf),
            'primeiro_acesso' => true,
        ])->save();

        $this->notificacaoSegurancaService->enviarEventoConta(
            alvo: $usuario,
            evento: 'reset_senha',
            ator: $ator,
            contexto: $contexto + [
                'senha_inicial' => $senhaNormalizada !== '' ? 'definida_manual' : 'cpf_sem_pontuacao',
            ]
        );

        return $usuario->fresh();
    }

    private function resolverUsuarioAlvo(?string $cpf, ?string $email, ?Usuario $usuarioBase = null): ?Usuario
    {
        $usuarioPorCpf = $this->buscarUsuarioPorCpf($cpf, $usuarioBase?->id);
        $usuarioPorEmail = $this->buscarUsuarioPorEmail($email, $usuarioBase?->id);

        if ($usuarioBase) {
            if ($usuarioPorCpf && $usuarioPorCpf->id !== $usuarioBase->id) {
                throw ValidationException::withMessages([
                    'cpf' => 'Ja existe outro usuario com este CPF.',
                ]);
            }

            if ($usuarioPorEmail && $usuarioPorEmail->id !== $usuarioBase->id) {
                throw ValidationException::withMessages([
                    'email' => 'Ja existe outro usuario com este e-mail.',
                ]);
            }

            return $usuarioBase;
        }

        if ($usuarioPorCpf && $usuarioPorEmail && $usuarioPorCpf->id !== $usuarioPorEmail->id) {
            throw ValidationException::withMessages([
                'cpf' => 'O CPF informado pertence a um usuario diferente do e-mail informado.',
                'email' => 'O e-mail informado pertence a um usuario diferente do CPF informado.',
            ]);
        }

        return $usuarioPorCpf ?: $usuarioPorEmail;
    }

    private function buscarUsuarioPorCpf(?string $cpf, ?int $ignorarUsuarioId = null): ?Usuario
    {
        $cpfNumerico = preg_replace('/\D+/', '', (string) $cpf) ?? '';

        if ($cpfNumerico === '') {
            return null;
        }

        return Usuario::query()
            ->when($ignorarUsuarioId, fn ($query) => $query->whereKeyNot($ignorarUsuarioId))
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', ''), ' ', '') = ?", [$cpfNumerico])
            ->first();
    }

    private function buscarUsuarioPorEmail(?string $email, ?int $ignorarUsuarioId = null): ?Usuario
    {
        $emailNormalizado = mb_strtolower(trim((string) $email));

        if ($emailNormalizado === '') {
            return null;
        }

        return Usuario::query()
            ->when($ignorarUsuarioId, fn ($query) => $query->whereKeyNot($ignorarUsuarioId))
            ->whereRaw('LOWER(email) = ?', [$emailNormalizado])
            ->first();
    }

    private function senhaPadraoPorCpf(string $cpf): string
    {
        return preg_replace('/\D+/', '', $cpf) ?: $cpf;
    }

    private function gerarEmailTecnicoPadre(string $cpf): string
    {
        $cpfNumerico = preg_replace('/\D+/', '', $cpf) ?? '';

        return 'celebrante.' . $cpfNumerico . '@sem-login.local';
    }

    private function ehEmailTecnicoSemLogin(string $email): bool
    {
        $email = mb_strtolower(trim($email));

        return $email !== '' && str_ends_with($email, '@sem-login.local');
    }

    private function normalizarCampoTexto(mixed $valor): ?string
    {
        $texto = trim((string) $valor);

        return $texto !== '' ? $texto : null;
    }

    private function normalizarPapeis(array $papeis): Collection
    {
        return collect($papeis)
            ->map(fn (PapelIgreja|string $papel) => PapelIgreja::fromValue($papel))
            ->unique(fn (PapelIgreja $papel) => $papel->value)
            ->values();
    }

    private function notificarPapeisConcedidos(
        Usuario $usuario,
        Igreja $igreja,
        Collection $papeisConcedidos,
        ?Usuario $ator,
        string $origem
    ): void {
        $papeisConcedidos
            ->each(function (PapelIgreja $papel) use ($usuario, $igreja, $ator, $origem): void {
                $this->notificacaoSegurancaService->enviarEventoConta(
                    alvo: $usuario,
                    evento: 'papel_local_concedido',
                    ator: $ator,
                    contexto: [
                        'origem' => $origem,
                        'igreja_id' => $igreja->id,
                        'igreja_nome' => $igreja->nome,
                        'papel' => $papel->value,
                        'papel_label' => $papel->label(),
                    ]
                );
            });
    }

    private function nivelLegadoPorPapeis(Collection $papeis, ?int $nivelAtual): int
    {
        $nivelAtual = $nivelAtual ?? 1;

        if ($papeis->contains(fn (PapelIgreja $papel) => $papel === PapelIgreja::ADMIN_LOCAL)) {
            return max($nivelAtual, 5);
        }

        return max($nivelAtual, 1);
    }
}

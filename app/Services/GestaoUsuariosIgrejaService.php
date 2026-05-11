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
        private readonly AuditoriaOperacionalService $auditoriaOperacionalService,
        private readonly NotificacaoSegurancaService $notificacaoSegurancaService,
        private readonly NotificacaoAcessoInicialService $notificacaoAcessoInicialService,
        private readonly StatusOperacionalIgrejaService $statusOperacionalIgrejaService
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
        $deveLiberarPrimeiroAcesso = !$usuario
            || ($usuario && $this->ehEmailTecnicoSemLogin((string) $usuario->email));
        $contaJaExistia = $usuario?->exists ?? false;
        $auditoriaAntes = $this->snapshotAuditoriaUsuario($usuario);

        $usuario = DB::transaction(function () use (
            $dados,
            $igreja,
            $papeisNormalizados,
            $ator,
            $origem,
            $usuario,
            $deveLiberarPrimeiroAcesso,
            &$papeisConcedidos
        ): Usuario {
            $conta = $usuario ?? new Usuario();

            $conta->fill([
                'nome' => trim((string) $dados['nome']),
                'cpf' => trim((string) $dados['cpf']),
                'email' => $this->normalizarEmail($dados['email'] ?? null),
                'telefone' => $this->normalizarCampoTexto($dados['telefone'] ?? null),
                'ativo' => array_key_exists('ativo', $dados) ? (bool) $dados['ativo'] : true,
                'eh_padre' => (bool) ($conta->eh_padre ?? false) || (bool) ($dados['eh_padre'] ?? false),
            ]);

            if (!$conta->exists) {
                $conta->perfil_global = 'usuario';
                $conta->nivel_global = 1;
                $conta->password = Str::password(32);
                $conta->primeiro_acesso = true;
            } else {
                if (!$conta->ehAdminMaster()) {
                    $conta->perfil_global = 'usuario';
                    $conta->nivel_global = 1;
                }

                if ($deveLiberarPrimeiroAcesso) {
                    $conta->password = Str::password(32);
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

        $this->auditoriaOperacionalService->registrar(
            evento: $contaJaExistia ? 'usuario_editado' : 'usuario_criado',
            ator: $ator,
            alvo: $usuario,
            igreja: $igreja,
            contexto: [
                'origem' => $origem,
                'resumo' => $contaJaExistia
                    ? 'Conta operacional atualizada pela gestao da igreja.'
                    : 'Conta operacional criada pela gestao da igreja.',
                'papeis_solicitados' => $papeisNormalizados->map(fn (PapelIgreja $papel) => $papel->value)->values()->all(),
                'alteracoes' => $this->diffAuditoriaUsuario($auditoriaAntes, $this->snapshotAuditoriaUsuario($usuario)),
            ]
        );

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
                ]
            );
        }

        $this->statusOperacionalIgrejaService->atualizar($igreja);

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
        $emailInformado = $this->normalizarEmail($dados['email'] ?? null);

        $usuario = $this->resolverUsuarioAlvo(
            cpf: (string) ($dados['cpf'] ?? ''),
            email: $emailInformado,
            usuarioBase: $usuarioBase
        );

        $eraAdminMaster = $usuario?->ehAdminMaster() ?? false;
        $nivelAnterior = $usuario?->nivelGlobal();
        $senhaFoiDefinida = false;
        $contaJaExistia = $usuario?->exists ?? false;
        $auditoriaAntes = $this->snapshotAuditoriaUsuario($usuario);

        $usuario = DB::transaction(function () use (
            $dados,
            $perfilGlobal,
            $ehAdminMaster,
            $ehPadre,
            $emailInformado,
            $usuario,
            &$senhaFoiDefinida
        ): Usuario {
            $conta = $usuario ?? new Usuario();
            $emailAtual = $this->normalizarEmail($conta->email ?? null);
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
                'nivel_global' => $ehAdminMaster ? 6 : 1,
                'eh_padre' => $ehPadre || (bool) ($conta->eh_padre ?? false),
                'ativo' => array_key_exists('ativo', $dados) ? (bool) $dados['ativo'] : true,
            ]);

            if (!$conta->exists) {
                if ($emailFinal !== null && !$this->ehEmailTecnicoSemLogin($emailFinal)) {
                    $conta->password = Str::password(32);
                    $conta->primeiro_acesso = true;
                    $senhaFoiDefinida = true;
                } else {
                    $conta->password = Str::password(24);
                    $conta->primeiro_acesso = false;
                }
            } else {
                $mudouEmailTecnicoParaLogin = $emailInformado !== null && $emailTecnicoAtual;

                if ($mudouEmailTecnicoParaLogin) {
                    $conta->password = Str::password(32);
                    $conta->primeiro_acesso = true;
                    $senhaFoiDefinida = true;
                }
            }

            $conta->save();

            return $conta->fresh();
        });

        $this->auditoriaOperacionalService->registrar(
            evento: $contaJaExistia ? 'usuario_editado' : 'usuario_criado',
            ator: $ator,
            alvo: $usuario,
            igreja: $usuario->igrejaAtiva()?->id ?? $usuario->igreja_id,
            contexto: [
                'origem' => $origem,
                'resumo' => $contaJaExistia
                    ? 'Dados da conta base atualizados.'
                    : 'Conta base criada no sistema.',
                'alteracoes' => $this->diffAuditoriaUsuario($auditoriaAntes, $this->snapshotAuditoriaUsuario($usuario)),
            ]
        );

        if ($usuario->ehAdminMaster()) {
            $nivelNovo = $usuario->nivelGlobal();
            $mudouAcessoGlobal = !$eraAdminMaster || $nivelAnterior !== $nivelNovo;

            if ($mudouAcessoGlobal && !($usuario->primeiro_acesso ?? false)) {
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

        if ($usuario->primeiro_acesso && $senhaFoiDefinida && filter_var((string) $usuario->email, FILTER_VALIDATE_EMAIL)) {
            $this->notificacaoAcessoInicialService->enviarConvite(
                alvo: $usuario,
                ator: $ator,
                contexto: [
                    'origem' => $origem,
                    'origem_id' => $usuario->id,
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
        $this->statusOperacionalIgrejaService->atualizar($igreja);

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

    public function revogarPapelDeUsuarioExistente(
        Usuario $usuario,
        Igreja $igreja,
        PapelIgreja|string $papel,
        ?Usuario $ator = null,
        string $origem = 'gestao_usuario_igreja_revogacao'
    ): Usuario {
        $papelEnum = PapelIgreja::fromValue($papel);

        DB::transaction(function () use ($usuario, $igreja, $papelEnum, $ator): void {
            $usuario->removerPapel($papelEnum, $igreja, $ator);
        });

        $eventoOperacional = match ($papelEnum) {
            PapelIgreja::ADMIN_LOCAL => 'admin_local_revogado',
            PapelIgreja::COORDENADOR => 'coordenador_revogado',
            PapelIgreja::MUSICO => 'musico_revogado',
        };

        $this->auditoriaOperacionalService->registrar(
            evento: $eventoOperacional,
            ator: $ator,
            alvo: $usuario,
            igreja: $igreja,
            contexto: [
                'origem' => $origem,
                'papel' => $papelEnum->value,
                'papel_label' => $papelEnum->label(),
                'resumo' => sprintf('%s deixou de atuar como %s nesta igreja.', $usuario->nome, mb_strtolower($papelEnum->label())),
            ]
        );

        $this->notificacaoSegurancaService->enviarEventoConta(
            alvo: $usuario,
            evento: 'papel_local_revogado',
            ator: $ator,
            contexto: [
                'origem' => $origem,
                'igreja_id' => $igreja->id,
                'igreja_nome' => $igreja->nome,
                'papel' => $papelEnum->value,
                'papel_label' => $papelEnum->label(),
            ]
        );

        $this->statusOperacionalIgrejaService->atualizar($igreja);

        return $usuario->fresh();
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

        $ativoAnterior = (bool) $usuario->ativo;

        $usuario->forceFill([
            'ativo' => $ativo,
        ])->save();

        $evento = $ativo ? 'conta_reativada' : 'conta_inativada';
        $this->auditoriaOperacionalService->registrar(
            evento: $evento,
            ator: $ator,
            alvo: $usuario,
            igreja: $contexto['igreja_id'] ?? $usuario->igrejaAtiva()?->id ?? $usuario->igreja_id,
            contexto: $contexto + [
                'resumo' => $ativo
                    ? 'Conta reativada para voltar a operar no sistema.'
                    : 'Conta inativada e retirada do fluxo operacional ativo.',
                'alteracoes' => [
                    'ativo' => [
                        'antes' => $ativoAnterior,
                        'depois' => $ativo,
                    ],
                ],
            ]
        );
        $this->notificacaoSegurancaService->enviarEventoConta(
            alvo: $usuario,
            evento: $evento,
            ator: $ator,
            contexto: $contexto
        );

        $usuario = $usuario->fresh();
        $this->statusOperacionalIgrejaService->atualizarPorUsuario($usuario);

        return $usuario;
    }

    public function enviarLinkDefinicaoSenha(
        Usuario $usuario,
        ?Usuario $ator = null,
        array $contexto = []
    ): Usuario {
        $primeiroAcessoAnterior = (bool) $usuario->primeiro_acesso;

        $usuario->forceFill([
            'password' => Str::password(32),
            'primeiro_acesso' => true,
        ])->save();

        $this->auditoriaOperacionalService->registrar(
            evento: 'reset_senha',
            ator: $ator,
            alvo: $usuario,
            igreja: $contexto['igreja_id'] ?? $usuario->igrejaAtiva()?->id ?? $usuario->igreja_id,
            contexto: $contexto + [
                'resumo' => 'Link de definicao de senha enviado com validade temporaria.',
                'alteracoes' => [
                    'primeiro_acesso' => [
                        'antes' => $primeiroAcessoAnterior,
                        'depois' => true,
                    ],
                    'credencial' => [
                        'antes' => 'mantida internamente',
                        'depois' => 'invalidada e aguardando definicao pelo usuario',
                    ],
                ],
            ]
        );

        if (filter_var((string) $usuario->email, FILTER_VALIDATE_EMAIL)) {
            $this->notificacaoAcessoInicialService->enviarConvite(
                alvo: $usuario,
                ator: $ator,
                contexto: $contexto + [
                    'origem' => $contexto['origem'] ?? 'reset_senha',
                    'origem_id' => $usuario->id,
                ]
            );
        }

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

    private function normalizarEmail(mixed $valor): ?string
    {
        $email = $this->normalizarCampoTexto($valor);

        return $email !== null ? mb_strtolower($email) : null;
    }

    private function normalizarPapeis(array $papeis): Collection
    {
        return collect($papeis)
            ->map(fn (PapelIgreja|string $papel) => PapelIgreja::fromValue($papel))
            ->unique(fn (PapelIgreja $papel) => $papel->value)
            ->values();
    }

    private function snapshotAuditoriaUsuario(?Usuario $usuario): array
    {
        if (!$usuario) {
            return [];
        }

        return [
            'nome' => $usuario->nome,
            'cpf' => $usuario->cpf,
            'email' => $usuario->email,
            'telefone' => $usuario->telefone,
            'perfil_global' => $usuario->perfil_global,
            'nivel_global' => $usuario->nivel_global,
            'eh_padre' => (bool) $usuario->eh_padre,
            'ativo' => (bool) $usuario->ativo,
            'primeiro_acesso' => (bool) $usuario->primeiro_acesso,
        ];
    }

    private function diffAuditoriaUsuario(array $antes, array $depois): array
    {
        $alteracoes = [];

        foreach ($depois as $campo => $valorDepois) {
            $valorAntes = $antes[$campo] ?? null;

            if ($valorAntes === $valorDepois) {
                continue;
            }

            $alteracoes[$campo] = [
                'antes' => $valorAntes,
                'depois' => $valorDepois,
            ];
        }

        return $alteracoes;
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
                $eventoOperacional = match ($papel) {
                    PapelIgreja::ADMIN_LOCAL => 'admin_local_vinculado',
                    PapelIgreja::COORDENADOR => 'coordenador_vinculado',
                    PapelIgreja::MUSICO => 'musico_vinculado',
                };

                $this->auditoriaOperacionalService->registrar(
                    evento: $eventoOperacional,
                    ator: $ator,
                    alvo: $usuario,
                    igreja: $igreja,
                    contexto: [
                        'origem' => $origem,
                        'papel' => $papel->value,
                        'papel_label' => $papel->label(),
                        'resumo' => sprintf('%s vinculado(a) a igreja como %s.', $usuario->nome, mb_strtolower($papel->label())),
                    ]
                );

                if (!($usuario->primeiro_acesso ?? false)) {
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
                }
            });
    }

}

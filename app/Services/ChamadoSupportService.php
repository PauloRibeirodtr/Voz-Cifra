<?php

namespace App\Services;

use App\Models\Chamado;
use App\Models\ChamadoMensagem;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class ChamadoSupportService
{
    public function __construct(
        private readonly TelegramNotificacaoService $telegramNotificacaoService,
        private readonly NotificacaoSegurancaService $notificacaoSegurancaService,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function statusOptions(): array
    {
        return [
            'aberto' => 'Aberto',
            'em_andamento' => 'Em andamento',
            'aguardando_usuario' => 'Aguardando usuario',
            'resolvido' => 'Resolvido',
            'fechado' => 'Fechado',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function prioridadeOptions(): array
    {
        return [
            'media' => 'Media',
            'alta' => 'Alta',
            'critica' => 'Critica',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function categoriaOptions(): array
    {
        return [
            'seguranca' => 'Seguranca',
            'acesso' => 'Acesso',
            'conta' => 'Conta',
            'contestacao_inativacao' => 'Contestacao de conta inativada',
            'pedido_acesso_musico' => 'Pedido de acesso de musico',
            'acorde' => 'Pedido de acorde',
            'musica' => 'Pedido de musica',
            'outro' => 'Outro',
        ];
    }

    public function statusLabel(?string $status): string
    {
        return $this->statusOptions()[$status ?? ''] ?? 'Nao definido';
    }

    public function prioridadeLabel(?string $prioridade): string
    {
        return $this->prioridadeOptions()[$prioridade ?? ''] ?? 'Nao definida';
    }

    public function categoriaLabel(?string $categoria): string
    {
        return $this->categoriaOptions()[$categoria ?? ''] ?? 'Nao definida';
    }

    public function statusBadgeClass(?string $status): string
    {
        return match ($status) {
            'aberto' => 'bg-red-100 text-red-700',
            'aguardando_usuario' => 'bg-amber-100 text-amber-700',
            'resolvido', 'fechado' => 'bg-emerald-100 text-emerald-700',
            default => 'bg-blue-100 text-blue-700',
        };
    }

    public function prioridadeBadgeClass(?string $prioridade): string
    {
        return match ($prioridade) {
            'critica' => 'bg-red-100 text-red-700',
            'alta' => 'bg-amber-100 text-amber-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    /**
     * @return array{abertos:int,em_andamento:int,resolvidos:int,alta_prioridade:int}
     */
    public function metricas(): array
    {
        return [
            'abertos' => Chamado::where('status', 'aberto')->count(),
            'em_andamento' => Chamado::where('status', 'em_andamento')->count(),
            'resolvidos' => Chamado::where('status', 'resolvido')->count(),
            'alta_prioridade' => Chamado::whereIn('prioridade', ['alta', 'critica'])->count(),
        ];
    }

    public function aplicarFiltros(Builder $query, string $status, string $prioridade, string $categoria, string $busca): Builder
    {
        return $query
            ->when($status !== '', fn (Builder $builder) => $builder->where('status', $status))
            ->when($prioridade !== '', fn (Builder $builder) => $builder->where('prioridade', $prioridade))
            ->when($categoria !== '', fn (Builder $builder) => $builder->where('categoria', $categoria))
            ->when($busca !== '', function (Builder $builder) use ($busca): void {
                $builder->where(function (Builder $subquery) use ($busca): void {
                    $subquery->where('protocolo', 'like', '%' . $busca . '%')
                        ->orWhere('titulo', 'like', '%' . $busca . '%')
                        ->orWhere('solicitante_nome', 'like', '%' . $busca . '%')
                        ->orWhere('solicitante_email', 'like', '%' . $busca . '%')
                        ->orWhere('igreja_nome', 'like', '%' . $busca . '%');
                });
            });
    }

    public function atualizarStatus(Chamado $chamado, string $status, ?string $resolucaoResumo, ?Usuario $responsavel): void
    {
        $chamado->status = $status;
        $chamado->responsavel_usuario_id = $responsavel?->id;
        $chamado->ultima_interacao_em = now();

        if ($status === 'resolvido' && $chamado->resolvido_em === null) {
            $chamado->resolvido_em = now();
        }

        if ($status === 'fechado' && $chamado->fechado_em === null) {
            $chamado->fechado_em = now();
        }

        if ($status !== 'resolvido') {
            $chamado->resolvido_em = $status === 'fechado' ? $chamado->resolvido_em : null;
        }

        if ($status !== 'fechado') {
            $chamado->fechado_em = null;
        }

        if ($resolucaoResumo !== null && trim($resolucaoResumo) !== '') {
            $chamado->resolucao_resumo = trim($resolucaoResumo);
        }

        $chamado->save();

        $chamado->loadMissing('solicitante');
        $this->telegramNotificacaoService->notificarAtualizacaoChamado($chamado, 'status');
    }

    public function registrarMensagem(Chamado $chamado, string $mensagem, bool $interno, ?Usuario $autor): ChamadoMensagem
    {
        $registro = ChamadoMensagem::create([
            'chamado_id' => $chamado->id,
            'autor_usuario_id' => $autor?->id,
            'autor_nome' => $autor?->nome,
            'origem' => 'suporte',
            'canal' => 'painel_admin',
            'interno' => $interno,
            'mensagem' => trim($mensagem),
        ]);

        $chamado->responsavel_usuario_id = $autor?->id;
        $chamado->ultima_interacao_em = now();

        if ($chamado->status === 'aberto') {
            $chamado->status = 'em_andamento';
        }

        $chamado->save();

        if (!$interno) {
            $chamado->loadMissing('solicitante');
            $this->telegramNotificacaoService->notificarAtualizacaoChamado($chamado, 'mensagem', trim($mensagem));
        }

        return $registro;
    }

    public function possuiPedidoAcessoAbertoParaMusico(int $musicoId): bool
    {
        return Chamado::query()
            ->where('categoria', 'pedido_acesso_musico')
            ->where('origem_tipo', 'usuario')
            ->where('origem_id', $musicoId)
            ->whereIn('status', ['aberto', 'em_andamento', 'aguardando_usuario'])
            ->exists();
    }

    public function abrirPedidoAcessoMusico(Usuario $adminLocal, Usuario $musico, string $motivo): Chamado
    {
        return DB::transaction(function () use ($adminLocal, $musico, $motivo): Chamado {
            $chamado = Chamado::create([
                'protocolo' => $this->gerarProtocoloChamado('ACE'),
                'titulo' => 'Pedido de acesso para ' . $musico->nome,
                'descricao' => trim($motivo),
                'status' => 'aberto',
                'prioridade' => 'media',
                'categoria' => 'pedido_acesso_musico',
                'canal_origem' => 'painel_local_admin',
                'origem_tipo' => 'usuario',
                'origem_id' => $musico->id,
                'solicitante_usuario_id' => $adminLocal->id,
                'solicitante_nome' => $adminLocal->nome,
                'solicitante_email' => $adminLocal->email,
                'igreja_id' => $adminLocal->igreja_id,
                'igreja_nome' => $adminLocal->igreja?->nome,
                'ultima_interacao_em' => now(),
            ]);

            ChamadoMensagem::create([
                'chamado_id' => $chamado->id,
                'autor_usuario_id' => $adminLocal->id,
                'autor_nome' => $adminLocal->nome,
                'origem' => 'usuario',
                'canal' => 'painel_local_admin',
                'interno' => false,
                'mensagem' => sprintf(
                    "Pedido de acesso para o musico %s (%s).\n\nMotivo informado:\n%s",
                    $musico->nome,
                    $musico->email ?: 'sem email',
                    trim($motivo)
                ),
            ]);

            return $chamado;
        });
    }

    public function abrirChamadoDoMusico(Usuario $musico, string $categoria, string $descricao): Chamado
    {
        $titulo = match ($categoria) {
            'acorde' => 'Pedido de acorde',
            'contestacao_inativacao' => 'Contestacao de conta inativada',
            'musica' => 'Pedido de musica',
            'acesso' => 'Problema de acesso',
            default => 'Solicitacao de suporte do musico',
        };

        $prioridade = $categoria === 'contestacao_inativacao' ? 'alta' : 'media';

        return DB::transaction(function () use ($musico, $categoria, $descricao, $titulo, $prioridade): Chamado {
            $chamado = Chamado::create([
                'protocolo' => $this->gerarProtocoloChamado('MUS'),
                'titulo' => $titulo,
                'descricao' => trim($descricao),
                'status' => 'aberto',
                'prioridade' => $prioridade,
                'categoria' => $categoria,
                'canal_origem' => 'painel_musico',
                'origem_tipo' => 'usuario',
                'origem_id' => $musico->id,
                'solicitante_usuario_id' => $musico->id,
                'solicitante_nome' => $musico->nome,
                'solicitante_email' => $musico->email,
                'igreja_id' => $musico->igreja_id,
                'igreja_nome' => $musico->igreja?->nome,
                'ultima_interacao_em' => now(),
            ]);

            ChamadoMensagem::create([
                'chamado_id' => $chamado->id,
                'autor_usuario_id' => $musico->id,
                'autor_nome' => $musico->nome,
                'origem' => 'usuario',
                'canal' => 'painel_musico',
                'interno' => false,
                'mensagem' => trim($descricao),
            ]);

            return $chamado;
        });
    }

    public function assumirChamado(Chamado $chamado, ?Usuario $responsavel): void
    {
        $chamado->responsavel_usuario_id = $responsavel?->id;
        $chamado->ultima_interacao_em = now();

        if ($chamado->status === 'aberto') {
            $chamado->status = 'em_andamento';
        }

        $chamado->save();

        $chamado->loadMissing('solicitante');
        $this->telegramNotificacaoService->notificarAtualizacaoChamado($chamado, 'status');
    }

    public function podeAprovarPedidoAcesso(Chamado $chamado): bool
    {
        return $chamado->categoria === 'pedido_acesso_musico'
            && $chamado->origem_tipo === 'usuario'
            && $chamado->origem_id !== null
            && in_array($chamado->status, ['aberto', 'em_andamento', 'aguardando_usuario'], true);
    }

    public function aprovarPedidoAcessoMusico(Chamado $chamado, ?Usuario $ator): ?Usuario
    {
        if (!$this->podeAprovarPedidoAcesso($chamado)) {
            return null;
        }

        /** @var Usuario|null $musico */
        $musico = Usuario::query()
            ->whereKey($chamado->origem_id)
            ->whereHas('papeisAtivosPorIgreja', fn ($query) => $query->where('papel', \App\Enums\PapelIgreja::MUSICO->value))
            ->first();

        if (!$musico) {
            return null;
        }

        DB::transaction(function () use ($chamado, $musico, $ator): void {
            $musicoFoiInativo = !$musico->ativo;

            $musico->forceFill([
                'ativo' => true,
                'primeiro_acesso' => true,
                'password' => preg_replace('/\D+/', '', (string) $musico->cpf) ?: (string) $musico->cpf,
            ])->save();

            $chamado->forceFill([
                'status' => 'resolvido',
                'responsavel_usuario_id' => $ator?->id,
                'ultima_interacao_em' => now(),
                'resolvido_em' => now(),
                'resolucao_resumo' => 'Acesso do musico aprovado. Conta liberada para novo acesso com senha baseada no CPF.',
            ])->save();

            ChamadoMensagem::create([
                'chamado_id' => $chamado->id,
                'autor_usuario_id' => $ator?->id,
                'autor_nome' => $ator?->nome,
                'origem' => 'suporte',
                'canal' => 'painel_admin',
                'interno' => false,
                'mensagem' => 'Pedido aprovado. O musico foi liberado para acessar novamente e devera redefinir a senha no proximo acesso.',
            ]);

            if ($musicoFoiInativo) {
                $this->notificacaoSegurancaService->enviarEventoConta(
                    alvo: $musico,
                    evento: 'conta_reativada',
                    ator: $ator,
                    contexto: [
                        'origem' => 'admin_chamados_aprovar_pedido_acesso',
                        'igreja_id' => $chamado->igreja_id,
                        'igreja_nome' => $chamado->igreja_nome,
                    ]
                );
            }

            $this->notificacaoSegurancaService->enviarEventoConta(
                alvo: $musico,
                evento: 'reset_senha',
                ator: $ator,
                contexto: [
                    'origem' => 'admin_chamados_aprovar_pedido_acesso',
                    'igreja_id' => $chamado->igreja_id,
                    'igreja_nome' => $chamado->igreja_nome,
                    'senha_inicial' => 'cpf_sem_pontuacao',
                ]
            );
        });

        $chamado->loadMissing('solicitante');
        $this->telegramNotificacaoService->notificarAtualizacaoChamado($chamado, 'status');

        return $musico->fresh();
    }

    public function pedirMaisDados(Chamado $chamado, string $mensagem, ?Usuario $ator): void
    {
        $this->registrarMensagem($chamado, $mensagem, false, $ator);
        $this->atualizarStatus($chamado, 'aguardando_usuario', null, $ator);
    }

    private function gerarProtocoloChamado(string $siglaCategoria): string
    {
        return sprintf(
            'SUP-%s-%s-%s',
            now('America/Cuiaba')->format('YmdHis'),
            Str::upper(Str::substr($siglaCategoria, 0, 3)),
            Str::upper(Str::random(4))
        );
    }
}

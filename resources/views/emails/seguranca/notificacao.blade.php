<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Notificacao de Seguranca</title>
</head>
<body style="margin:0; padding:24px; background:#f3f4f6; font-family:Arial, sans-serif; color:#1f2937; line-height:1.6;">
    @php
        $tituloEvento = match ($evento) {
            'reset_senha' => 'Senha redefinida',
            'conta_inativada' => 'Conta inativada',
            'conta_reativada' => 'Conta reativada',
            'troca_nivel_global' => 'Nivel global alterado',
            'papel_local_concedido' => 'Papel local concedido',
            'papel_local_revogado' => 'Papel local revogado',
            default => 'Atualizacao de seguranca',
        };

        $mensagemPrincipal = match ($evento) {
            'reset_senha' => 'Registramos uma redefinicao de senha na sua conta. No proximo acesso, a senha devera ser trocada.',
            'conta_inativada' => 'Sua conta foi temporariamente inativada no sistema.',
            'conta_reativada' => 'Sua conta foi reativada no sistema.',
            'troca_nivel_global' => 'O nivel de acesso global da sua conta foi alterado.',
            'papel_local_concedido' => 'Um novo papel local foi concedido para a sua conta.',
            'papel_local_revogado' => 'Um papel local foi removido da sua conta.',
            default => 'Registramos uma atualizacao de seguranca relacionada a sua conta.',
        };

        $detalhes = [];

        if (!empty($contexto['responsavel_nome'])) {
            $detalhes['Responsavel pela acao'] = trim($contexto['responsavel_nome'] . (!empty($contexto['responsavel_funcao']) ? ' - ' . $contexto['responsavel_funcao'] : ''));
        }

        if (!empty($contexto['igreja_nome'])) {
            $detalhes['Igreja'] = $contexto['igreja_nome'];
        }

        if (!empty($contexto['nivel_anterior']) || array_key_exists('nivel_anterior', $contexto)) {
            $detalhes['Nivel anterior'] = $contexto['nivel_anterior'] === null ? 'nao informado' : (string) $contexto['nivel_anterior'];
        }

        if (!empty($contexto['nivel_novo'])) {
            $detalhes['Novo nivel'] = (string) $contexto['nivel_novo'];
        }

        if (!empty($contexto['protocolo'])) {
            $detalhes['Protocolo'] = $contexto['protocolo'];
        }

        $senhaExplicacao = null;
        if (($contexto['senha_inicial'] ?? null) === 'cpf_sem_pontuacao') {
            $senhaExplicacao = 'Se a senha provisoria foi definida pelo CPF, use somente os numeros do seu CPF, sem pontos nem tracos. Exemplo de formato: 12345678900.';
        } elseif (($contexto['senha_inicial'] ?? null) === 'definida_manual') {
            $senhaExplicacao = 'Uma senha provisoria foi definida manualmente pelo administrador responsavel.';
        }

        $canalSuporte = trim((string) ($contexto['canal_suporte'] ?? 'suporte oficial do sistema'));
        $canalSuporteUrl = trim((string) ($contexto['canal_suporte_url'] ?? ''));
    @endphp

    <div style="max-width:680px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:18px; overflow:hidden; box-shadow:0 12px 24px rgba(15, 23, 42, 0.06);">
        <div style="background:#0f172a; color:#ffffff; padding:24px 28px;">
            <div style="font-size:12px; letter-spacing:0.12em; text-transform:uppercase; color:#93c5fd; font-weight:700;">Voz &amp; Cifra</div>
            <h1 style="margin:8px 0 0; font-size:24px; line-height:1.3;">{{ $tituloEvento }}</h1>
        </div>

        <div style="padding:28px;">
            <p style="margin-top:0;">Ola, <strong>{{ $alvo->nome }}</strong>.</p>

            <p>{{ $mensagemPrincipal }}</p>

            @if ($evento === 'conta_inativada')
                <p>Se esta acao nao foi esperada por voce, entre em contato com o suporte para validar o motivo e pedir a revisao do acesso.</p>
            @endif

            @if ($senhaExplicacao)
                <div style="margin:20px 0; padding:16px 18px; border-radius:14px; background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a;">
                    <strong style="display:block; margin-bottom:6px;">Orientacao de acesso</strong>
                    <span>{{ $senhaExplicacao }}</span>
                </div>
            @endif

            @if ($detalhes)
                <div style="margin:22px 0; padding:18px; border:1px solid #e5e7eb; border-radius:14px; background:#f9fafb;">
                    <h2 style="margin:0 0 12px; font-size:16px;">Detalhes da notificacao</h2>
                    <table role="presentation" style="width:100%; border-collapse:collapse;">
                        <tbody>
                        @foreach ($detalhes as $rotulo => $valor)
                            <tr>
                                <td style="padding:8px 0; font-weight:700; width:180px; vertical-align:top; color:#374151;">{{ $rotulo }}</td>
                                <td style="padding:8px 0; color:#111827;">{{ $valor }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="padding:8px 0; font-weight:700; width:180px; vertical-align:top; color:#374151;">Data e hora</td>
                            <td style="padding:8px 0; color:#111827;">{{ now('America/Cuiaba')->format('d/m/Y H:i:s') }} (America/Cuiaba)</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            @endif

            <div style="margin-top:24px; padding:16px 18px; border-radius:14px; background:#fef2f2; border:1px solid #fecaca; color:#7f1d1d;">
                <strong style="display:block; margin-bottom:6px;">Nao reconhece esta acao?</strong>
                <span>Procure o {{ $canalSuporte }} e informe o protocolo acima para agilizar o atendimento.</span>
            </div>

            @if ($canalSuporteUrl !== '')
                <div style="margin-top:18px;">
                    <a
                        href="{{ $canalSuporteUrl }}"
                        style="display:inline-block; border-radius:12px; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:700; padding:12px 18px;"
                    >
                        Falar com o suporte no Telegram
                    </a>
                    <p style="margin:12px 0 0; word-break:break-all; color:#475569; font-size:12px;">
                        Link direto: <a href="{{ $canalSuporteUrl }}" style="color:#2563eb;">{{ $canalSuporteUrl }}</a>
                    </p>
                </div>
            @endif

            <p style="margin:24px 0 0; color:#6b7280; font-size:13px;">
                Esta mensagem foi enviada automaticamente pelo sistema para proteger a sua conta.
            </p>
        </div>
    </div>
</body>
</html>

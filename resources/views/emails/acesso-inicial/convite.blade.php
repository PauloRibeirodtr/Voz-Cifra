<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Convite de acesso</title>
</head>
<body style="margin:0; padding:24px; background:#f5f5f4; font-family:Arial, sans-serif; color:#1f2937; line-height:1.6;">
    @php
        $appName = config('app.name', 'Voz & Cifra');
        $appUrl = rtrim((string) config('app.url', ''), '/');
        $definirSenhaUrl = (string) ($contexto['definir_senha_url'] ?? ($appUrl !== '' ? $appUrl . route('login', [], false) : route('login')));
        $expiraEmMinutos = (int) ($contexto['expira_em_minutos'] ?? 60);
        $papeis = [];

        if (!empty($contexto['papel_label'])) {
            $papeis[] = $contexto['papel_label'];
        }

        if (!empty($contexto['papeis_labels']) && is_array($contexto['papeis_labels'])) {
            foreach ($contexto['papeis_labels'] as $papelLabel) {
                if (is_string($papelLabel) && trim($papelLabel) !== '') {
                    $papeis[] = trim($papelLabel);
                }
            }
        }

        $papeis = collect($papeis)->unique()->values();
    @endphp

    <div style="max-width:680px; margin:0 auto; background:#ffffff; border:1px solid #e7e5e4; border-radius:18px; overflow:hidden; box-shadow:0 12px 24px rgba(28, 25, 23, 0.08);">
        <div style="background:#292524; color:#ffffff; padding:24px 28px;">
            <div style="font-size:12px; letter-spacing:0.12em; text-transform:uppercase; color:#fcd34d; font-weight:700;">{{ $appName }}</div>
            <h1 style="margin:8px 0 0; font-size:24px; line-height:1.3;">Seu acesso inicial foi liberado</h1>
        </div>

        <div style="padding:28px;">
            <p style="margin-top:0;">Ola, <strong>{{ $alvo->nome }}</strong>.</p>
            <p>Uma conta foi preparada para voce no sistema {{ $appName }}.</p>

            <div style="margin:22px 0; padding:18px; border:1px solid #e7e5e4; border-radius:14px; background:#fafaf9;">
                <h2 style="margin:0 0 12px; font-size:16px;">Como liberar seu acesso</h2>
                <table role="presentation" style="width:100%; border-collapse:collapse;">
                    <tbody>
                        <tr>
                            <td style="padding:8px 0; font-weight:700; width:170px; vertical-align:top; color:#44403c;">E-mail de acesso</td>
                            <td style="padding:8px 0; color:#1c1917;">{{ $alvo->email }}</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; font-weight:700; width:170px; vertical-align:top; color:#44403c;">Validade do link</td>
                            <td style="padding:8px 0; color:#1c1917;">{{ $expiraEmMinutos }} minutos</td>
                        </tr>
                        @if (!empty($contexto['igreja_nome']))
                            <tr>
                                <td style="padding:8px 0; font-weight:700; width:170px; vertical-align:top; color:#44403c;">Igreja</td>
                                <td style="padding:8px 0; color:#1c1917;">{{ $contexto['igreja_nome'] }}</td>
                            </tr>
                        @endif
                        @if ($papeis->isNotEmpty())
                            <tr>
                                <td style="padding:8px 0; font-weight:700; width:170px; vertical-align:top; color:#44403c;">Papeis liberados</td>
                                <td style="padding:8px 0; color:#1c1917;">{{ $papeis->implode(' / ') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div style="margin-top:20px;">
                <a
                    href="{{ $definirSenhaUrl }}"
                    style="display:inline-block; border-radius:12px; background:#6f4726; color:#ffffff; text-decoration:none; font-weight:700; padding:12px 18px;"
                >
                    Definir minha senha
                </a>
                <p style="margin:12px 0 0; word-break:break-all; color:#57534e; font-size:12px;">
                    Link direto: <a href="{{ $definirSenhaUrl }}" style="color:#2563eb;">{{ $definirSenhaUrl }}</a>
                </p>
            </div>

            <div style="margin-top:24px; padding:16px 18px; border-radius:14px; background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a;">
                <strong style="display:block; margin-bottom:6px;">Acesso seguro</strong>
                <span>O link so pode ser usado uma vez. Depois de definir a senha, voce sera direcionado ao painel correto.</span>
            </div>

            <p style="margin:24px 0 0; color:#57534e; font-size:13px;">
                Se voce nao reconhece este convite, desconsidere esta mensagem e fale com a Equipe Voz &amp; Cifra.
            </p>
        </div>
    </div>
</body>
</html>

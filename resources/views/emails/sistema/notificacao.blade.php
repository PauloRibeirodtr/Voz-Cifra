<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Atualizacao do Sistema</title>
</head>
<body style="margin:0; padding:24px; background:#f5f5f4; font-family:Arial, sans-serif; color:#1f2937; line-height:1.6;">
    @php
        $tituloEvento = match ($evento) {
            'musica_cadastrada' => 'Nova musica cadastrada',
            'versao_musical_criada' => 'Nova versao musical cadastrada',
            'acorde_cadastrado' => 'Novo acorde cadastrado',
            'musica_inativada' => 'Musica inativada',
            'acorde_inativado' => 'Acorde inativado',
            'acordes_marco_alcancado' => 'Marco de acordes atingido',
            'aviso_admin' => $contexto['titulo'] ?? 'Aviso do Voz & Cifra',
            default => 'Atualizacao do sistema',
        };

        $mensagemPrincipal = match ($evento) {
            'musica_cadastrada' => 'Uma nova musica foi adicionada ao acervo principal do Voz & Cifra.',
            'versao_musical_criada' => 'Uma nova versao musical com cifras foi adicionada ao acervo principal do Voz & Cifra.',
            'acorde_cadastrado' => 'Um novo acorde foi adicionado a biblioteca principal do sistema.',
            'musica_inativada' => 'Uma musica foi inativada no acervo principal.',
            'acorde_inativado' => 'Um acorde foi inativado na biblioteca principal.',
            'acordes_marco_alcancado' => 'A biblioteca de acordes atingiu um novo marco numerico.',
            'aviso_admin' => $contexto['mensagem'] ?? 'Voce recebeu um aviso do admin master.',
            default => 'O sistema registrou uma nova atualizacao relevante.',
        };

        $dataEnvio = now('America/Sao_Paulo');
        $detalhes = [];

        if ($evento !== 'aviso_admin' && !empty($contexto['nome'])) {
            $detalhes[match ($evento) {
                'musica_cadastrada', 'musica_inativada', 'versao_musical_criada' => 'Musica',
                'acorde_cadastrado', 'acorde_inativado' => 'Acorde',
                default => 'Item',
            }] = $contexto['nome'];
        }
    @endphp

    <div style="max-width:680px; margin:0 auto; background:#ffffff; border:1px solid #e7e5e4; border-radius:18px; overflow:hidden; box-shadow:0 12px 24px rgba(28, 25, 23, 0.08);">
        <div style="background:#292524; color:#ffffff; padding:24px 28px;">
            <div style="font-size:12px; letter-spacing:0.12em; text-transform:uppercase; color:#fcd34d; font-weight:700;">Voz &amp; Cifra</div>
            <h1 style="margin:8px 0 0; font-size:24px; line-height:1.3;">{{ $tituloEvento }}</h1>
        </div>

        <div style="padding:28px;">
            <p style="margin-top:0;">Ola.</p>
            <p>{{ $mensagemPrincipal }}</p>

            @if ($evento !== 'aviso_admin' && $detalhes)
                <div style="margin:22px 0; padding:18px; border:1px solid #e7e5e4; border-radius:14px; background:#fafaf9;">
                    <h2 style="margin:0 0 12px; font-size:16px;">Detalhes</h2>
                    <table role="presentation" style="width:100%; border-collapse:collapse;">
                        <tbody>
                        @foreach ($detalhes as $rotulo => $valor)
                            <tr>
                                <td style="padding:8px 0; font-weight:700; width:180px; vertical-align:top; color:#44403c;">{{ $rotulo }}</td>
                                <td style="padding:8px 0; color:#1c1917;">{{ $valor }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="padding:8px 0; font-weight:700; width:180px; vertical-align:top; color:#44403c;">Data e hora</td>
                            <td style="padding:8px 0; color:#1c1917;">{{ $dataEnvio->format('d/m/Y') }} &agrave;s {{ $dataEnvio->format('H:i') }}</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; font-weight:700; width:180px; vertical-align:top; color:#44403c;">Acao</td>
                            <td style="padding:8px 0; color:#1c1917;">Realizada pela Equipe Voz &amp; Cifra</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            @endif

            <p style="margin:24px 0 0; color:#57534e; font-size:13px;">
                Equipe Voz &amp; Cifra
            </p>
        </div>
    </div>
</body>
</html>

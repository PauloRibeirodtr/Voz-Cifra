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
            'acorde_cadastrado' => 'Novo acorde cadastrado',
            'musica_inativada' => 'Musica inativada',
            'acorde_inativado' => 'Acorde inativado',
            'acordes_marco_alcancado' => 'Marco de acordes atingido',
            default => 'Atualizacao do sistema',
        };

        $mensagemPrincipal = match ($evento) {
            'musica_cadastrada' => 'Uma nova musica foi adicionada ao acervo principal do Voz & Cifra.',
            'acorde_cadastrado' => 'Um novo acorde foi adicionado a biblioteca principal do sistema.',
            'musica_inativada' => 'Uma musica foi inativada no acervo principal.',
            'acorde_inativado' => 'Um acorde foi inativado na biblioteca principal.',
            'acordes_marco_alcancado' => 'A biblioteca de acordes atingiu um novo marco numerico.',
            default => 'O sistema registrou uma nova atualizacao relevante.',
        };

        $detalhes = [];

        if (!empty($contexto['titulo'])) {
            $detalhes['Titulo'] = $contexto['titulo'];
        }

        if (!empty($contexto['nome'])) {
            $detalhes['Nome'] = $contexto['nome'];
        }

        if (!empty($contexto['quantidade'])) {
            $detalhes['Quantidade atual'] = $contexto['quantidade'];
        }

        if (!empty($contexto['responsavel_nome'])) {
            $detalhes['Responsavel'] = trim($contexto['responsavel_nome'] . (!empty($contexto['responsavel_funcao']) ? ' - ' . $contexto['responsavel_funcao'] : ''));
        }

        if (!empty($contexto['protocolo'])) {
            $detalhes['Protocolo'] = $contexto['protocolo'];
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

            @if ($detalhes)
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
                            <td style="padding:8px 0; color:#1c1917;">{{ now('America/Sao_Paulo')->format('d/m/Y H:i:s') }} (America/Sao_Paulo)</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            @endif

            <p style="margin:24px 0 0; color:#57534e; font-size:13px;">
                Esta mensagem foi enviada automaticamente pelo sistema.
            </p>
        </div>
    </div>
</body>
</html>

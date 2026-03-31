<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? (($folha['titulo'] ?? 'Folha') . ' | Voz & Cifra') }}</title>
</head>
<body style="margin:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div class="print-shell" style="max-width:1100px;margin:0 auto;padding:24px;">
        <div class="no-print" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <div>
                <p style="margin:0;color:#64748b;font-size:12px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;">Folha pronta para ensaio</p>
                <h1 style="margin:6px 0 0;font-size:24px;font-weight:900;">{{ $folha['titulo'] }}</h1>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                @if (!empty($pdfUrl))
                    <a href="{{ $pdfUrl }}" style="display:inline-flex;align-items:center;justify-content:center;border-radius:14px;border:1px solid #fed7aa;background:#fff7ed;padding:12px 18px;color:#9a3412;font-size:14px;font-weight:700;text-decoration:none;">Baixar PDF</a>
                @endif
                <button type="button" onclick="window.print()" style="display:inline-flex;align-items:center;justify-content:center;border-radius:14px;border:1px solid #bbf7d0;background:#ecfdf5;padding:12px 18px;color:#166534;font-size:14px;font-weight:700;cursor:pointer;">Imprimir agora</button>
                @if (!empty($backUrl))
                    <a href="{{ $backUrl }}" style="display:inline-flex;align-items:center;justify-content:center;border-radius:14px;border:1px solid #cbd5e1;background:#fff;padding:12px 18px;color:#334155;font-size:14px;font-weight:700;text-decoration:none;">Voltar</a>
                @endif
            </div>
        </div>

        @include('partials.versao-print-sheet', ['folha' => $folha, 'etiquetaFolha' => $etiquetaFolha ?? 'Folha de estudo'])
    </div>
</body>
</html>

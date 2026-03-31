<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle ?? (($folha['titulo'] ?? 'Folha') . ' | Voz & Cifra') }}</title>
</head>
<body style="font-family: DejaVu Sans, sans-serif; background: #ffffff; color: #0f172a; font-size: 12px;">
    @include('partials.versao-print-sheet', ['folha' => $folha, 'etiquetaFolha' => $etiquetaFolha ?? 'Folha de estudo'])
</body>
</html>

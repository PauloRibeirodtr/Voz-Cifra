@php
    $erro403 = $exception ?? null;
    $mensagem403 = trim((string) ($erro403?->getMessage() ?: 'Voce nao tem permissao para acessar esta area.'));
@endphp

@include('errors._page', [
    'statusCode' => '403',
    'eyebrow' => 'Acesso restrito',
    'title' => 'Este trecho nao esta liberado',
    'message' => $mensagem403,
    'hint' => 'Se voce acredita que deveria acessar esta tela, confirme se esta na igreja ou perfil correto antes de tentar novamente.',
])

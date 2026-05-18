@include('errors._page', [
    'statusCode' => '404',
    'eyebrow' => 'Pagina nao encontrada',
    'title' => 'Essa pagina saiu da pauta',
    'message' => 'Nao encontramos o endereco que voce tentou abrir. Ele pode ter mudado, sido removido ou digitado com algum detalhe diferente.',
    'hint' => 'Use os botoes abaixo para voltar com seguranca ao fluxo do Voz & Cifra.',
    'illustration' => asset('images/errors/erro404.png'),
    'illustrationAlt' => 'Ilustracao da pagina 404 do Voz e Cifra com instrumentos musicais e uma igreja ao fundo.',
])

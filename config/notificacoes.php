<?php

return [
    'seguranca_ativa' => (bool) env('NOTIFICACOES_SEGURANCA_ATIVAS', false),

    'sistema_ativa' => (bool) env('NOTIFICACOES_SISTEMA_ATIVAS', false),

    'acesso_inicial_ativa' => (bool) env('NOTIFICACOES_ACESSO_INICIAL_ATIVAS', true),

    'eventos_habilitados' => array_filter(array_map(
        static fn (string $item): string => trim($item),
        explode(',', (string) env(
            'NOTIFICACOES_EVENTOS_HABILITADOS',
            'reset_senha,conta_inativada,conta_reativada,troca_nivel_global,papel_local_concedido,papel_local_revogado'
        ))
    )),

    'eventos_sistema_habilitados' => array_filter(array_map(
        static fn (string $item): string => trim($item),
        explode(',', (string) env(
            'NOTIFICACOES_SISTEMA_EVENTOS_HABILITADOS',
            'musica_cadastrada,versao_musical_criada,acorde_cadastrado,musica_inativada,acorde_inativado,acordes_marco_alcancado,aviso_admin'
        ))
    )),

    'cc_admin' => env('NOTIFICACOES_EMAIL_CC_ADMIN'),

    'cc_acesso_inicial' => env('NOTIFICACOES_ACESSO_INICIAL_CC'),

    'protocolo_prefixo' => env('NOTIFICACOES_PROTOCOLO_PREFIXO', 'SEG'),

    'canal_suporte' => env('NOTIFICACOES_CANAL_SUPORTE', 'suporte oficial do sistema'),

    'canal_suporte_url' => env('NOTIFICACOES_CANAL_SUPORTE_URL'),
];

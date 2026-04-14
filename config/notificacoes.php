<?php

return [
    'seguranca_ativa' => (bool) env('NOTIFICACOES_SEGURANCA_ATIVAS', false),

    'eventos_habilitados' => array_filter(array_map(
        static fn (string $item): string => trim($item),
        explode(',', (string) env(
            'NOTIFICACOES_EVENTOS_HABILITADOS',
            'reset_senha,conta_inativada,conta_reativada,troca_nivel_global,papel_local_concedido,papel_local_revogado'
        ))
    )),

    'cc_admin' => env('NOTIFICACOES_EMAIL_CC_ADMIN'),

    'protocolo_prefixo' => env('NOTIFICACOES_PROTOCOLO_PREFIXO', 'SEG'),

    'canal_suporte' => env('NOTIFICACOES_CANAL_SUPORTE', 'suporte oficial do sistema'),

    'telegram_bot_username' => env('NOTIFICACOES_TELEGRAM_BOT_USERNAME', ''),

    'telegram_bot_base_url' => env('NOTIFICACOES_TELEGRAM_BOT_BASE_URL', ''),

    'telegram_bot_token' => env('NOTIFICACOES_TELEGRAM_BOT_TOKEN', ''),
];

<?php

namespace App\Services;

class SuporteTelegramService
{
    public function gerarUrl(?string $protocolo = null): ?string
    {
        $baseUrl = trim((string) config('notificacoes.telegram_bot_base_url', ''));
        $username = trim((string) config('notificacoes.telegram_bot_username', ''));
        $protocolo = is_string($protocolo) ? trim($protocolo) : '';

        if ($baseUrl === '' && $username === '') {
            return null;
        }

        $url = $baseUrl !== '' ? $baseUrl : 'https://t.me/' . ltrim($username, '@');

        if ($protocolo === '') {
            return $url;
        }

        $separador = str_contains($url, '?') ? '&' : '?';

        return $url . $separador . 'start=' . urlencode($protocolo);
    }
}

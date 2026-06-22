<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PreventDuplicateSubmission
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = trim((string) $request->input('_submission_token', ''));

        if ($token === '' || preg_match('/^[A-Za-z0-9-]{20,80}$/', $token) !== 1) {
            return $next($request);
        }

        $actor = (string) ($request->user()?->getAuthIdentifier() ?? $request->session()->getId());
        $fingerprint = hash('sha256', $actor . '|' . $request->route()?->getName() . '|' . $token);
        $lockKey = 'submission-lock:' . $fingerprint;
        $resultKey = 'submission-result:' . $fingerprint;

        try {
            return Cache::lock($lockKey, 30)->block(8, function () use ($request, $next, $resultKey): Response {
                $processed = Cache::get($resultKey);

                if (is_array($processed) && filled($processed['location'] ?? null)) {
                    return redirect()
                        ->to((string) $processed['location'])
                        ->with('info', 'Este envio já havia sido processado. A segunda tentativa foi ignorada.');
                }

                $response = $next($request);
                $hasErrors = $request->session()->has('errors') || $request->session()->has('duplicidade_musica');
                $location = $response->headers->get('Location');

                if (!$hasErrors && $response->isRedirection() && filled($location)) {
                    Cache::put($resultKey, ['location' => $location], now()->addMinutes(10));
                }

                return $response;
            });
        } catch (LockTimeoutException) {
            return back()
                ->withInput()
                ->with('info', 'O primeiro envio ainda está sendo processado. Aguarde um instante.');
        }
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Rules\StrongPassword;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DefinirSenhaController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $email = $this->normalizarEmail((string) $request->query('email'));
        $token = (string) $request->query('token');

        if (!$this->tokenValido($email, $token)) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'O link para definir senha expirou ou ja foi utilizado. Solicite um novo envio ao administrador.']);
        }

        return view('auth.definir-senha', [
            'email' => $email,
            'token' => $token,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', new StrongPassword()],
        ], [
            'password.confirmed' => 'A confirmacao da senha nao confere.',
        ]);

        $email = $this->normalizarEmail((string) $dados['email']);
        $token = (string) $dados['token'];

        if (!$this->tokenValido($email, $token)) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'O link para definir senha expirou ou ja foi utilizado. Solicite um novo envio ao administrador.']);
        }

        /** @var Usuario|null $usuario */
        $usuario = Usuario::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (!$usuario || !($usuario->ativo ?? false)) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Nao foi possivel liberar este acesso. Confirme a situacao da conta com o administrador.']);
        }

        $usuario->forceFill([
            'password' => (string) $dados['password'],
            'primeiro_acesso' => false,
        ])->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        Auth::login($usuario);
        $request->session()->regenerate();

        $rotaDestino = $usuario->rotaDestinoAposLogin();

        if ($rotaDestino !== null) {
            return redirect()
                ->route($rotaDestino)
                ->with('success', 'Senha definida com sucesso. Seu acesso foi liberado.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors(['email' => 'Senha definida, mas a conta ainda nao possui vinculo operacional ativo.']);
    }

    private function tokenValido(string $email, string $token): bool
    {
        if ($email === '' || $token === '') {
            return false;
        }

        $registro = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', hash('sha256', $token))
            ->first();

        if (!$registro || empty($registro->created_at)) {
            return false;
        }

        $criadoEm = CarbonImmutable::parse($registro->created_at);
        $minutosExpiracao = (int) config('auth.passwords.users.expire', 60);

        return $criadoEm->addMinutes($minutosExpiracao)->isFuture();
    }

    private function normalizarEmail(string $email): string
    {
        return Str::lower(trim($email));
    }
}

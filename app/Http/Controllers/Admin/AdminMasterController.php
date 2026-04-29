<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PapelIgreja;
use App\Http\Controllers\Controller;
use App\Models\Acorde;
use App\Models\Igreja;
use App\Models\Missa;
use App\Models\Musica;
use App\Models\Usuario;
use App\Models\UsuarioIgrejaPapel;
use App\Rules\StrongPassword;
use App\Services\GestaoUsuariosIgrejaService;
use App\Services\IgrejaAtivaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminMasterController extends Controller
{
    public function __construct(
        private readonly GestaoUsuariosIgrejaService $gestaoUsuariosIgrejaService
    ) {
    }

    public function dashboard(): View
    {
        $metricas = [
            'total_usuarios' => Usuario::count(),
            'total_igrejas' => Igreja::count(),
            'total_musicas' => Musica::count(),
            'total_missas' => Missa::count(),
            'admins_locais' => UsuarioIgrejaPapel::query()
                ->where('papel', PapelIgreja::ADMIN_LOCAL->value)
                ->where('ativo', true)
                ->count(),
            'musicos' => UsuarioIgrejaPapel::query()
                ->where('papel', PapelIgreja::MUSICO->value)
                ->where('ativo', true)
                ->count(),
        ];

        return view('admin.dashboard', [
            'metrics' => $metricas,
        ]);
    }

    public function settings(): View
    {
        return view('admin.settings.index', [
            'metricasSistema' => [
                'total_igrejas' => Igreja::count(),
                'total_musicas' => Musica::count(),
                'total_acordes' => Acorde::count(),
                'total_usuarios' => Usuario::count(),
            ],
        ]);
    }

    public function profile(): View
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();
        $usuario->load(['vinculosIgreja.igreja', 'vinculosIgreja.papeisAtivos']);
        $papeisPorIgreja = $usuario->vinculosIgreja
            ->where('ativo', true)
            ->mapWithKeys(fn ($vinculo) => [
                (string) $vinculo->igreja_id => $vinculo->listarPapeisAtivos()
                    ->map(fn (PapelIgreja $papel) => $papel->value)
                    ->values()
                    ->all(),
            ]);

        return view('admin.settings.profile', [
            'user' => $usuario,
            'igrejas' => Igreja::query()->orderBy('nome')->get(),
            'igrejasDisponiveisParaAtivacao' => $usuario->igrejasDisponiveisParaAtivacao(),
            'papeisPorIgreja' => $papeisPorIgreja,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();
        $primeiroAcesso = (bool) ($usuario->primeiro_acesso ?? false);

        $dados = $request->validate([
            'email' => ['required', 'email', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
            'telefone' => ['nullable', 'string', 'max:20'],
            'foto_perfil' => ['nullable', 'image', 'max:2048'],
            'password' => [$primeiroAcesso ? 'required' : 'nullable', 'confirmed', new StrongPassword()],
            'theme_preference' => ['required', Rule::in(['system', 'light', 'dark'])],
        ], [
            'password.required' => 'No primeiro acesso, defina uma nova senha forte para liberar o painel administrativo.',
            'password.confirmed' => 'A confirmacao da senha nao confere.',
        ]);

        $usuario->email = $dados['email'];
        $usuario->telefone = $dados['telefone'] ?? null;
        $usuario->theme_preference = $dados['theme_preference'];

        if (!empty($dados['password'])) {
            $usuario->password = $dados['password'];
            $usuario->primeiro_acesso = false;
        }

        if ($request->hasFile('foto_perfil')) {
            $caminhoAnterior = $usuario->foto_perfil_path;
            $disk = (string) config('filesystems.public_uploads_disk', config('filesystems.default'));
            $usuario->foto_perfil_path = $request->file('foto_perfil')->store('usuarios/fotos', $disk);

            if (is_string($caminhoAnterior) && $caminhoAnterior !== '') {
                Storage::disk($disk)->delete($caminhoAnterior);
            }
        }

        $usuario->save();

        return back()->with('success', $primeiroAcesso
            ? 'Senha atualizada com sucesso. O painel administrativo foi liberado.'
            : 'Perfil atualizado com sucesso.');
    }

    public function storeProfileVinculo(Request $request): RedirectResponse
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        $dados = $request->validate([
            'igreja_id' => ['required', 'exists:igrejas,id'],
            'papeis' => ['required', 'array', 'min:1'],
            'papeis.*' => ['required', Rule::in(PapelIgreja::values())],
        ]);

        $igreja = Igreja::query()->findOrFail((int) $dados['igreja_id']);

        $this->gestaoUsuariosIgrejaService->atribuirPapeisAoUsuarioExistente(
            usuario: $usuario,
            igreja: $igreja,
            papeis: (array) $dados['papeis'],
            ator: $usuario,
            origem: 'admin_profile_store_vinculo'
        );
        app(IgrejaAtivaService::class)->set($igreja);

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Vinculo operacional aplicado ao seu perfil com sucesso.');
    }
}

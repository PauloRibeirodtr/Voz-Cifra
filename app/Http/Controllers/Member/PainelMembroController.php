<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Missa;
use App\Models\Usuario;
use App\Rules\StrongPassword;
use App\Services\AuditoriaOperacionalService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PainelMembroController extends Controller
{
    public function __construct(
        private readonly AuditoriaOperacionalService $auditoriaOperacionalService
    ) {
        $this->middleware(['auth', 'verified_custom', 'role:member']);
    }

    public function dashboard(): View
    {
        $usuario = $this->obterUsuario();
        $igreja = $usuario->igrejaAtiva() ?? $usuario->igreja;
        $hoje = CarbonImmutable::now('America/Cuiaba')->toDateString();
        $proximaMissa = $igreja
            ? Missa::query()
                ->with(['tempoLiturgico', 'missaMusicas'])
                ->where('igreja_id', $igreja->id)
                ->where(function ($query) use ($hoje) {
                    $query->where('ativo', true)
                        ->orWhereDate('data_missa', '>=', $hoje);
                })
                ->orderByRaw('case when ativo then 0 else 1 end')
                ->orderBy('data_missa')
                ->orderBy('hora_inicio')
                ->first()
            : null;

        return view('member.dashboard', compact('usuario', 'igreja', 'proximaMissa'));
    }

    public function profile(): View
    {
        $usuario = $this->obterUsuario();
        $isCoordenadorArea = request()->routeIs('coordenador.*');

        return view('member.profile', [
            'user' => $usuario,
            'igreja' => $usuario->igrejaAtiva() ?? $usuario->igreja,
            'isCoordenadorArea' => $isCoordenadorArea,
            'tituloPerfil' => $isCoordenadorArea ? 'Perfil do coordenador' : 'Meu perfil',
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $usuario = $this->obterUsuario();
        $primeiroAcesso = (bool) ($usuario->primeiro_acesso ?? false);

        $dados = $request->validate([
            'email' => ['required', 'email', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
            'telefone' => ['nullable', 'string', 'max:20'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => [$primeiroAcesso ? 'required' : 'nullable', 'confirmed', new StrongPassword()],
            'theme_preference' => ['required', Rule::in(['system', 'light', 'dark'])],
        ], [
            'password.required' => 'No primeiro acesso, defina uma nova senha para liberar o painel do músico.',
            'password.confirmed' => 'A confirmação da senha não confere.',
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
            $disk = $this->discoUploadsPublicos();
            $usuario->foto_perfil_path = $request->file('foto_perfil')->store('usuarios/fotos', $disk);

            if (is_string($caminhoAnterior) && $caminhoAnterior !== '') {
                Storage::disk($disk)->delete($caminhoAnterior);
            }
        }

        $usuario->save();

        $this->auditoriaOperacionalService->registrar(
            evento: 'perfil_atualizado',
            ator: $usuario,
            alvo: $usuario,
            igreja: $usuario->igrejaAtiva() ?? $usuario->igreja,
            contexto: [
                'origem' => 'member_profile_update',
                'primeiro_acesso_finalizado' => $primeiroAcesso && !($usuario->primeiro_acesso ?? false),
                'foto_perfil_alterada' => $request->hasFile('foto_perfil'),
                'senha_alterada' => !empty($dados['password']),
            ]
        );

        $rotaDestino = $request->routeIs('coordenador.*')
            ? 'coordenador.dashboard'
            : 'member.dashboard';

        return redirect()->route($rotaDestino)->with('success', $primeiroAcesso
            ? 'Senha atualizada com sucesso. O acesso do músico foi liberado.'
            : 'Perfil do músico atualizado com sucesso.');
    }

    private function obterUsuario(): Usuario
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && $usuario->ehMembro(), 403);

        return $usuario;
    }

    private function discoUploadsPublicos(): string
    {
        return (string) config('filesystems.public_uploads_disk', config('filesystems.default'));
    }
}

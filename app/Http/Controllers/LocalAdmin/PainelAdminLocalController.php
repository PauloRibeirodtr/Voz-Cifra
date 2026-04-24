<?php

namespace App\Http\Controllers\LocalAdmin;

use App\Enums\PapelIgreja;
use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Missa;
use App\Models\Usuario;
use App\Rules\StrongPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PainelAdminLocalController extends Controller
{
    public function dashboard(): View
    {
        $usuario = $this->obterUsuario();
        $igreja = $this->adicionarDadosPublicos($this->obterIgreja($usuario));
        $igrejasAdministradas = $this->obterIgrejasAdministradas($usuario, $igreja);

        $metricas = [
            'total_missas' => Missa::where('igreja_id', $igreja->id)->count(),
            'missas_ativas' => Missa::where('igreja_id', $igreja->id)->where('ativo', true)->count(),
            'membros_ativos' => $igreja->musicos()->where('usuarios.ativo', true)->count(),
            'membros_pendentes' => $igreja->musicos()->where('usuarios.ativo', false)->count(),
        ];

        $proximasMissas = Missa::with('tempoLiturgico')
            ->where('igreja_id', $igreja->id)
            ->orderBy('data_missa')
            ->orderBy('hora_inicio')
            ->take(5)
            ->get();

        return view('local-admin.dashboard', [
            'usuario' => $usuario,
            'igreja' => $igreja,
            'igrejasAdministradas' => $igrejasAdministradas,
            'metricas' => $metricas,
            'proximasMissas' => $proximasMissas,
        ]);
    }

    public function church(): View
    {
        $usuario = $this->obterUsuario();
        $igreja = $this->adicionarDadosPublicos($this->obterIgreja($usuario));

        return view('local-admin.church', [
            'igreja' => $igreja,
            'igrejasAdministradas' => $this->obterIgrejasAdministradas($usuario, $igreja),
            'usuario' => $usuario,
        ]);
    }

    public function profile(): View
    {
        return view('local-admin.profile', [
            'user' => $this->obterUsuario(),
            'igreja' => $this->adicionarDadosPublicos($this->obterIgreja($this->obterUsuario())),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $usuario = $this->obterUsuario();
        $primeiroAcesso = (bool) ($usuario->primeiro_acesso ?? false);

        $dados = $request->validate([
            'email' => ['required', 'email', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
            'telefone' => ['nullable', 'string', 'max:20'],
            'foto_perfil' => ['nullable', 'image', 'max:2048'],
            'password' => [$primeiroAcesso ? 'required' : 'nullable', 'confirmed', new StrongPassword()],
            'theme_preference' => ['required', Rule::in(['system', 'light', 'dark'])],
        ], [
            'password.required' => 'No primeiro acesso, defina uma nova senha para liberar o painel da igreja.',
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
            $usuario->foto_perfil_path = $request->file('foto_perfil')->store('usuarios/fotos', 'public');

            if (is_string($caminhoAnterior) && $caminhoAnterior !== '') {
                Storage::disk('public')->delete($caminhoAnterior);
            }
        }

        $usuario->save();

        return back()->with('success', $primeiroAcesso
            ? 'Senha atualizada com sucesso. O acesso completo ao painel da igreja foi liberado.'
            : 'Perfil do administrador local atualizado com sucesso.');
    }

    private function obterUsuario(): Usuario
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && $usuario->ehAdminLocal(), 403);

        return $usuario;
    }

    private function obterIgreja(Usuario $usuario): Igreja
    {
        $igreja = $usuario->igrejaAtiva() ?? $usuario->igreja;

        abort_unless($igreja !== null, 404, 'Igreja nao encontrada para este administrador local.');

        return $igreja;
    }

    private function adicionarDadosPublicos(Igreja $igreja): Igreja
    {
        $linkPublico = route('igrejas.public.show', ['slug' => $igreja->slug]);

        $igreja->setAttribute('link_publico', $linkPublico);
        $igreja->setAttribute(
            'qr_code_url',
            'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . urlencode($linkPublico)
        );

        return $igreja;
    }

    private function obterIgrejasAdministradas(Usuario $usuario, Igreja $igrejaAtiva): array
    {
        return $usuario->igrejasDisponiveisPorPapel(PapelIgreja::ADMIN_LOCAL)
            ->map(function (Igreja $igreja) use ($igrejaAtiva): Igreja {
                $igrejaComDados = $this->adicionarDadosPublicos($igreja);
                $igrejaComDados->setAttribute('eh_ativa', (int) $igreja->id === (int) $igrejaAtiva->id);

                return $igrejaComDados;
            })
            ->all();
    }
}

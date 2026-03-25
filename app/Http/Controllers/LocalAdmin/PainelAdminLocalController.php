<?php

namespace App\Http\Controllers\LocalAdmin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PainelAdminLocalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();
        $igreja = $usuario->igreja;

        if (!$igreja || $usuario->perfil_global !== 'admin_local') {
            return redirect()->route('home')->with('error', 'Acesso restrito ao administrador local.');
        }

        $membros = Usuario::where('igreja_id', $igreja->id)
            ->where('perfil_global', 'member')
            ->orderBy('nome')
            ->get();

        $pendingMembers = $membros->where('ativo', false)->values();

        return view('local-admin.dashboard', compact('igreja', 'membros', 'pendingMembers'));
    }

    public function approveMember(int $userId): RedirectResponse
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();
        $igreja = $usuario->igreja;

        if (!$igreja || $usuario->perfil_global !== 'admin_local') {
            return redirect()->route('home')->with('error', 'Ação não autorizada.');
        }

        $membro = Usuario::where('igreja_id', $igreja->id)
            ->where('perfil_global', 'member')
            ->findOrFail($userId);

        $membro->update(['ativo' => true]);

        return back()->with('success', "Membro {$membro->nome} ativado com sucesso.");
    }

    public function rejectMember(int $userId): RedirectResponse
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();
        $igreja = $usuario->igreja;

        if (!$igreja || $usuario->perfil_global !== 'admin_local') {
            return redirect()->route('home')->with('error', 'Ação não autorizada.');
        }

        $membro = Usuario::where('igreja_id', $igreja->id)
            ->where('perfil_global', 'member')
            ->findOrFail($userId);

        $membro->update(['ativo' => false]);

        return back()->with('success', "Membro {$membro->nome} foi desativado.");
    }
}

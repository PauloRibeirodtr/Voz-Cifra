<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\IgrejaAtivaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IgrejaAtivaController extends Controller
{
    public function __construct(
        private readonly IgrejaAtivaService $igrejaAtivaService
    ) {
    }

    public function update(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'igreja_id' => ['required', 'integer', 'min:1'],
        ]);

        $this->igrejaAtivaService->set((int) $dados['igreja_id']);

        return back()->with('success', 'Igreja ativa atualizada para este acesso.');
    }
}

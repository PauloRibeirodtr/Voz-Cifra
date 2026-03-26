<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use Illuminate\View\View;

class IgrejaPublicaController extends Controller
{
    public function show(string $slug): View
    {
        $igreja = Igreja::query()
            ->where('slug', $slug)
            ->where('ativo', true)
            ->firstOrFail();

        return view('publico.igreja', [
            'igreja' => $igreja,
        ]);
    }
}

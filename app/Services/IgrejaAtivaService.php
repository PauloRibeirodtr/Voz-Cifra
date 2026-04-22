<?php

namespace App\Services;

use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class IgrejaAtivaService
{
    private const SESSION_KEY = 'igreja_ativa_id';

    private ?bool $tabelaVinculosDisponivel = null;

    public function set(Igreja|int $igreja): void
    {
        $usuario = $this->usuarioAutenticado();

        if (!$usuario) {
            return;
        }

        $igrejaId = $igreja instanceof Igreja ? (int) $igreja->id : (int) $igreja;

        if ($igrejaId <= 0) {
            return;
        }

        if (!$this->usuarioPertenceIgreja($usuario, $igrejaId)) {
            return;
        }

        session([self::SESSION_KEY => $igrejaId]);
    }

    public function get(): ?Igreja
    {
        $igrejaId = $this->getId();

        if ($igrejaId === null) {
            return null;
        }

        return Igreja::find($igrejaId);
    }

    public function getId(): ?int
    {
        $usuario = $this->usuarioAutenticado();

        if (!$usuario) {
            return null;
        }

        $idSessao = session(self::SESSION_KEY);
        $igrejaIdSessao = is_numeric($idSessao) ? (int) $idSessao : null;

        if ($igrejaIdSessao !== null && $igrejaIdSessao > 0) {
            if ($this->usuarioPertenceIgreja($usuario, $igrejaIdSessao)) {
                return $igrejaIdSessao;
            }

            session()->forget(self::SESSION_KEY);
        }

        if ($this->tabelaVinculosDisponivel()) {
            $primeiroVinculoAtivo = $usuario->vinculosIgreja()
                ->where('ativo', true)
                ->orderByDesc('responsavel_principal')
                ->orderBy('id')
                ->value('igreja_id');

            if (is_numeric($primeiroVinculoAtivo)) {
                $igrejaId = (int) $primeiroVinculoAtivo;
                session([self::SESSION_KEY => $igrejaId]);

                return $igrejaId;
            }
        }

        $igrejaIdLegada = $usuario->igreja_id !== null ? (int) $usuario->igreja_id : null;

        if ($igrejaIdLegada !== null && $igrejaIdLegada > 0 && $this->usuarioPertenceIgreja($usuario, $igrejaIdLegada)) {
            session([self::SESSION_KEY => $igrejaIdLegada]);

            return $igrejaIdLegada;
        }

        return null;
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    private function usuarioAutenticado(): ?Usuario
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::user();

        return $usuario;
    }

    private function usuarioPertenceIgreja(Usuario $usuario, int $igrejaId): bool
    {
        if ($igrejaId <= 0) {
            return false;
        }

        if ((int) ($usuario->igreja_id ?? 0) === $igrejaId) {
            return true;
        }

        if (!$this->tabelaVinculosDisponivel()) {
            return false;
        }

        return $usuario->vinculosIgreja()
            ->where('igreja_id', $igrejaId)
            ->where('ativo', true)
            ->exists();
    }

    private function tabelaVinculosDisponivel(): bool
    {
        if ($this->tabelaVinculosDisponivel !== null) {
            return $this->tabelaVinculosDisponivel;
        }

        $this->tabelaVinculosDisponivel = Schema::hasTable('usuario_igreja');

        return $this->tabelaVinculosDisponivel;
    }
}

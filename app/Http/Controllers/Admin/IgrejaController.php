<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Usuario;
use App\Rules\StrongPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class IgrejaController extends Controller
{
    public function index(): View
    {
        $igrejas = Igreja::with([
            'usuarios' => fn ($query) => $query
                ->where('perfil_global', 'admin_local')
                ->orderBy('nome'),
        ])
            ->orderBy('nome')
            ->get()
            ->map(fn (Igreja $igreja) => $this->adicionarDadosPublicos($igreja));

        return view('admin.churches.index', [
            'igrejas' => $igrejas,
        ]);
    }

    public function create(): View
    {
        return view('admin.churches.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('igrejas', 'slug')],
            'cnpj' => ['required', 'string', 'max:18', Rule::unique('igrejas', 'cnpj')],
            'cep' => ['nullable', 'string', 'max:9'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'size:2'],
            'ativo' => ['nullable', 'boolean'],
            'admin_nome' => ['required', 'string', 'max:255'],
            'admin_cpf' => ['required', 'string', 'max:14', Rule::unique('usuarios', 'cpf')],
            'admin_email' => ['required', 'email', 'max:255', Rule::unique('usuarios', 'email')],
            'admin_telefone' => ['nullable', 'string', 'max:20'],
        ]);

        $igreja = DB::transaction(function () use ($dados): Igreja {
            $slug = $this->resolverSlug(
                $dados['slug'] ?? null,
                $dados['nome']
            );

            $igreja = Igreja::create([
                'nome' => $dados['nome'],
                'slug' => $slug,
                'cnpj' => $dados['cnpj'],
                'cep' => $dados['cep'] ?? null,
                'endereco' => $dados['endereco'] ?? null,
                'cidade' => $dados['cidade'],
                'estado' => strtoupper($dados['estado']),
                'ativo' => (bool) ($dados['ativo'] ?? true),
            ]);

            Usuario::create([
                'igreja_id' => $igreja->id,
                'nome' => $dados['admin_nome'],
                'cpf' => $dados['admin_cpf'],
                'email' => $dados['admin_email'],
                'telefone' => $dados['admin_telefone'] ?? null,
                'password' => $this->gerarSenhaInicialPorCpf($dados['admin_cpf']),
                'perfil_global' => 'admin_local',
                'ativo' => true,
                'primeiro_acesso' => true,
            ]);

            return $igreja;
        });

        return redirect()
            ->route('admin.igrejas.edit', $igreja)
            ->with('success', 'Igreja e administrador local cadastrados com sucesso. O link publico fixo e o QR Code desta igreja ja estao prontos para uso futuro.');
    }

    public function edit(Igreja $igreja): View
    {
        $adminLocal = $this->obterAdminLocal($igreja);
        $igreja = $this->adicionarDadosPublicos($igreja);

        return view('admin.churches.edit', [
            'igreja' => $igreja,
            'adminLocal' => $adminLocal,
        ]);
    }

    public function update(Request $request, Igreja $igreja): RedirectResponse
    {
        $adminLocal = $this->obterAdminLocal($igreja);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('igrejas', 'slug')->ignore($igreja->id)],
            'cnpj' => ['required', 'string', 'max:18', Rule::unique('igrejas', 'cnpj')->ignore($igreja->id)],
            'cep' => ['nullable', 'string', 'max:9'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'size:2'],
            'ativo' => ['nullable', 'boolean'],
            'admin_nome' => ['required', 'string', 'max:255'],
            'admin_cpf' => ['required', 'string', 'max:14', Rule::unique('usuarios', 'cpf')->ignore($adminLocal?->id)],
            'admin_email' => ['required', 'email', 'max:255', Rule::unique('usuarios', 'email')->ignore($adminLocal?->id)],
            'admin_telefone' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($dados, $igreja, $adminLocal): void {
            $slug = $this->resolverSlug(
                $dados['slug'] ?? null,
                $dados['nome'],
                $igreja->id
            );

            $igreja->update([
                'nome' => $dados['nome'],
                'slug' => $slug,
                'cnpj' => $dados['cnpj'],
                'cep' => $dados['cep'] ?? null,
                'endereco' => $dados['endereco'] ?? null,
                'cidade' => $dados['cidade'],
                'estado' => strtoupper($dados['estado']),
                'ativo' => (bool) ($dados['ativo'] ?? false),
            ]);

            if (!$adminLocal) {
                Usuario::create([
                    'igreja_id' => $igreja->id,
                    'nome' => $dados['admin_nome'],
                    'cpf' => $dados['admin_cpf'],
                    'email' => $dados['admin_email'],
                    'telefone' => $dados['admin_telefone'] ?? null,
                    'password' => $this->gerarSenhaInicialPorCpf($dados['admin_cpf']),
                    'perfil_global' => 'admin_local',
                    'ativo' => true,
                    'primeiro_acesso' => true,
                ]);

                return;
            }

            $dadosAdmin = [
                'igreja_id' => $igreja->id,
                'nome' => $dados['admin_nome'],
                'cpf' => $dados['admin_cpf'],
                'email' => $dados['admin_email'],
                'telefone' => $dados['admin_telefone'] ?? null,
                'perfil_global' => 'admin_local',
                'ativo' => true,
            ];

            $adminLocal->update($dadosAdmin);
        });

        return redirect()
            ->route('admin.igrejas.edit', $igreja)
            ->with('success', 'Igreja atualizada com sucesso.');
    }

    public function resetAdminLocalPassword(Request $request, Igreja $igreja): RedirectResponse
    {
        $adminLocal = $this->obterAdminLocal($igreja);
        $origem = $request->input('origem') === 'edit' ? 'edit' : 'index';

        if (!$adminLocal) {
            return redirect()
                ->route($origem === 'edit' ? 'admin.igrejas.edit' : 'admin.igrejas.index', $origem === 'edit' ? $igreja : [])
                ->with('error', 'Esta igreja ainda nao possui administrador local cadastrado.');
        }

        $validator = Validator::make($request->all(), [
            'password' => ['nullable', 'confirmed', new StrongPassword()],
        ], [
            'password.confirmed' => 'A confirmacao da nova senha nao confere.',
        ]);

        if ($validator->fails()) {
            $redirecionamento = redirect()
                ->route($origem === 'edit' ? 'admin.igrejas.edit' : 'admin.igrejas.index', $origem === 'edit' ? $igreja : [])
                ->withErrors($validator)
                ->withInput();

            if ($origem === 'index') {
                $redirecionamento->with('abrir_reset_modal', $igreja->id);
            }

            return $redirecionamento;
        }

        $dados = $validator->validated();

        $adminLocal->update([
            'password' => filled($dados['password'] ?? null)
                ? $dados['password']
                : $this->gerarSenhaInicialPorCpf($adminLocal->cpf),
            'primeiro_acesso' => true,
        ]);

        return redirect()
            ->route($origem === 'edit' ? 'admin.igrejas.edit' : 'admin.igrejas.index', $origem === 'edit' ? $igreja : [])
            ->with('success', 'Senha redefinida com sucesso. O usuario devera trocar no proximo acesso.');
    }

    protected function obterAdminLocal(Igreja $igreja): ?Usuario
    {
        return $igreja->usuarios()
            ->where('perfil_global', 'admin_local')
            ->orderBy('id')
            ->first();
    }

    protected function resolverSlug(?string $slugInformado, string $nome, ?int $ignorarIgrejaId = null): string
    {
        $base = Str::slug($slugInformado ?: $nome);

        if ($base === '') {
            $base = 'igreja';
        }

        $slug = $base;
        $contador = 2;

        while ($this->slugJaExiste($slug, $ignorarIgrejaId)) {
            $slug = "{$base}-{$contador}";
            $contador++;
        }

        return $slug;
    }

    protected function slugJaExiste(string $slug, ?int $ignorarIgrejaId = null): bool
    {
        return Igreja::query()
            ->when($ignorarIgrejaId, fn ($query) => $query->where('id', '!=', $ignorarIgrejaId))
            ->where('slug', $slug)
            ->exists();
    }

    protected function gerarSenhaInicialPorCpf(string $cpf): string
    {
        $cpfNumerico = $this->somenteDigitos($cpf);

        if (strlen($cpfNumerico) < 8) {
            throw ValidationException::withMessages([
                'admin_cpf' => 'Informe um CPF valido para gerar a senha inicial.',
            ]);
        }

        return $cpfNumerico;
    }

    protected function somenteDigitos(string $valor): string
    {
        return preg_replace('/\D+/', '', $valor) ?? '';
    }

    protected function adicionarDadosPublicos(Igreja $igreja): Igreja
    {
        $linkPublico = route('igrejas.public.show', ['slug' => $igreja->slug]);

        $igreja->setAttribute('link_publico', $linkPublico);
        $igreja->setAttribute(
            'qr_code_url',
            'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . urlencode($linkPublico)
        );

        return $igreja;
    }
}

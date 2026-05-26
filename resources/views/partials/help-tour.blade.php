@php
    use App\Enums\PapelIgreja;
    use Illuminate\Support\Facades\Route;

    $usuarioAjuda = auth()->user();
    $igrejaAtivaAjuda = $usuarioAjuda ? $usuarioAjuda->igrejaAtiva() : null;
    $igrejaAtivaIdAjuda = $igrejaAtivaAjuda ? $igrejaAtivaAjuda->id : null;
    $rotaAtualAjuda = Route::currentRouteName();
    $urlAtualAjuda = url()->current();
    $urlAjuda = static fn (string $routeName): ?string => Route::has($routeName) ? route($routeName) : null;
    $guiaEditarCifraAjuda = static function (?string $rotaAtual): ?array {
        if (!in_array($rotaAtual, [
            'admin.versoes-musicais.create',
            'admin.versoes-musicais.edit',
            'coordenador.versoes-musicais.create',
            'coordenador.versoes-musicais.edit',
        ], true)) {
            return null;
        }

        return [
            'id' => 'editar-cifra',
            'rota' => $rotaAtual,
            'passos' => [
                ['alvo' => '[data-guide-target="cifra-editor"]', 'foco' => '#letra_com_cifras', 'titulo' => 'Cole ou edite a cifra', 'texto' => 'Cole a cifra inteira aqui. Pode vir com acordes em cima da letra, entre colchetes ou no formato do Cifra Club.'],
                ['alvo' => '[data-guide-target="cifra-organizar"]', 'titulo' => 'Arrume automaticamente', 'texto' => 'Clique para converter acordes acima da letra, padronizar refroes e preparar o texto para salvar.'],
                ['alvo' => '[data-guide-target="cifra-preview"]', 'titulo' => 'Confira a previa', 'texto' => 'Acompanhe do lado como o musico vai ler. Se algo ficar fora do lugar, ajuste no editor e confira novamente.'],
                ['alvo' => '[data-guide-target="cifra-alertas"]', 'titulo' => 'Revise avisos leves', 'texto' => 'Se aparecer aviso, ele nao bloqueia. Use como checklist para refrao vazio, acordes soltos ou letra sem cifra.'],
                ['alvo' => '[data-guide-target="cifra-tom-bpm"]', 'titulo' => 'Defina tom e BPM', 'texto' => 'Informe o tom original e o BPM quando souber. Isso ajuda o musico no estudo e na missa.'],
                ['alvo' => '[data-guide-target="cifra-youtube"]', 'titulo' => 'Adicione video se tiver', 'texto' => 'Cole o ID ou link do YouTube para facilitar o estudo da equipe.'],
            ],
        ];
    };

    $acoesAjuda = [];
    $adicionarAcaoAjuda = static function (string $perfil, string $titulo, string $url, string $icone, array $termos = [], ?array $guia = null, string $descricao = '') use (&$acoesAjuda): void {
        if ($url === '') {
            return;
        }

        if ($guia === null) {
            $guia = [
                'id' => 'acao-' . substr(sha1($perfil . '|' . $titulo . '|' . $url), 0, 14),
                'url' => preg_replace('/#.*$/', '', $url),
                'passos' => [
                    [
                        'alvo' => '#mainContent',
                        'titulo' => $titulo,
                        'texto' => $descricao !== '' ? $descricao : 'Esta e a area principal desta acao. Comece lendo o titulo e os avisos da tela.',
                    ],
                    [
                        'alvo' => '#mainContent form, #mainContent [data-guide-target], #mainContent table, #mainContent .grid',
                        'titulo' => 'Preencha ou filtre com calma',
                        'texto' => 'Use os campos, filtros e listas desta tela. Quando houver busca, digite poucas palavras e confira os resultados antes de salvar.',
                    ],
                    [
                        'alvo' => '#mainContent button[type="submit"], #mainContent a[href], #mainContent button',
                        'titulo' => 'Conclua a acao',
                        'texto' => 'Depois de revisar, use o botao principal da tela. Botoes de inativar ou remover exigem mais atencao antes de confirmar.',
                    ],
                ],
            ];
        }

        $acoesAjuda[] = [
            'perfil' => $perfil,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'url' => $url,
            'icone' => $icone,
            'busca' => mb_strtolower($perfil . ' ' . $titulo . ' ' . $descricao . ' ' . implode(' ', $termos)),
            'guia' => $guia,
        ];
    };

    if ($usuarioAjuda && $usuarioAjuda->ehAdminMaster()) {
        $adicionarAcaoAjuda('Admin master', 'Painel central', $urlAjuda('admin.dashboard') ?? '', 'fa-house', ['inicio', 'indicadores'], null, 'Veja indicadores gerais e atalhos principais do sistema.');
        $adicionarAcaoAjuda('Admin master', 'Cadastrar usuario', $urlAjuda('admin.usuarios.create') ?? '', 'fa-user-plus', ['pessoa', 'admin', 'musico', 'coordenador'], [
            'id' => 'cadastro-usuario',
            'rota' => 'admin.usuarios.create',
            'passos' => [
                ['alvo' => '[data-guide-target="usuario-tipo"]', 'foco' => '[data-tipo-cadastro]', 'titulo' => 'Escolha o perfil permitido', 'texto' => 'Admin master pode criar admin master, coordenador, admin local, musico e padre. Coordenador e admin local usam os fluxos proprios da igreja.'],
                ['alvo' => '[data-guide-target="usuario-igreja"]', 'foco' => '[data-igreja-filtro]', 'titulo' => 'Escolha a igreja inicial', 'texto' => 'Coordenador, admin local e musico precisam de igreja. Admin master nao precisa. Padre pode ter igreja, mas nao e obrigatorio.'],
                ['alvo' => '[data-guide-target="usuario-dados"]', 'foco' => '[name="nome"]', 'titulo' => 'Digite o nome completo', 'texto' => 'Use o nome que a equipe reconhece. Isso ajuda na busca, nos chamados e na auditoria.'],
                ['alvo' => '[data-guide-target="usuario-cpf"]', 'foco' => '[name="cpf"]', 'titulo' => 'Informe o CPF', 'texto' => 'O CPF evita cadastro duplicado. Se a pessoa ja existir, o sistema reaproveita a conta.'],
                ['alvo' => '[data-guide-target="usuario-email"]', 'foco' => '[name="email"]', 'titulo' => 'Informe o e-mail de acesso', 'texto' => 'Esse e-mail recebe o convite e a redefinicao de senha. Padre sem login pode ficar em branco.'],
                ['alvo' => '[data-guide-target="usuario-telefone"]', 'foco' => '[name="telefone"]', 'titulo' => 'Adicione o telefone', 'texto' => 'Nao e obrigatorio, mas facilita contato e suporte quando a pessoa tiver dificuldade de acesso.'],
                ['alvo' => '[data-guide-target="usuario-acesso"]', 'foco' => '[name="enviar_convite"]', 'titulo' => 'Defina o acesso inicial', 'texto' => 'Deixe ativo para liberar a conta. Marque convite se quiser enviar o link de primeiro acesso agora.'],
                ['alvo' => '[data-guide-target="usuario-salvar"]', 'titulo' => 'Conclua o cadastro', 'texto' => 'Revise os dados e salve. Depois voce pode ajustar papeis, ativar ou reenviar convite.'],
            ],
        ], 'Crie contas e atribua o primeiro perfil conforme a permissao correta.');
        $adicionarAcaoAjuda('Admin master', 'Gerenciar usuarios', $urlAjuda('admin.usuarios.index') ?? '', 'fa-users-gear', ['perfis', 'papeis', 'acesso'], null, 'Pesquise pessoas, edite dados, ative contas e reenvie convites.');
        $adicionarAcaoAjuda('Admin master', 'Ver hierarquia de usuarios', $urlAjuda('admin.usuarios.hierarquia') ?? '', 'fa-sitemap', ['hierarquia', 'vinculos', 'perfis'], null, 'Confira como os usuarios estao distribuidos por perfil e igreja.');
        $adicionarAcaoAjuda('Admin master', 'Cadastrar igreja', $urlAjuda('admin.igrejas.create') ?? '', 'fa-church', ['paroquia', 'comunidade'], null, 'Crie uma igreja ou comunidade e depois vincule equipe local.');
        $adicionarAcaoAjuda('Admin master', 'Gerenciar igrejas', $urlAjuda('admin.igrejas.index') ?? '', 'fa-building-columns', ['dados', 'links', 'paroquia'], null, 'Edite dados, links publicos, coordenadores e admins locais das igrejas.');
        $adicionarAcaoAjuda('Admin master', 'Administrar admins locais', $urlAjuda('admin.admins-locais.index') ?? '', 'fa-user-shield', ['admin local', 'senha', 'acesso'], null, 'Acompanhe admins locais e envie novo link de senha quando precisar.');
        $adicionarAcaoAjuda('Admin master', 'Cadastrar musica ou cifra', $urlAjuda('admin.musicas.create') ?? '', 'fa-music', ['musica', 'cifra', 'versao'], null, 'Inclua musicas e crie versoes de cifras para uso nas missas.');
        if ($guiaEditarCifraAjuda($rotaAtualAjuda)) {
            $adicionarAcaoAjuda('Admin master', 'Editar cifra passo a passo', $urlAtualAjuda, 'fa-wand-magic-sparkles', ['cifra', 'organizar', 'cifra club', 'refrao'], $guiaEditarCifraAjuda($rotaAtualAjuda), 'Cole, arrume automaticamente, revise a previa e salve com seguranca.');
        }
        $adicionarAcaoAjuda('Admin master', 'Consultar musicas', $urlAjuda('admin.musicas.index') ?? '', 'fa-magnifying-glass', ['biblioteca', 'cifra', 'tom'], null, 'Pesquise, revise e edite a biblioteca musical global.');
        $adicionarAcaoAjuda('Admin master', 'Ver acordes', $urlAjuda('admin.acordes.index') ?? '', 'fa-guitar', ['acordes', 'cifras', 'violao'], null, 'Consulte e mantenha o dicionario de acordes usado nas cifras.');
        $adicionarAcaoAjuda('Admin master', 'Tempos liturgicos', $urlAjuda('admin.tempos-liturgicos.index') ?? '', 'fa-calendar-days', ['liturgia', 'tempo'], null, 'Organize tempos como Advento, Quaresma, Pascoa e Tempo Comum.');
        $adicionarAcaoAjuda('Admin master', 'Momentos liturgicos', $urlAjuda('admin.momentos-liturgicos.index') ?? '', 'fa-list-ol', ['entrada', 'comunhao', 'ofertorio'], null, 'Organize os momentos da celebracao para montar repertorios com ordem.');
        $adicionarAcaoAjuda('Admin master', 'Auditoria', $urlAjuda('admin.auditoria.index') ?? '', 'fa-shield-halved', ['logs', 'seguranca', 'historico'], null, 'Veja registros importantes de alteracoes, acessos e acoes administrativas.');
        $adicionarAcaoAjuda('Admin master', 'Configuracoes do sistema', $urlAjuda('admin.settings') ?? '', 'fa-gear', ['configuracoes', 'sistema'], null, 'Ajuste preferencias e parametros administrativos do sistema.');
        $adicionarAcaoAjuda('Admin master', 'Ver chamados abertos', ($urlAjuda('admin.chamados.index') ?? '') . '?visao=atendimento', 'fa-headset', ['suporte', 'atendimento'], null, 'Atenda solicitacoes que ainda precisam de resposta da equipe.');
        $adicionarAcaoAjuda('Admin master', 'Ver chamados encerrados', ($urlAjuda('admin.chamados.index') ?? '') . '?visao=encerrados', 'fa-box-archive', ['resolvidos', 'fechados'], null, 'Consulte historico de chamados resolvidos ou fechados.');
    }

    if ($usuarioAjuda && $usuarioAjuda->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igrejaAtivaIdAjuda)) {
        $adicionarAcaoAjuda('Admin local', 'Resumo da igreja', $urlAjuda('local-admin.dashboard') ?? '', 'fa-church', ['painel', 'igreja'], null, 'Veja a situacao da igreja ativa e caminhos rapidos da rotina local.');
        $adicionarAcaoAjuda('Admin local', 'Cadastrar musico', $urlAjuda('local-admin.musicos.create') ?? '', 'fa-user-plus', ['usuario', 'equipe', 'perfil'], [
            'id' => 'cadastro-musico-local',
            'rota' => 'local-admin.musicos.create',
            'passos' => [
                ['alvo' => '[data-guide-target="musico-nome"]', 'foco' => '[name="nome"]', 'titulo' => 'Digite o nome do musico', 'texto' => 'Admin local cadastra apenas musicos da igreja ativa. Use o nome completo para achar a pessoa depois.'],
                ['alvo' => '[data-guide-target="musico-cpf"]', 'foco' => '[name="cpf"]', 'titulo' => 'Informe o CPF', 'texto' => 'O CPF impede duplicidade. Se a pessoa ja existe, ela e vinculada como musico desta igreja.'],
                ['alvo' => '[data-guide-target="musico-email"]', 'foco' => '[name="email"]', 'titulo' => 'Informe o e-mail', 'texto' => 'O musico usa esse e-mail para acessar o painel, repertorio e cifras.'],
                ['alvo' => '[data-guide-target="musico-igreja"]', 'titulo' => 'Confira a igreja', 'texto' => 'Neste fluxo a igreja ja vem travada na igreja ativa do admin local.'],
                ['alvo' => '[data-guide-target="musico-acesso"]', 'foco' => '[name="enviar_convite"]', 'titulo' => 'Escolha o convite', 'texto' => 'Mantenha ativo e envie o convite se o musico ja deve acessar agora.'],
                ['alvo' => '[data-guide-target="musico-salvar"]', 'titulo' => 'Salve o musico', 'texto' => 'Depois de salvar, ele aparece na equipe musical da igreja.'],
            ],
        ], 'Adicione musicos apenas na igreja ativa do admin local.');
        $adicionarAcaoAjuda('Admin local', 'Gerenciar equipe musical', $urlAjuda('local-admin.musicos.index') ?? '', 'fa-users', ['musicos', 'coordenadores'], null, 'Veja musicos da igreja, edite dados e envie acesso quando necessario.');
        $adicionarAcaoAjuda('Admin local', 'Montar uma missa', $urlAjuda('local-admin.missas.create') ?? '', 'fa-calendar-plus', ['celebracao', 'repertorio'], null, 'Crie a celebracao e depois monte o repertorio com as musicas corretas.');
        $adicionarAcaoAjuda('Admin local', 'Ver missas cadastradas', $urlAjuda('local-admin.missas.index') ?? '', 'fa-calendar-check', ['repertorio', 'publicar'], null, 'Acompanhe missas, edite repertorios e publique quando estiver pronto.');
        $adicionarAcaoAjuda('Admin local', 'Atualizar dados e links da igreja', $urlAjuda('local-admin.church') ?? '', 'fa-link', ['qr', 'publico'], null, 'Atualize informacoes publicas, links e dados exibidos para a comunidade.');
    }

    if ($usuarioAjuda && $usuarioAjuda->temPapelNaIgreja(PapelIgreja::COORDENADOR, $igrejaAtivaIdAjuda)) {
        $adicionarAcaoAjuda('Coordenador', 'Painel da coordenacao', $urlAjuda('coordenador.dashboard') ?? '', 'fa-diagram-project', ['painel', 'coordenacao'], null, 'Veja atalhos da coordenacao musical e admin local da igreja ativa.');
        $adicionarAcaoAjuda('Coordenador', 'Cadastrar musico', $urlAjuda('coordenador.musicos.create') ?? '', 'fa-user-plus', ['usuario', 'equipe'], [
            'id' => 'cadastro-musico-coordenador',
            'rota' => 'coordenador.musicos.create',
            'passos' => [
                ['alvo' => '[data-guide-target="musico-nome"]', 'foco' => '[name="nome"]', 'titulo' => 'Digite o nome do musico', 'texto' => 'Coordenador pode cadastrar musicos da igreja ativa e tambem atribuir admin local pelo fluxo da igreja.'],
                ['alvo' => '[data-guide-target="musico-cpf"]', 'foco' => '[name="cpf"]', 'titulo' => 'Informe o CPF', 'texto' => 'O CPF evita duplicar pessoas e permite reaproveitar um usuario ja existente.'],
                ['alvo' => '[data-guide-target="musico-email"]', 'foco' => '[name="email"]', 'titulo' => 'Informe o e-mail', 'texto' => 'Esse e-mail sera usado para convite, login e recuperacao de senha.'],
                ['alvo' => '[data-guide-target="musico-igreja"]', 'titulo' => 'Confira a igreja ativa', 'texto' => 'O cadastro entra na igreja selecionada no topo do painel do coordenador.'],
                ['alvo' => '[data-guide-target="musico-acesso"]', 'foco' => '[name="enviar_convite"]', 'titulo' => 'Defina o acesso', 'texto' => 'Ativo libera o usuario. Convite envia o link de primeiro acesso com seguranca.'],
                ['alvo' => '[data-guide-target="musico-salvar"]', 'titulo' => 'Salve o cadastro', 'texto' => 'Depois de salvar, o musico fica disponivel para repertorios e rotinas da igreja.'],
            ],
        ], 'Adicione musicos da igreja ativa e envie convite de acesso.');
        $adicionarAcaoAjuda('Coordenador', 'Cadastrar admin local', ($urlAjuda('coordenador.dashboard') ?? '') . '#admin-local-form', 'fa-user-shield', ['usuario', 'gestao', 'igreja'], [
            'id' => 'cadastro-admin-local-coordenador',
            'rota' => 'coordenador.dashboard',
            'passos' => [
                ['alvo' => '[data-guide-target="atalho-admin-local"]', 'titulo' => 'Acesse pelo painel', 'texto' => 'O coordenador pode atribuir admin local apenas para a igreja ativa.'],
                ['alvo' => '[data-guide-target="admin-local-form"]', 'titulo' => 'Confira a igreja ativa', 'texto' => 'Este formulario nao deixa escolher outra igreja. Assim evita cadastrar admin local no lugar errado.'],
                ['alvo' => '[data-guide-target="admin-local-nome"]', 'foco' => '[name="nome"]', 'titulo' => 'Digite o nome completo', 'texto' => 'Use o nome real da pessoa que vai cuidar da administracao local.'],
                ['alvo' => '[data-guide-target="admin-local-cpf"]', 'foco' => '[name="cpf"]', 'titulo' => 'Informe o CPF', 'texto' => 'O CPF evita duplicar conta e ajuda o sistema a reaproveitar usuario existente.'],
                ['alvo' => '[data-guide-target="admin-local-email"]', 'foco' => '[name="email"]', 'titulo' => 'Informe o e-mail', 'texto' => 'Esse e-mail sera usado para convite, login e recuperacao de senha.'],
                ['alvo' => '[data-guide-target="admin-local-telefone"]', 'foco' => '[name="telefone"]', 'titulo' => 'Adicione o telefone', 'texto' => 'Opcional, mas ajuda no suporte quando houver dificuldade de acesso.'],
                ['alvo' => '[data-guide-target="admin-local-salvar"]', 'titulo' => 'Conclua com seguranca', 'texto' => 'Ao salvar, a pessoa recebe papel de admin local somente nesta igreja.'],
            ],
        ], 'Atribua uma pessoa como admin local somente na igreja ativa.');
        $adicionarAcaoAjuda('Coordenador', 'Gerenciar equipe musical', $urlAjuda('coordenador.musicos.index') ?? '', 'fa-users', ['musicos', 'equipe'], null, 'Acompanhe musicos, vincule pessoas existentes e gerencie acessos.');
        $adicionarAcaoAjuda('Coordenador', 'Cadastrar musica ou cifra', $urlAjuda('coordenador.musicas.create') ?? '', 'fa-music', ['biblioteca', 'versao'], null, 'Inclua novas musicas e cifras para a biblioteca da igreja.');
        if ($guiaEditarCifraAjuda($rotaAtualAjuda)) {
            $adicionarAcaoAjuda('Coordenador', 'Editar cifra passo a passo', $urlAtualAjuda, 'fa-wand-magic-sparkles', ['cifra', 'organizar', 'cifra club', 'refrao'], $guiaEditarCifraAjuda($rotaAtualAjuda), 'Cole, arrume automaticamente, revise a previa e salve com seguranca.');
        }
        $adicionarAcaoAjuda('Coordenador', 'Consultar biblioteca', $urlAjuda('coordenador.musicas.index') ?? '', 'fa-magnifying-glass', ['musicas', 'cifras', 'tom'], null, 'Pesquise e edite musicas, cifras e versoes cadastradas.');
        $adicionarAcaoAjuda('Coordenador', 'Organizar tempos liturgicos', $urlAjuda('coordenador.tempos-liturgicos.index') ?? '', 'fa-calendar-days', ['liturgia', 'tempo'], null, 'Mantenha tempos liturgicos organizados para classificar repertorios.');
        $adicionarAcaoAjuda('Coordenador', 'Organizar momentos liturgicos', $urlAjuda('coordenador.momentos-liturgicos.index') ?? '', 'fa-list-ol', ['entrada', 'comunhao', 'final'], null, 'Defina momentos da missa para orientar a montagem do repertorio.');
        $adicionarAcaoAjuda('Coordenador', 'Ver chamados abertos', ($urlAjuda('coordenador.chamados.index') ?? '') . '?visao=atendimento', 'fa-headset', ['suporte', 'atendimento'], null, 'Atenda pedidos ligados a musicos e rotina da coordenacao.');
        $adicionarAcaoAjuda('Coordenador', 'Ver chamados encerrados', ($urlAjuda('coordenador.chamados.index') ?? '') . '?visao=encerrados', 'fa-box-archive', ['resolvidos', 'fechados'], null, 'Consulte chamados ja resolvidos para historico e acompanhamento.');
    }

    if ($usuarioAjuda && $usuarioAjuda->temPapelNaIgreja(PapelIgreja::MUSICO, $igrejaAtivaIdAjuda)) {
        $adicionarAcaoAjuda('Musico', 'Painel do musico', $urlAjuda('member.dashboard') ?? '', 'fa-house', ['painel', 'inicio'], null, 'Veja proximas tarefas, repertorio e atalhos do seu perfil.');
        $adicionarAcaoAjuda('Musico', 'Ver repertorio', $urlAjuda('member.repertorio') ?? '', 'fa-list-check', ['missa', 'ensaio'], null, 'Acompanhe as musicas preparadas para a missa ou celebracao.');
        $adicionarAcaoAjuda('Musico', 'Consultar musicas', $urlAjuda('member.musicas.index') ?? '', 'fa-magnifying-glass', ['cifra', 'tom'], null, 'Pesquise cifras e versoes disponiveis na biblioteca.');
        $adicionarAcaoAjuda('Musico', 'Meus estudos', $urlAjuda('member.colecoes.index') ?? '', 'fa-book-open-reader', ['colecao', 'favoritos'], null, 'Organize musicas para estudo pessoal e preparacao.');
        $adicionarAcaoAjuda('Musico', 'Abrir chamado de suporte', $urlAjuda('member.chamados.create') ?? '', 'fa-circle-plus', ['problema', 'ajuda'], null, 'Peca ajuda quando tiver dificuldade de acesso, repertorio ou uso do sistema.');
        $adicionarAcaoAjuda('Musico', 'Acompanhar meus chamados', $urlAjuda('member.chamados.index') ?? '', 'fa-message', ['suporte', 'resposta'], null, 'Veja respostas da equipe e historico dos seus pedidos.');
    }
@endphp

@if (count($acoesAjuda) > 0)
    <style>
        .help-actions-launcher {
            position: fixed;
            right: 1.25rem;
            bottom: 1.25rem;
            z-index: 60;
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            border: 1px solid rgba(214, 173, 108, .45);
            border-radius: 999px;
            background: #1f1514;
            color: #fff8ed;
            padding: .8rem 1rem;
            font-weight: 800;
            box-shadow: 0 18px 40px rgba(20, 10, 8, .28);
        }

        .help-actions-panel {
            position: fixed;
            inset: auto 1.25rem 5.5rem auto;
            z-index: 70;
            width: min(27rem, calc(100vw - 2rem));
            max-height: min(38rem, calc(100vh - 7rem));
            overflow: auto;
            border: 1px solid rgba(140, 105, 51, .18);
            border-radius: 1.25rem;
            background: #fffdf8;
            color: #1d1513;
            box-shadow: 0 24px 70px rgba(20, 10, 8, .28);
        }

        @media (max-width: 640px) {
            .help-actions-launcher {
                right: .85rem;
                bottom: .85rem;
                padding: .75rem .9rem;
            }

            .help-actions-panel {
                inset: auto .75rem 4.75rem .75rem;
                width: auto;
            }
        }
    </style>

    <button type="button" class="help-actions-launcher" data-help-open aria-haspopup="dialog" aria-controls="helpActionsPanel">
        <i class="fa-solid fa-magnifying-glass"></i>
        <span>Ajuda</span>
    </button>

    <section id="helpActionsPanel" class="help-actions-panel hidden" data-help-panel aria-label="Ajuda por busca">
        <div class="sticky top-0 z-10 border-b border-[#eadfce] bg-[#fffdf8] px-5 py-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#8a5a1f]">Ajuda</p>
                    <h2 class="mt-1 text-lg font-black text-[#1d1513]">O que voce quer fazer?</h2>
                </div>
                <button type="button" class="rounded-full border border-[#eadfce] px-3 py-2 text-sm font-bold text-[#5a3a1d]" data-help-close aria-label="Fechar ajuda">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <label class="mt-4 flex items-center gap-2 rounded-2xl border border-[#eadfce] bg-white px-3 py-2">
                <i class="fa-solid fa-magnifying-glass text-[#8a5a1f]"></i>
                <input type="search" class="min-w-0 flex-1 border-0 bg-transparent text-sm text-[#1d1513] outline-none" placeholder="Buscar: missa, usuario, chamado..." data-help-search>
            </label>
        </div>

        <div class="space-y-2 p-4" data-help-list>
            @foreach ($acoesAjuda as $acaoAjuda)
                <a
                    href="{{ $acaoAjuda['url'] }}"
                    class="help-action-item flex items-center gap-3 rounded-2xl border border-[#eadfce] bg-white px-4 py-3 text-[#1d1513] transition hover:bg-[#fff7e8]"
                    data-help-item
                    data-help-search-text="{{ $acaoAjuda['busca'] }}"
                    @if (!empty($acaoAjuda['guia']))
                        data-guide-id="{{ $acaoAjuda['guia']['id'] }}"
                    @endif
                >
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[#7a501f] text-white">
                        <i class="fa-solid {{ $acaoAjuda['icone'] }}"></i>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-xs font-black uppercase tracking-[0.14em] text-[#8a5a1f]">{{ $acaoAjuda['perfil'] }}</span>
                        <span class="mt-1 block text-sm font-black">{{ $acaoAjuda['titulo'] }}</span>
                        @if ($acaoAjuda['descricao'] !== '')
                            <span class="mt-1 block text-xs leading-5 text-[#6d5242]">{{ $acaoAjuda['descricao'] }}</span>
                        @endif
                    </span>
                </a>
            @endforeach

            <div class="hidden rounded-2xl border border-dashed border-[#eadfce] px-4 py-6 text-center text-sm font-semibold text-[#6d5242]" data-help-empty>
                Nenhuma acao encontrada para sua busca.
            </div>
        </div>
    </section>

    <div class="hidden fixed z-[75] rounded-2xl border-2 border-[#f59e0b] bg-transparent shadow-[0_0_0_4px_rgba(245,158,11,.18),0_18px_42px_rgba(20,10,8,.28)] transition-all before:absolute before:-right-2 before:-top-2 before:h-5 before:w-5 before:rounded-full before:border-4 before:border-white before:bg-[#f59e0b] before:shadow-lg" data-guide-highlight></div>
    <aside class="hidden fixed z-[76] w-[min(23rem,calc(100vw-2rem))] rounded-2xl border border-[#eadfce] bg-[#fffdf8] p-4 text-[#1d1513] shadow-[0_24px_70px_rgba(20,10,8,.28)]" data-guide-card aria-live="polite"></aside>

    <script>
        window.vozCifraGuias = @json(collect($acoesAjuda)->pluck('guia')->filter()->values(), JSON_UNESCAPED_UNICODE);

        document.addEventListener('DOMContentLoaded', () => {
            const painel = document.querySelector('[data-help-panel]');
            const abrir = document.querySelector('[data-help-open]');
            const fechar = document.querySelector('[data-help-close]');
            const busca = document.querySelector('[data-help-search]');
            const itens = Array.from(document.querySelectorAll('[data-help-item]'));
            const vazio = document.querySelector('[data-help-empty]');
            const guias = window.vozCifraGuias || [];
            const highlight = document.querySelector('[data-guide-highlight]');
            const card = document.querySelector('[data-guide-card]');
            const rotaAtual = '{{ Route::currentRouteName() }}';
            const urlAtual = window.location.href.split('#')[0];
            const storageKey = 'vozCifraGuiaPendente';
            let guiaAtual = null;
            let passoAtual = 0;

            const normalizarUrl = (url) => {
                try {
                    return new URL(url, window.location.origin).href.split('#')[0];
                } catch (error) {
                    return String(url || '').split('#')[0];
                }
            };

            const guiaEstaNaTela = (guia) => {
                if (!guia) {
                    return false;
                }

                if (guia.url) {
                    return normalizarUrl(guia.url) === urlAtual;
                }

                return guia.rota === rotaAtual;
            };

            const mostrarPainel = (mostrar) => {
                painel?.classList.toggle('hidden', !mostrar);
                if (mostrar) {
                    setTimeout(() => busca?.focus(), 50);
                }
            };

            const filtrar = () => {
                const termo = (busca?.value || '').trim().toLowerCase();
                let visiveis = 0;

                itens.forEach((item) => {
                    const combina = termo === '' || (item.dataset.helpSearchText || '').includes(termo);
                    item.classList.toggle('hidden', !combina);
                    if (combina) {
                        visiveis += 1;
                    }
                });

                vazio?.classList.toggle('hidden', visiveis > 0);
            };

            const encerrarGuia = () => {
                guiaAtual = null;
                passoAtual = 0;
                highlight?.classList.add('hidden');
                card?.classList.add('hidden');
                if (card) {
                    card.innerHTML = '';
                }
            };

            const posicionarCard = (rect) => {
                if (!card) {
                    return;
                }

                const margem = 16;
                const largura = Math.min(368, window.innerWidth - (margem * 2));
                let left = rect.right + margem;
                let top = rect.top;

                if (left + largura > window.innerWidth - margem) {
                    left = Math.max(margem, rect.left - largura - margem);
                }

                if (window.innerWidth < 768 || left < margem) {
                    left = margem;
                    top = Math.min(rect.bottom + margem, window.innerHeight - 230);
                }

                card.style.left = `${Math.max(margem, left)}px`;
                card.style.top = `${Math.max(margem, top)}px`;
                card.style.width = `${largura}px`;
            };

            const renderizarGuia = () => {
                if (!guiaAtual || !card || !highlight) {
                    return;
                }

                const passos = guiaAtual.passos || [];
                const passo = passos[passoAtual];

                if (!passo) {
                    encerrarGuia();
                    return;
                }

                const alvo = document.querySelector(passo.alvo);

                if (!alvo) {
                    passoAtual += 1;
                    renderizarGuia();
                    return;
                }

                alvo.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'smooth' });

                window.setTimeout(() => {
                    const rect = alvo.getBoundingClientRect();
                    const foco = passo.foco ? alvo.querySelector(passo.foco) || document.querySelector(passo.foco) : null;

                    if (foco && typeof foco.focus === 'function') {
                        foco.focus({ preventScroll: true });
                    }

                    highlight.classList.remove('hidden');
                    highlight.style.top = `${Math.max(8, rect.top - 8)}px`;
                    highlight.style.left = `${Math.max(8, rect.left - 8)}px`;
                    highlight.style.width = `${Math.min(window.innerWidth - 16, rect.width + 16)}px`;
                    highlight.style.height = `${Math.min(window.innerHeight - 16, rect.height + 16)}px`;

                    card.classList.remove('hidden');
                    card.innerHTML = `
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#8a5a1f]">${passoAtual + 1} de ${passos.length}</p>
                                <h3 class="mt-1 text-lg font-black text-[#1d1513]">${passo.titulo}</h3>
                            </div>
                            <button type="button" class="rounded-full border border-[#eadfce] px-3 py-2 text-sm font-bold text-[#5a3a1d]" data-guide-close aria-label="Fechar guia">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <p class="mt-3 text-sm leading-6 text-[#5f4a3d]">${passo.texto}</p>
                        <div class="mt-4 flex items-center justify-between gap-2">
                            <button type="button" class="rounded-xl border border-[#eadfce] px-3 py-2 text-sm font-black text-[#3d2a1e] disabled:opacity-40" data-guide-prev ${passoAtual === 0 ? 'disabled' : ''}>Voltar</button>
                            <button type="button" class="rounded-xl bg-[#7a501f] px-4 py-2 text-sm font-black text-white" data-guide-next>${passoAtual === passos.length - 1 ? 'Concluir' : 'Proximo'}</button>
                        </div>
                    `;

                    posicionarCard(rect);
                }, 220);
            };

            const iniciarGuia = (id) => {
                guiaAtual = guias.find((guia) => guia && guia.id === id) || null;
                passoAtual = 0;
                mostrarPainel(false);
                renderizarGuia();
            };

            abrir?.addEventListener('click', () => mostrarPainel(painel?.classList.contains('hidden')));
            fechar?.addEventListener('click', () => mostrarPainel(false));
            busca?.addEventListener('input', filtrar);

            itens.forEach((item) => {
                item.addEventListener('click', (event) => {
                    const guideId = item.dataset.guideId;
                    if (!guideId) {
                        return;
                    }

                    const guia = guias.find((registro) => registro && registro.id === guideId);

                    if (guia && guiaEstaNaTela(guia)) {
                        event.preventDefault();
                        iniciarGuia(guideId);
                        return;
                    }

                    if (guia) {
                        sessionStorage.setItem(storageKey, guideId);
                    }
                });
            });

            const guiaPendente = sessionStorage.getItem(storageKey);
            if (guiaPendente) {
                const guia = guias.find((registro) => registro && registro.id === guiaPendente);
                if (guia && guiaEstaNaTela(guia)) {
                    sessionStorage.removeItem(storageKey);
                    window.setTimeout(() => iniciarGuia(guiaPendente), 350);
                }
            }

            document.addEventListener('click', (event) => {
                if (event.target.closest('[data-guide-close]')) {
                    encerrarGuia();
                    return;
                }

                if (event.target.closest('[data-guide-next]')) {
                    if (guiaAtual && passoAtual < (guiaAtual.passos || []).length - 1) {
                        passoAtual += 1;
                        renderizarGuia();
                    } else {
                        encerrarGuia();
                    }
                    return;
                }

                if (event.target.closest('[data-guide-prev]') && passoAtual > 0) {
                    passoAtual -= 1;
                    renderizarGuia();
                }
            });

            window.addEventListener('resize', () => {
                if (guiaAtual) {
                    renderizarGuia();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    mostrarPainel(false);
                    encerrarGuia();
                }
            });
        });
    </script>
@endif

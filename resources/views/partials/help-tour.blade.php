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
                ['alvo' => '[data-guide-target="cifra-editor"]', 'foco' => '#letra_com_cifras', 'titulo' => 'Letra com cifra', 'texto' => 'Cole ou edite a cifra aqui. A letra deve continuar sendo a parte mais fácil de revisar.'],
                ['alvo' => '[data-guide-target="cifra-organizar"]', 'titulo' => 'Arrumar cifra', 'texto' => 'Depois de colar, use este botão para organizar acordes, partes e refrões.'],
                ['alvo' => '[data-guide-target="cifra-preview"]', 'titulo' => 'Prévia de leitura', 'texto' => 'Confira se a letra ficou clara para o músico antes de salvar.'],
                ['alvo' => '[data-guide-target="cifra-ferramentas"]', 'titulo' => 'Ajustes extras', 'texto' => 'Use apenas quando precisar inserir refrão, parte ou linha de acordes.'],
                ['alvo' => '[data-guide-target="cifra-tom-bpm"]', 'titulo' => 'Tom e BPM', 'texto' => 'Informe quando souber. Se não souber, a cifra ainda pode ser salva.'],
                ['alvo' => '[data-guide-target="cifra-youtube"]', 'titulo' => 'Vídeo de apoio', 'texto' => 'Opcional. Ajuda o músico a estudar a mesma versão.'],
            ],
        ];
    };
    $guiaCadastrarIgrejaAjuda = [
        'id' => 'cadastro-igreja',
        'rota' => 'admin.igrejas.create',
        'passos' => [
            ['alvo' => '[data-guide-target="igreja-imagem"]', 'foco' => '[name="imagem"]', 'titulo' => 'Imagem ou logo', 'texto' => 'Opcional, mas ajuda a comunidade a reconhecer a igreja nos cards e links públicos.'],
            ['alvo' => '[data-guide-target="igreja-nome"]', 'foco' => '[name="nome"]', 'titulo' => 'Nome da igreja', 'texto' => 'Digite o nome oficial ou o nome mais conhecido pela comunidade.'],
            ['alvo' => '[data-guide-target="igreja-cnpj"]', 'foco' => '[name="cnpj"]', 'titulo' => 'CNPJ', 'texto' => 'Informe o CNPJ para evitar cadastro duplicado e manter a identificação da igreja.'],
            ['alvo' => '[data-guide-target="igreja-telefone"]', 'foco' => '[name="telefone_secretaria"]', 'titulo' => 'Telefone da secretaria', 'texto' => 'Opcional. Use o contato oficial para fiéis e equipe musical terem uma referência confiável.'],
            ['alvo' => '[data-guide-target="igreja-cep"]', 'foco' => '[name="cep"]', 'titulo' => 'CEP', 'texto' => 'Se souber o CEP, preencha primeiro. O sistema pode ajudar a sugerir cidade, estado e endereço.'],
            ['alvo' => '[data-guide-target="igreja-estado"]', 'foco' => '[name="estado"]', 'titulo' => 'Estado', 'texto' => 'Use a sigla com duas letras, como MS, MT ou SP.'],
            ['alvo' => '[data-guide-target="igreja-endereco"]', 'foco' => '[name="endereco"]', 'titulo' => 'Endereço', 'texto' => 'Coloque rua, número e complemento. Esse texto aparece para quem abrir a página pública.'],
            ['alvo' => '[data-guide-target="igreja-cidade"]', 'foco' => '[name="cidade"]', 'titulo' => 'Cidade', 'texto' => 'A cidade ajuda na busca pública e na organização das igrejas cadastradas.'],
            ['alvo' => '[data-guide-target="igreja-admin-toggle"]', 'foco' => '[name="criar_admin_local_agora"]', 'titulo' => 'Administrador local', 'texto' => 'Marque apenas se a igreja já tiver uma pessoa responsável. Se não tiver, pode deixar para depois.'],
            ['alvo' => '[data-guide-target="igreja-admin-dados"]', 'foco' => '[name="admin_nome"]', 'titulo' => 'Dados do administrador', 'texto' => 'Se marcou administrador local, preencha nome, CPF, e-mail e telefone para enviar acesso seguro.'],
            ['alvo' => '[data-guide-target="igreja-salvar"]', 'titulo' => 'Salvar igreja', 'texto' => 'Revise os dados. Se o sistema avisar sobre igreja parecida, confira antes de confirmar.'],
        ],
    ];
    $guiaGerenciarUsuariosAjuda = [
        'id' => 'gerenciar-usuarios',
        'rota' => 'admin.usuarios.index',
        'passos' => [
            ['alvo' => '[data-guide-target="usuarios-resumo"]', 'titulo' => 'Comece pelos indicadores', 'texto' => 'Use estes cards para entender rapidamente quantos usuários existem por perfil, presença e vínculo. Eles também funcionam como atalhos de filtro.'],
            ['alvo' => '[data-guide-target="usuarios-busca"]', 'foco' => '[data-user-search-input]', 'titulo' => 'Busque a pessoa certa', 'texto' => 'Digite nome, e-mail, CPF, igreja ou cidade. Com poucas letras o sistema ja ajuda a encontrar sem precisar rolar a lista inteira.'],
            ['alvo' => '[data-guide-target="usuarios-tipo"]', 'foco' => '[name="tipo"]', 'titulo' => 'Filtre por perfil', 'texto' => 'Use tipo para separar admin master, coordenador, admin local, músico, padre ou usuários sem vínculo.'],
            ['alvo' => '[data-guide-target="usuarios-presenca"]', 'foco' => '[name="presenca"]', 'titulo' => 'Confira presença', 'texto' => 'Quando precisar dar suporte, filtre quem está online agora ou quem está offline.'],
            ['alvo' => '[data-guide-target="usuarios-filtrar"]', 'titulo' => 'Aplique os filtros', 'texto' => 'Depois de escolher os critérios, clique em buscar. Se o resultado ficar estreito demais, limpe os filtros e tente de novo.'],
            ['alvo' => '[data-guide-target="usuarios-lista"]', 'titulo' => 'Abra ou revise usuários', 'texto' => 'Na base de usuários você abre o cadastro, confere vínculos, status e ações de acesso de cada pessoa.'],
            ['alvo' => '[data-guide-target="usuarios-criar"]', 'titulo' => 'Cadastre quando não existir', 'texto' => 'Se a busca confirmar que a pessoa ainda não existe, use cadastrar usuário para criar a conta pelo fluxo guiado.'],
        ],
    ];
    $guiaAcordesAjuda = [
        'id' => 'consultar-acordes',
        'rota' => 'admin.acordes.index',
        'passos' => [
            ['alvo' => '[data-guide-target="acordes-busca"]', 'foco' => '[name="search"]', 'titulo' => 'Procure pelo acorde', 'texto' => 'Digite o nome do acorde ou parte da descrição para encontrar variações rapidamente.'],
            ['alvo' => '[data-guide-target="acordes-acoes"]', 'titulo' => 'Busque ou cadastre', 'texto' => 'Use buscar para filtrar a galeria. Se a posição ainda não existir, cadastre um novo desenho de acorde.'],
            ['alvo' => '[data-guide-target="acordes-galeria"]', 'titulo' => 'Revise a galeria', 'texto' => 'Cada card mostra o desenho, casa base e ações de visualizar, editar ou inativar o acorde.'],
        ],
    ];
    $guiaTemposLiturgicosAjuda = static fn (string $rota): array => [
        'id' => 'consultar-tempos-liturgicos-' . str_replace('.', '-', $rota),
        'rota' => $rota,
        'passos' => [
            ['alvo' => '[data-guide-target="tempos-cabecalho"]', 'titulo' => 'Entenda o catálogo', 'texto' => 'Tempos litúrgicos organizam períodos como Advento, Quaresma, Páscoa e Tempo Comum para classificar missas e músicas.'],
            ['alvo' => '[data-guide-target="tempos-lista"]', 'titulo' => 'Confira os tempos cadastrados', 'texto' => 'A lista mostra nome, descrição e status. Edite quando precisar ajustar texto ou inative quando não quiser mais usar.'],
            ['alvo' => '[data-guide-target="tempos-criar"]', 'titulo' => 'Cadastre um novo tempo', 'texto' => 'Use este botão apenas quando o tempo ainda não existir no catálogo.'],
        ],
    ];
    $guiaMomentosLiturgicosAjuda = static fn (string $rota): array => [
        'id' => 'consultar-momentos-liturgicos-' . str_replace('.', '-', $rota),
        'rota' => $rota,
        'passos' => [
            ['alvo' => '[data-guide-target="momentos-cabecalho"]', 'titulo' => 'Organize a ordem da missa', 'texto' => 'Momentos litúrgicos indicam onde cada música entra: entrada, ofertório, comunhão, final e outros pontos da celebração.'],
            ['alvo' => '[data-guide-target="momentos-lista"]', 'titulo' => 'Revise momentos cadastrados', 'texto' => 'Confira nome, descrição, ordem e status antes de editar ou inativar. A ordem ajuda a montar repertórios mais claros.'],
            ['alvo' => '[data-guide-target="momentos-criar"]', 'titulo' => 'Cadastre um momento novo', 'texto' => 'Crie outro momento quando sua igreja precisar de uma etapa que ainda não existe na lista.'],
        ],
    ];
    $guiaCadastrarMissaAjuda = [
        'id' => 'cadastrar-missa',
        'rota' => 'local-admin.missas.create',
        'passos' => [
            ['alvo' => '[data-guide-target="missa-cabecalho"]', 'titulo' => 'Crie a celebração', 'texto' => 'Aqui você cadastra a missa e depois segue para montar o repertório com as músicas.'],
            ['alvo' => '[data-guide-target="missa-reaproveitar"]', 'foco' => '[name="reaproveitar_repertorio"]', 'titulo' => 'Reaproveite quando fizer sentido', 'texto' => 'Se houver uma missa parecida em uma igreja que você administra, escolha uma opção para usar dados e repertório como ponto de partida.'],
            ['alvo' => '[data-guide-target="missa-titulo"]', 'foco' => '[name="titulo"]', 'titulo' => 'Nomeie a missa', 'texto' => 'Use um título que a equipe reconheça facilmente, como missa dominical da noite ou celebração do padroeiro.'],
            ['alvo' => '[data-guide-target="missa-data-tempo"]', 'foco' => '[name="data_missa"]', 'titulo' => 'Defina data e tempo', 'texto' => 'Informe a data e, se já souber, o tempo litúrgico. Isso ajuda na busca e na escolha das músicas.'],
            ['alvo' => '[data-guide-target="missa-horarios"]', 'foco' => '[name="hora_inicio"]', 'titulo' => 'Preencha os horários', 'texto' => 'Hora de início e término evitam conflito de celebrante e deixam a agenda clara para a equipe.'],
            ['alvo' => '[data-guide-target="missa-celebrante"]', 'foco' => '[name="padre_id"]', 'titulo' => 'Escolha o celebrante', 'texto' => 'Quando o padre estiver cadastrado, selecione aqui para o sistema validar possíveis conflitos no mesmo horário.'],
            ['alvo' => '[data-guide-target="missa-publicacao"]', 'titulo' => 'Controle a publicação', 'texto' => 'Defina se a missa fica ativa e se aparece para fiéis e músicos. Isso evita publicar algo antes da hora.'],
            ['alvo' => '[data-guide-target="missa-salvar"]', 'titulo' => 'Salve e monte o repertório', 'texto' => 'Depois de revisar, cadastre a missa. Em seguida você poderá adicionar as músicas na ordem correta.'],
        ],
    ];
    $guiaMontarRepertorioAjuda = [
        'id' => 'montar-repertorio-missa',
        'rota' => 'local-admin.missas.show',
        'passos' => [
            ['alvo' => '[data-guide-target="missa-repertorio-add"]', 'foco' => '#busca_musica', 'titulo' => 'Adicione a música', 'texto' => 'Busque pelo nome, artista ou trecho. Escolha a música, confirme momento, cifra e tom somente se precisar.'],
            ['alvo' => '[data-guide-target="missa-sequencia"]', 'titulo' => 'Confira a sequência', 'texto' => 'Depois de adicionar, use esta linha para ver a ordem dos cantos e abrir o item certo rapidamente.'],
            ['alvo' => '.repertorio-item-card:first-of-type', 'titulo' => 'Ajuste um item', 'texto' => 'Em cada música você pode subir, descer, visualizar a cifra ou abrir os ajustes de momento, cifra e tom.'],
            ['alvo' => '[data-guide-target="missa-conferencia"]', 'titulo' => 'Revise pendências', 'texto' => 'Abra esta área apenas quando quiser checar status, músicas sem cifra, momentos pendentes ou duplicidades.'],
            ['alvo' => '[data-guide-target="missa-acoes"]', 'titulo' => 'Publique com calma', 'texto' => 'Use os botões para ver como fiel, ver como músico, gerar PDF ou duplicar para outra igreja permitida.'],
        ],
    ];

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
                        'alvo' => '#mainContent [data-guide-target], #mainContent .admin-page-header, #mainContent section:first-of-type, #mainContent',
                        'titulo' => $titulo,
                        'texto' => $descricao !== '' ? $descricao : 'Esta é a área principal desta ação. Comece lendo o título e os avisos da tela.',
                    ],
                    [
                        'alvo' => '#mainContent [data-guide-target], #mainContent form, #mainContent table, #mainContent .grid',
                        'titulo' => 'Preencha ou filtre com calma',
                        'texto' => 'Use os campos, filtros e listas desta tela. Quando houver busca, digite poucas palavras e confira os resultados antes de salvar.',
                    ],
                    [
                        'alvo' => '#mainContent button[type="submit"], #mainContent a[href], #mainContent button',
                        'titulo' => 'Conclua a ação',
                        'texto' => 'Depois de revisar, use o botão principal da tela. Botões de inativar ou remover exigem mais atenção antes de confirmar.',
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
        $adicionarAcaoAjuda('Admin master', 'Cadastrar usuário', $urlAjuda('admin.usuarios.create') ?? '', 'fa-user-plus', ['pessoa', 'admin', 'músico', 'coordenador'], [
            'id' => 'cadastro-usuario',
            'rota' => 'admin.usuarios.create',
            'passos' => [
                ['alvo' => '[data-guide-target="usuario-tipo"]', 'foco' => '[data-tipo-cadastro]', 'titulo' => 'Escolha o perfil permitido', 'texto' => 'Admin master pode criar admin master, coordenador, admin local, músico e padre. Coordenador e admin local usam os fluxos próprios da igreja.'],
                ['alvo' => '[data-guide-target="usuario-igreja"]', 'foco' => '[data-igreja-filtro]', 'titulo' => 'Escolha a igreja inicial', 'texto' => 'Coordenador, admin local e músico precisam de igreja. Admin master não precisa. Padre pode ter igreja, mas não é obrigatório.'],
                ['alvo' => '[data-guide-target="usuario-dados"]', 'foco' => '[name="nome"]', 'titulo' => 'Digite o nome completo', 'texto' => 'Use o nome que a equipe reconhece. Isso ajuda na busca, nos chamados e na auditoria.'],
                ['alvo' => '[data-guide-target="usuario-cpf"]', 'foco' => '[name="cpf"]', 'titulo' => 'Informe o CPF', 'texto' => 'O CPF evita cadastro duplicado. Se a pessoa já existir, o sistema reaproveita a conta.'],
                ['alvo' => '[data-guide-target="usuario-email"]', 'foco' => '[name="email"]', 'titulo' => 'Informe o e-mail de acesso', 'texto' => 'Esse e-mail recebe o convite e a redefinição de senha. Padre sem login pode ficar em branco.'],
                ['alvo' => '[data-guide-target="usuario-telefone"]', 'foco' => '[name="telefone"]', 'titulo' => 'Adicione o telefone', 'texto' => 'Não é obrigatório, mas facilita contato e suporte quando a pessoa tiver dificuldade de acesso.'],
                ['alvo' => '[data-guide-target="usuario-acesso"]', 'foco' => '[name="enviar_convite"]', 'titulo' => 'Defina o acesso inicial', 'texto' => 'Deixe ativo para liberar a conta. Marque convite se quiser enviar o link de primeiro acesso agora.'],
                ['alvo' => '[data-guide-target="usuario-salvar"]', 'titulo' => 'Conclua o cadastro', 'texto' => 'Revise os dados e salve. Depois você pode ajustar papéis, ativar ou reenviar convite.'],
            ],
        ], 'Crie contas e atribua o primeiro perfil conforme a permissão correta.');
        $adicionarAcaoAjuda('Admin master', 'Gerenciar usuários', $urlAjuda('admin.usuarios.index') ?? '', 'fa-users-gear', ['perfis', 'papéis', 'acesso'], $guiaGerenciarUsuariosAjuda, 'Pesquise pessoas, edite dados, ative contas e reenvie convites.');
        $adicionarAcaoAjuda('Admin master', 'Ver hierarquia de usuários', $urlAjuda('admin.usuarios.hierarquia') ?? '', 'fa-sitemap', ['hierarquia', 'vínculos', 'perfis'], null, 'Confira como os usuários estão distribuídos por perfil e igreja.');
        $adicionarAcaoAjuda('Admin master', 'Cadastrar igreja', $urlAjuda('admin.igrejas.create') ?? '', 'fa-church', ['paroquia', 'comunidade'], $guiaCadastrarIgrejaAjuda, 'Crie uma igreja ou comunidade e depois vincule equipe local.');
        $adicionarAcaoAjuda('Admin master', 'Gerenciar igrejas', $urlAjuda('admin.igrejas.index') ?? '', 'fa-building-columns', ['dados', 'links', 'paroquia'], null, 'Edite dados, links públicos, coordenadores e admins locais das igrejas.');
        $adicionarAcaoAjuda('Admin master', 'Administrar admins locais', $urlAjuda('admin.admins-locais.index') ?? '', 'fa-user-shield', ['admin local', 'senha', 'acesso'], null, 'Acompanhe admins locais e envie novo link de senha quando precisar.');
        $adicionarAcaoAjuda('Admin master', 'Cadastrar música ou cifra', $urlAjuda('admin.musicas.create') ?? '', 'fa-music', ['música', 'cifra', 'versão'], null, 'Inclua músicas e crie versões de cifras para uso nas missas.');
        if ($guiaEditarCifraAjuda($rotaAtualAjuda)) {
            $adicionarAcaoAjuda('Admin master', 'Editar cifra passo a passo', $urlAtualAjuda, 'fa-wand-magic-sparkles', ['cifra', 'organizar', 'cifra club', 'refrão'], $guiaEditarCifraAjuda($rotaAtualAjuda), 'Cole, arrume automaticamente, revise a prévia e salve com segurança.');
        }
        $adicionarAcaoAjuda('Admin master', 'Consultar músicas', $urlAjuda('admin.musicas.index') ?? '', 'fa-magnifying-glass', ['biblioteca', 'cifra', 'tom'], null, 'Pesquise, revise e edite a biblioteca musical global.');
        $adicionarAcaoAjuda('Admin master', 'Ver acordes', $urlAjuda('admin.acordes.index') ?? '', 'fa-guitar', ['acordes', 'cifras', 'violão'], $guiaAcordesAjuda, 'Consulte e mantenha o dicionário de acordes usado nas cifras.');
        $adicionarAcaoAjuda('Admin master', 'Tempos litúrgicos', $urlAjuda('admin.tempos-liturgicos.index') ?? '', 'fa-calendar-days', ['liturgia', 'tempo'], $guiaTemposLiturgicosAjuda('admin.tempos-liturgicos.index'), 'Organize tempos como Advento, Quaresma, Páscoa e Tempo Comum.');
        $adicionarAcaoAjuda('Admin master', 'Momentos litúrgicos', $urlAjuda('admin.momentos-liturgicos.index') ?? '', 'fa-list-ol', ['entrada', 'comunhão', 'ofertório'], $guiaMomentosLiturgicosAjuda('admin.momentos-liturgicos.index'), 'Organize os momentos da celebração para montar repertórios com ordem.');
        $adicionarAcaoAjuda('Admin master', 'Auditoria', $urlAjuda('admin.auditoria.index') ?? '', 'fa-shield-halved', ['logs', 'segurança', 'histórico'], null, 'Veja registros importantes de alterações, acessos e ações administrativas.');
        $adicionarAcaoAjuda('Admin master', 'Configurações do sistema', $urlAjuda('admin.settings') ?? '', 'fa-gear', ['configurações', 'sistema'], null, 'Ajuste preferências e parâmetros administrativos do sistema.');
        $adicionarAcaoAjuda('Admin master', 'Ver chamados abertos', ($urlAjuda('admin.chamados.index') ?? '') . '?visao=atendimento', 'fa-headset', ['suporte', 'atendimento'], null, 'Atenda solicitações que ainda precisam de resposta da equipe.');
        $adicionarAcaoAjuda('Admin master', 'Ver chamados encerrados', ($urlAjuda('admin.chamados.index') ?? '') . '?visao=encerrados', 'fa-box-archive', ['resolvidos', 'fechados'], null, 'Consulte histórico de chamados resolvidos ou fechados.');
    }

    if ($usuarioAjuda && $usuarioAjuda->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igrejaAtivaIdAjuda)) {
        $adicionarAcaoAjuda('Admin local', 'Resumo da igreja', $urlAjuda('local-admin.dashboard') ?? '', 'fa-church', ['painel', 'igreja'], null, 'Veja a situação da igreja ativa e caminhos rápidos da rotina local.');
        $adicionarAcaoAjuda('Admin local', 'Cadastrar músico', $urlAjuda('local-admin.musicos.create') ?? '', 'fa-user-plus', ['usuário', 'equipe', 'perfil'], [
            'id' => 'cadastro-musico-local',
            'rota' => 'local-admin.musicos.create',
            'passos' => [
                ['alvo' => '[data-guide-target="musico-nome"]', 'foco' => '[name="nome"]', 'titulo' => 'Digite o nome do músico', 'texto' => 'Admin local cadastra apenas músicos da igreja ativa. Use o nome completo para achar a pessoa depois.'],
                ['alvo' => '[data-guide-target="musico-cpf"]', 'foco' => '[name="cpf"]', 'titulo' => 'Informe o CPF', 'texto' => 'O CPF impede duplicidade. Se a pessoa já existe, ela é vinculada como músico desta igreja.'],
                ['alvo' => '[data-guide-target="musico-email"]', 'foco' => '[name="email"]', 'titulo' => 'Informe o e-mail', 'texto' => 'O músico usa esse e-mail para acessar o painel, repertório e cifras.'],
                ['alvo' => '[data-guide-target="musico-igreja"]', 'titulo' => 'Confira a igreja', 'texto' => 'Neste fluxo a igreja já vem travada na igreja ativa do admin local.'],
                ['alvo' => '[data-guide-target="musico-acesso"]', 'foco' => '[name="enviar_convite"]', 'titulo' => 'Escolha o convite', 'texto' => 'Mantenha ativo e envie o convite se o músico já deve acessar agora.'],
                ['alvo' => '[data-guide-target="musico-salvar"]', 'titulo' => 'Salve o músico', 'texto' => 'Depois de salvar, ele aparece na equipe musical da igreja.'],
            ],
        ], 'Adicione músicos apenas na igreja ativa do admin local.');
        $adicionarAcaoAjuda('Admin local', 'Gerenciar equipe musical', $urlAjuda('local-admin.musicos.index') ?? '', 'fa-users', ['músicos', 'coordenadores'], null, 'Veja músicos da igreja, edite dados e envie acesso quando necessário.');
        $adicionarAcaoAjuda('Admin local', 'Montar uma missa', $urlAjuda('local-admin.missas.create') ?? '', 'fa-calendar-plus', ['celebração', 'repertório'], $guiaCadastrarMissaAjuda, 'Crie a celebração e depois monte o repertório com as músicas corretas.');
        if ($rotaAtualAjuda === 'local-admin.missas.show') {
            $adicionarAcaoAjuda('Admin local', 'Montar repertório desta missa', $urlAtualAjuda, 'fa-list-check', ['missa', 'repertório', 'músicas'], $guiaMontarRepertorioAjuda, 'Adicione músicas, confira a ordem e publique somente quando estiver pronto.');
        }
        $adicionarAcaoAjuda('Admin local', 'Ver missas cadastradas', $urlAjuda('local-admin.missas.index') ?? '', 'fa-calendar-check', ['repertório', 'publicar'], null, 'Acompanhe missas, edite repertórios e publique quando estiver pronto.');
        $adicionarAcaoAjuda('Admin local', 'Atualizar dados e links da igreja', $urlAjuda('local-admin.church') ?? '', 'fa-link', ['qr', 'público'], null, 'Atualize informações públicas, links e dados exibidos para a comunidade.');
    }

    if ($usuarioAjuda && $usuarioAjuda->temPapelNaIgreja(PapelIgreja::COORDENADOR, $igrejaAtivaIdAjuda)) {
        $adicionarAcaoAjuda('Coordenador', 'Painel da coordenação', $urlAjuda('coordenador.dashboard') ?? '', 'fa-diagram-project', ['painel', 'coordenação'], null, 'Veja atalhos da coordenação musical e admin local da igreja ativa.');
        $adicionarAcaoAjuda('Coordenador', 'Cadastrar músico', $urlAjuda('coordenador.musicos.create') ?? '', 'fa-user-plus', ['usuário', 'equipe'], [
            'id' => 'cadastro-musico-coordenador',
            'rota' => 'coordenador.musicos.create',
            'passos' => [
                ['alvo' => '[data-guide-target="musico-nome"]', 'foco' => '[name="nome"]', 'titulo' => 'Digite o nome do músico', 'texto' => 'Coordenador pode cadastrar músicos da igreja ativa e também atribuir admin local pelo fluxo da igreja.'],
                ['alvo' => '[data-guide-target="musico-cpf"]', 'foco' => '[name="cpf"]', 'titulo' => 'Informe o CPF', 'texto' => 'O CPF evita duplicar pessoas e permite reaproveitar um usuário já existente.'],
                ['alvo' => '[data-guide-target="musico-email"]', 'foco' => '[name="email"]', 'titulo' => 'Informe o e-mail', 'texto' => 'Esse e-mail será usado para convite, login e recuperação de senha.'],
                ['alvo' => '[data-guide-target="musico-igreja"]', 'titulo' => 'Confira a igreja ativa', 'texto' => 'O cadastro entra na igreja selecionada no topo do painel do coordenador.'],
                ['alvo' => '[data-guide-target="musico-acesso"]', 'foco' => '[name="enviar_convite"]', 'titulo' => 'Defina o acesso', 'texto' => 'Ativo libera o usuário. Convite envia o link de primeiro acesso com segurança.'],
                ['alvo' => '[data-guide-target="musico-salvar"]', 'titulo' => 'Salve o cadastro', 'texto' => 'Depois de salvar, o músico fica disponível para repertórios e rotinas da igreja.'],
            ],
        ], 'Adicione músicos da igreja ativa e envie convite de acesso.');
        $adicionarAcaoAjuda('Coordenador', 'Cadastrar admin local', ($urlAjuda('coordenador.dashboard') ?? '') . '#admin-local-form', 'fa-user-shield', ['usuario', 'gestao', 'igreja'], [
            'id' => 'cadastro-admin-local-coordenador',
            'rota' => 'coordenador.dashboard',
            'passos' => [
                ['alvo' => '[data-guide-target="atalho-admin-local"]', 'titulo' => 'Acesse pelo painel', 'texto' => 'O coordenador pode atribuir admin local apenas para a igreja ativa.'],
                ['alvo' => '[data-guide-target="admin-local-form"]', 'titulo' => 'Confira a igreja ativa', 'texto' => 'Este formulário não deixa escolher outra igreja. Assim evita cadastrar admin local no lugar errado.'],
                ['alvo' => '[data-guide-target="admin-local-nome"]', 'foco' => '[name="nome"]', 'titulo' => 'Digite o nome completo', 'texto' => 'Use o nome real da pessoa que vai cuidar da administração local.'],
                ['alvo' => '[data-guide-target="admin-local-cpf"]', 'foco' => '[name="cpf"]', 'titulo' => 'Informe o CPF', 'texto' => 'O CPF evita duplicar conta e ajuda o sistema a reaproveitar usuário existente.'],
                ['alvo' => '[data-guide-target="admin-local-email"]', 'foco' => '[name="email"]', 'titulo' => 'Informe o e-mail', 'texto' => 'Esse e-mail será usado para convite, login e recuperação de senha.'],
                ['alvo' => '[data-guide-target="admin-local-telefone"]', 'foco' => '[name="telefone"]', 'titulo' => 'Adicione o telefone', 'texto' => 'Opcional, mas ajuda no suporte quando houver dificuldade de acesso.'],
                ['alvo' => '[data-guide-target="admin-local-salvar"]', 'titulo' => 'Conclua com segurança', 'texto' => 'Ao salvar, a pessoa recebe papel de admin local somente nesta igreja.'],
            ],
        ], 'Atribua uma pessoa como admin local somente na igreja ativa.');
        $adicionarAcaoAjuda('Coordenador', 'Gerenciar equipe musical', $urlAjuda('coordenador.musicos.index') ?? '', 'fa-users', ['músicos', 'equipe'], null, 'Acompanhe músicos, vincule pessoas existentes e gerencie acessos.');
        $adicionarAcaoAjuda('Coordenador', 'Cadastrar música ou cifra', $urlAjuda('coordenador.musicas.create') ?? '', 'fa-music', ['biblioteca', 'versão'], null, 'Inclua novas músicas e cifras para a biblioteca da igreja.');
        if ($guiaEditarCifraAjuda($rotaAtualAjuda)) {
            $adicionarAcaoAjuda('Coordenador', 'Editar cifra passo a passo', $urlAtualAjuda, 'fa-wand-magic-sparkles', ['cifra', 'organizar', 'cifra club', 'refrão'], $guiaEditarCifraAjuda($rotaAtualAjuda), 'Cole, arrume automaticamente, revise a prévia e salve com segurança.');
        }
        $adicionarAcaoAjuda('Coordenador', 'Consultar biblioteca', $urlAjuda('coordenador.musicas.index') ?? '', 'fa-magnifying-glass', ['músicas', 'cifras', 'tom'], null, 'Pesquise e edite músicas, cifras e versões cadastradas.');
        $adicionarAcaoAjuda('Coordenador', 'Organizar tempos litúrgicos', $urlAjuda('coordenador.tempos-liturgicos.index') ?? '', 'fa-calendar-days', ['liturgia', 'tempo'], $guiaTemposLiturgicosAjuda('coordenador.tempos-liturgicos.index'), 'Mantenha tempos litúrgicos organizados para classificar repertórios.');
        $adicionarAcaoAjuda('Coordenador', 'Organizar momentos litúrgicos', $urlAjuda('coordenador.momentos-liturgicos.index') ?? '', 'fa-list-ol', ['entrada', 'comunhão', 'final'], $guiaMomentosLiturgicosAjuda('coordenador.momentos-liturgicos.index'), 'Defina momentos da missa para orientar a montagem do repertório.');
        if ($rotaAtualAjuda === 'local-admin.missas.show') {
            $adicionarAcaoAjuda('Coordenador', 'Montar repertório desta missa', $urlAtualAjuda, 'fa-list-check', ['missa', 'repertório', 'músicas'], $guiaMontarRepertorioAjuda, 'Adicione músicas, confira a ordem e publique somente quando estiver pronto.');
        }
        $adicionarAcaoAjuda('Coordenador', 'Ver chamados abertos', ($urlAjuda('coordenador.chamados.index') ?? '') . '?visao=atendimento', 'fa-headset', ['suporte', 'atendimento'], null, 'Atenda pedidos ligados a músicos e rotina da coordenação.');
        $adicionarAcaoAjuda('Coordenador', 'Ver chamados encerrados', ($urlAjuda('coordenador.chamados.index') ?? '') . '?visao=encerrados', 'fa-box-archive', ['resolvidos', 'fechados'], null, 'Consulte chamados já resolvidos para histórico e acompanhamento.');
    }

    if ($usuarioAjuda && $usuarioAjuda->temPapelNaIgreja(PapelIgreja::MUSICO, $igrejaAtivaIdAjuda)) {
        $adicionarAcaoAjuda('Músico', 'Painel do músico', $urlAjuda('member.dashboard') ?? '', 'fa-house', ['painel', 'início'], null, 'Veja próximas tarefas, repertório e atalhos do seu perfil.');
        $adicionarAcaoAjuda('Músico', 'Ver repertório', $urlAjuda('member.repertorio') ?? '', 'fa-list-check', ['missa', 'ensaio'], null, 'Acompanhe as músicas preparadas para a missa ou celebração.');
        $adicionarAcaoAjuda('Músico', 'Consultar músicas', $urlAjuda('member.musicas.index') ?? '', 'fa-magnifying-glass', ['cifra', 'tom'], null, 'Pesquise cifras e versões disponíveis na biblioteca.');
        $adicionarAcaoAjuda('Músico', 'Meus estudos', $urlAjuda('member.colecoes.index') ?? '', 'fa-book-open-reader', ['coleção', 'favoritos'], null, 'Organize músicas para estudo pessoal e preparação.');
        $adicionarAcaoAjuda('Músico', 'Abrir chamado de suporte', $urlAjuda('member.chamados.create') ?? '', 'fa-circle-plus', ['problema', 'ajuda'], null, 'Peça ajuda quando tiver dificuldade de acesso, repertório ou uso do sistema.');
        $adicionarAcaoAjuda('Músico', 'Acompanhar meus chamados', $urlAjuda('member.chamados.index') ?? '', 'fa-message', ['suporte', 'resposta'], null, 'Veja respostas da equipe e histórico dos seus pedidos.');
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
            justify-content: center;
            border: 1px solid rgba(214, 173, 108, .45);
            border-radius: 999px;
            background: #1f1514;
            color: #fff8ed;
            width: 3.15rem;
            height: 3.15rem;
            padding: 0;
            font-weight: 800;
            box-shadow: 0 18px 40px rgba(20, 10, 8, .28);
        }

        .help-actions-launcher span {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
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
                width: 2.95rem;
                height: 2.95rem;
            }

            .help-actions-panel {
                inset: auto .75rem 4.75rem .75rem;
                width: auto;
            }
        }
    </style>

    <button type="button" class="help-actions-launcher" data-help-open aria-haspopup="dialog" aria-controls="helpActionsPanel" aria-label="Abrir ajuda guiada">
        <i class="fa-solid fa-circle-question"></i>
        <span>Ajuda</span>
    </button>

    <section id="helpActionsPanel" class="help-actions-panel hidden" data-help-panel aria-label="Ajuda por busca">
        <div class="sticky top-0 z-10 border-b border-[#eadfce] bg-[#fffdf8] px-5 py-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#8a5a1f]">Ajuda</p>
                    <h2 class="mt-1 text-lg font-black text-[#1d1513]">O que voc&ecirc; quer fazer?</h2>
                </div>
                <button type="button" class="rounded-full border border-[#eadfce] px-3 py-2 text-sm font-bold text-[#5a3a1d]" data-help-close aria-label="Fechar ajuda">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <label class="mt-4 flex items-center gap-2 rounded-2xl border border-[#eadfce] bg-white px-3 py-2">
                <i class="fa-solid fa-magnifying-glass text-[#8a5a1f]"></i>
                <input type="search" class="min-w-0 flex-1 border-0 bg-transparent text-sm text-[#1d1513] outline-none" placeholder="Buscar: missa, usuário, chamado..." data-help-search>
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
                        <span class="block text-sm font-black">{{ $acaoAjuda['titulo'] }}</span>
                        @if ($acaoAjuda['descricao'] !== '')
                            <span class="mt-1 block text-xs leading-5 text-[#6d5242]">{{ $acaoAjuda['descricao'] }}</span>
                        @endif
                    </span>
                </a>
            @endforeach

            <div class="hidden rounded-2xl border border-dashed border-[#eadfce] px-4 py-6 text-center text-sm font-semibold text-[#6d5242]" data-help-empty>
                Nenhuma ação encontrada para sua busca.
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

            const guiaTemAlvoDisponivel = (guia) => {
                return Boolean((guia?.passos || []).find((passo) => passo?.alvo && document.querySelector(passo.alvo)));
            };

            const guiaContextual = () => {
                return guias.find((guia) => guiaEstaNaTela(guia) && guiaTemAlvoDisponivel(guia)) || null;
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
                            <button type="button" class="rounded-xl bg-[#7a501f] px-4 py-2 text-sm font-black text-white" data-guide-next>${passoAtual === passos.length - 1 ? 'Concluir' : 'Próximo'}</button>
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

            abrir?.addEventListener('click', () => {
                const guia = guiaContextual();

                if (guia) {
                    iniciarGuia(guia.id);
                    return;
                }

                mostrarPainel(painel?.classList.contains('hidden'));
            });
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

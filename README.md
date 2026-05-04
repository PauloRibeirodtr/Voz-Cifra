# Voz & Cifra

![Versao](https://img.shields.io/badge/versao-v0.9.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Neon%20Ready-336791)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4-06B6D4)
![Vite](https://img.shields.io/badge/Vite-7-646CFF)
![Status](https://img.shields.io/badge/status-em%20refinamento-brightgreen)

Sistema web em Laravel 12 para organizar igrejas, usuarios, musicas, cifras, acordes, missas e repertorios. O projeto trabalha com administracao central, operacao por igreja e acesso publico separado para fieis e musicos.

## Visao Geral

A versao atual de referencia e `v0.9.0`.

O sistema atende quatro contextos principais:

- `admin_master`: gestao central do sistema.
- `admin_local`: administracao operacional de uma ou mais igrejas.
- `coordenador`: apoio operacional na equipe musical da igreja.
- `member` / musico: estudo, biblioteca musical e consulta de repertorio.

Um mesmo usuario pode acumular papeis na mesma igreja ou em igrejas diferentes. O painel usa a igreja ativa da sessao para manter missas, musicos, repertorios e links no contexto correto.

## O Que Funciona Hoje

- Login, logout e primeiro acesso com troca obrigatoria de senha.
- Regras de acesso por perfil global e papeis por igreja.
- Admin master com gestao de igrejas, usuarios, papeis, musicas, acordes, tempos e momentos liturgicos.
- Admin local com dados da igreja, links publicos, QR Codes, equipe musical, missas e repertorio.
- Coordenador com acesso operacional a equipe e conteudo musical permitido.
- Musico com perfil, biblioteca musical, repertorio da igreja e colecoes de estudo.
- Cadastro e edicao de musicas base e versoes musicais com cifra.
- Biblioteca visual de acordes com editor de shape.
- Tons maiores e menores padronizados no arquivo `config/musical.php`.
- Cadastro de missas com publicacao para fieis e musicos.
- Reaproveitamento de missa anterior com dados principais e copia de repertorio.
- Montagem de repertorio com momento liturgico, versao musical, ordem e tom usado na missa.
- Conclusao da montagem da missa redirecionando para a lista de missas.
- PDF completo da missa.
- Pagina publica da igreja para fieis.
- Pagina publica da igreja para musicos, com link separado.
- Home publica com busca/listagem de igrejas.
- Paginas de erro personalizadas.
- Locale padrao `pt_BR` e paginacao em portugues.

## Tecnologias

- PHP 8.2+
- Laravel 12
- PostgreSQL / Neon
- Blade
- Tailwind CSS 4
- Vite
- DomPDF

## Perfis E Papeis

### Admin Master

Responsavel pela administracao central:

- painel central
- igrejas
- usuarios e vinculos por igreja
- papeis acumulados por usuario
- auditoria
- acordes
- tempos e momentos liturgicos
- musicas e versoes musicais
- configuracoes

### Admin Local

Responsavel pela operacao da igreja ativa:

- resumo da igreja
- dados e links publicos
- equipe musical
- coordenacao musical
- cadastro e edicao de missas
- montagem de repertorios
- publicacao para fieis e musicos
- PDF e visualizacao da missa

### Coordenador

Responsavel por apoiar a organizacao musical:

- equipe musical
- cadastro/consulta musical conforme permissao
- apoio na rotina da igreja ativa

### Musico / Member

Responsavel por estudar e consultar o repertorio:

- perfil e acesso
- repertorio da igreja
- biblioteca musical
- versoes com cifras
- colecoes de estudo

## Fluxos Principais

### Igreja Ativa

Usuarios com acesso a mais de uma igreja podem trocar a igreja ativa. As telas operacionais usam esse contexto para listar e cadastrar dados da igreja certa.

### Usuarios E Papeis

O admin master pode criar usuarios, editar dados da conta, ativar/inativar, resetar senha e ajustar papeis por igreja. Ao selecionar uma igreja no formulario, os papeis atuais aparecem marcados e podem ser concedidos ou removidos.

### Missas E Repertorio

O admin local cadastra a missa, define dados principais, escolhe se publica para fieis e/ou musicos e monta o repertorio.

No cadastro de missa, e possivel reaproveitar uma missa anterior da mesma igreja. Ao selecionar a missa anterior, o formulario preenche:

- titulo
- data
- horario de inicio e termino
- tempo liturgico
- celebrante
- observacoes
- resumo das musicas copiadas

Depois de salvar, o sistema copia o repertorio anterior como ponto de partida.

### Links Publicos

Cada igreja possui:

- link publico dos fieis
- link publico dos musicos
- QR Code dos fieis
- QR Code dos musicos

O link dos fieis mostra a experiencia publica simplificada. O link dos musicos abre a visualizacao voltada para repertorio e cifras quando a missa estiver publicada para musicos.

### Musicas, Versoes E Acordes

A musica base guarda letra limpa e classificacao. A versao musical guarda cifra, tom, BPM, video e apoio de execucao.

A biblioteca de acordes funciona como apoio visual e dicionario de shapes. O sistema tambem usa a configuracao musical para validar tons maiores e menores.

## Estrutura Principal

```text
app/
  Enums/
  Http/
    Controllers/
      Admin/
      Auth/
      LocalAdmin/
      Member/
      Publico/
    Middleware/
  Models/
  Rules/
  Services/
config/
database/
  migrations/
  seeders/
lang/
  pt_BR/
public/
resources/
  views/
    admin/
    auth/
    errors/
    local-admin/
    member/
    publico/
routes/
  web.php
tests/
```

## Modelo de Dados

Esta secao documenta o modelo relacional usado pelo Voz & Cifra como base para DER, diagrama de casos de uso e documentacao academica do projeto. O mapeamento foi feito a partir das migrations em `database/migrations` e dos Models em `app/Models`.

### Tabelas principais

#### `igrejas`

- Finalidade: armazena as comunidades/igrejas cadastradas e seus links publicos.
- Campos principais: `nome`, `slug`, `slug_publico_musicos`, `cnpj`, `cep`, `endereco`, `numero`, `bairro`, `cidade`, `estado`, `imagem_path`, `status_operacional`, `ativo`.
- Chave primaria: `id`.
- Chaves estrangeiras: nenhuma.
- Observacoes: `slug` identifica a pagina publica dos fieis; `slug_publico_musicos` identifica a pagina publica dos musicos; `cnpj` e dado administrativo e a unicidade foi removida por migration posterior; `status_operacional` indica se a igreja ja possui operacao local liberada.

#### `usuarios`

- Finalidade: armazena contas de acesso, dados de contato e perfil global.
- Campos principais: `igreja_id`, `nome`, `cpf`, `email`, `telefone`, `foto_perfil_path`, `password`, `perfil_global`, `nivel_global`, `eh_padre`, `ativo`, `primeiro_acesso`, `theme_preference`.
- Chave primaria: `id`.
- Chaves estrangeiras: `igreja_id` referencia `igrejas.id`.
- Observacoes: `cpf` e `email` sao unicos; `password` e armazenado com hash; `igreja_id` permanece como campo legado/principal, mas o vinculo oficial multi-igreja e feito por `usuario_igreja`.

#### `usuario_igreja`

- Finalidade: representa o vinculo entre um usuario e uma igreja.
- Campos principais: `usuario_id`, `igreja_id`, `ativo`, `responsavel_principal`, `vinculado_em`, `desvinculado_em`.
- Chave primaria: `id`.
- Chaves estrangeiras: `usuario_id` referencia `usuarios.id`; `igreja_id` referencia `igrejas.id`.
- Observacoes: possui unicidade por `usuario_id` + `igreja_id`; permite que um usuario atue em mais de uma igreja e que uma igreja tenha varios usuarios.

#### `usuario_igreja_papeis`

- Finalidade: controla os papeis operacionais de um vinculo usuario-igreja.
- Campos principais: `usuario_igreja_id`, `papel`, `ativo`, `origem`, `concedido_por`, `revogado_por`, `concedido_em`, `revogado_em`.
- Chave primaria: `id`.
- Chaves estrangeiras: `usuario_igreja_id` referencia `usuario_igreja.id`; `concedido_por` e `revogado_por` referenciam `usuarios.id`.
- Observacoes: um mesmo vinculo pode acumular `admin_local`, `coordenador` e `musico`; a combinacao `usuario_igreja_id` + `papel` e unica.

#### `tempos_liturgicos`

- Finalidade: cadastra tempos liturgicos usados na classificacao de musicas e missas.
- Campos principais: `nome`, `descricao`, `ativo`.
- Chave primaria: `id`.
- Chaves estrangeiras: nenhuma.
- Observacoes: `nome` e unico.

#### `momentos_liturgicos`

- Finalidade: cadastra momentos da celebracao, como entrada, salmo, comunhao e final.
- Campos principais: `nome`, `descricao`, `ordem_exibicao`, `ativo`.
- Chave primaria: `id`.
- Chaves estrangeiras: nenhuma.
- Observacoes: `nome` e unico e `ordem_exibicao` apoia a ordenacao visual.

#### `musicas`

- Finalidade: armazena a musica base, com letra limpa e classificacao liturgica.
- Campos principais: `titulo`, `artista`, `letra`, `momento_liturgico_id`, `tempo_liturgico_id`, `criado_por`, `ativo`.
- Chave primaria: `id`.
- Chaves estrangeiras: `momento_liturgico_id` referencia `momentos_liturgicos.id`; `tempo_liturgico_id` referencia `tempos_liturgicos.id`; `criado_por` referencia `usuarios.id`.
- Observacoes: a musica base pode ter varias melodias, varias versoes musicais e aparecer em varias missas.

#### `melodias`

- Finalidade: registra variantes melodicas de uma musica.
- Campos principais: `musica_id`, `nome`, `descricao`, `ativo`.
- Chave primaria: `id`.
- Chaves estrangeiras: `musica_id` referencia `musicas.id`.
- Observacoes: a combinacao `musica_id` + `nome` e unica.

#### `acordes`

- Finalidade: armazena o dicionario visual de acordes.
- Campos principais: `nome`, `descricao`, `dados_diagrama`, `ativo`.
- Chave primaria: `id`.
- Chaves estrangeiras: nenhuma.
- Observacoes: `dados_diagrama` e JSON e representa o desenho do acorde.

#### `versoes_musicais`

- Finalidade: armazena versoes cifradas de uma musica.
- Campos principais: `musica_id`, `melodia_id`, `titulo`, `tom_musical`, `bpm`, `youtube_video_id`, `letra_com_cifras`, `criado_por`, `ativo`.
- Chave primaria: `id`.
- Chaves estrangeiras: `musica_id` referencia `musicas.id`; `melodia_id` referencia `melodias.id`; `criado_por` referencia `usuarios.id`.
- Observacoes: uma musica pode ter varias versoes; a versao pode ser usada em repertorios de missa e colecoes de estudo.

#### `missas`

- Finalidade: armazena celebracoes/missas cadastradas por igreja.
- Campos principais: `igreja_id`, `celebrante_usuario_id`, `tempo_liturgico_id`, `titulo`, `data_missa`, `hora_inicio`, `hora_fim`, `observacoes`, `publica_para_fieis`, `publica_para_musicos`, `ativo`.
- Chave primaria: `id`.
- Chaves estrangeiras: `igreja_id` referencia `igrejas.id`; `celebrante_usuario_id` referencia `usuarios.id`; `tempo_liturgico_id` referencia `tempos_liturgicos.id`.
- Observacoes: possui indices para consultas publicas de fieis e musicos; pode atravessar a madrugada conforme horario de termino.

#### `missa_musicas`

- Finalidade: tabela associativa que monta o repertorio de uma missa.
- Campos principais: `missa_id`, `musica_id`, `versao_musical_id`, `tom_usado`, `momento_liturgico_id`, `ordem`.
- Chave primaria: `id`.
- Chaves estrangeiras: `missa_id` referencia `missas.id`; `musica_id` referencia `musicas.id`; `versao_musical_id` referencia `versoes_musicais.id`; `momento_liturgico_id` referencia `momentos_liturgicos.id`.
- Observacoes: a combinacao `missa_id` + `ordem` e unica; `tom_usado` permite tocar em outro tom sem alterar o tom original da versao.

#### `colecoes_estudo`

- Finalidade: armazena playlists/colecoes pessoais de estudo de um usuario.
- Campos principais: `usuario_id`, `nome`.
- Chave primaria: `id`.
- Chaves estrangeiras: `usuario_id` referencia `usuarios.id`.
- Observacoes: cada colecao pertence a um unico usuario.

#### `colecao_estudo_itens`

- Finalidade: tabela associativa entre colecoes, musicas e versoes musicais.
- Campos principais: `colecao_estudo_id`, `musica_id`, `versao_musical_id`.
- Chave primaria: `id`.
- Chaves estrangeiras: `colecao_estudo_id` referencia `colecoes_estudo.id`; `musica_id` referencia `musicas.id`; `versao_musical_id` referencia `versoes_musicais.id`.
- Observacoes: a combinacao `colecao_estudo_id` + `versao_musical_id` e unica para evitar duplicidade da mesma versao na mesma colecao.

#### `auditoria_eventos`

- Finalidade: registra eventos relevantes de seguranca, gestao e operacao.
- Campos principais: `protocolo`, `evento`, `categoria`, `ator_id`, `ator_nome`, `ator_funcao`, `alvo_id`, `alvo_nome`, `alvo_email`, `igreja_id`, `igreja_nome`, `contexto`, `resultado`, `notificacao_enviada_em`, `erro_envio`, `ip`, `user_agent`.
- Chave primaria: `id`.
- Chaves estrangeiras: `ator_id` e `alvo_id` referenciam `usuarios.id`; `igreja_id` referencia `igrejas.id`.
- Observacoes: guarda dados de contexto em JSON e mantem historico mesmo quando usuario/igreja forem removidos, por meio de campos textuais de apoio.

#### `feedbacks`

- Finalidade: registra opinioes, erros, sugestoes e elogios enviados por usuarios.
- Campos principais: `usuario_id`, `igreja_id`, `tipo`, `mensagem`, `status`.
- Chave primaria: `id`.
- Chaves estrangeiras: `usuario_id` referencia `usuarios.id`; `igreja_id` referencia `igrejas.id`.
- Observacoes: `tipo` aceita `critica`, `sugestao`, `ajuste` e `elogio`; `status` aceita `novo`, `lido` e `arquivado`.

#### `chamados`

- Finalidade: armazena atendimentos de suporte.
- Campos principais: `protocolo`, `auditoria_evento_id`, `titulo`, `descricao`, `status`, `prioridade`, `categoria`, `canal_origem`, `origem_tipo`, `origem_id`, `solicitante_usuario_id`, `responsavel_usuario_id`, `igreja_id`, `ultima_interacao_em`, `resolvido_em`, `fechado_em`, `resolucao_resumo`, `avaliacao_nota`, `avaliacao_comentario`.
- Chave primaria: `id`.
- Chaves estrangeiras: `auditoria_evento_id` referencia `auditoria_eventos.id`; `solicitante_usuario_id` e `responsavel_usuario_id` referenciam `usuarios.id`; `igreja_id` referencia `igrejas.id`.
- Observacoes: `status` controla o ciclo do atendimento; `origem_tipo` + `origem_id` permitem associar o chamado a um ponto de origem do sistema.

#### `chamado_mensagens`

- Finalidade: registra o historico de conversa de um chamado.
- Campos principais: `chamado_id`, `autor_usuario_id`, `autor_nome`, `origem`, `canal`, `interno`, `mensagem`.
- Chave primaria: `id`.
- Chaves estrangeiras: `chamado_id` referencia `chamados.id`; `autor_usuario_id` referencia `usuarios.id`.
- Observacoes: mensagens podem ser de `usuario`, `suporte` ou `sistema`; `interno` permite separar notas administrativas.

#### `historico_envios_email`

- Finalidade: registra tentativas e resultados de envio de e-mails do sistema.
- Campos principais: `usuario_id`, `auditoria_evento_id`, `origem_tipo`, `origem_id`, `destinatario_email`, `destinatario_nome`, `tipo_email`, `assunto`, `status_envio`, `mensagem_retorno`, `mensagem_id_provedor`, `mailer`, `payload`, `enviado_em`.
- Chave primaria: `id`.
- Chaves estrangeiras: `usuario_id` referencia `usuarios.id`; `auditoria_evento_id` referencia `auditoria_eventos.id`.
- Observacoes: `payload` e JSON; `status_envio` aceita `pendente`, `enviado`, `falhou` e `cancelado`.

#### Tabelas tecnicas do Laravel

- `password_reset_tokens`: tokens de redefinicao de senha por e-mail.
- `sessions`: sessoes de usuarios quando `SESSION_DRIVER=database`.
- `cache` e `cache_locks`: armazenamento de cache e locks.
- `jobs`, `job_batches` e `failed_jobs`: filas, lotes e falhas de jobs.

### Relacionamentos

| Origem | Relacionamento | Destino | Tipo | Tabela intermediaria | Observacao |
|---|---|---|---|---|---|
| `usuarios` | pertence a varias | `igrejas` | N:N | `usuario_igreja` | Usuario pode atuar em mais de uma igreja |
| `igrejas` | possui varios | `usuarios` | N:N | `usuario_igreja` | Igreja pode ter varios usuarios vinculados |
| `usuario_igreja` | possui varios | `usuario_igreja_papeis` | 1:N | - | Um vinculo pode acumular varios papeis |
| `usuario_igreja_papeis` | e concedido/revogado por | `usuarios` | N:1 | - | Guarda quem concedeu ou revogou o papel |
| `igrejas` | possui varias | `missas` | 1:N | - | Cada igreja possui suas missas |
| `usuarios` | pode celebrar varias | `missas` | 1:N | - | Relacao opcional pelo campo `celebrante_usuario_id` |
| `tempos_liturgicos` | classifica varias | `missas` | 1:N | - | Tempo liturgico da celebracao |
| `tempos_liturgicos` | classifica varias | `musicas` | 1:N | - | Tempo liturgico sugerido para a musica |
| `momentos_liturgicos` | classifica varias | `musicas` | 1:N | - | Momento sugerido da musica |
| `momentos_liturgicos` | classifica varios itens | `missa_musicas` | 1:N | - | Momento usado no repertorio da missa |
| `usuarios` | cria varias | `musicas` | 1:N | - | Campo `criado_por` |
| `usuarios` | cria varias | `versoes_musicais` | 1:N | - | Campo `criado_por` |
| `musicas` | possui varias | `melodias` | 1:N | - | Variantes melodicas da musica |
| `musicas` | possui varias | `versoes_musicais` | 1:N | - | Uma musica pode ter varias cifras/versoes |
| `melodias` | possui varias | `versoes_musicais` | 1:N | - | Relacao opcional da versao com uma melodia |
| `missas` | possui varias musicas | `musicas` | N:N | `missa_musicas` | Define repertorio e ordem da missa |
| `missa_musicas` | usa opcionalmente | `versoes_musicais` | N:1 | - | Define qual cifra foi usada na missa |
| `usuarios` | possui varias | `colecoes_estudo` | 1:N | - | Colecoes pessoais do musico |
| `colecoes_estudo` | possui varias musicas | `musicas` | N:N | `colecao_estudo_itens` | Playlist/colecao de estudo |
| `colecao_estudo_itens` | aponta para | `versoes_musicais` | N:1 | - | Item guarda a versao estudada |
| `usuarios` | abre varios | `chamados` | 1:N | - | Campo `solicitante_usuario_id` |
| `usuarios` | atende varios | `chamados` | 1:N | - | Campo `responsavel_usuario_id` |
| `igrejas` | possui varios | `chamados` | 1:N | - | Chamado pode estar ligado a uma igreja |
| `chamados` | possui varias | `chamado_mensagens` | 1:N | - | Historico do atendimento |
| `usuarios` | escreve varias | `chamado_mensagens` | 1:N | - | Autor da mensagem |
| `usuarios` | gera/recebe varios | `auditoria_eventos` | 1:N | - | Campos `ator_id` e `alvo_id` |
| `igrejas` | aparece em varios | `auditoria_eventos` | 1:N | - | Evento pode estar vinculado a uma igreja |
| `auditoria_eventos` | origina varios | `historico_envios_email` | 1:N | - | Historico de notificacoes por e-mail |
| `usuarios` | possui varios | `historico_envios_email` | 1:N | - | E-mails destinados ao usuario |
| `usuarios` | envia varios | `feedbacks` | 1:N | - | Feedback pode ser anonimo quando `usuario_id` for nulo |
| `igrejas` | recebe varios | `feedbacks` | 1:N | - | Feedback pode estar ligado a uma igreja |

### Regras de cardinalidade

- Um usuario pode ter varios vinculos com igrejas.
- Uma igreja pode ter varios usuarios vinculados.
- Um vinculo usuario-igreja pode ter varios papeis operacionais.
- Um papel operacional pertence a exatamente um vinculo usuario-igreja.
- Uma missa pertence a uma igreja.
- Uma igreja pode possuir varias missas.
- Uma missa pode ter varias musicas.
- Uma musica pode estar em varias missas.
- Uma musica pode ter varias melodias.
- Uma musica pode ter varias versoes musicais.
- Uma versao musical pertence a uma musica e pode estar vinculada a uma melodia.
- Uma colecao de estudo pertence a um usuario.
- Uma colecao pode ter varios itens.
- Um item de colecao liga a colecao a uma musica e a uma versao musical.
- Um chamado pode ter varias mensagens.
- Um chamado pode ter solicitante, responsavel e igreja vinculados.
- Um evento de auditoria pode ter ator, alvo e igreja, mas tambem preserva nomes/e-mails para historico.

### Tabelas pivo

#### `usuario_igreja`

Existe porque a relacao entre usuarios e igrejas e N:N. Um usuario pode participar de mais de uma igreja e uma igreja pode ter varios usuarios. A tabela tambem guarda dados do vinculo, como status ativo, igreja principal e datas de vinculacao/desvinculacao.

#### `usuario_igreja_papeis`

Existe para permitir que um mesmo vinculo usuario-igreja acumule varios papeis. Em vez de guardar apenas um tipo de acesso no usuario, o sistema registra cada papel de forma independente, com historico de concessao e revogacao.

#### `missa_musicas`

Existe porque a relacao entre missas e musicas e N:N. A tabela define a ordem do repertorio, o momento liturgico usado, a versao musical escolhida e o tom executado naquela missa.

#### `colecao_estudo_itens`

Existe porque uma colecao pode reunir varias musicas/versoes e uma mesma musica pode aparecer em colecoes diferentes. A tabela tambem evita duplicar a mesma versao dentro da mesma colecao.

### Base para DER

Para construir o DER, considerar:

- Entidades principais: `usuarios`, `igrejas`, `musicas`, `versoes_musicais`, `missas`, `colecoes_estudo`, `chamados`.
- Entidades de apoio: `tempos_liturgicos`, `momentos_liturgicos`, `melodias`, `acordes`.
- Entidades associativas: `usuario_igreja`, `usuario_igreja_papeis`, `missa_musicas`, `colecao_estudo_itens`.
- Entidades de controle e historico: `auditoria_eventos`, `historico_envios_email`, `feedbacks`, `chamado_mensagens`.
- Atributos principais: usar os campos listados em "Tabelas principais" como base para o diagrama.
- Cardinalidades centrais: `usuarios` N:N `igrejas`; `usuario_igreja` 1:N `usuario_igreja_papeis`; `igrejas` 1:N `missas`; `missas` N:N `musicas`; `musicas` 1:N `versoes_musicais`; `usuarios` 1:N `colecoes_estudo`; `colecoes_estudo` N:N `musicas`.
- Campos opcionais: marcar como opcionais os campos `nullable` das migrations, como celebrante da missa, classificacoes liturgicas da musica, responsavel do chamado e vinculos de auditoria.
- Observacao de compatibilidade: `usuarios.igreja_id` aparece no schema e nos Models como referencia legada/principal; para o DER atual, o relacionamento multi-igreja deve ser representado por `usuario_igreja`.

### Base para Casos de Uso

#### Admin Master

- Gerenciar igrejas.
- Gerenciar usuarios.
- Vincular usuarios a igrejas.
- Conceder e revogar papeis por igreja.
- Gerenciar musicas, acordes, tempos e momentos liturgicos.
- Acompanhar auditoria, feedbacks, avisos e chamados.
- Redefinir acesso e controlar status de contas.

#### Admin Local

- Gerenciar dados da igreja ativa.
- Administrar links publicos e QR Codes.
- Cadastrar e editar missas.
- Montar repertorios.
- Publicar missa para fieis e/ou musicos.
- Acompanhar equipe musical da igreja.

#### Coordenador

- Apoiar a equipe musical da igreja ativa.
- Consultar e organizar conteudo musical permitido.
- Apoiar repertorios e rotina musical conforme papel recebido.

#### Musico

- Atualizar perfil e acesso.
- Consultar repertorio da igreja.
- Estudar cifras e versoes musicais.
- Criar colecoes de estudo.
- Consultar biblioteca musical.
- Abrir e acompanhar chamados/feedbacks.

#### Fiel/Visitante publico

- Acessar a home publica.
- Buscar igreja por nome/cidade.
- Abrir pagina publica da igreja.
- Consultar celebracoes publicadas para fieis.
- Acessar links/QR Codes compartilhados pela comunidade.

### Observacoes de conferencia

- Os relacionamentos principais dos Models batem com as chaves estrangeiras das migrations.
- O Model `TelegramVinculo` aponta para a tabela `telegram_vinculos`, mas nao foi encontrada migration correspondente em `database/migrations`. Se a integracao Telegram for usada em producao, a migration dessa tabela precisa existir no historico do banco.
- A tabela `feedbacks` existe nas migrations, mas nao ha Model dedicado em `app/Models` nesta versao analisada.

## Rotas Principais

- `/` home publica.
- `/login` login.
- `/admin/*` painel admin master.
- `/igreja/*` painel admin local.
- `/coordenador/*` area do coordenador.
- `/musico/*` area do musico.
- `/{slug}` pagina publica dos fieis.
- `/{slug}/musicos` pagina publica dos musicos.

## Como Rodar O Projeto

### 1. Configurar `.env`

Exemplo para PostgreSQL / Neon:

```env
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

DB_CONNECTION=pgsql
DB_HOST=SEU_HOST_NEON
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
DB_SCHEMA=public
DB_SSLMODE=require

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax
```

### 2. Instalar Dependencias

```bash
composer install
npm install
```

### 3. Preparar Aplicacao

```bash
php artisan key:generate
php artisan migrate --seed
```

### 4. Limpar Caches

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 5. Subir Localmente

```bash
php artisan serve
```

Acesso local:

```text
http://127.0.0.1:8000
```

## Usuario Inicial

O `admin_master` e criado pelo `AdminMasterSeeder`.

Padrao atual:

- email: `admin@ministeriomusical.com`
- senha: definida por `ADMIN_MASTER_PASSWORD` ou `admin123456`
- CPF padrao fake: `00000000000`

O seeder tambem atualiza uma conta existente quando encontra o mesmo email/CPF, garantindo o perfil global de admin master e nivel adequado.

## Testes

```bash
php artisan test
```

Tambem existem testes de referencia para a area publica de fieis e musicos em `tests/Feature/Publico`.

## Observacoes Tecnicas

- `usuarios` pode ter perfil global e papeis por igreja.
- `usuario_igreja` representa o vinculo da conta com a igreja.
- `usuario_igreja_papeis` controla papeis ativos/revogados no vinculo.
- `missa_musicas.tom_usado` define o tom tocado naquela missa sem sobrescrever o tom original da versao.
- A missa pode atravessar a madrugada; o encerramento considera data e horario final reais.
- O cadastro de missa limita datas em uma janela segura.
- A pagina publica dos fieis nao mostra cifras.
- A pagina publica dos musicos depende da publicacao para musicos.
- O QR Code usa links fixos baseados no slug da igreja.
- A aplicacao usa `pt_BR` como locale padrao.

## Modulos Ainda Em Evolucao

- telemetria e metricas administrativas
- monitoramento de usuarios online
- notificacoes administrativas por grupo/igreja
- painel de auditoria mais acionavel
- versoes musicais exclusivas por igreja
- biblioteca de arquivos por igreja
- testes automatizados mais amplos para papeis, missas e repertorio

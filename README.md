# Voz & Cifra

![Versao](https://img.shields.io/badge/versao-v0.8.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Neon%20Ready-336791)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4-06B6D4)
![Vite](https://img.shields.io/badge/Vite-7-646CFF)
![Status](https://img.shields.io/badge/status-admin_master%2C%20admin_local%20e%20member%20funcionais-brightgreen)

Sistema web em Laravel 12 para organizacao do ministerio musical e do nucleo liturgico, com cadastro centralizado, operacao por perfis e evolucao por etapas sem abrir o fluxo para cadastro publico.

## Visao geral

A versao atual de referencia do projeto e `v0.8.0`.

Nesta etapa, o sistema ja atende tres perfis reais de uso:

- `admin_master` para administracao central
- `admin_local` para operacao da igreja e das missas
- `member` para leitura, estudo e acompanhamento do repertorio da propria igreja

Hoje o projeto ja permite:

- autenticar `admin_master`, `admin_local` e `member`
- manter o fluxo de primeiro acesso com troca obrigatoria de senha
- aplicar regra de senha forte na troca ou definicao manual da senha
- administrar igrejas, administradores locais e outros `admin_master`
- administrar musicos por igreja e no painel central
- manter biblioteca de acordes, tempos liturgicos e momentos liturgicos
- cadastrar musicas base e versoes musicais com cifras
- montar missas e repertorios por igreja
- aceitar missa que atravessa a madrugada com encerramento automatico correto
- limitar datas de missa a uma janela segura de 1 mes para tras e 1 mes para frente
- validar tom musical e `tom_usado` com formato compativel com a leitura e transposicao
- visualizar cifras no contexto da missa
- gerar PDF da missa com repertorio e blocos de cifra
- permitir que o musico estude musicas e versoes fora de uma missa especifica
- permitir que o musico organize playlists / colecoes de estudo
- exibir a missa publica sem cifras no link fixo da igreja

## Tecnologias

- PHP 8.2+
- Laravel 12
- PostgreSQL / Neon
- Blade
- Tailwind CSS 4
- Vite
- DomPDF

## Perfis do sistema

### `admin_master`

Administrador central do sistema.

Responsabilidades atuais:

- dashboard central
- configuracoes e perfil
- cadastro de outros `admin_master`
- gestao de igrejas e administradores locais
- gestao global de musicos
- gestao de padres
- gestao da biblioteca de acordes
- gestao de tempos e momentos liturgicos
- gestao de musicas base
- gestao de versoes musicais com cifra

### `admin_local`

Administrador vinculado a uma igreja.

Responsabilidades atuais:

- painel da igreja
- perfil proprio
- visualizacao da propria igreja
- link publico fixo e QR Code da igreja
- criacao, edicao e ativacao de missas
- montagem e reordenacao do repertorio
- escolha de versao musical por item do repertorio
- definicao do `tom_usado` na missa sem sobrescrever o tom original da versao
- visualizacao com cifra
- modo de apresentacao
- PDF da missa com cifras
- gestao dos musicos da propria igreja

### `member`

Musico / membro vinculado a uma igreja.

Responsabilidades atuais:

- login e logout
- troca obrigatoria de senha no primeiro acesso
- painel proprio
- perfil proprio
- acesso ao repertorio da igreja
- acesso a biblioteca musical para estudo
- visualizacao completa de versoes musicais com cifras
- uso do sistema para leitura e treino sem depender sempre de uma missa especifica

## O que ja funciona

### Autenticacao e seguranca

- login de `admin_master`, `admin_local` e `member`
- logout nas areas autenticadas
- protecao de rotas por perfil
- middleware de primeiro acesso para todos os perfis autenticados
- troca obrigatoria de senha no primeiro acesso
- regra de senha forte com validacao no backend
- medidor visual de forca da senha nas telas relevantes
- limitacao de tentativas no login com bloqueio temporario
- mensagens genericas para falha de credenciais
- regeneracao de sessao no login e invalidacao segura no logout
- headers de seguranca nas respostas HTTP
- ajuste de `session secure cookie` e `same site` no `.env.example`

### Painel do admin master

- dashboard inicial
- acessos rapidos para os modulos centrais
- perfil
- configuracoes
- alteracao de email, telefone e senha
- cadastro de outros `admin_master`
- reset de senha de `admin_local` com obrigacao de troca no proximo acesso
- encerramento de sessao por `Configuracoes`
- tema por usuario com preferencia persistida
- modelagem futura do `admin_master` nivel `7` em `docs/modelagem-admin-master-nivel-7.md`

### Area do admin local

- painel inicial da igreja
- visualizacao dos dados da propria igreja
- link publico fixo e QR da igreja
- perfil proprio do administrador local
- navegacao responsiva da area da igreja

### Missas e repertorio da igreja

- listar missas da propria igreja
- criar missa
- reaproveitar repertorio de missa anterior da mesma igreja ao criar nova missa
- editar missa
- ativar e desativar missa
- desativacao automatica de missa encerrada pelo horario
- suporte a missa atravessando a madrugada
- validacao de janela de datas para evitar agendamentos absurdos
- montar repertorio com a base central de musicas
- buscar musica por titulo, artista ou trecho da letra
- associar momento liturgico ao item do repertorio
- vincular versao musical existente ao item
- definir `tom_usado` por item da missa
- validar `tom_usado` com formato de acorde / tom reconhecido
- preservar o tom original da versao musical
- reordenar repertorio com seguranca
- visualizacao com cifra por item
- modo de apresentacao da missa em sequencia
- transposicao visual de tom para leitura
- controles de fonte e auto rolagem nas telas de leitura
- gerar PDF da missa com repertorio e cifra

### Musicos

- listar musicos
- cadastrar musico
- editar musico
- ativar e inativar musico
- excluir musico
- resetar senha do musico com obrigacao de troca no proximo acesso
- restringir o `admin_local` aos musicos da propria igreja
- permitir ao `admin_master` visualizar e administrar musicos de todas as igrejas
- painel inicial do `member`
- repertorio da igreja para o `member`
- biblioteca musical para estudo do `member`
- visualizacao de cifras completas fora da missa
- playlists / colecoes de estudo pessoais
- logout acessivel nas areas autenticadas

### Igrejas

- listar igrejas
- cadastrar igreja
- cadastrar `admin_local` junto com a igreja
- editar igreja
- editar dados do administrador local vinculado
- redefinir senha do `admin_local` a partir da gestao da igreja
- gerar e manter `slug` unico por igreja
- preparar link publico fixo por igreja
- preparar QR Code fixo apontando para o link publico
- permitir ao `admin_master` visualizar link publico e QR da igreja

### Padres

- listar padres
- criar padre
- editar padre
- ativar e inativar padre
- vincular padre opcionalmente a uma igreja
- manter padre apenas como cadastro administrativo, sem login e sem painel

### Acordes

- listar acordes
- criar acorde
- visualizar acorde
- editar acorde
- excluir acorde
- editor visual de shape

### Tempos liturgicos

- listar
- criar
- editar
- excluir

### Momentos liturgicos

- listar
- criar
- editar
- excluir

### Musicas

- listar musica base
- criar musica base
- visualizar musica
- editar musica
- excluir musica
- validacao para impedir cifras na letra base
- formulario guiado para reduzir erro de cadastro
- redirecionamento da musica base para a criacao da versao musical

### Versoes musicais

- criar versao vinculada a uma musica
- visualizar versao
- editar versao
- excluir versao
- editor com `letra_com_cifras`
- formato oficial com colchetes
- aceite de cifra com colchetes
- aceite de cifra estilo Cifra Club
- conversao automatica para o formato interno
- extracao de acordes
- validacao permissiva contra a biblioteca de acordes
- preview admin
- preview musico
- preview sem cifra
- video do YouTube embutido quando houver `youtube_video_id`
- auto rolagem
- metronomo simples baseado no BPM
- dicionario lateral de acordes com shape
- validacao de tom musical no backend

### Area publica por igreja

- rota publica fixa por `slug`
- pagina publica inicial da igreja
- sincronizacao automatica de estado da missa publica
- contagem regressiva da proxima missa
- estado automatico de celebracao em andamento
- exibicao da missa publica sem cifras para o fiel
- controles de fonte na tela publica
- estrutura pronta para o QR Code fixo da igreja
- favicon compartilhado com a logo principal da aplicacao

## Atualizacoes recentes da versao `v0.8.0`

- senha forte padronizada nos fluxos de perfil, reset e criacao manual
- medidor de forca da senha nas telas de senha manual
- middleware unico de primeiro acesso para `admin_master`, `admin_local` e `member`
- painel do `member` ampliado com repertorio e biblioteca musical
- visualizacao individual de versao musical para estudo do musico
- suporte a `tom_usado` no contexto da missa
- PDF da missa preparado para repertorio com cifra
- melhoria da leitura musical para `admin_local` e `member`
- suporte a missa atravessando a madrugada com encerramento correto
- limitacao de datas absurdas ao cadastrar missa
- validacao de tom musical e `tom_usado`
- contagem publica mantida apenas para inicio da proxima missa
- navegacao principal refinada com logo clicavel e cards de metricas clicaveis
- tema por usuario com preferencia persistida

## Modulos fora do escopo atual

Ainda nao foram abertos nesta etapa:

- biblioteca propria de arquivos por igreja
- versao musical exclusiva por igreja
- transposicao completa de tom com persistencia da cifra transposta por contexto
- experiencia publica final completa da missa por igreja em tempo real
- telemetria / analytics de acesso publico por igreja
- monitoramento de usuarios online com painel administrativo dedicado

## O que saiu do fluxo

- cadastro publico
- verificacao por codigo
- Telegram

## Estrutura principal

```text
app/
  Http/
    Controllers/
      Admin/
      LocalAdmin/
      Member/
    Middleware/
  Models/
  Rules/
  Services/
database/
  migrations/
  seeders/
resources/
  views/
    admin/
    local-admin/
    member/
    publico/
routes/
  web.php
```

## Como rodar o projeto

### 1. Configurar o `.env`

Exemplo para PostgreSQL / Neon:

```env
DB_CONNECTION=pgsql
DB_HOST=SEU_HOST_NEON
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
DB_SCHEMA=public
DB_SSLMODE=require
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax
```

Para desenvolvimento local com a configuracao atual de exemplo:

```env
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

### 3. Preparar a aplicacao

```bash
php artisan key:generate
php artisan migrate --seed
```

Se voce estiver atualizando uma base que ja existia antes desta versao, confirme que a migration abaixo foi executada:

```text
database/migrations/2026_03_30_000018_add_tom_usado_to_missa_musicas_table.php
```

### 4. Limpar caches

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 5. Subir localmente

```bash
php artisan serve
```

Acesso local:

```text
http://127.0.0.1:8000
```

## Usuario inicial

O `admin_master` e criado via seeder.

Padrao atual:

- email: `admin@ministeriomusical.com`
- senha: definida por `ADMIN_MASTER_PASSWORD` ou `admin123456`
- cpf padrao fake: `00000000000`

## Observacoes tecnicas

- `musicas` e `versoes_musicais` estao separadas por responsabilidade.
- A musica base guarda letra limpa e classificacao liturgica.
- A versao musical guarda cifra, tom, BPM e apoio de execucao.
- `missa_musicas.tom_usado` define o tom usado naquela missa sem sobrescrever `versoes_musicais.tom_musical`.
- O sistema valida `tom_musical` e `tom_usado` com formato compativel com acordes e transposicao.
- O cadastro de missas aceita celebracoes que atravessam a madrugada.
- A desativacao automatica da missa considera o horario real de termino, inclusive quando a celebracao termina no dia seguinte.
- O cadastro de missas limita a data a uma janela de 1 mes para tras e 1 mes para frente.
- Hoje nao existe relacao persistida entre `versoes_musicais` e `acordes`.
- A biblioteca de acordes funciona como apoio visual, validacao e dicionario.
- O modulo de `padres` e apenas administrativo; padre nao entra no sistema como usuario.
- O modulo de `musicos` usa o perfil `member` como usuario do sistema, sempre vinculado a uma igreja.
- O link publico da igreja e fixo e baseado no `slug`.
- O QR Code da igreja pode apontar para esse link fixo.
- A pagina publica da igreja identifica automaticamente a proxima missa e a celebracao em andamento.
- A contagem publica aparece apenas para o inicio da proxima missa.
- A pagina de login informa preventivamente o bloqueio temporario por excesso de tentativas invalidas.
- A aplicacao usa a logo principal como favicon nas areas principais.
- O PDF da missa agora pode carregar repertorio com cifra, respeitando o tom exibido da missa.
- O projeto ja possui preferencia de tema por usuario.

## Proximos passos recomendados

- revisar visualmente as telas novas do `member` com dados reais
- aplicar a migration de `tom_usado` em todos os ambientes
- adicionar PDF individual por musica ou versao, se fizer sentido para uso da igreja
- criar testes automatizados para primeiro acesso, senha forte e fluxo musical do `member`
- evoluir o dashboard do `admin_master` para um painel de pendencias do sistema

# Voz & Cifra

![Versao](https://img.shields.io/badge/versao-v0.9.0-blue)
![Status](https://img.shields.io/badge/status-em%20refinamento-brightgreen)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Neon%20Ready-336791)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4-06B6D4)
![Vite](https://img.shields.io/badge/Vite-7-646CFF)
![Laravel Cloud](https://img.shields.io/badge/deploy-Laravel%20Cloud-fb503b)

**Voz & Cifra** e uma plataforma web feita em Laravel para organizar a rotina musical de igrejas: usuarios, igrejas, papeis, musicas, cifras, acordes, missas, repertorios, paginas publicas, notificacoes internas, suporte e auditoria.

O projeto esta rodando em **Laravel Cloud** e usa Laravel 12 com Blade, Tailwind CSS, Vite e banco relacional PostgreSQL.

## Tecnologias

- PHP 8.2+
- Laravel 12
- Blade
- Tailwind CSS 4
- Vite 7
- PostgreSQL / Neon
- Laravel DomPDF
- Flysystem AWS S3 V3 para armazenamento externo quando configurado
- PHPUnit para testes automatizados
- Laravel Cloud para deploy

## Objetivo do Sistema

O sistema centraliza informacoes que normalmente ficam espalhadas entre mensagens, arquivos, folhas impressas e conversas da equipe musical.

Com ele, a igreja consegue:

- cadastrar musicas e cifras;
- montar repertorios de missa;
- organizar momentos liturgicos;
- liberar links publicos para fieis e musicos;
- controlar papeis por igreja;
- registrar auditoria;
- acompanhar chamados;
- avisar usuarios por notificacoes internas;
- permitir estudo musical com tom, capotraste, fonte, acordes e auto rolagem.

## Perfis Atendidos

### Admin Master

Perfil central do sistema. Gerencia:

- igrejas;
- usuarios;
- vinculos e papeis por igreja;
- musicas e versoes musicais;
- acordes;
- tempos liturgicos;
- momentos liturgicos;
- chamados;
- auditoria;
- avisos internos;
- configuracoes da propria conta.

### Admin Local

Perfil de administracao de uma igreja especifica. Atua sobre a igreja ativa da sessao:

- painel da igreja;
- dados publicos e links;
- equipe e papeis;
- missas;
- repertorio;
- visualizacao da missa;
- PDFs;
- chamados relacionados a rotina da igreja.

### Coordenador

Perfil de apoio operacional da equipe musical:

- consulta e apoio ao repertorio;
- acompanhamento de musicos;
- acesso conforme os papeis vinculados a igreja.

### Musico

Perfil voltado para estudo e execucao musical:

- painel do musico;
- biblioteca musical;
- repertorio da igreja;
- modo estudo da cifra;
- auto rolagem;
- metronomo;
- tom e capotraste;
- dicionario de acordes;
- colecoes de estudo;
- sugestao de mudanca de tom no repertorio;
- chamados de suporte.

### Publico

Area aberta para consulta:

- pagina publica da igreja;
- missas publicadas para fieis;
- repertorio publicado para musicos;
- historico de celebracoes quando disponivel;
- links e QR Codes publicos.

## Funcionalidades Principais

### Autenticacao e Acesso

- Login e logout.
- Primeiro acesso com definicao de senha.
- Usuario pode ter vinculo com mais de uma igreja.
- Usuario pode acumular papeis na mesma igreja.
- Igreja ativa na sessao para separar corretamente os dados operacionais.
- Preferencia de tema: sistema, claro ou escuro.
- Preferencia para receber ou nao avisos gerais por e-mail.

### Usuarios, Igrejas e Papeis

- Cadastro e edicao de usuarios.
- Vinculo de usuario com igreja.
- Papeis por igreja: admin local, coordenador e musico.
- Ativacao e inativacao de contas.
- Reenvio de convite de acesso.
- Notificacoes internas quando papeis ou acessos mudam.

### Musicas e Cifras

- Cadastro de musica base.
- Cadastro de versoes musicais com cifra.
- Tom musical padronizado.
- BPM.
- Video de apoio do YouTube.
- Normalizacao de cifras coladas.
- Previa da cifra no cadastro.
- Visualizacao admin, visualizacao de musico e versao sem cifra.
- Dicionario de acordes com desenhos.

### Missas e Repertorio

- Cadastro de missas por igreja.
- Data, horario, celebrante, tempo liturgico e observacoes.
- Publicacao separada para fieis e musicos.
- Janela de cadastro: ate 1 mes para tras e ate 3 meses para frente.
- Reativacao de missa futura ate 3 meses para frente.
- Reaproveitamento de missa anterior quando existir missa da mesma igreja.
- Montagem do repertorio por momento liturgico.
- Ordem dos cantos na missa.
- Tom usado apenas naquele item do repertorio.
- Pedido de mudanca de tom pelo musico.
- Aprovacao ou recusa de mudanca de tom por quem tem permissao.
- Notificacao interna para pedidos de tom.

### Estudo Musical

- Modo estudo da cifra.
- Transposicao de tom.
- Capotraste.
- Ajuste de fonte.
- Auto rolagem com velocidade.
- Metronomo.
- Dicionario de acordes.
- Video de apoio.
- Impressao e PDF.

### Paginas Publicas

- Pagina publica da igreja para fieis.
- Pagina publica para musicos quando a missa estiver liberada.
- Links publicos por slug.
- QR Codes para acesso rapido.
- Home publica com busca/listagem de igrejas.

### Notificacoes, Suporte e Auditoria

- Central de notificacoes internas no sininho.
- Marcar aviso como lido.
- Marcar todos como lidos.
- Avisos direcionais para alteracao de papeis, conta e pedidos de tom.
- Chamados de suporte.
- Mensagens em chamados.
- Status, prioridade e avaliacao do atendimento.
- Auditoria de acoes administrativas e operacionais.
- Historico de envio de e-mail.

## Estrutura do Projeto

```text
app/
  Http/
    Controllers/
      Admin/
      Auth/
      Coordenador/
      LocalAdmin/
      Member/
      Publico/
  Models/
  Services/
config/
database/
  migrations/
  seeders/
public/
  js/
resources/
  css/
  js/
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

## Rotas Principais

- `/` home publica.
- `/login` login.
- `/admin/*` area do admin master.
- `/igreja/*` area do admin local.
- `/coordenador/*` area do coordenador.
- `/musico/*` area do musico.
- `/{slug}` pagina publica da igreja para fieis.
- `/{slug}/musicos` pagina publica da igreja para musicos.

## Como Rodar Localmente

### 1. Instalar dependencias

```bash
composer install
npm install
```

### 2. Configurar ambiente

Crie o `.env` a partir do `.env.example` e configure banco, app key, filas, cache e e-mail conforme o ambiente.

Exemplo de banco PostgreSQL:

```env
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

DB_CONNECTION=pgsql
DB_HOST=SEU_HOST
DB_PORT=5432
DB_DATABASE=SEU_BANCO
DB_USERNAME=SEU_USUARIO
DB_PASSWORD=SUA_SENHA
DB_SCHEMA=public
DB_SSLMODE=require

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

### 3. Preparar aplicacao

```bash
php artisan key:generate
php artisan migrate --seed
npm run build
```

### 4. Rodar localmente

```bash
php artisan serve
```

Acesse:

```text
http://127.0.0.1:8000
```

## Usuario Inicial

O usuario `admin_master` e criado pelo `AdminMasterSeeder`.

Por padrao, o e-mail pode vir de `ADMIN_MASTER_EMAIL`. Se a senha inicial nao for definida por variavel de ambiente, o fluxo usa convite/primeiro acesso conforme configuracao do sistema.

## Testes

Rodar toda a suite:

```bash
php artisan test
```

Rodar build de frontend:

```bash
npm run build
```

## Deploy

O projeto esta sendo executado em **Laravel Cloud**.

Para deploy, manter atenção em:

- variaveis de ambiente;
- conexao PostgreSQL;
- migrations;
- build de assets com Vite;
- configuracao de e-mail;
- configuracao de armazenamento externo, caso use S3 ou compativel.

## Documentacao Visual

As imagens e evidencias do sistema serao adicionadas depois em uma pasta de documentacao, por exemplo:

```text
docs/
  imagens/
  fluxos/
```

Essa parte deve conter prints reais das telas, fluxos de usuario e exemplos de uso.

## Observacoes Tecnicas

- O tom original da versao musical nao e sobrescrito pelo repertorio.
- O campo `missa_musicas.tom_usado` guarda o tom usado naquela missa.
- A sugestao de mudanca de tom cria uma solicitacao vinculada ao item do repertorio.
- Aprovacao de tom atualiza apenas aquela musica naquela missa.
- Notificacoes internas importantes continuam existindo mesmo se o usuario desligar avisos gerais por e-mail.
- A pagina publica dos fieis deve ser mais simples que a area do musico.
- A area do musico concentra os controles musicais: tom, capotraste, fonte, acordes, metronomo e auto rolagem.
- A aplicacao usa `pt_BR` como locale padrao.

## Status Atual

O sistema esta em fase de refinamento de usabilidade, responsividade, organizacao de frontend e fluxos guiados de ajuda.

Proximos pontos naturais:

- melhorar a experiencia publica mobile;
- padronizar guias de ajuda por tela;
- ampliar testes automatizados de papeis e vinculos;
- organizar ainda mais CSS e JavaScript por modulo;
- adicionar documentacao visual em `docs/`.

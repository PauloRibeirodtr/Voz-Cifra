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

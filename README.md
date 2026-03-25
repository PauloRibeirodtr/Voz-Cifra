# Voz & Cifra

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Neon%20Ready-336791)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4-06B6D4)
![Vite](https://img.shields.io/badge/Vite-7-646CFF)
![Status](https://img.shields.io/badge/status-admin_master%20funcional-brightgreen)

Sistema web em Laravel 12 para organizacao do ministerio musical e do nucleo liturgico, com cadastro centralizado, fluxo fechado e evolucao por etapas.

## Visao geral

O projeto foi estruturado como um sistema administrativo fechado.

Nesta etapa, o foco esta no `admin_master`, responsavel pelos cadastros centrais, configuracoes do sistema e organizacao inicial da base.

Hoje o projeto ja permite:

- autenticar o `admin_master`
- administrar igrejas e administradores locais
- manter biblioteca de acordes
- manter tempos e momentos liturgicos
- cadastrar musicas base
- cadastrar versoes musicais com cifras

## Tecnologias

- PHP 8.2+
- Laravel 12
- PostgreSQL / Neon
- Blade
- Tailwind CSS 4
- Vite

## Perfis do sistema

- `admin_master`
  Administrador central do sistema.
- `admin_local`
  Administrador vinculado a uma igreja.
- `member`
  Musico / membro da igreja.

## O que ja funciona

### Autenticacao

- login do `admin_master`
- logout
- protecao de rotas administrativas

### Painel do admin master

- dashboard inicial
- perfil
- configuracoes
- alteracao de email, telefone e senha

### Igrejas

- listar igrejas
- cadastrar igreja
- cadastrar `admin_local` junto com a igreja
- editar igreja
- editar dados do administrador local vinculado

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

### Seeders centrais

- `AdminMasterSeeder`
- `TempoLiturgicoSeeder`
- `MomentoLiturgicoSeeder`
- `AcordeSeeder`

## Modulos fora do escopo atual

Ainda nao foram abertos nesta etapa:

- fluxo real do `admin_local`
- fluxo real do `member`
- modulo de padres
- modulo de missas
- parte publica por igreja
- QR Code para pagina publica da igreja

## O que saiu do fluxo

- cadastro publico
- verificacao por codigo
- Telegram

## Estrutura principal

```text
app/
  Http/
    Controllers/Admin/
  Models/
  Services/
database/
  migrations/
  seeders/
resources/
  views/admin/
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
```

Para desenvolvimento local:

```env
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

### 3. Preparar a aplicacao

```bash
php artisan key:generate
php artisan migrate:fresh --seed
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
http://127.0.0.1:8000/login
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
- Hoje nao existe relacao persistida entre `versoes_musicais` e `acordes`.
- A biblioteca de acordes funciona como apoio visual, validacao e dicionario.
- O foco atual do projeto esta fechado no `admin_master`.

## Proxima etapa

O proximo passo natural do projeto e abrir o fluxo do `admin_local`, com funcoes especificas por igreja e administracao dos musicos vinculados.

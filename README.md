# Event Invite System

Micro sistema completo de cadastro e envio de convites para eventos.

**Stack:** PHP 8.2+ · MySQL 8 · Tailwind CSS · PHPMailer · Evolution API (WhatsApp) · QR Code · Database Queue

## Funcionalidades

- ✅ Autenticação (registro + login)
- ✅ Dashboard com métricas
- ✅ CRUD completo de Eventos (data, hora, local, mapa, imagem, texto personalizado)
- ✅ Cadastro de Convidados (manual + importação CSV)
- ✅ Link único por convidado
- ✅ Página pública de confirmação de presença
- ✅ QR Code automático no convite
- ✅ Envio de convites por **E-mail** (PHPMailer)
- ✅ Envio de convites por **WhatsApp** (Evolution API)
- ✅ **Fila assíncrona** (database queue + worker)
- ✅ Exportação CSV (lista de presença + cliques no convite)
- ✅ Rastreamento de cliques

## Requisitos

- PHP 8.2+
- MySQL 8.0+
- Composer
- Extensões: `pdo_mysql`, `gd`, `curl`, `mbstring`, `fileinfo`

## Instalação

```bash
git clone https://github.com/ebercosta/event-invite-system.git
cd event-invite-system
composer install
cp .env.example .env
```

Configure o arquivo `.env`:

```env
APP_URL=http://localhost:8000

DB_HOST=127.0.0.1
DB_DATABASE=event_invite
DB_USERNAME=root
DB_PASSWORD=

MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=nao-responda@seudominio.com
MAIL_FROM_NAME="Event Invite"

EVOLUTION_API_URL=https://sua-evolution-api.com
EVOLUTION_API_KEY=sua_chave
EVOLUTION_INSTANCE=nome_da_instancia
```

Importe o banco de dados:

```bash
mysql -u root -p < database/schema.sql
```

Suba o servidor de desenvolvimento:

```bash
php -S localhost:8000 -t public
```

## Worker da Fila (obrigatório para envios)

Em outro terminal:

```bash
php bin/worker.php
```

Ou via cron (recomendado em produção):

```cron
* * * * * php /caminho/completo/bin/worker.php >> /var/log/event-invite-worker.log 2>&1
```

## Estrutura do Projeto

```
├── app/
│   ├── Controllers/     # Auth, Dashboard, Event, Guest, Invite, Export
│   ├── Models/          # Guest, Event
│   ├── Services/        # Email, WhatsApp, Queue, QRCode, CsvImport
│   ├── Jobs/            # SendEmailInvite, SendWhatsAppInvite
│   ├── Helpers/         # Database, Auth, Router
│   └── Mail/templates/  # Templates de e-mail
├── bin/worker.php       # Worker da fila
├── config/routes.php
├── database/schema.sql
├── public/              # Document root
├── views/               # Blade-like PHP views (Tailwind)
└── storage/
```

## Fluxo de Uso

1. Crie uma conta em `/register`
2. Crie um evento
3. Adicione convidados (manual ou CSV)
4. Clique em **Enviar Convites** → jobs vão para a fila
5. O worker processa e envia e-mail + WhatsApp
6. Convidado recebe o link único + QR Code
7. Confirma presença na página pública
8. Exporte a lista de presença e cliques

## WhatsApp (Evolution API)

1. Instale a [Evolution API](https://github.com/EvolutionAPI/evolution-api)
2. Crie uma instância e conecte o WhatsApp
3. Preencha as variáveis no `.env`

## Licença

MIT

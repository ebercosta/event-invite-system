# Event Invite System

Micro sistema de cadastro e envio de convites para eventos.

**Stack:** PHP 8.2+ · MySQL 8 · Tailwind CSS · PHPMailer · Evolution API (WhatsApp) · QR Code · Database Queue

## Funcionalidades

- Cadastro de eventos (data, hora, local com mapa, texto e imagem personalizada)
- Cadastro de convidados + importação via CSV
- Link único por convidado
- Confirmação de presença
- Envio de convites por **E-mail** e **WhatsApp**
- QR Code no convite
- Fila assíncrona (database queue)
- Exportação de lista de presença e cliques

## Requisitos

- PHP 8.2+
- MySQL 8.0+
- Composer
- Extensões: pdo_mysql, gd, curl, mbstring, fileinfo

## Instalação

```bash
git clone https://github.com/ebercosta/event-invite-system.git
cd event-invite-system
composer install
cp .env.example .env
# Configure o .env
php database/migrate.php
```

### Worker da Fila

```bash
php bin/worker.php
# Ou via cron a cada minuto:
# * * * * * php /caminho/para/bin/worker.php
```

## Configuração WhatsApp (Evolution API)

1. Instale a [Evolution API](https://github.com/EvolutionAPI/evolution-api)
2. Crie uma instância e obtenha a API Key
3. Preencha no `.env`:

```env
EVOLUTION_API_URL=https://sua-api.com
EVOLUTION_API_KEY=sua_chave
EVOLUTION_INSTANCE=nome_da_instancia
```

## Licença

MIT

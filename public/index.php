<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Simple front controller placeholder
echo "<h1>Event Invite System</h1>";
echo "<p>Sistema de convites rodando. Configure as rotas e controllers.</p>";
echo "<p><a href='/convite/teste'>Exemplo de convite</a></p>";

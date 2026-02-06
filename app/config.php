
<?php
// Configurações do Banco
define('DB_HOST', 'localhost');
define('DB_NAME', 'delivery'); // Altere aqui
define('DB_USER', 'root'); // Altere aqui
define('DB_PASS', ''); // Altere aqui

// URL DINÂMICA (Corrige o problema da porta 8180 automaticamente)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST']; // Pega "localhost:8180"
// Detecta a pasta do projeto (ex: /sistema_delivery/public)
$scriptName = dirname($_SERVER['SCRIPT_NAME']); 

define('BASE_URL', $protocol . '://' . $host . $scriptName);

date_default_timezone_set('America/Sao_Paulo');
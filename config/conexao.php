<?php


$caminhoEnv = __DIR__ . '/../.env';

if (!file_exists($caminhoEnv)) {
    die('Arquivo .env não encontrado. Copie o .env.example, renomeie a cópia para .env e preencha os dados.');
}

// Lê o .env linha por linha e guarda tudo no array $env
$env = [];
$linhas = file($caminhoEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($linhas as $linha) {
    $linha = trim($linha);

    // Pula linhas vazias, comentários (#) e linhas sem "="
    if ($linha === '' || str_starts_with($linha, '#') || !str_contains($linha, '=')) {
        continue;
    }

    // Separa "DB_HOST=localhost" em "DB_HOST" e "localhost"
    [$chave, $valor] = explode('=', $linha, 2);
    $env[trim($chave)] = trim($valor);
}

// Monta o endereço do banco
$dsn = 'mysql:host=' . ($env['DB_HOST'] ?? '') . ';dbname=' . ($env['DB_NAME'] ?? '') . ';charset=utf8mb4';

try {
    $pdo = new PDO($dsn, $env['DB_USER'] ?? '', $env['DB_PASS'] ?? '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // mostra erros como exceção
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // resultados como array com nomes
        PDO::ATTR_EMULATE_PREPARES   => false,                  // prepared statements de verdade
    ]);
} catch (PDOException $erro) {

    die('Erro ao conectar com o banco de dados. Confira se o MySQL está ligado no XAMPP e os dados do .env.');
}
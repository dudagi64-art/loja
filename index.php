<?php
require 'config/conexao.php';

$total = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();

echo "Conectado ao banco! A loja tem $total produtos cadastrados.";
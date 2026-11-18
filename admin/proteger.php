<?php
// =========================================================
// proteger.php
// Todas as páginas do /admin começam puxando este arquivo.
// Ele conecta no banco e BLOQUEIA quem não é administrador.
// =========================================================
require __DIR__ . '/../config/conexao.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['perfil'] !== 'admin') {
    $_SESSION['depois_do_login'] = 'admin/index.php';
    header('Location: ../login.php');
    exit;
}
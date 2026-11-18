<?php
require 'config/conexao.php';

// Tira o usuário da sessão (o carrinho continua guardado)
unset($_SESSION['usuario']);
session_regenerate_id(true);

header('Location: index.php');
exit;
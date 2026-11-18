<?php
require 'config/conexao.php';

// Só mostra se a pessoa acabou de fazer um pedido
if (!isset($_SESSION['usuario'], $_SESSION['ultimo_pedido'])) {
    header('Location: index.php');
    exit;
}

// Busca o pedido (confere que é do usuário logado)
$consulta = $pdo->prepare(
    "SELECT id, data_pedido, forma_pagamento, total FROM pedidos WHERE id = :id AND usuario_id = :usuario"
);
$consulta->bindValue(':id', $_SESSION['ultimo_pedido'], PDO::PARAM_INT);
$consulta->bindValue(':usuario', $_SESSION['usuario']['id'], PDO::PARAM_INT);
$consulta->execute();
$pedido = $consulta->fetch();

// Nomes bonitos para mostrar na tela
$nomesPagamento = ['PIX' => 'PIX', 'Boleto' => 'Boleto bancário', 'Cartao' => 'Cartão de crédito'];

$titulo = 'TechStore - Pedido confirmado';
require 'includes/cabecalho.php';
?>

<div class="col-md-8 col-lg-6 mx-auto bg-white rounded shadow-sm p-4 text-center">
    <?php if ($pedido): ?>
        <h1 class="h3 text-success">Pagamento confirmado! 🎉</h1>
        <p class="lead">Obrigado pela compra, <?= htmlspecialchars($_SESSION['usuario']['nome']) ?>.</p>
        <p class="mb-1">Número do pedido: <strong>#<?= $pedido['id'] ?></strong></p>
        <p class="mb-1">Forma de pagamento: <strong><?= $nomesPagamento[$pedido['forma_pagamento']] ?? htmlspecialchars($pedido['forma_pagamento']) ?></strong></p>
        <p>Total: <strong>R$ <?= number_format($pedido['total'], 2, ',', '.') ?></strong></p>
    <?php else: ?>
        <div class="alert alert-warning">Pedido não encontrado.</div>
    <?php endif; ?>

    <a href="index.php" class="btn btn-dark">Voltar para a loja</a>
</div>

<?php require 'includes/rodape.php'; ?>
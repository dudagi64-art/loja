<?php
require 'proteger.php';

// ========== 1. TODOS OS PEDIDOS, COM O NOME DO CLIENTE ==========
$pedidos = $pdo->query(
    "SELECT p.id, p.data_pedido, p.forma_pagamento, p.total, u.nome AS cliente, u.email
     FROM pedidos p
     JOIN usuarios u ON u.id = p.usuario_id
     ORDER BY p.id DESC"
)->fetchAll();

// ========== 2. CONSULTA DOS ITENS DE CADA PEDIDO ==========
$consultaItens = $pdo->prepare(
    "SELECT i.quantidade, i.preco_unitario, pr.nome
     FROM itens_pedido i
     JOIN produtos pr ON pr.id = i.produto_id
     WHERE i.pedido_id = :pedido"
);

$nomesPagamento = ['PIX' => 'PIX', 'Boleto' => 'Boleto', 'Cartao' => 'Cartão de crédito'];

$titulo = 'Admin - Pedidos';
require 'cabecalho-admin.php';
?>

<h1 class="h3 mb-3">Pedidos realizados</h1>

<?php if (count($pedidos) === 0): ?>
    <div class="alert alert-info">Nenhum pedido ainda.</div>
<?php endif; ?>

<?php foreach ($pedidos as $pedido): ?>
    <?php
    $consultaItens->bindValue(':pedido', $pedido['id'], PDO::PARAM_INT);
    $consultaItens->execute();
    $itens = $consultaItens->fetchAll();
    ?>
    <div class="bg-white rounded shadow-sm p-3 mb-3">
        <div class="d-flex flex-wrap justify-content-between gap-2">
            <h2 class="h5 mb-1">Pedido #<?= $pedido['id'] ?></h2>
            <span class="text-muted small"><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></span>
        </div>
        <p class="mb-2 small">
            Cliente: <strong><?= htmlspecialchars($pedido['cliente']) ?></strong> (<?= htmlspecialchars($pedido['email']) ?>)
            · Pagamento: <strong><?= $nomesPagamento[$pedido['forma_pagamento']] ?? htmlspecialchars($pedido['forma_pagamento']) ?></strong>
        </p>

        <table class="table table-sm mb-0">
            <thead>
                <tr><th>Produto</th><th>Qtd.</th><th>Preço unitário</th><th class="text-end">Subtotal</th></tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nome']) ?></td>
                        <td><?= $item['quantidade'] ?></td>
                        <td>R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                        <td class="text-end">R$ <?= number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="fw-bold">
                    <td colspan="3">Total</td>
                    <td class="text-end">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>
    </div>
<?php endforeach; ?>

<?php require '../includes/rodape.php'; ?>
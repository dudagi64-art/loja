<?php
require 'config/conexao.php';

// ========== 1. SÓ QUEM ESTÁ LOGADO PODE FINALIZAR A COMPRA ==========
if (!isset($_SESSION['usuario'])) {
    $_SESSION['depois_do_login'] = 'checkout.php';   // volta para cá depois do login
    header('Location: login.php');
    exit;
}

// Carrinho vazio? Volta para o carrinho
if (empty($_SESSION['carrinho'])) {
    header('Location: carrinho.php');
    exit;
}

// Formas de pagamento aceitas (iguais ao ENUM do banco)
$formasPagamento = [
    'PIX'    => 'PIX',
    'Boleto' => 'Boleto bancário',
    'Cartao' => 'Cartão de crédito',
];


// ========== 2. MONTA O RESUMO COM OS PREÇOS DO BANCO ==========
$itens = [];
$total = 0;

$consulta = $pdo->prepare("SELECT id, nome, preco, estoque FROM produtos WHERE id = :id AND ativo = 1");

foreach ($_SESSION['carrinho'] as $produtoId => $quantidade) {
    $consulta->bindValue(':id', $produtoId, PDO::PARAM_INT);
    $consulta->execute();
    $produto = $consulta->fetch();

    if (!$produto) {
        continue;
    }

    $itens[] = [
        'id'         => $produto['id'],
        'nome'       => $produto['nome'],
        'preco'      => $produto['preco'],
        'quantidade' => $quantidade,
        'subtotal'   => $produto['preco'] * $quantidade,
    ];
    $total += $produto['preco'] * $quantidade;
}

$erro = '';


// ========== 3. CONFIRMAR O PAGAMENTO E FINALIZAR O PEDIDO ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $forma = $_POST['forma_pagamento'] ?? '';

    if (!array_key_exists($forma, $formasPagamento)) {
        $erro = 'Escolha uma forma de pagamento.';
    } elseif (count($itens) === 0) {
        $erro = 'Nenhum produto disponível no carrinho.';
    } else {
        try {
            // Transação: ou salva TUDO, ou não salva NADA
            $pdo->beginTransaction();

            // Passo 1: cria o registro do pedido
            $inserirPedido = $pdo->prepare(
                "INSERT INTO pedidos (usuario_id, forma_pagamento, total) VALUES (:usuario, :forma, :total)"
            );
            $inserirPedido->bindValue(':usuario', $_SESSION['usuario']['id'], PDO::PARAM_INT);
            $inserirPedido->bindValue(':forma', $forma);
            $inserirPedido->bindValue(':total', $total);
            $inserirPedido->execute();
            $pedidoId = (int) $pdo->lastInsertId();

            $inserirItem = $pdo->prepare(
                "INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario)
                 VALUES (:pedido, :produto, :quantidade, :preco)"
            );

            // Só baixa o estoque se ainda tiver a quantidade suficiente
            $baixarEstoque = $pdo->prepare(
                "UPDATE produtos SET estoque = estoque - :quantidade
                 WHERE id = :produto AND ativo = 1 AND estoque >= :minimo"
            );

            foreach ($itens as $item) {
                // Passo 2: registra cada item, congelando o preço da compra
                $inserirItem->bindValue(':pedido', $pedidoId, PDO::PARAM_INT);
                $inserirItem->bindValue(':produto', $item['id'], PDO::PARAM_INT);
                $inserirItem->bindValue(':quantidade', $item['quantidade'], PDO::PARAM_INT);
                $inserirItem->bindValue(':preco', $item['preco']);
                $inserirItem->execute();

                // Passo 3: baixa o estoque
                $baixarEstoque->bindValue(':quantidade', $item['quantidade'], PDO::PARAM_INT);
                $baixarEstoque->bindValue(':produto', $item['id'], PDO::PARAM_INT);
                $baixarEstoque->bindValue(':minimo', $item['quantidade'], PDO::PARAM_INT);
                $baixarEstoque->execute();

                // Se nenhuma linha mudou, é porque não tinha estoque suficiente
                if ($baixarEstoque->rowCount() === 0) {
                    throw new Exception('Estoque insuficiente para: ' . $item['nome']);
                }
            }

            $pdo->commit();   // deu tudo certo: salva de vez

            // Passo 4: limpa o carrinho
            $_SESSION['carrinho'] = [];
            $_SESSION['ultimo_pedido'] = $pedidoId;

            header('Location: pedido-confirmado.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();   // deu algum erro: desfaz tudo
            $erro = $e->getMessage() . '. Ajuste a quantidade no carrinho.';
        }
    }
}


// ========== 4. MOSTRA A PÁGINA ==========
$titulo = 'TechStore - Finalizar compra';
require 'includes/cabecalho.php';
?>

<h1 class="mb-4">Finalizar compra</h1>

<?php if ($erro): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<div class="row g-4">

    <!-- Resumo do pedido -->
    <div class="col-lg-7">
        <div class="bg-white rounded shadow-sm p-3">
            <h2 class="h5">Resumo do pedido</h2>
            <table class="table mb-0">
                <?php foreach ($itens as $item): ?>
                    <tr>
                        <td><?= $item['quantidade'] ?>x <?= htmlspecialchars($item['nome']) ?></td>
                        <td class="text-end">R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="fw-bold">
                    <td>Total</td>
                    <td class="text-end">R$ <?= number_format($total, 2, ',', '.') ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Pagamento (simulado) -->
    <div class="col-lg-5">
        <form method="post" action="checkout.php" class="bg-white rounded shadow-sm p-3">
            <h2 class="h5">Forma de pagamento</h2>

            <?php foreach ($formasPagamento as $valor => $nome): ?>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="forma_pagamento"
                           id="pg<?= $valor ?>" value="<?= $valor ?>" required>
                    <label class="form-check-label" for="pg<?= $valor ?>"><?= $nome ?></label>
                </div>
            <?php endforeach; ?>

            <p class="small text-muted">Pagamento simulado: a confirmação é imediata.</p>

            <button type="submit" class="btn btn-success w-100">Confirmar pagamento</button>
            <a href="carrinho.php" class="btn btn-link w-100">Voltar ao carrinho</a>
        </form>
    </div>
</div>

<?php require 'includes/rodape.php'; ?>
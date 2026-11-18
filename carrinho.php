<?php
require 'config/conexao.php';

// O carrinho fica guardado na sessão assim:
// $_SESSION['carrinho'] = [ id_do_produto => quantidade ]
// Exemplo: [1 => 2, 7 => 1]  (2 mouses e 1 cabo)
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Busca o estoque de um produto ativo (ou false se não existir)
function buscarEstoque($pdo, $produtoId)
{
    $consulta = $pdo->prepare("SELECT estoque FROM produtos WHERE id = :id AND ativo = 1");
    $consulta->bindValue(':id', $produtoId, PDO::PARAM_INT);
    $consulta->execute();
    return $consulta->fetchColumn();
}


// ========== 1. AÇÕES DO CARRINHO (vêm de formulários com POST) ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $produtoId = (int) ($_POST['produto_id'] ?? 0);
    $quantidade = (int) ($_POST['quantidade'] ?? 0);
    $estoque = buscarEstoque($pdo, $produtoId);

    if ($acao === 'adicionar' && $estoque !== false && $quantidade > 0) {
        // Soma com o que já estava no carrinho
        $novaQuantidade = ($_SESSION['carrinho'][$produtoId] ?? 0) + $quantidade;

        // Validação: não pode passar do estoque
        if ($novaQuantidade > $estoque) {
            $novaQuantidade = $estoque;
            $_SESSION['aviso'] = "Só temos $estoque unidade(s) desse produto em estoque.";
        }

        if ($novaQuantidade > 0) {
            $_SESSION['carrinho'][$produtoId] = $novaQuantidade;
        }
    }

    if ($acao === 'atualizar' && isset($_SESSION['carrinho'][$produtoId])) {
        if ($quantidade <= 0 || $estoque === false) {
            unset($_SESSION['carrinho'][$produtoId]);   // quantidade 0 = remover
        } elseif ($quantidade > $estoque) {
            $_SESSION['carrinho'][$produtoId] = $estoque;
            $_SESSION['aviso'] = "Só temos $estoque unidade(s) desse produto em estoque.";
        } else {
            $_SESSION['carrinho'][$produtoId] = $quantidade;
        }
    }

    if ($acao === 'remover') {
        unset($_SESSION['carrinho'][$produtoId]);
    }

    // Recarrega a página do carrinho (evita reenviar o formulário ao apertar F5)
    header('Location: carrinho.php');
    exit;
}


// ========== 2. MONTA A LISTA DE ITENS PARA MOSTRAR ==========
$itens = [];
$totalGeral = 0;

$consulta = $pdo->prepare("SELECT id, nome, preco, estoque FROM produtos WHERE id = :id AND ativo = 1");

foreach ($_SESSION['carrinho'] as $produtoId => $quantidade) {
    $consulta->bindValue(':id', $produtoId, PDO::PARAM_INT);
    $consulta->execute();
    $produto = $consulta->fetch();

    // Se o produto foi apagado ou desativado, tira do carrinho
    if (!$produto) {
        unset($_SESSION['carrinho'][$produtoId]);
        continue;
    }

    $subtotal = $produto['preco'] * $quantidade;
    $totalGeral += $subtotal;

    $itens[] = [
        'id'         => $produto['id'],
        'nome'       => $produto['nome'],
        'preco'      => $produto['preco'],
        'estoque'    => $produto['estoque'],
        'quantidade' => $quantidade,
        'subtotal'   => $subtotal,
    ];
}

// Pega o aviso (se tiver) e apaga, para aparecer só uma vez
$aviso = $_SESSION['aviso'] ?? null;
unset($_SESSION['aviso']);


// ========== 3. MOSTRA A PÁGINA ==========
$titulo = 'TechStore - Carrinho';
require 'includes/cabecalho.php';
?>

<h1 class="mb-4">Meu carrinho</h1>

<?php if ($aviso): ?>
    <div class="alert alert-warning"><?= htmlspecialchars($aviso) ?></div>
<?php endif; ?>

<?php if (count($itens) === 0): ?>

    <div class="alert alert-info">Seu carrinho está vazio.</div>
    <a href="index.php" class="btn btn-dark">Ver produtos</a>

<?php else: ?>

    <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Preço</th>
                    <th>Quantidade</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                    <tr>
                        <td><a href="produto.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['nome']) ?></a></td>
                        <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                        <td>
                            <form method="post" action="carrinho.php" class="d-flex gap-1">
                                <input type="hidden" name="acao" value="atualizar">
                                <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                <input type="number" name="quantidade" value="<?= $item['quantidade'] ?>"
                                       min="0" max="<?= $item['estoque'] ?>" class="form-control form-control-sm" style="width: 70px">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Atualizar</button>
                            </form>
                        </td>
                        <td>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></td>
                        <td>
                            <form method="post" action="carrinho.php">
                                <input type="hidden" name="acao" value="remover">
                                <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Remover</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 gap-3">
        <a href="index.php" class="btn btn-outline-secondary">&larr; Continuar comprando</a>
        <div class="text-end">
            <p class="fs-4 mb-2">Total: <strong>R$ <?= number_format($totalGeral, 2, ',', '.') ?></strong></p>
            <a href="checkout.php" class="btn btn-success btn-lg">Finalizar compra</a>
        </div>
    </div>

<?php endif; ?>

<?php require 'includes/rodape.php'; ?>
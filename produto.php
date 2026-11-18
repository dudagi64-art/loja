<?php
require 'config/conexao.php';

// ========== 1. PEGA O ID DO PRODUTO PELA URL ==========
// Exemplo: produto.php?id=3
$id = (int) ($_GET['id'] ?? 0);


// ========== 2. BUSCA O PRODUTO NO BANCO (prepared statement) ==========
$consulta = $pdo->prepare(
    "SELECT p.*, c.nome AS categoria
     FROM produtos p
     JOIN categorias c ON c.id = p.categoria_id
     WHERE p.id = :id AND p.ativo = 1"
);
$consulta->bindValue(':id', $id, PDO::PARAM_INT);
$consulta->execute();
$produto = $consulta->fetch();   // fetch() pega UMA linha (ou false se não achar)


// ========== 3. MOSTRA A PÁGINA ==========
$titulo = $produto ? 'TechStore - ' . $produto['nome'] : 'Produto não encontrado';
require 'includes/cabecalho.php';
?>

<a href="index.php" class="btn btn-outline-secondary btn-sm mb-4">&larr; Voltar para a loja</a>

<?php if (!$produto): ?>

    <div class="alert alert-warning">Produto não encontrado.</div>

<?php else: ?>

    <div class="row g-4 bg-white rounded shadow-sm p-3">

        <!-- Foto -->
        <div class="col-md-5">
            <?php if (is_file('img/produtos/' . $produto['imagem'])): ?>
                <img src="img/produtos/<?= htmlspecialchars($produto['imagem']) ?>"
                     class="img-fluid rounded" alt="<?= htmlspecialchars($produto['nome']) ?>">
            <?php else: ?>
                <div class="sem-foto rounded d-flex align-items-center justify-content-center">Sem foto</div>
            <?php endif; ?>
        </div>

        <!-- Informações -->
        <div class="col-md-7">
            <span class="badge bg-secondary mb-2"><?= htmlspecialchars($produto['categoria']) ?></span>
            <h1 class="h3"><?= htmlspecialchars($produto['nome']) ?></h1>
            <p class="text-muted small">Código do produto: #<?= $produto['id'] ?></p>

            <p class="fs-3 fw-bold">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></p>

            <p><?= nl2br(htmlspecialchars($produto['descricao'] ?? '')) ?></p>

            <!-- Indicador de estoque -->
            <?php if ($produto['estoque'] == 0): ?>
                <p class="text-danger fw-bold">Esgotado</p>
            <?php elseif ($produto['estoque'] <= 5): ?>
                <p class="text-warning fw-bold">Últimas <?= $produto['estoque'] ?> unidades!</p>
            <?php else: ?>
                <p class="text-success fw-bold">Em estoque (<?= $produto['estoque'] ?> unidades)</p>
            <?php endif; ?>

            <!-- Adicionar ao carrinho (o carrinho é a próxima etapa) -->
            <?php if ($produto['estoque'] > 0): ?>
                <form method="post" action="carrinho.php" class="d-flex gap-2 align-items-center">
                    <input type="hidden" name="acao" value="adicionar">
                    <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">

                    <label for="quantidade" class="form-label mb-0">Qtd.</label>
                    <input type="number" id="quantidade" name="quantidade" value="1"
                           min="1" max="<?= $produto['estoque'] ?>" class="form-control" style="width: 90px">

                    <button type="submit" class="btn btn-success">Adicionar ao carrinho</button>
                </form>
            <?php else: ?>
                <button class="btn btn-secondary" disabled>Indisponível</button>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>

<?php require 'includes/rodape.php'; ?>
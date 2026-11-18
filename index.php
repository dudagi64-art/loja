<?php
require 'config/conexao.php';

// ========== 1. LÊ O FILTRO E A BUSCA QUE VIERAM PELA URL ==========
// Exemplo: index.php?categoria=2&busca=fone
$categoriaId = (int) ($_GET['categoria'] ?? 0);   // 0 = todas as categorias
$busca = trim($_GET['busca'] ?? '');


// ========== 2. BUSCA AS CATEGORIAS (para os botões de filtro) ==========
$categorias = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome")->fetchAll();


// ========== 3. BUSCA OS PRODUTOS (com prepared statement) ==========
$sql = "SELECT p.id, p.nome, p.preco, p.estoque, p.imagem, c.nome AS categoria
        FROM produtos p
        JOIN categorias c ON c.id = p.categoria_id
        WHERE p.ativo = 1
          AND (:todas = 0 OR p.categoria_id = :categoria)
          AND p.nome LIKE :busca
        ORDER BY p.nome";

$consulta = $pdo->prepare($sql);
$consulta->bindValue(':todas', $categoriaId, PDO::PARAM_INT);
$consulta->bindValue(':categoria', $categoriaId, PDO::PARAM_INT);
$consulta->bindValue(':busca', '%' . $busca . '%');
$consulta->execute();
$produtos = $consulta->fetchAll();


// ========== 4. MOSTRA A PÁGINA ==========
$titulo = 'TechStore - Produtos';
require 'includes/cabecalho.php';
?>

<h1 class="text-center mb-4">Nossos Produtos</h1>

<!-- Campo de busca -->
<form method="get" action="index.php" class="row g-2 justify-content-center mb-3">
    <input type="hidden" name="categoria" value="<?= $categoriaId ?>">
    <div class="col-8 col-md-5">
        <input type="search" name="busca" class="form-control" placeholder="Buscar produto..."
               value="<?= htmlspecialchars($busca) ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-dark">Buscar</button>
    </div>
</form>

<!-- Botões de filtro por categoria -->
<div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
    <a href="index.php" class="btn btn-sm <?= $categoriaId === 0 ? 'btn-primary' : 'btn-outline-primary' ?>">Todas</a>
    <?php foreach ($categorias as $categoria): ?>
        <a href="index.php?categoria=<?= $categoria['id'] ?>"
           class="btn btn-sm <?= $categoriaId === (int) $categoria['id'] ? 'btn-primary' : 'btn-outline-primary' ?>">
            <?= htmlspecialchars($categoria['nome']) ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if (count($produtos) === 0): ?>
    <p class="text-center text-muted">Nenhum produto encontrado.</p>
<?php endif; ?>

<!-- Vitrine: 1 por linha no celular, 2 no tablet, 4 no computador -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
    <?php foreach ($produtos as $produto): ?>
        <div class="col">
            <div class="card h-100 shadow-sm">

                <?php if (is_file('img/produtos/' . $produto['imagem'])): ?>
                    <img src="img/produtos/<?= htmlspecialchars($produto['imagem']) ?>"
                         class="card-img-top foto-produto" alt="<?= htmlspecialchars($produto['nome']) ?>">
                <?php else: ?>
                    <div class="sem-foto d-flex align-items-center justify-content-center">Sem foto</div>
                <?php endif; ?>

                <div class="card-body d-flex flex-column">
                    <span class="badge bg-secondary align-self-start mb-2"><?= htmlspecialchars($produto['categoria']) ?></span>
                    <h2 class="h6 card-title"><?= htmlspecialchars($produto['nome']) ?></h2>
                    <p class="fs-5 fw-bold mb-2">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></p>

                    <?php if ($produto['estoque'] > 0): ?>
                        <p class="text-success small">Em estoque</p>
                    <?php else: ?>
                        <p class="text-danger small">Esgotado</p>
                    <?php endif; ?>

                    <a href="produto.php?id=<?= $produto['id'] ?>" class="btn btn-dark mt-auto">Ver detalhes</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require 'includes/rodape.php'; ?>
<?php
require 'proteger.php';

// ========== 1. AÇÕES: ATIVAR/DESATIVAR E EXCLUIR ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($acao === 'alternar') {
        // Se está ativo vira inativo, e vice-versa
        $alterar = $pdo->prepare("UPDATE produtos SET ativo = 1 - ativo WHERE id = :id");
        $alterar->bindValue(':id', $id, PDO::PARAM_INT);
        $alterar->execute();
        $_SESSION['aviso'] = 'Situação do produto alterada.';
    }

    if ($acao === 'excluir') {
        try {
            $excluir = $pdo->prepare("DELETE FROM produtos WHERE id = :id");
            $excluir->bindValue(':id', $id, PDO::PARAM_INT);
            $excluir->execute();
            $_SESSION['aviso'] = 'Produto excluído.';
        } catch (PDOException $e) {
            // A chave estrangeira não deixa apagar produto que já foi vendido
            $_SESSION['aviso'] = 'Este produto já aparece em pedidos e não pode ser excluído. Use "Desativar".';
        }
    }

    header('Location: index.php');
    exit;
}


// ========== 2. LISTA TODOS OS PRODUTOS (inclusive os desativados) ==========
$produtos = $pdo->query(
    "SELECT p.id, p.nome, p.preco, p.estoque, p.ativo, c.nome AS categoria
     FROM produtos p
     JOIN categorias c ON c.id = p.categoria_id
     ORDER BY p.nome"
)->fetchAll();

$aviso = $_SESSION['aviso'] ?? null;
unset($_SESSION['aviso']);

$titulo = 'Admin - Produtos';
require 'cabecalho-admin.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="h3 mb-0">Produtos</h1>
    <a href="produto-form.php" class="btn btn-success">+ Novo produto</a>
</div>

<?php if ($aviso): ?>
    <div class="alert alert-info"><?= htmlspecialchars($aviso) ?></div>
<?php endif; ?>

<div class="table-responsive bg-white rounded shadow-sm">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th>#</th>
                <th>Produto</th>
                <th>Categoria</th>
                <th>Preço</th>
                <th>Estoque</th>
                <th>Situação</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $produto): ?>
                <tr class="<?= $produto['ativo'] ? '' : 'text-muted' ?>">
                    <td><?= $produto['id'] ?></td>
                    <td><?= htmlspecialchars($produto['nome']) ?></td>
                    <td><?= htmlspecialchars($produto['categoria']) ?></td>
                    <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                    <td><?= $produto['estoque'] ?></td>
                    <td>
                        <?php if ($produto['ativo']): ?>
                            <span class="badge bg-success">Ativo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Desativado</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="produto-form.php?id=<?= $produto['id'] ?>" class="btn btn-sm btn-primary">Editar</a>

                        <form method="post" action="index.php" class="d-inline">
                            <input type="hidden" name="acao" value="alternar">
                            <input type="hidden" name="id" value="<?= $produto['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <?= $produto['ativo'] ? 'Desativar' : 'Ativar' ?>
                            </button>
                        </form>

                        <form method="post" action="index.php" class="d-inline"
                              onsubmit="return confirm('Excluir este produto?');">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id" value="<?= $produto['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require '../includes/rodape.php'; ?>
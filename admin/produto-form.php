<?php
require 'proteger.php';

// Se veio um id na URL, é EDIÇÃO; se não, é um produto NOVO
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$categorias = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome")->fetchAll();

// Valores iniciais do formulário
$produto = [
    'nome'         => '',
    'categoria_id' => '',
    'descricao'    => '',
    'preco'        => '',
    'estoque'      => '0',
    'imagem'       => '',
];

// Edição: carrega os dados do produto
if ($id > 0) {
    $consulta = $pdo->prepare("SELECT * FROM produtos WHERE id = :id");
    $consulta->bindValue(':id', $id, PDO::PARAM_INT);
    $consulta->execute();
    $encontrado = $consulta->fetch();

    if (!$encontrado) {
        header('Location: index.php');
        exit;
    }
    $produto = $encontrado;
}

$erros = [];


// ========== SALVAR (CRIAR ou ATUALIZAR) ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto['nome'] = trim($_POST['nome'] ?? '');
    $produto['categoria_id'] = (int) ($_POST['categoria_id'] ?? 0);
    $produto['descricao'] = trim($_POST['descricao'] ?? '');
    $produto['preco'] = str_replace(',', '.', trim($_POST['preco'] ?? ''));   // aceita 89,90 ou 89.90
    $produto['estoque'] = trim($_POST['estoque'] ?? '');
    $produto['imagem'] = trim($_POST['imagem'] ?? '');

    // Validações
    if ($produto['nome'] === '') {
        $erros[] = 'Informe o nome do produto.';
    }
    $categoriaValida = false;
    foreach ($categorias as $categoria) {
        if ((int) $categoria['id'] === $produto['categoria_id']) {
            $categoriaValida = true;
        }
    }
    if (!$categoriaValida) {
        $erros[] = 'Escolha uma categoria.';
    }
    if (!is_numeric($produto['preco']) || $produto['preco'] <= 0) {
        $erros[] = 'Informe um preço válido (ex.: 89,90).';
    }
    if (filter_var($produto['estoque'], FILTER_VALIDATE_INT) === false || $produto['estoque'] < 0) {
        $erros[] = 'O estoque precisa ser um número inteiro, 0 ou maior.';
    }

    if (count($erros) === 0) {
        if ($id > 0) {
            // UPDATE
            $salvar = $pdo->prepare(
                "UPDATE produtos SET nome = :nome, categoria_id = :categoria, descricao = :descricao,
                        preco = :preco, estoque = :estoque, imagem = :imagem
                 WHERE id = :id"
            );
            $salvar->bindValue(':id', $id, PDO::PARAM_INT);
        } else {
            // CREATE
            $salvar = $pdo->prepare(
                "INSERT INTO produtos (nome, categoria_id, descricao, preco, estoque, imagem)
                 VALUES (:nome, :categoria, :descricao, :preco, :estoque, :imagem)"
            );
        }

        $salvar->bindValue(':nome', $produto['nome']);
        $salvar->bindValue(':categoria', $produto['categoria_id'], PDO::PARAM_INT);
        $salvar->bindValue(':descricao', $produto['descricao']);
        $salvar->bindValue(':preco', $produto['preco']);
        $salvar->bindValue(':estoque', (int) $produto['estoque'], PDO::PARAM_INT);
        $salvar->bindValue(':imagem', $produto['imagem']);
        $salvar->execute();

        $_SESSION['aviso'] = $id > 0 ? 'Produto atualizado.' : 'Produto cadastrado.';
        header('Location: index.php');
        exit;
    }
}

$titulo = $id > 0 ? 'Admin - Editar produto' : 'Admin - Novo produto';
require 'cabecalho-admin.php';
?>

<div class="col-lg-8 mx-auto bg-white rounded shadow-sm p-4">
    <h1 class="h3 mb-3"><?= $id > 0 ? 'Editar produto #' . $id : 'Novo produto' ?></h1>

    <?php if (count($erros) > 0): ?>
        <div class="alert alert-danger">
            <?php foreach ($erros as $erro): ?>
                <div><?= htmlspecialchars($erro) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="produto-form.php">
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" id="nome" name="nome" class="form-control" maxlength="100"
                   value="<?= htmlspecialchars($produto['nome']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="categoria_id" class="form-label">Categoria</label>
            <select id="categoria_id" name="categoria_id" class="form-select" required>
                <option value="">Escolha...</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= $categoria['id'] ?>"
                        <?= (int) $produto['categoria_id'] === (int) $categoria['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($categoria['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea id="descricao" name="descricao" class="form-control" rows="3"><?= htmlspecialchars($produto['descricao'] ?? '') ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="preco" class="form-label">Preço (R$)</label>
                <input type="text" id="preco" name="preco" class="form-control"
                       value="<?= htmlspecialchars($produto['preco']) ?>" placeholder="89,90" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="estoque" class="form-label">Estoque</label>
                <input type="number" id="estoque" name="estoque" class="form-control" min="0"
                       value="<?= htmlspecialchars($produto['estoque']) ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="imagem" class="form-label">Nome do arquivo da imagem</label>
            <input type="text" id="imagem" name="imagem" class="form-control"
                   value="<?= htmlspecialchars($produto['imagem'] ?? '') ?>" placeholder="mouse-gamer.jpg">
            <div class="form-text">A imagem precisa estar na pasta <code>img/produtos</code>.</div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require '../includes/rodape.php'; ?>
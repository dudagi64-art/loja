<?php
require 'config/conexao.php';

// Se já está logado, não precisa se cadastrar
if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$erros = [];
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    // ========== 1. VALIDAÇÕES ==========
    if ($nome === '') {
        $erros[] = 'Informe o seu nome.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Informe um e-mail válido.';
    }
    if (strlen($senha) < 6) {
        $erros[] = 'A senha precisa ter pelo menos 6 caracteres.';
    }
    if ($senha !== $confirmar) {
        $erros[] = 'As senhas não são iguais.';
    }

    // E-mail já cadastrado? (o banco também impede, com o UNIQUE)
    if (count($erros) === 0) {
        $consulta = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
        $consulta->bindValue(':email', $email);
        $consulta->execute();
        if ($consulta->fetch()) {
            $erros[] = 'Este e-mail já está cadastrado.';
        }
    }

    // ========== 2. SALVA O USUÁRIO COM A SENHA CRIPTOGRAFADA ==========
    if (count($erros) === 0) {
        $senhaCriptografada = password_hash($senha, PASSWORD_DEFAULT);

        $inserir = $pdo->prepare(
            "INSERT INTO usuarios (nome, email, senha, perfil) VALUES (:nome, :email, :senha, 'cliente')"
        );
        $inserir->bindValue(':nome', $nome);
        $inserir->bindValue(':email', $email);
        $inserir->bindValue(':senha', $senhaCriptografada);
        $inserir->execute();

        // Já entra logado depois de se cadastrar
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id'     => (int) $pdo->lastInsertId(),
            'nome'   => $nome,
            'perfil' => 'cliente',
        ];

        $destino = $_SESSION['depois_do_login'] ?? 'index.php';
        unset($_SESSION['depois_do_login']);
        header('Location: ' . $destino);
        exit;
    }
}

$titulo = 'TechStore - Criar conta';
require 'includes/cabecalho.php';
?>

<div class="col-md-6 col-lg-5 mx-auto bg-white rounded shadow-sm p-4">
    <h1 class="h3 mb-3 text-center">Criar conta</h1>

    <?php if (count($erros) > 0): ?>
        <div class="alert alert-danger">
            <?php foreach ($erros as $erro): ?>
                <div><?= htmlspecialchars($erro) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="cadastro.php">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" id="nome" name="nome" class="form-control" value="<?= htmlspecialchars($nome) ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
        </div>
        <div class="mb-3">
            <label for="senha" class="form-label">Senha (mínimo 6 caracteres)</label>
            <input type="password" id="senha" name="senha" class="form-control" minlength="6" required>
        </div>
        <div class="mb-3">
            <label for="confirmar" class="form-label">Confirmar senha</label>
            <input type="password" id="confirmar" name="confirmar" class="form-control" minlength="6" required>
        </div>
        <button type="submit" class="btn btn-dark w-100">Cadastrar</button>
    </form>

    <p class="text-center mt-3 mb-0">Já tem conta? <a href="login.php">Entrar</a></p>
</div>

<?php require 'includes/rodape.php'; ?>
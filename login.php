<?php
require 'config/conexao.php';

// Se já está logado, volta para a loja
if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // ========== 1. PROCURA O USUÁRIO PELO E-MAIL ==========
    $consulta = $pdo->prepare("SELECT id, nome, senha, perfil FROM usuarios WHERE email = :email");
    $consulta->bindValue(':email', $email);
    $consulta->execute();
    $usuario = $consulta->fetch();

    // ========== 2. CONFERE A SENHA COM password_verify() ==========
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        session_regenerate_id(true);   // gera um novo id de sessão (segurança)
        $_SESSION['usuario'] = [
            'id'     => (int) $usuario['id'],
            'nome'   => $usuario['nome'],
            'perfil' => $usuario['perfil'],
        ];

        // Volta para onde a pessoa estava (ex.: o checkout) ou para a loja
        $destino = $_SESSION['depois_do_login'] ?? 'index.php';
        unset($_SESSION['depois_do_login']);
        header('Location: ' . $destino);
        exit;
    }

    // Mensagem genérica: não conta se o erro foi no e-mail ou na senha
    $erro = 'E-mail ou senha incorretos.';
}

$titulo = 'TechStore - Entrar';
require 'includes/cabecalho.php';
?>

<div class="col-md-6 col-lg-4 mx-auto bg-white rounded shadow-sm p-4">
    <h1 class="h3 mb-3 text-center">Entrar</h1>

    <?php if (isset($_SESSION['depois_do_login'])): ?>
        <div class="alert alert-info">Entre ou crie uma conta para finalizar a compra.</div>
    <?php endif; ?>

    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
        </div>
        <div class="mb-3">
            <label for="senha" class="form-label">Senha</label>
            <input type="password" id="senha" name="senha" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-dark w-100">Entrar</button>
    </form>

    <p class="text-center mt-3 mb-0">Não tem conta? <a href="cadastro.php">Criar conta</a></p>
</div>

<?php require 'includes/rodape.php'; ?>
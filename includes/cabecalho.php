<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'TechStore') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">TechStore</a>

            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Produtos</a></li>
                                        <li class="nav-item">
                        <a class="nav-link" href="carrinho.php">
                            Carrinho
                            <span class="badge bg-success"><?= array_sum($_SESSION['carrinho'] ?? []) ?></span>
                        </a>
                    </li>
                                       <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['perfil'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link text-warning" href="admin/index.php">Painel admin</a></li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['usuario'])): ?>
                        <li class="nav-item">
                            <span class="nav-link text-white">Olá, <?= htmlspecialchars($_SESSION['usuario']['nome']) ?></span>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Sair</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Entrar</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-4">
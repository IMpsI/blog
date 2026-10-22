<?php $search = trim($_GET['q'] ?? ''); ?>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="index.php">O Blog <span>/ biblioteca</span></a>
        <form class="search-form" action="index.php" method="get">
            <input id="searchInput" name="q" value="<?= e($search) ?>" placeholder="Pesquisar histórias..." autocomplete="off">
            <button aria-label="Pesquisar" type="submit">⌕</button>
            <div id="search-results" class="search-results"></div>
        </form>
        <nav class="main-nav">
            <?php if (loggedIn()): ?>
                <?php if (($_SESSION['user_level'] ?? '') !== 'leitor'): ?>
                    <a href="dashboard.php">Painel</a>
                <?php else: ?>
                    <span>Olá, <?= e($_SESSION['user_name'] ?? '') ?></span>
                <?php endif; ?>
                <a href="logout.php">Sair</a>
            <?php else: ?>
                <a href="login.php">Entrar</a>
                <a class="button button-primary" href="register.php">Criar conta</a>
            <?php endif; ?>
            <button class="theme-button" id="themeBtn" type="button" title="Alternar tema">◐</button>
        </nav>
    </div>
</header>

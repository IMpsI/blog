<header>
    <?php
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if ($basePath === '' || $basePath === '.') {
        $basePath = '';
    }
    $homeUrl = ($basePath === '' ? '/' : $basePath . '/');

    $uriPath = strtolower(trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/'));
    $slug = pathinfo($uriPath === '' ? 'index' : $uriPath, PATHINFO_FILENAME);
    $mapaPagina = [
        'index' => 'inicio',
        'dashboard' => 'painel',
        'painel' => 'painel',
        'admin' => 'estudio',
        'estudio' => 'estudio',
        'editar_autor' => 'contas',
        'cadastrar_autor' => 'autores',
        'editar_post' => 'editar post',
        'cadastro' => 'criar conta',
        'login' => 'acessar',
        'confirmar_email' => 'confirmacao',
        'minha_conta' => 'perfil',
        'ler' => 'leitura'
    ];

    $paginaAtual = '';
    if (!empty($pageTitle)) {
        $paginaAtual = trim(preg_replace('/\s*-\s*O Blog.*$/i', '', (string)$pageTitle));
    }
    if ($paginaAtual === '' && isset($mapaPagina[$slug])) {
        $paginaAtual = $mapaPagina[$slug];
    }
    if ($paginaAtual === '' || strcasecmp($paginaAtual, 'O Blog') === 0) {
        $paginaAtual = 'biblioteca';
    }
    ?>
    <div class="logo-wrap">
        <a href="<?= $homeUrl ?>" class="logo">O Blog</a>
        <span class="page-soft-label">/ <?= htmlspecialchars(strtolower($paginaAtual)) ?></span>
    </div>
    
    <div class="search-container">
        <form action="<?= $homeUrl ?>" method="GET" class="search-form" id="mainSearchForm">
            <input type="text" name="q" id="searchInput" autocomplete="off" placeholder="Pesquisar histórias..." value="<?= htmlspecialchars($busca ?? '') ?>">
            <button type="submit" title="Pesquisar">
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </button>
        </form>
        <div id="search-results"></div>
    </div>

    <div class="nav-links">
        <?php if(isset($_SESSION['logado'])): ?>
            <?php 
                if($_SESSION['nivel'] !== 'leitor'){
                    echo '<a href="painel">Painel</a>';
                }else{
                    echo  "<span>Olá, <strong> {$_SESSION['autor_nome']}</strong></span>";
                }
                ?>
            <a href="sair" class="logout-link">Sair</a>
        <?php else: ?>
            <a href="entrar">Entrar</a>
            <a href="cadastro" class="btn-destaque">Criar Conta</a>
        <?php endif; ?>
        <button class="btn-dark" id="themeBtn" title="Alternar Modo Noturno">🌓</button>
    </div>
</header>
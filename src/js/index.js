   // 1. MODO NOTURNO PERSISTENTE
    const themeBtn = document.getElementById('themeBtn');
    if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode');
    themeBtn.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
    });
        // 2. PESQUISA INSTANTÂNEA (AUTOCOMPLETE 3 OPÇÕES)
    const searchInp = document.getElementById('searchInput');
    const searchRes = document.getElementById('search-results');
    searchInp.addEventListener('input', async () => {
        const q = searchInp.value;
        if (q.length < 2) { searchRes.style.display = 'none'; return; }
        const r = await fetch('buscar_ajuda.php?q=' + encodeURIComponent(q));
        const data = await r.json();
        if (data.length > 0) {
            searchRes.innerHTML = data.map(p => `<a href="ler?id=${p.id}" class="search-item"> ${p.titulo}</a>`).join('');
            searchRes.style.display = 'block';
        } else { searchRes.style.display = 'none'; }
    });
        // Fecha sugestões ao clicar fora
    document.addEventListener('click', (e) => { if(!searchRes.contains(e.target) && e.target !== searchInp) searchRes.style.display = 'none'; });



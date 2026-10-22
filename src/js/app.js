(() => {
    const themeButton = document.querySelector('#themeBtn');
    const searchInput = document.querySelector('#searchInput');
    const searchResults = document.querySelector('#search-results');

    document.querySelectorAll('.toast').forEach(toast => {
        let timeout;
        const hide = () => {
            toast.classList.add('is-hidden');
        };
        const scheduleHide = () => {
            clearTimeout(timeout);
            timeout = setTimeout(hide, 4500);
        };
        toast.addEventListener('mouseenter', () => {
            clearTimeout(timeout);
            toast.classList.remove('is-hidden');
        });
        toast.addEventListener('mouseleave', scheduleHide);
        scheduleHide();
    });

    if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode');
    themeButton?.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
    });

    searchInput?.addEventListener('input', async () => {
        if (searchInput.value.trim().length < 2) {
            searchResults.style.display = 'none';
            return;
        }
        const response = await fetch(`search.php?q=${encodeURIComponent(searchInput.value)}`);
        if (!response.ok) return;
        const posts = await response.json();
        searchResults.replaceChildren(...posts.map(post => {
            const link = document.createElement('a');
            link.href = `read.php?id=${encodeURIComponent(post.id)}`;
            link.textContent = post.titulo;
            return link;
        }));
        searchResults.style.display = posts.length ? 'block' : 'none';
    });

    document.addEventListener('click', event => {
        if (searchResults && event.target !== searchInput && !searchResults.contains(event.target)) {
            searchResults.style.display = 'none';
        }
    });

    document.querySelectorAll('.reaction').forEach(button => {
        button.addEventListener('click', async () => {
            const response = await fetch('reaction.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: button.dataset.id, type: button.dataset.type })
            });
            if (!response.ok) return;
            const data = await response.json();
            document.querySelectorAll(`.reaction[data-id="${button.dataset.id}"]`).forEach(item => {
                item.textContent = item.dataset.type === 'like' ? `👍 ${data.likes}` : `👎 ${data.dislikes}`;
            });
        });
    });
})();

function sharePost(id, title) {
    const url = `${window.location.origin}${window.location.pathname.replace(/[^/]+$/, '')}read.php?id=${id}`;
    if (navigator.share) {
        navigator.share({ title: `O Blog: ${title}`, url }).catch(() => { });
    } else {
        navigator.clipboard.writeText(url).then(() => alert('Link copiado para a área de transferência.'));
    }
}

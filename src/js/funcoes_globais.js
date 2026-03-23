    // 3. REAÇÕES AJAX
    document.querySelectorAll('.btn-reacao').forEach(b => {
        b.addEventListener('click', async () => {
            const pid = b.dataset.id, tipo = b.dataset.tipo;
            const res = await fetch('reagir.php', { 
                method: 'POST', 
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({post_id: pid, tipo: tipo}) 
            });
            const d = await res.json();
            if(d.sucesso) { 
                document.getElementById('l-'+pid).innerText = d.likes; 
                document.getElementById('d-'+pid).innerText = d.dislikes; 
            }
        });
    });
    
    // 4. UTILITÁRIOS
    function toggleComent(id) { 
        const c = document.getElementById('cb-'+id); 
        c.style.display = (c.style.display === 'block') ? 'none' : 'block'; 
    }
    
    function editCom(id) { 
        document.getElementById('ct-'+id).style.display = 'none'; 
        document.getElementById('fe-'+id).style.display = 'block'; 
    }
    
    function share(id, t) { 
        const url = window.location.origin + '/ler?id=' + id;
        if (navigator.share) {
            navigator.share({title: 'O Blog: ' + t, url: url}).catch(() => {});
        } else {
            navigator.clipboard.writeText(url);
            alert('Link copiado para a área de transferência!');
        }
    }
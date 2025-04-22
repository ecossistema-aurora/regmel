document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('table.js-grid').forEach(table => {
        const headers = Array.from(table.querySelectorAll('thead th'))
            .map(th => th.textContent.trim());
        const data = Array.from(table.querySelectorAll('tbody tr'))
            .map(tr =>
                Array.from(tr.querySelectorAll('td'))
                    .map(td => td.innerHTML.trim())
            );

        const wrapper = document.createElement('div');
        table.parentNode.insertBefore(wrapper, table);

        const grid = new gridjs.Grid({
            columns: headers.map(name => ({
                name,
                sort: !['foto', 'imagem', 'ações'].includes(name.toLowerCase()),
                formatter: cell => gridjs.html(cell),
            })),
            data: data,
            search: true,
            pagination: { enabled: true, limit: 10 },
            className: {
                table: 'table table-striped table-hover'
            }
        });
        grid.render(wrapper);
        table.remove();
    });
});

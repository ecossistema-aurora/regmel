import {
    trans,
    PREVIOUS,
    NEXT,
    SHOWING,
    RESULTS,
    OF,
    TO,
    TABLE_TYPE_A_KEYWORD,
    TABLE_NO_RECORDS_FOUND,
    TABLE_ERROR
} from "../../translator.js";

document.addEventListener('DOMContentLoaded', () => {
    const table = document.querySelector('table.js-grid');

    const headers = Array.from(table.querySelectorAll('thead th'))
        .map(th => th.textContent.trim())
        .filter(name => name);

    const columns = [
        {
            id: 'select',
            name: '',
            sort: false,
            plugin: {
                component: window.gridjs.plugins.selection.RowSelection,
            }
        },
        ...headers.map(name => ({
            name,
            sort: !['foto', 'imagem', 'ações'].includes(name.toLowerCase()),
            formatter: cell => gridjs.html(cell),
            hidden: 'id' === name.toLowerCase(),
        }))
    ];

    const data = Array.from(table.querySelectorAll('tbody tr'))
        .map(tr => Array.from(tr.querySelectorAll('td')).map(td => td.innerHTML.trim()));

    const wrapper = document.createElement('div');

    const container = selectContainer();
    container.appendChild(labelPageLimit());
    const selectWrapper = selectPageLimit();
    const selectLimit = selectWrapper.children[0];
    container.appendChild(selectWrapper);

    wrapper.appendChild(container);

    const gridContainer = document.createElement('div');
    wrapper.appendChild(gridContainer);

    table.parentNode.insertBefore(wrapper, table);

    const grid = new gridjs.Grid({
        columns,
        data,
        search: true,
        pagination: Number(selectLimit.value) === -1 ? false : {
            enabled: true,
            limit: Number(selectLimit.value),
            page: 1,
            resetPageOnUpdate: true
        },
        className: {
            table: 'table table-striped table-hover',
            th: 'bg-dark text-white',
            td: 'bg-light'
        },
        language: {
            search: { placeholder: trans(TABLE_TYPE_A_KEYWORD) },
            pagination: {
                previous: trans(PREVIOUS),
                next: trans(NEXT),
                showing: trans(SHOWING),
                results: trans(RESULTS),
                of: trans(OF),
                to: trans(TO),
            },
            noRecordsFound: trans(TABLE_NO_RECORDS_FOUND),
            error: trans(TABLE_ERROR),
        },
        plugins: [
            {
                id: 'select',
                component: window.gridjs.plugins.selection.RowSelection,
            }
        ],
    });

    grid.render(gridContainer);
    table.remove();

    container.appendChild(labelUpdateStatus());
    container.appendChild(selectUpdateStatus(grid));

    selectLimit.addEventListener('change', () => {
        const newLimit = Number(selectLimit.value);

        const newPagination = newLimit === -1 ? false : {
            enabled: true,
            limit: newLimit,
            page: 1,
            resetPageOnUpdate: true
        };

        grid
            .updateConfig({
                data,
                pagination: newPagination
            })
            .forceRender();
    });
});

const selectContainer = () => {
    const selectContainer = document.createElement('div');
    selectContainer.className = 'd-flex flex-wrap align-items-center gap-3 mb-3';

    return selectContainer;
};

const labelPageLimit = () => {
    const labelWrapper = document.createElement('div');
    labelWrapper.className = 'col-auto';

    const selectLabel = document.createElement('label');
    selectLabel.textContent = 'Itens por página:';
    selectLabel.className = 'col-form-label';
    selectLabel.setAttribute('for', 'gridLimitSelect');

    labelWrapper.appendChild(selectLabel);

    return labelWrapper;
};

const selectPageLimit = () => {
    const selectWrapper = document.createElement('div');
    selectWrapper.className = 'col-auto';

    const selectLimit = document.createElement('select');
    selectLimit.id = 'gridLimitSelect';
    selectLimit.className = 'form-select form-select-sm w-auto';

    [10, 20, 30, 50, 100, 'all'].forEach(option => {
        const opt = document.createElement('option');
        opt.value = option === 'all' ? -1 : option;
        opt.textContent = option === 'all' ? 'Mostrar todos' : option;
        if (option === 50) opt.selected = true;
        selectLimit.appendChild(opt);
    });

    selectWrapper.appendChild(selectLimit);

    return selectWrapper;
};

const labelUpdateStatus = () => {
    const labelWrapper = document.createElement('div');
    labelWrapper.className = 'col-auto';

    const labelStatus = document.createElement('label');
    labelStatus.textContent = 'Atualizar Status em Massa:';
    labelStatus.className = 'col-form-label';
    labelStatus.setAttribute('for', 'gridUpdateStatusSelect');

    labelWrapper.appendChild(labelStatus);

    return labelWrapper;
};

const selectUpdateStatus = (grid) => {
    const selectWrapper = document.createElement('div');
    selectWrapper.className = 'col d-flex flex-row gap-3';

    const selectStatus = document.createElement('select');
    selectStatus.id = 'gridUpdateStatusSelect';
    selectStatus.className = 'form-select form-select-sm w-auto';

    const status = [
        'all',
        'Enviada',
        'Recebida',
        'Sem Adesão do Município',
        'Anuída',
        'Não Anuída',
        'Selecionada',
        'Não Selecionada',
    ];

    status.forEach(option => {
        const opt = document.createElement('option');
        opt.value = option === 'all' ? -1 : option;
        opt.textContent = option === 'all' ? 'Selecione o status' : option;
        selectStatus.appendChild(opt);
    });

    selectWrapper.appendChild(selectStatus);

    const bulkUpdateButton = document.createElement('button');
    bulkUpdateButton.className = 'btn btn-primary btn-sm';
    bulkUpdateButton.textContent = 'Atualizar Status';
    bulkUpdateButton.addEventListener('click', () => {
        bulkUpdateButton.disabled = true;
        bulkUpdateButton.textContent = 'Atualizando...';

        const selectedRows = grid.config.store.state.rowSelection.rowIds;
        const selectedData = grid.config.store.state.data.rows.reduce((acc, row) => {
            if (selectedRows.includes(row.id)) {
                acc.push(row.toArray()[1]);
            }
            return acc;
        }, []);

        if (selectedData.length === 0) {
            alert('Nenhuma proposta selecionada.');
            return;
        }

        const statusValue = selectStatus.value;
        if (statusValue === 'all' || !statusValue) {
            alert('Por favor, selecione um status válido.');
            return;
        }

        fetch('/painel/admin/propostas/bulk-update-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                ids: selectedData,
                status: statusValue,
            }),
        }).then(() => {
            window.location.reload();
        });
    });
    selectWrapper.appendChild(bulkUpdateButton);

    return selectWrapper;
};

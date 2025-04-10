document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-company').forEach(button => {
        button.addEventListener('click', function () {
            const companyId = this.getAttribute('data-id');
            const token = this.getAttribute('data-token');

            fetch(`/api/organizations/${companyId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit-company').action = `/painel/admin/empresas/${companyId}/editar`;
                    document.getElementById('company-name').value = data.name;
                    document.getElementById('company-description').value = data.description ?? '';
                    document.getElementById('company-cnpj').value = data.extraFields.cnpj ?? '';
                    document.getElementById('company-site').value = data.extraFields.site ?? '';
                    document.getElementById('company-phone').value = data.extraFields.telefone ?? '';
                    document.getElementById('company-email').value = data.extraFields.email ?? '';
                    document.getElementById('company-tipo').value = data.extraFields.tipo ?? '';
                })
                .catch(error => console.error('Error:', error));
        });
    });
});

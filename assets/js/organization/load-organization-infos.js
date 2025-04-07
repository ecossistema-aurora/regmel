document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-organization').forEach(button => {
        button.addEventListener('click', function () {
            const organizationId = this.getAttribute('data-id');
            const token = this.getAttribute('data-token');

            fetch(`/api/organizations/${organizationId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit-organization').action = `/painel/admin/municipios/${organizationId}/editar`;
                    document.getElementById('organization-name').value = data.name;
                    document.getElementById('organization-description').value = data.description ?? '';
                    document.getElementById('organization-cnpj').value = data.extraFields.cnpj ?? '';
                    document.getElementById('organization-site').value = data.extraFields.site ?? '';
                    document.getElementById('organization-phone').value = data.extraFields.phone ?? '';
                    document.getElementById('organization-email').value = data.extraFields.email ?? '';
                    document.getElementById('organization-tipo').value = data.extraFields.tipo ?? '';
                    document.getElementById('organization-company-name').value = data.extraFields.companyName ?? '';
                })
                .catch(error => console.error('Error:', error));
        });
    });
});

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
                    document.getElementById('profile-img').src = data.image || 'img/city.png';
                    document.getElementById('edit-organization').action = `/painel/admin/municipios/${organizationId}/editar`;
                    document.getElementById('organization-description').value = data.description ?? '';
                    document.getElementById('organization-site').value = data.extraFields.site ?? '';
                    document.getElementById('organization-phone').value = data.extraFields.phone ?? '';
                    document.getElementById('organization-email').value = data.extraFields.email ?? '';
                })
                .catch(error => console.error('Error:', error));
        });
    });
});

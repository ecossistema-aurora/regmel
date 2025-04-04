function previewImage(event, agentId) {
    const input = event.target;
    const reader = new FileReader();
    reader.onload = function() {
        const imgElement = document.getElementById('profile-img');
        imgElement.src = reader.result;
    }
    reader.readAsDataURL(input.files[0]);
}

document.getElementById('agent-select').addEventListener('change', function() {
    const agentId = this.value;
    const token = document.getElementById("user-edit-form").getAttribute("token");

    fetch(`/api/agents/${agentId}`, {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    })
        .then(response => response.json())
        .then(data => {
            document.getElementById('name').value = data.name;
            document.getElementById('short-description').value = data.shortBio;
            document.getElementById('long-description').value = data.longBio;
            document.getElementById('cargo').value = data.extraFields.cargo;
            document.getElementById('cpf').value = data.extraFields.cpf;
            document.getElementById('profile-img').src = data.image;
        })
        .catch(error => console.error('Error:', error));
});
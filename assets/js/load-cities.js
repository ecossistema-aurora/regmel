document.addEventListener("DOMContentLoaded", () => {
    const state = document.getElementById("state");
    const city = document.getElementById("city");
    const error = document.getElementById("error-message");

    const clearCities = () => {
        city.innerHTML = `<option value="">${city.dataset.placeholder || "Selecione"}</option>`;
    };

    const showError = (msg) => {
        if (error) {
            error.textContent = msg;
            error.classList.remove("d-none");
        }
    };

    const hideError = () => {
        if (error) {
            error.textContent = "";
            error.classList.add("d-none");
        }
    };

    const fetchData = (url) =>
        fetch(url).then(res => res.ok ? res.json() : []).catch(() => []);

    state.addEventListener("change", async () => {
        clearCities();
        if (!state.value) return;
        const cities = await fetchData(`/api/states/${state.value}/cities`);
        cities.forEach(c => city.innerHTML += `<option value="${c.id}">${c.name}</option>`);
    });

    city.addEventListener("change", async () => {
        if (!error) {
            return;
        }

        hideError();
        const id = city.value;
        const name = city.options[city.selectedIndex]?.text?.trim();
        if (!id || !name) return;

        const { exists } = await fetchData(`/organizations/check-duplicate?name=${encodeURIComponent(name)}&cityId=${encodeURIComponent(id)}`);
        if (exists) {
            showError("Este município já foi credenciado.");
            state.dispatchEvent(new Event("change"));
        }
    });
});

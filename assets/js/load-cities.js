document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById('region')) {
        const region = document.getElementById("region");

        region.addEventListener("change", async () => {
            clearStates();
            clearCities();
            if (!region.value) return;
            const states = await fetchData(`/api/regions/${region.value}/states`);
            states.forEach(s => state.innerHTML += `<option value="${s.id}">${s.name}</option>`);
        });
    }

    const state = document.getElementById("state");
    const city = document.getElementById("city");
    const error = document.getElementById("error-message");

    const clearCities = () => {
        city.innerHTML = `<option value="">${city.dataset.placeholder || "Selecione"}</option>`;
    };

    const clearStates = () => {
        state.innerHTML = `<option value="">${state.dataset.placeholder || "Selecione"}</option>`;
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
            const contactEmail = window.SNP_EMAIL;
            showError(`Este município já foi credenciado. Entre em contato com ${contactEmail} para mais informações.`);
            state.dispatchEvent(new Event("change"));
        }
    });
});

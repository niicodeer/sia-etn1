function clientError() {
    /* TODO: Mostrar un mensaje de error */
}

const getProvincias = (config) => {
    return new Promise((resolve, reject) => {
        const client = new XMLHttpRequest();

        client.onload = () => {
            if (client.status === 200) {
                const response = JSON.parse(client.responseText);
                resolve(console.log(response.provincias));
            } else {
                reject(new Error(clientError()));
            }
        };
        client.open(config.method, config.url);
        client.send();
    });
};

document.addEventListener("DOMContentLoaded", () => {
    const config = {
        url: "https://apis.datos.gob.ar/georef/api/provincias",
        method: "GET",
    };

    getProvincias(config);
});

document.addEventListener("DOMContentLoaded", () => {

    fetch("api/movies.php")
        .then(response => response.json())
        .then(data => {
            const grid = document.getElementById("moviesGrid");

            grid.innerHTML = data.map(movie => `
                <div class="card">
                    <img src="${movie.image_url}" alt="${movie.title}">
                    <h3>${movie.title}</h3>
                    <a href="${movie.image_url}" target="_blank">
                        Ver imagen
                    </a>
                </div>
            `).join("");
        })
        .catch(error => {
            console.error("Error:", error);
        });

});
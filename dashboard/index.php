<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watchlist</title>

    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <header>
        <h1>Movies Next</h1>
    </header>

    <main>

        <form class="movie-form" method="post" onsubmit="addMovie(event, this)">
            <input type="text" name="movie_title" class="movie-title" placeholder="Movie Title" required>

            <div class="genre-container">
                <select class="genres" name="genre_id" required>
                    <option value="0">Select Genre</option>

                </select>
                <button type="button" onclick="showGenreModal()">New Genre</button>
            </div>

            <button type="submit">Add Movie</button>

        </form>

        <div class="movies-filter-container">
            <div class="filter-container">
                <select class="filter genres" name="filter_id">
                    <option value="0">All Genre</option>

                </select>
                <button type="button" onclick="showMovies()">Filter</button>
            </div>
            <div class="movies-container"></div>
        </div>
    </main>

    <div class="movie-container-template">
        <span class="title"></span>
        <span class="genre"></span>
        <button class="action"></button>
    </div>

    <!-- Genre Popup -->
    <div class="genre-popup-container">
        <div class="genre-popup">
            <img src="../assets/movie.png" alt="Genre Illustration">
            <form class="genre-form" action="../actions/process.php?action=add_genre" method="post" onsubmit="addGenre(event)">
                <input type="text" name="genre_title" class="genre-title" placeholder="Genre Title">

                <button type="submit">Add Genre</button>
            </form>

            <span onclick="closeModal()"><img src="../assets/cross.png" alt="Cross Icon"></span>
        </div>
    </div>
    <script>
        async function toggleWatched(id, button) {
            let buttonText = button.innerText
            const updateStatus = buttonText == "Watched" ? "Unwatched" : "Watched"
            let formData = new FormData()

            formData.append("action", "toggle_watched")
            formData.append("movie_id", id)
            formData.append("status", updateStatus)

            try {
                const response = await fetch("../actions/process.php", {
                    method: "POST",
                    body: formData
                })

                if (!response.ok) throw new Error(response.statusText);

                const data = await response.text()

                if (data.trim() === "error")
                    throw new Error("Error while changing the status");

                button.innerText = buttonText === "Watched" ? "Mark As Watched" : "Watched"
            } catch (error) {
                console.warn(error);

            }

        }

        function showGenreModal() {
            document.querySelector(".genre-popup-container").style.display = "grid"
            document.querySelector(".genre-title").focus()
        }

        function closeModal() {
            document.querySelector(".genre-popup-container").style.display = "none"
        }

        async function addGenre(event, genreForm) {
            event.preventDefault()

            try {
                const genreInput = document.querySelector(".genre-title")
                if (!genreInput.value) return

                let formData = new FormData()
                formData.append("genre_title", genreInput.value)

                const response = await fetch("../actions/process.php?action=add_genre", {
                    method: "POST",
                    body: formData
                })

                if (!response.ok) return
                const data = await response.text()
                if (data === "error") alert("Error while adding genre")

                genreInput.value = "Genre Added Successfully!"
                setTimeout(() => {
                    genreInput.value = ""
                }, 500);

                showGenres()
            } catch (error) {
                console.error("Error While Adding Genre: ", error);
            }

        }

        async function showMovies() {
            try {
                const filter = document.querySelector(".movies-filter-container select").value
                const parentContainer = document.querySelector(".movies-container")
                const deleteContainers = Array.from(parentContainer.getElementsByClassName("movie-container"))

                const response = await fetch("../actions/process.php?action=show_movies&filter=" + filter);

                if (!response.ok) throw new Error("Network response was not ok");

                const data = await response.json();

                if (deleteContainers) {
                    deleteContainers.forEach(container => {
                        container.remove()
                    });
                }

                data.forEach(movie => {
                    const template = document.querySelector(".movie-container-template").cloneNode(true)
                    const button = template.querySelector(".action")
                    const buttonText = movie.status == "Unwatched" ? "Mark As Watched" : "Watched"

                    template.classList.add("movie-container")
                    template.querySelector(".title").innerText = movie.movie
                    template.querySelector(".genre").innerText = movie.genre
                    button.innerText = buttonText
                    button.addEventListener("click", () => toggleWatched(movie.id, button))

                    parentContainer.append(template)
                });

            } catch (error) {
                console.error("Fetch error:", error);
            }


        }

        async function showGenres() {
            try {
                const response = await fetch("../actions/process.php?action=show_genres")

                if (!response.ok) throw new Error("Response was not okay");

                const genres = await response.json()
                const genreParents = Array.from(document.querySelectorAll(".genres"))

                genreParents.forEach((genreParent) => {
                    const defaultOption = genreParent.firstElementChild
                    genreParent.innerHTML = ""
                    genreParent.appendChild(defaultOption)

                    genres.forEach(genre => {
                        let optionElement = document.createElement("option")

                        optionElement.value = genre.genre_id
                        optionElement.innerText = genre.genre_title
                        genreParent.append(optionElement)

                    });
                })

            } catch (error) {
                console.warn("Genre Error: ", error);
            }
        }

        async function addMovie(event, form) {
            event.preventDefault()

            const movieTitle = form.querySelector(".movie-title")
            const genre = form.querySelector(".genres")

            if (!(genre.value && movieTitle.value.trim())) return

            try {
                let formData = new FormData(form)
                formData.append("action", "add_movie")

                const response = await fetch("../actions/process.php", {
                    method: "POST",
                    body: formData
                })
                if (!response.ok) {
                    throw new Error(`Server responded with ${response.status}`);
                }

                const data = await response.text()
                if (data.trim() === "error") {
                    throw new Error("Error While Adding Movie");
                }

                movieTitle.value = "Movie Added Successfully"
                genre.value = "0"

                setTimeout(() => movieTitle.value = "", 500)

                showMovies()

            } catch (error) {
                console.warn(error);
            }
        }

        window.onload = function() {
            let genrePopup = document.querySelector(".genre-popup-container")
            if (genrePopup) {
                document.addEventListener("keyup", (event) => {
                    if (event.key === "Escape" && genrePopup.style.display !== "none")
                        closeModal()
                })
            }

            showMovies()
            showGenres()
        }
    </script>
</body>

</html>
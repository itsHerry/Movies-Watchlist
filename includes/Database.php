<?php

class Database
{
    private $mysql = null;
    private $result = null;
    private $statement = null;

    public function __construct()
    {
        mysqli_report(false);
        $this->mysql = mysqli_connect("localhost", "root", "", "watchlist");
    }

    public function login($email, $password)
    {
        $query = "SELECT * FROM user WHERE email = ? AND password = ?";

        if (!($this->statement = mysqli_prepare($this->mysql, $query))) {
            file_put_contents("logs.txt", "MYSQLI_PREPARE: " . mysqli_error($this->mysql));
        }
        mysqli_stmt_bind_param($this->statement, "ss", $email, $password);

        if (!(mysqli_stmt_execute($this->statement))) {
            file_put_contents("logs.txt", "MYSQLI_EXECUTE: " . mysqli_error($this->mysql));
        }

        return $this->result = mysqli_stmt_get_result($this->statement);
    }

    public function fetch_categories()
    {
        $query = "SELECT * FROM genre";


        if (!($this->result = mysqli_query($this->mysql, $query))) {
            file_put_contents("logs.txt", "FETCH_CATEGORIES: " . mysqli_error($this->mysql));
        }

        return $this->result;
    }

    public function fetch_movies($filter)
    {
        if ($filter == "0") {
            $query = "SELECT movie.movie_id AS id, movie.movie_title AS movie, movie.status AS status, genre.genre_title AS genre FROM movie JOIN genre USING(genre_id)";
        } else {
            $query = "SELECT movie.movie_id AS id, movie.movie_title AS movie, movie.status AS status, genre.genre_title AS genre FROM movie JOIN genre USING(genre_id) WHERE genre.genre_id = $filter";
        }

        if (!($this->result = mysqli_query($this->mysql, query: $query))) {
            file_put_contents("logs.txt", "fetch_movies: " . mysqli_error($this->mysql));
        }

        return $this->result;
    }

    public function add_genre($genre_title)
    {
        $query = "INSERT INTO genre (genre_title) VALUES (?)";

        if (!($this->statement = mysqli_prepare($this->mysql, $query))) {
            file_put_contents("logs.txt", "MYSQLI_PREPARE: " . mysqli_error($this->mysql));
            return false;
        }


        mysqli_stmt_bind_param($this->statement, "s", $genre_title);

        if (!(mysqli_stmt_execute($this->statement))) {
            file_put_contents("logs.txt", "MYSQLI_EXECUTE: " . mysqli_error($this->mysql));
            return false;
        }

        return true;
    }

    public function add_movie(string $movie, int $genre_id)
    {
        $query = "INSERT INTO movie (genre_id, movie_title) VALUES (?,?)";

        if (!($this->statement = mysqli_prepare($this->mysql, $query))) {
            file_put_contents("logs.txt", "MYSQLI_PREPARE In Add_Movie: " . mysqli_error($this->mysql));
            return false;
        }

        mysqli_stmt_bind_param($this->statement, "is",  $genre_id, $movie);

        if (!(mysqli_stmt_execute($this->statement))) {
            file_put_contents("logs.txt", "MYSQLI_EXECUTE: " . mysqli_error($this->mysql));
            return false;
        }

        return true;
    }

    public function toggle_watched(int $id, string $status)
    {
        $query = "UPDATE movie SET status = ? WHERE movie_id = ?";

        if (!($this->statement = mysqli_prepare($this->mysql,   $query))) {
            file_put_contents("logs.txt", "MYSQLI_PREPARE: " . mysqli_error($this->mysql));
            return false;
        }

        mysqli_stmt_bind_param($this->statement, "si", $status, $id);

        if (!(mysqli_stmt_execute($this->statement))) {
            file_put_contents("logs.txt", "Toggle_Watched MYSQLI_EXECUTE: " . mysqli_error($this->mysql));
            return false;
        }

        return true;
    }

    public function __destruct()
    {
        if ($this->result !== null && $this->result !== false) {
            mysqli_free_result($this->result);
        }

        if ($this->statement !== null && $this->result !== false) {
            mysqli_stmt_free_result($this->statement);
        }

        mysqli_close($this->mysql);
    }
}

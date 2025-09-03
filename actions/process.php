<?php
require_once "../includes/Database.php";

$database = new Database();
if (isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $result =  $database->login($email, $password);
    if ($result && $result->num_rows) {
        $user = mysqli_fetch_assoc($result);
        // $_SESSION["user"] = $user;
        header("location:../dashboard/index.php");
    } else {
        header("location:../index.php?message=Invalid email or password");
    }
} elseif (isset($_GET["action"]) && $_GET["action"] == "show_movies") {
    $result = $database->fetch_movies($_GET["filter"]);
    echo $result->num_rows ? json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC)) : "";
} elseif (isset($_GET["action"]) && $_GET["action"] == "show_genres") {
    $result = $database->fetch_categories();

    if ($result && $result->num_rows) {
        echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
    }
} elseif (isset($_GET["action"]) && $_GET["action"] == "add_genre") {
    if (!($database->add_genre($_POST["genre_title"]))) {
        echo "error";
    }
} elseif (isset($_POST["action"]) && $_POST["action"] == "add_movie") {
    $movie = $_POST["movie_title"];
    $genre_id = $_POST["genre_id"];
    if (!($database->add_movie($movie, $genre_id))) {
        echo "error";
    }
} elseif (isset($_POST["action"]) && $_POST["action"] == "toggle_watched") {
    $id = $_POST["movie_id"];
    $status = $_POST["status"];
    if (!($database->toggle_watched($id, $status))) {
        echo "error";
    }
}

<?php

require_once(__DIR__ . '/../config/database.php');

class Movie {

    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {

        $sql = "SELECT id, title, image_url FROM movies";
        $result = $this->conn->query($sql);

        $movies = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $movies[] = $row;
            }
        }

        return $movies;
    }
}
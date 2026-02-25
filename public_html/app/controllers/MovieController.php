<?php

require_once(__DIR__ . '/../models/Movie.php');

class MovieController {

    public function index() {
        $movie = new Movie();
        return $movie->getAll();
    }
}
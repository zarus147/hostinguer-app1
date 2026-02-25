<?php

require_once(__DIR__ . '/../controllers/MovieController.php');

$controller = new MovieController();
return $controller->index();
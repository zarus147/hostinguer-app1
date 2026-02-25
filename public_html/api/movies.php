<?php

header("Content-Type: application/json");

require_once("../app/routes/web.php");

echo json_encode($controller->index());
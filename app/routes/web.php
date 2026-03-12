<?php

require_once __DIR__ . '/../controllers/VisitaController.php';

$controller = new VisitaController();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $controller->index();
        break;

    case 'POST':

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(["error" => "JSON inválido"]);
            exit;
        }

        $controller->store($data);
        break;

    case 'DELETE':

        // 🔴 eliminar todo
        if (isset($_GET['all'])) {
            $controller->destroyAll();
            break;
        }

        // 🔴 eliminar uno
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "ID requerido"]);
            exit;
        }

        $controller->destroy($id);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
}
<?php

require_once __DIR__ . '/../controllers/VehiculoController.php';

$controller = new VehiculoController();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        if (isset($_GET['resumen'])) {
            $controller->getResumen();
            break;
        }
        // obtener primer día por placa
        if (isset($_GET['firstDay'])) {
            $controller->getFirstDay($_GET['firstDay']);
            break;
        }

        // obtener bitácora específica
        if (isset($_GET['bitacoraId'])) {
            $controller->getBitacora($_GET['bitacoraId']);
            break;
        }

        // obtener días de una placa
        if (isset($_GET['placa'])) {
            $controller->getDaysByPlaca($_GET['placa']);
            break;
        }

        // obtener todos
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

        // eliminar todos
        if (isset($_GET['all'])) {
            $controller->destroyAll();
            break;
        }

        // eliminar uno
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
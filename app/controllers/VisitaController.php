<?php

require_once __DIR__ . '/../models/Visita.php';

class VisitaController {

    private $model;

    public function __construct() {
        $this->model = new Visita();
    }

    public function index() {
        echo json_encode($this->model->getAll());
    }

    public function store($data) {
        $success = $this->model->create($data);
        echo json_encode(["success" => $success]);
    }

    public function destroy($id) {
        $success = $this->model->delete($id);
        echo json_encode(["success" => $success]);
    }
    
    public function destroyAll() {
        $success = $this->model->deleteAll();
        echo json_encode(["success" => $success]);
    }
}
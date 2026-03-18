<?php

require_once __DIR__ . '/../models/Vehiculo.php';

class VehiculoController {

    private $model;

    public function __construct() {
        $this->model = new Vehiculo();
    }

    public function getResumen() {
        $data = $this->model->getAll();
        echo json_encode($data);
    }

    public function index() {
        echo json_encode($this->model->getAll());
    }

    public function store($data) {
        $result = $this->model->create($data);
        echo json_encode($result);
    }

    public function destroy($id) {
        $success = $this->model->delete($id);
        echo json_encode(["success" => $success]);
    }

    public function destroyAll() {
        $success = $this->model->deleteAll();
        echo json_encode(["success" => $success]);
    }

      // obtener primer día de una placa
    public function getFirstDay($placa) {

        $data = $this->model->getFirstDayByPlaca($placa);

        echo json_encode($data);
    }

    // obtener todos los días de una placa (para botones)
    public function getDaysByPlaca($placa) {

        $data = $this->model->getDaysByPlaca($placa);

        echo json_encode($data);
    }

    // obtener una bitácora específica
    public function getBitacora($id) {

        $data = $this->model->getBitacora($id);

        echo json_encode($data);
    }
}
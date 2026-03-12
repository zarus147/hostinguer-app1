<?php

require_once __DIR__ . '/../config/database.php';

class Visita {

    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getAll() {

        $sql = "
            SELECT 
                v.*,
                c.nombre,
                c.tipo,
                c.doc,
                c.reinfo
            FROM visitas v
            LEFT JOIN contactos c
            ON v.id = c.visitaId
            WHERE v.status = 1
            ORDER BY v.fecha DESC, v.horaInicio DESC
        ";

        $result = $this->conn->query($sql);

        if (!$result) {
            http_response_code(500);
            echo json_encode(["error" => $this->conn->error]);
            exit;
        }

        $visitas = [];

        while ($row = $result->fetch_assoc()) {

            $id = $row['id'];

            if (!isset($visitas[$id])) {

                $visitas[$id] = $row;
                $visitas[$id]['contactos'] = [];
            }

            if ($row['nombre']) {

                $visitas[$id]['contactos'][] = [
                    "nombre" => $row['nombre'],
                    "tipo" => $row['tipo'],
                    "doc" => $row['doc'],
                    "reinfo" => $row['reinfo']
                ];
            }
        }

        return array_values($visitas);
    }

    public function create($data) {

        $stmt = $this->conn->prepare("
            INSERT INTO visitas 
            (
                agente,
                cargo,
                fecha,
                horaInicio,
                horaFin,
                duracion,
                zona,
                tipoZona,
                provincia,
                distrito,
                motivo,
                estado,
                observaciones,
                acciones,
                nContactos,
                nombresContactos
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            http_response_code(500);
            echo json_encode(["error" => $this->conn->error]);
            exit;
        }

        $stmt->bind_param(
            "ssssssssssssssis",
            $data['agente'],
            $data['cargo'],
            $data['fecha'],
            $data['horaInicio'],
            $data['horaFin'],
            $data['duracion'],
            $data['zona'],
            $data['tipoZona'],
            $data['provincia'],
            $data['distrito'],
            $data['motivo'],
            $data['estado'],
            $data['observaciones'],
            $data['acciones'],
            $data['nContactos'],
            $data['nombresContactos']
        );

        $stmt->execute();

        $visitaId = $this->conn->insert_id;


        if (!empty($data['contactos'])) {

            foreach ($data['contactos'] as $c) {

                $stmtC = $this->conn->prepare("
                    INSERT INTO contactos
                    (visitaId, nombre, tipo, doc, reinfo)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmtC->bind_param(
                    "issss",
                    $visitaId,
                    $c['nombre'],
                    $c['tipo'],
                    $c['doc'],
                    $c['reinfo']
                );

                $stmtC->execute();
            }
        }

        return true;
    }

    public function delete($id) {

        $stmt = $this->conn->prepare("
            UPDATE visitas
            SET status = 0
            WHERE id = ?
        ");

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }


    public function deleteAll() {

        $sql = "
            UPDATE visitas
            SET status = 0
        ";

        return $this->conn->query($sql);
    }
}
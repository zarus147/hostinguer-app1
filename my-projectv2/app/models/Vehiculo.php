<?php

require_once __DIR__ . '/../config/database.php';

class Vehiculo {

    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getAll() {

        $sql = "
            SELECT 
                v.*,
                d.id as bitacoraDiaId,
                d.fecha,
                d.zona,
                d.conductor1,
                d.dni1,
                d.conductor2,
                d.dni2,
                d.fuelIni,
                d.fuelAbast,
                d.fuelFin,
                d.fuelConsumo,
                d.fuelComp,

                a.id as actividadId,
                a.frente,
                a.descripcion,
                a.kmIni,
                a.kmFin,
                a.hrIni,
                a.hrFin,
                a.observaciones

            FROM vehiculos v

            LEFT JOIN bitacora_dias d
            ON v.id = d.vehiculoId

            LEFT JOIN actividades a
            ON d.id = a.bitacoraDiaId

            WHERE v.status = 1

            ORDER BY v.placa, d.fecha
        ";

        $result = $this->conn->query($sql);

        if (!$result) {
            http_response_code(500);
            echo json_encode(["error" => $this->conn->error]);
            exit;
        }

        $vehiculos = [];

        while ($row = $result->fetch_assoc()) {

            $vehiculoId = $row['id'];
            $diaId = $row['bitacoraDiaId'];

            if (!isset($vehiculos[$vehiculoId])) {

                $vehiculos[$vehiculoId] = $row;
                $vehiculos[$vehiculoId]['days'] = [];
            }

            if ($diaId) {

                if (!isset($vehiculos[$vehiculoId]['days'][$diaId])) {

                    $vehiculos[$vehiculoId]['days'][$diaId] = [
                        "header" => [
                            "zona"=>$row['zona'],
                            "placa"=>$row['placa'],
                            "fecha"=>$row['fecha'],
                            "conductor1"=>$row['conductor1'],
                            "dni1"=>$row['dni1'],
                            "conductor2"=>$row['conductor2'],
                            "dni2"=>$row['dni2'],
                            "fuelIni"=>$row['fuelIni'],
                            "fuelAbast"=>$row['fuelAbast'],
                            "fuelFin"=>$row['fuelFin'],
                            "fuelConsumo"=>$row['fuelConsumo'],
                            "fuelComp"=>$row['fuelComp']
                        ],
                        "activities" => []
                    ];
                }

                if ($row['actividadId']) {

                    $vehiculos[$vehiculoId]['days'][$diaId]["activities"][] = [
                        "frente"=>$row['frente'],
                        "desc"=>$row['descripcion'],
                        "kmIni"=>$row['kmIni'],
                        "kmFin"=>$row['kmFin'],
                        "hrIni"=>$row['hrIni'],
                        "hrFin"=>$row['hrFin'],
                        "obs"=>$row['observaciones']
                    ];
                }
            }
        }

        return array_values($vehiculos);
    }

    public function createv($data) {

        echo "<pre>";
        print_r($data);
        echo "</pre>";
        exit;

    }
    
    public function getFirstDayByPlaca($placa) {

        $stmt = $this->conn->prepare("
            SELECT 
                bd.*,
                v.placa,
                v.modelo,
                v.equipo,
                v.id as vehiculoId
            FROM bitacora_dias bd
            JOIN vehiculos v ON v.id = bd.vehiculoId
            WHERE v.placa = ?
            ORDER BY bd.fecha ASC
        ");

        $stmt->bind_param("s", $placa);
        $stmt->execute();

        $days = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (!$days) {
            return null;
        }

        $resultDays = [];

        foreach ($days as $index => $d) {

            $stmt2 = $this->conn->prepare("
                SELECT *
                FROM actividades
                WHERE bitacoraDiaId = ?
            ");

            $stmt2->bind_param("i", $d['id']);
            $stmt2->execute();

            $activities = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

            $resultDays[] = [
                "dayNumber" => $index + 1,
                "bitacoraId" => $d['id'],
                "header" => $d,
                "activities" => $activities
            ];
        }

        return [
            "vehiculoId" => $days[0]["vehiculoId"],
            "placa" => $placa,
            "totalDays" => count($resultDays),
            "days" => $resultDays
        ];
    }


    public function create($data) {
    try {

        $this->conn->begin_transaction();

        $h = $data["header"];
        $activities = $data["activities"] ?? [];

        if (empty($h['placa'])) {
            throw new Exception("Placa requerida");
        }

        $vehiculoId = $h['vehiculoId'] ?? null;

        $placa  = $h['placa'];
        $modelo = $h['modelo'] ?? null;
        $equipo = $h['equipo'] ?? null;

        /* =========================
           1️⃣ VEHICULO
        ========================= */

        if (!empty($vehiculoId)) {

            $stmt = $this->conn->prepare("
                UPDATE vehiculos
                SET placa = ?, modelo = ?, equipo = ?
                WHERE id = ?
            ");

            $stmt->bind_param("sssi", $placa, $modelo, $equipo, $vehiculoId);
            $stmt->execute();

        } else {

            $stmt = $this->conn->prepare("
                INSERT INTO vehiculos (placa, modelo, equipo)
                VALUES (?, ?, ?)
            ");

            $stmt->bind_param("sss", $placa, $modelo, $equipo);
            $stmt->execute();

            $vehiculoId = $this->conn->insert_id;
        }

        if (empty($h['fecha'])) {
            throw new Exception("Fecha requerida");
        }

        $fuelIni = $h['fuelIni'] ?: 0;
        $fuelAbast = $h['fuelAbast'] ?: 0;
        $fuelFin = $h['fuelFin'] ?: 0;
        $fuelConsumo = $h['fuelConsumo'] ?: 0;
        $fuelComp = $h['fuelComp'] ?: 0;

            $bitacoraDiaId = $h['bitacoraId'] ?? null;

            /* =========================
            2️⃣ BITACORA
            ========================= */

            if (!empty($bitacoraDiaId)) {

                $stmt = $this->conn->prepare("
                    UPDATE bitacora_dias
                    SET vehiculoId = ?, fecha = ?, zona = ?, conductor1 = ?, dni1 = ?, conductor2 = ?, dni2 = ?,
                        fuelIni = ?, fuelAbast = ?, fuelFin = ?, fuelConsumo = ?, fuelComp = ?
                    WHERE id = ?
                ");

                $stmt->bind_param(
                    "issssssdddddi",
                    $vehiculoId,
                    $h['fecha'],
                    $h['zona'],
                    $h['conductor1'],
                    $h['dni1'],
                    $h['conductor2'],
                    $h['dni2'],
                    $fuelIni,
                    $fuelAbast,
                    $fuelFin,
                    $fuelConsumo,
                    $fuelComp,
                    $bitacoraDiaId
                );

                $stmt->execute();

                // borrar actividades anteriores
                $stmt = $this->conn->prepare("
                    DELETE FROM actividades WHERE bitacoraDiaId = ?
                ");

                $stmt->bind_param("i", $bitacoraDiaId);
                $stmt->execute();

            } else {

                $stmt = $this->conn->prepare("
                    INSERT INTO bitacora_dias
                    (vehiculoId, fecha, zona, conductor1, dni1, conductor2, dni2,
                    fuelIni, fuelAbast, fuelFin, fuelConsumo, fuelComp)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
                ");

                $stmt->bind_param(
                    "issssssddddd",
                    $vehiculoId,
                    $h['fecha'],
                    $h['zona'],
                    $h['conductor1'],
                    $h['dni1'],
                    $h['conductor2'],
                    $h['dni2'],
                    $fuelIni,
                    $fuelAbast,
                    $fuelFin,
                    $fuelConsumo,
                    $fuelComp
                );

                $stmt->execute();

                $bitacoraDiaId = $this->conn->insert_id;
            }

            /* =========================
            3️⃣ ACTIVIDADES
            ========================= */

            foreach ($activities as $act) {

                if (
                    empty($act['frente']) &&
                    empty($act['desc']) &&
                    empty($act['kmIni']) &&
                    empty($act['hrIni'])
                ) {
                    continue;
                }

                $kmIni = $act['kmIni'] ?: 0;
                $kmFin = $act['kmFin'] ?: 0;

                $stmt = $this->conn->prepare("
                    INSERT INTO actividades
                    (bitacoraDiaId, frente, descripcion, kmIni, kmFin, hrIni, hrFin, observaciones)
                    VALUES (?,?,?,?,?,?,?,?)
                ");

                $stmt->bind_param(
                    "issiiiss",
                    $bitacoraDiaId,
                    $act['frente'],
                    $act['desc'],
                    $kmIni,
                    $kmFin,
                    $act['hrIni'],
                    $act['hrFin'],
                    $act['obs']
                );

                $stmt->execute();
            }

            $this->conn->commit();

            return [
                "status" => true,
                "message" => "Datos procesados correctamente",
                "bitacoraId" => $bitacoraDiaId,
                "vehiculoId" => $vehiculoId
            ];

        } catch (Exception $e) {

            $this->conn->rollback();

            return [
                "status" => false,
                "message" => "Error al procesar datos",
                "error" => $e->getMessage()
            ];
        }
    }

    public function delete($id) {

        $stmt = $this->conn->prepare("
            UPDATE vehiculos
            SET status = 0
            WHERE id = ?
        ");

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    public function deleteAll() {

    $this->conn->query("DELETE FROM actividades");
    $this->conn->query("DELETE FROM bitacora_dias");
    $sql = "DELETE FROM vehiculos";

    return $this->conn->query($sql);
}
}
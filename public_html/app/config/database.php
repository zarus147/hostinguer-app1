<?php

class Database {

    private $host = "db";               // 🔥 nombre del servicio en docker-compose
    private $dbname = "movies_db";      // igual que MYSQL_DATABASE
    private $user = "user";             // igual que MYSQL_USER
    private $pass = "password";         // igual que MYSQL_PASSWORD

    public function connect() {

        $conn = new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->dbname
        );

        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }

        return $conn;
    }
}
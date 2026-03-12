<?php

class Database {

    private $environment = 'docker';

    private $docker_config = [
        'host'   => 'db',             
        'dbname' => 'empresa_db',      
        'user'   => 'user',
        'pass'   => 'password'
    ];



    private $hostinger_config = [
        'host'   => 'localhost',       
        'dbname' => 'u123456_empresa_db',
        'user'   => 'u123456_user',
        'pass'   => 'TU_PASSWORD_REAL'
    ];



    public function connect() {
        if ($this->environment === 'docker') {
            $config = $this->docker_config;
        } else {
            $config = $this->hostinger_config;
        }

        $conn = new mysqli(
            $config['host'],
            $config['user'],
            $config['pass'],
            $config['dbname']
        );

        if ($conn->connect_error) {
            die("Error de conexión a la base de datos: " . $conn->connect_error);
        }

        $conn->set_charset("utf8mb4");

        return $conn;
    }
}
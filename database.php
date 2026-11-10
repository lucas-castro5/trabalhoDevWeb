<?php

class Database
{
    public static function getConnection(): PDO
    {
        $host = "127.0.0.1";
        $dbname = "petcare";
        $user = "root";
        $pass = ""; 
        $port = "3306";

        $dns = "mysql:host={$host};dbname={$dbname};port={$port};charset=utf8mb4";

        return new PDO($dns, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
}
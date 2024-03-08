<?php
session_start(); // Добавляем session_start() здесь

$host = "localhost";
$username = "root";
$password = "";
$database = "Test_project";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

// Устанавливаем кодировку utf-8
$conn->set_charset("utf8");
?>

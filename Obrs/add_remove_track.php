<?php
session_start();
include("../db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $track_id = $_POST['track_id'];

    // Проверяем, есть ли трек уже в таблице пользователя
    $check_query = "SELECT * FROM music_$user_id WHERE track_id = $track_id";
    $check_result = $conn->query($check_query);

    if ($check_result->num_rows == 0) {
        // Если трека еще нет в таблице пользователя, добавляем его
        $insert_query = "INSERT INTO music_$user_id (track_id) VALUES ($track_id)";
        $conn->query($insert_query);
        header("Location: ../index.php") ;
    } else {
        // Если трек уже есть в таблице пользователя, удаляем его
        $delete_query = "DELETE FROM music_$user_id WHERE track_id = $track_id";
        $conn->query($delete_query);
        header("Location: ../index.php");
    }
} else {
    // Обрабатываем несанкционированный доступ или недопустимый метод запроса
    echo "Несанкционированный доступ или недопустимый метод запроса";
}

$conn->close();
?>

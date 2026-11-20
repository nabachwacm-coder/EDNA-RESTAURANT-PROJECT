<?php
// Demonstration: connect to the DBMS and create the Perissos database.
require_once __DIR__ . '/config/connect.php';
$sql = "CREATE DATABASE IF NOT EXISTS perissos_events_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($conn, $sql)) echo 'Perissos database created or already exists. Now import database/perissos_events_db.sql.';
else echo 'Database not created: ' . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
?>
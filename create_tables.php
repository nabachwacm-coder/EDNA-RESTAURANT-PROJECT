<?php
// Demonstration: execute the SQL script that creates the Perissos tables.
require_once __DIR__ . '/config/connect.php';
$sqlFile = __DIR__ . '/database/perissos_events_db.sql';
$sql = file_get_contents($sqlFile);
if ($sql === false) {
    die('Could not read the database SQL file.');
}
if (mysqli_multi_query($conn, $sql)) {
    do {
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_more_results($conn) && mysqli_next_result($conn));
    if (mysqli_errno($conn)) {
        die('A database statement failed: ' . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8'));
    }
    echo '<h2>Perissos database tables created successfully.</h2>';
    echo '<p>Open phpMyAdmin and select perissos_events_db to view the tables.</p>';
} else {
    die('Tables were not created: ' . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8'));
}
?>
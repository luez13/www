<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logData = json_decode(file_get_contents('php://input'), true);
    $logText = "";

    foreach ($logData as $entry) {
        $logText .= $entry . "\n";
    }

    file_put_contents('log_data.txt', $logText, FILE_APPEND);
    echo 'Logs saved successfully';
} else {
    echo 'Invalid request method';
}
?>
<?php

// file path
$logFilePath = "scraping_log.txt";
$output_dir = "loaded_pages/";

// Open log file for writing
$logFile = fopen($logFilePath, "a");

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date("Y-m-d H:i:s");
    $logMessage = "[$timestamp] $message\n";
    fwrite($logFile, $logMessage);
}

?>
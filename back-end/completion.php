<?php

require "config.php";

$files = glob($output_dir . '*');
$i = 0;
foreach ($files as $file) {
    if (is_file($file)) {
        $i++;
    }
}

// Create a response array
$response = [
    'htmlFiles' => $i,
];

// Set the response header to indicate JSON content
header('Content-Type: application/json');

// Send the JSON response
echo json_encode($response);

?>
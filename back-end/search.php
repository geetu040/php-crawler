<?php

require "config.php";
require "search-utils.php";

// Read the JSON input data
$requestData = json_decode(file_get_contents('php://input'), true);
$query = $requestData['searchText'];

// ======> PARAMS

// $query = "Pacman";
$n = 100;

// ======> WORK

$domObjects = parseHtmlFiles($output_dir);
$domData = extractDom($domObjects);
$relevant = getTopN($domData, $query, $n);
$answer = makeOne($relevant);
$answer = highlight($answer, $query);

// Prepare the response
$response = [
	'message' => $answer,
];
// Set the response header to indicate JSON content
header('Content-Type: application/json');
// Send the JSON response
echo json_encode($response);


?>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$filePath = 'data.json';

// Define a function to handle GET requests
function get_data() {
  // Implement logic to retrieve data (e.g., from database)
  $filePath = 'data.json';
  $data = file_get_contents($filePath);
  echo $data;
}

// Define a function to handle POST requests
function post_data() {
  // Implement logic to handle POST data (e.g., save to database)
  $filePath = 'data.json';
  $currentJsonData = file_get_contents($filePath);

  $receivedData = file_get_contents('php://input');
  $jsonDataReceived = json_decode($receivedData, true);

  // Check if the JSON data is valid
  if (json_last_error()!== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON format']);
    return;
  }

  $requiredParams = ['Name', 'Price', 'Description', 'Ingredients'];

  // Check for required parameters and their data types
  foreach ($requiredParams as $param) {
    if (!isset($jsonDataReceived[$param]) ||!is_string($jsonDataReceived[$param])) {
      http_response_code(400);
      echo json_encode(['error' => $param. 's required and must be a string']);
      return;
    }
  }

  // Check for additional parameters
  $extraParams = array_diff(array_keys($jsonDataReceived), $requiredParams);
  if (!empty($extraParams)) {
    http_response_code(400);
    echo json_encode(['error' => 'Additional parameters are not allowed: '. implode(', ', $extraParams)]);
    return;
  }

  // Check for the presence of the API key in the request headers
  if (!isset($_SERVER['HTTP_AUTH'])) {
    http_response_code(401);
    echo json_encode(['error' => 'API key is required']);
    return;
  }

  // Validate the API key
  $apiKey = $_SERVER['HTTP_AUTH'];
  if ($apiKey!== '30112001') {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid API key']);
    return;
  }



  $jsonArray = json_decode($currentJsonData, true);
  // Find the maximum id value in the current JSON data
  $maxId = 0;
  foreach ($jsonArray as $item) {
    if (isset($item['id']) &&is_numeric($item['id']) &&$item['id'] >$maxId) {
      $maxId = $item['id'];
    }
  }

  // Generate the new id value as one greater than the maximum id value
  $jsonDataReceived['id'] = $maxId + 1;

  

  // Append the received data to the associative array
  $jsonArray[] = $jsonDataReceived;
  // Encode the updated associative array back to JSON
  $updatedJsonData = json_encode($jsonArray, JSON_PRETTY_PRINT);
  // Write the updated JSON data back to the file
  file_put_contents($filePath, $updatedJsonData);

  // Send a response (e.g., success message)
  http_response_code(200);
  echo json_encode(['message' => 'Data received and added successfully']);
}

// Check the request method
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  get_data();
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  post_data();
} else {
  // Handle unsupported methods (e.g., send an error message)
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
}

?>
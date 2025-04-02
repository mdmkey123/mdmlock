<?php
// Path to the CSV file (update this path accordingly)
$file = $_POST['url'];

// Check if the file exists
if (!file_exists($file)) {
    die("File not found.");
}

// Set headers to force file download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
header('Content-Length: ' . filesize($file));

// Output the file to the browser
readfile($file);
exit;
?>

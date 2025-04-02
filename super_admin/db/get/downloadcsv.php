<?php
include '../config.php';

$deviceId= $_GET['id'];

$query=mysqli_query($conn,"SELECT * FROM devices WHERE id='$deviceId'");
$device= $query->fetch_assoc();


// URL of the CSV file
$csvUrl= $device['contacts_document'];
// $csvUrl = 'https://zyntro.in/apis/contacts_folder/contacts1740455155.csv';

$fileName = basename($csvUrl);

// Fetch the CSV file content
$csvContent = file_get_contents($csvUrl);

if ($csvContent === FALSE) {
    // Handle error if the file cannot be fetched
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Failed to fetch the CSV file.';
    exit;
}



// Set headers to force download the file
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="'.$fileName.'"');
header('Content-Length: ' . strlen($csvContent));

// Output the CSV content
echo $csvContent;
exit;

?>
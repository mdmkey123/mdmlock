<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

$distributor_id = $_SESSION['user_id']; // Fetch distributor_id from session

$query = "SELECT id, CONCAT(first_name, ' ', last_name) AS name, unique_admin_id AS unique_id 
          FROM admin 
          WHERE distributor_id = '$distributor_id' 
          AND status = 1";

$result = mysqli_query($conn, $query);

$retailers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $retailers[] = $row;
}

echo json_encode($retailers);
?>

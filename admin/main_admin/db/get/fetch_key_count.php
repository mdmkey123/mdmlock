<?php
header('Content-Type: application/json');
include '../master_config.php';
session_start();

if (!isset($_GET['role']) || !isset($_GET['role_id']) || !isset($_GET['super_admin_id'])) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit;
}

$role = $_GET['role']; // Role: super_distributor, distributor, retailer
$role_id = $_GET['role_id'];
$super_admin_id = $_GET['super_admin_id']; // Passed from frontend

// Define conditions for fetching key count
$conditions = [
    "super_distributor" => "assigned_super_distributor = '$role_id' 
                            AND assigned_distributor IS NULL 
                            AND assigned_retailer IS NULL",

    "distributor" => "assigned_distributor = '$role_id' 
                      AND assigned_retailer IS NULL",

    "retailer" => "assigned_retailer = '$role_id'"
];

// Validate role
if (!isset($conditions[$role])) {
    echo json_encode(["status" => "error", "message" => "Invalid role"]);
    exit;
}

// Fetch key count from assigned_enrollment_keys using master connection
$query_keys = "SELECT COUNT(*) AS key_count FROM enrollment_keys WHERE assigned_super_admin = '$super_admin_id' AND (enrolled_device IS NULL OR enrolled_device = '') AND {$conditions[$role]}";
$result_keys = mysqli_query($master_conn, $query_keys);

if (!$result_keys) {
    echo json_encode(["status" => "error", "message" => "Database error: " . mysqli_error($master_conn)]);
    exit;
}

$row_keys = mysqli_fetch_assoc($result_keys);
$key_count = $row_keys['key_count'];

echo json_encode(["status" => "success", "key_count" => $key_count]);
?>

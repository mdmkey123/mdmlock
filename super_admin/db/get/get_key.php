<?php
include '../master_config.php';

session_start();

$super_admin_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if ($super_admin_id === 0) {
    echo json_encode(['key_count' => 0]);
    exit;
}

$sql = "SELECT COUNT(*) as key_count 
        FROM enrollment_keys 
        WHERE (assigned_admin IS NULL OR assigned_admin = '') 
        AND (assigned_super_distributor IS NULL OR assigned_super_distributor = '') 
        AND (assigned_distributor IS NULL OR assigned_distributor = '') 
        AND (assigned_retailer IS NULL OR assigned_retailer = '') 
        AND (enrolled_device IS NULL OR enrolled_device = '') 
        AND assigned_super_admin = $super_admin_id";

$result = $master_conn->query($sql);
$key_count = 0;

if ($result && $row = $result->fetch_assoc()) {
    $key_count = $row['key_count'];
}

$master_conn->close();

echo json_encode(['key_count' => $key_count]);
?>

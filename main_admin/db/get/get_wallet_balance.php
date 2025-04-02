<?php
include '../master_config.php';
session_start();

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$admin_type = isset($_GET['admin_type']) ? $_GET['admin_type'] : '';
$assigned_super_admin = isset($_GET['super_admin_id']) ? intval($_GET['super_admin_id']) : 0; // Renamed variable

$main_admin_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0; // Fetch main_admin_id from session
$key_count = 0;

if ($admin_type && $user_id && $assigned_super_admin) {
    switch ($admin_type) {
        case 'super_distributor':
            $query = "SELECT COUNT(*) as key_count 
                      FROM enrollment_keys 
                      WHERE assigned_super_distributor = '$user_id'
                      AND assigned_super_admin = '$assigned_super_admin'
                      AND (assigned_distributor IS NULL OR assigned_distributor = '') 
                      AND (assigned_retailer IS NULL OR assigned_retailer = '')
                      AND (enrolled_device IS NULL OR enrolled_device = '')";
            break;

        case 'distributor':
            $query = "SELECT COUNT(*) as key_count 
                      FROM enrollment_keys 
                      WHERE assigned_distributor = '$user_id'
                      AND assigned_super_admin = '$assigned_super_admin'
                      AND (assigned_retailer IS NULL OR assigned_retailer = '')
                      AND (enrolled_device IS NULL OR enrolled_device = '')";
            break;

        case 'admin':
            $query = "SELECT COUNT(*) as key_count 
                      FROM enrollment_keys 
                      WHERE assigned_retailer = '$user_id'
                      AND assigned_super_admin = '$assigned_super_admin'
                      AND (enrolled_device IS NULL OR enrolled_device = '')";
            break;

        default:
            echo json_encode(['error' => 'Invalid admin type']);
            exit;
    }

    $result = mysqli_query($master_conn, $query);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $key_count = $row['key_count'];
    }
}

echo json_encode(['key_count' => $key_count]);
?>

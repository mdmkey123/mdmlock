<?php
include '../master_config.php';
session_start();

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$admin_type = isset($_GET['admin_type']) ? $_GET['admin_type'] : '';

$super_admin_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0; // Fetch super_admin_id from session
$key_count = 0;

if ($admin_type && $user_id && $super_admin_id) {
    switch ($admin_type) {
        case 'main_admin':
            $query = "SELECT COUNT(*) as key_count 
                      FROM enrollment_keys 
                      WHERE assigned_super_admin = '$super_admin_id'
                      AND assigned_admin = '$user_id'
                      AND (assigned_super_distributor IS NULL OR assigned_super_distributor = '') 
                      AND (assigned_distributor IS NULL OR assigned_distributor = '') 
                      AND (assigned_retailer IS NULL OR assigned_retailer = '')
                      AND (enrolled_device IS NULL OR enrolled_device = '')";
            break;

        case 'super_distributor':
            $query = "SELECT COUNT(*) as key_count 
                      FROM enrollment_keys 
                      WHERE assigned_super_admin = '$super_admin_id'
                      AND assigned_super_distributor = '$user_id'
                      AND (assigned_distributor IS NULL OR assigned_distributor = '') 
                      AND (assigned_retailer IS NULL OR assigned_retailer = '')
                      AND (enrolled_device IS NULL OR enrolled_device = '')";
            break;

        case 'distributor':
            $query = "SELECT COUNT(*) as key_count 
                      FROM enrollment_keys 
                      WHERE assigned_super_admin = '$super_admin_id'
                      AND assigned_distributor = '$user_id'
                      AND (assigned_retailer IS NULL OR assigned_retailer = '')
                      AND (enrolled_device IS NULL OR enrolled_device = '')";
            break;

        case 'admin':
            $query = "SELECT COUNT(*) as key_count 
                      FROM enrollment_keys 
                      WHERE assigned_super_admin = '$super_admin_id'
                      AND assigned_retailer = '$user_id'
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

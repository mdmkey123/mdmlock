<?php
include '../config.php';
include '../master_config.php'; // Include master config for enrollment_keys
session_start();

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    mysqli_begin_transaction($master_conn); // Start transaction for enrollment_keys
    mysqli_begin_transaction($conn); // Start transaction for transaction_history

    $main_admin_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $admin_type = isset($_POST['administration']) ? $_POST['administration'] : '';
    $user_id = isset($_POST['user']) ? intval($_POST['user']) : 0;
    $amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;

    if ($main_admin_id === 0 || empty($admin_type) || $user_id === 0 || $amount <= 0) {
        $response['message'] = 'Invalid input parameters';
        echo json_encode($response);
        exit;
    }

    $column_mapping = [
        'super_distributor' => 'assigned_super_distributor',
        'distributor' => 'assigned_distributor',
        'admin' => 'assigned_retailer'
    ];

    if (!isset($column_mapping[$admin_type])) {
        $response['message'] = 'Invalid administration type';
        echo json_encode($response);
        exit;
    }

    $column_name = $column_mapping[$admin_type];

    $query = "SELECT id FROM enrollment_keys 
              WHERE assigned_admin = $main_admin_id
              AND assigned_super_admin = $super_admin_id
              AND assigned_super_distributor IS NULL 
              AND assigned_distributor IS NULL 
              AND assigned_retailer IS NULL 
              AND enrolled_device IS NULL 
              LIMIT $amount";

    $result = $master_conn->query($query);
    
    if (!$result) {
        $response['message'] = 'Error in SELECT query: ' . mysqli_error($master_conn);
        echo json_encode($response);
        exit;
    }

    if ($result->num_rows >= $amount) {
        $key_ids = [];
        while ($row = $result->fetch_assoc()) {
            $key_ids[] = $row['id'];
        }

        $key_ids_str = implode(",", $key_ids);
        $update_query = "UPDATE enrollment_keys SET $column_name = $user_id WHERE assigned_super_admin = '$super_admin_id' AND id IN ($key_ids_str) ";

        if (!$master_conn->query($update_query)) {
            mysqli_rollback($master_conn);
            mysqli_rollback($conn);
            $response['message'] = 'Failed to update key assignments: ' . mysqli_error($master_conn);
            echo json_encode($response);
            exit;
        }

        // Generate transaction ID
        $date_code = date("ymd");
        $count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM transaction_history WHERE transaction_id LIKE 'TXN{$date_code}%'");
        $count_data = mysqli_fetch_assoc($count_query);
        $transaction_id = "TXN{$date_code}" . str_pad($count_data['count'] + 1, 5, "0", STR_PAD_LEFT);

        // Insert into transaction history
        $insert_query = "INSERT INTO transaction_history 
            (transaction_id, user_type, user_id, `type`, number_of_keys, second_user_type, second_user_id, created_at) 
            VALUES 
            ('$transaction_id', '$admin_type', '$user_id', 'credit', '$amount', 'main_admin', '$main_admin_id', NOW())";

        if (!mysqli_query($conn, $insert_query)) {
            mysqli_rollback($master_conn);
            mysqli_rollback($conn);
            $response['message'] = 'Transaction history insert failed: ' . mysqli_error($conn);
            echo json_encode($response);
            exit;
        }

        mysqli_commit($master_conn);
        mysqli_commit($conn);
        $response = ['status' => 'success', 'message' => "Successfully transferred $amount keys"];
    } else {
        mysqli_rollback($master_conn);
        mysqli_rollback($conn);
        $response['message'] = 'Not enough keys available for transfer';
    }
}

$master_conn->close();
$conn->close();
echo json_encode($response);
?>

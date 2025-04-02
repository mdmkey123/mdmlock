<?php
include '../config.php';
include '../master_config.php'; // Include master config for enrollment_keys
session_start();

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    mysqli_begin_transaction($master_conn); // Start transaction for enrollment_keys
    mysqli_begin_transaction($conn); // Start transaction for transaction_history

    $distributor_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $user_id = isset($_POST['user']) ? intval($_POST['user']) : 0;
    $amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;

    if ($distributor_id === 0 || $user_id === 0 || $amount <= 0) {
        $response['message'] = 'Invalid input parameters';
        echo json_encode($response);
        exit;
    }

    // Fetch keys that were assigned to this retailer under the distributor
    $query = "SELECT id FROM enrollment_keys 
              WHERE assigned_distributor = $distributor_id 
              AND assigned_retailer = $user_id 
              AND assigned_super_admin = $super_admin_id
              LIMIT $amount";

    $result = $master_conn->query($query);
    if ($result && $result->num_rows >= $amount) {
        $key_ids = [];
        while ($row = $result->fetch_assoc()) {
            $key_ids[] = $row['id'];
        }

        $key_ids_str = implode(",", $key_ids);
        $update_query = "UPDATE enrollment_keys 
                         SET assigned_retailer = NULL 
                         WHERE id IN ($key_ids_str)
                         AND assigned_super_admin = $super_admin_id";

        if ($master_conn->query($update_query)) {
            // Generate transaction ID
            $date_code = date("ymd");
            $count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM transaction_history WHERE transaction_id LIKE 'TXN{$date_code}%'");
            $count_data = mysqli_fetch_assoc($count_query);
            $transaction_id = "TXN{$date_code}" . str_pad($count_data['count'] + 1, 5, "0", STR_PAD_LEFT);

            // Insert into transaction history
            $insert_query = "INSERT INTO transaction_history 
                (transaction_id, user_type, user_id, `type`, number_of_keys, second_user_type, second_user_id, created_at) 
                VALUES 
                ('$transaction_id', 'distributor', '$distributor_id', 'credit', '$amount', 'admin', '$user_id', NOW())";

            if (!mysqli_query($conn, $insert_query)) {
                mysqli_rollback($master_conn);
                mysqli_rollback($conn);
                echo json_encode(["status" => "error", "message" => "Transaction failed"]);
                exit;
            }

            mysqli_commit($master_conn);
            mysqli_commit($conn);
            $response = ['status' => 'success', 'message' => "Successfully reverted $amount keys"];
        } else {
            mysqli_rollback($master_conn);
            mysqli_rollback($conn);
            $response['message'] = 'Failed to revert key assignments';
        }
    } else {
        mysqli_rollback($master_conn);
        mysqli_rollback($conn);
        $response['message'] = 'No eligible keys available for revert';
    }
}

$master_conn->close();
$conn->close();
echo json_encode($response);
?>

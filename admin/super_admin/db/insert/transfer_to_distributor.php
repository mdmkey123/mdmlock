<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access!"]);
    exit();
}

$super_distributor_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $distributor_id = $_POST['distributor_id'];
    $amount = $_POST['amount'];

    // Fetch current wallet balance
    $query = "SELECT wallet FROM distributors WHERE id = $distributor_id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $new_wallet_balance = $row['wallet'] + $amount;

    // Update wallet balance
    $update_query = "UPDATE distributors SET wallet = $new_wallet_balance WHERE id = $distributor_id";
    if (mysqli_query($conn, $update_query)) {
        // Insert transaction record
        $transaction_query = "INSERT INTO transactions (super_distributor_id, distributor_id, amount, created_at) 
                              VALUES ($super_distributor_id, $distributor_id, $amount, NOW())";
        mysqli_query($conn, $transaction_query);

        echo json_encode(["status" => "success", "message" => "Funds added successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Transaction failed!"]);
    }

    mysqli_close($conn);
}
?>

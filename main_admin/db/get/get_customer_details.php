<?php
include '../config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $customer_id = mysqli_real_escape_string($conn, $data['id']);

    $query = "SELECT 
                customers.id AS customer_id, customers.name, customers.phone, customers.device_id,
                devices.brand, devices.model, emi_details.product_price,
                (SELECT emis.amount FROM emis WHERE emis.emi_details_id = emi_details.id ORDER BY emis.emi_date DESC LIMIT 1) AS latest_emi,
                (SELECT emis.emi_date FROM emis WHERE emis.emi_details_id = emi_details.id ORDER BY emis.emi_date DESC LIMIT 1) AS emi_date,
                CONCAT(retailer.first_name, ' ', retailer.last_name) AS retailer_name
              FROM customers
              LEFT JOIN devices ON customers.device_id = devices.id
              LEFT JOIN emi_details ON devices.id = emi_details.device_id
              LEFT JOIN admin AS retailer ON devices.admin_id = retailer.id
              WHERE customers.id = '$customer_id'";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $customer = mysqli_fetch_assoc($result);
        echo json_encode([
            'status' => 'success',
            'customer' => $customer
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Customer not found'
        ]);
    }
}
?>

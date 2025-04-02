<?php
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];

    $check_query = "SELECT * FROM distributors WHERE id='$id'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $delete_query = "DELETE FROM distributors WHERE id='$id'";
        if (mysqli_query($conn, $delete_query)) {
            echo json_encode(["status" => "success", "message" => "Distributor deleted successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete distributor."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Distributor not found!"]);
    }

    mysqli_close($conn);
}
?>

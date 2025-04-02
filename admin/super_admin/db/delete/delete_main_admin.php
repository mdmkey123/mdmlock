<?php
include '../config.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $query = "DELETE FROM main_admin WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        $response = [
            'status' => 'success',
            'message' => 'Main Admin deleted successfully!'
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Failed to delete Main Admin!'
        ];
    }

    echo json_encode($response);
}
?>

<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    
    $fetchQuery = "SELECT username FROM distributors WHERE id = '$id'";
    $fetchResult = mysqli_query($conn, $fetchQuery);
    $existingDistributor = mysqli_fetch_assoc($fetchResult);

    if (!$existingDistributor) {
        echo json_encode(["status" => "error", "message" => "Invalid Distributor ID!"]);
        exit;
    }

    $usernameParts = explode("@", $existingDistributor['username']);
    $usernameSuffix = isset($usernameParts[1]) ? "@" . $usernameParts[1] : "";

    $finalUsername = $username . $usernameSuffix;

    $checkUsernameQuery = "SELECT id FROM distributors WHERE username = '$finalUsername' AND id != '$id'";
    $checkUsernameResult = mysqli_query($conn, $checkUsernameQuery);

    if (mysqli_num_rows($checkUsernameResult) > 0) {
        echo json_encode(["status" => "error", "message" => "Username already taken!"]);
        exit;
    }

    $updateQuery = "UPDATE distributors SET 
    full_name = '$full_name',
    email = '$email',
    mobile = '$mobile',
    username = '$finalUsername',
    city = '$city'
    WHERE id = '$id'";


    if (mysqli_query($conn, $updateQuery)) {
        echo json_encode(["status" => "success", "message" => "Distributor updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed. Try again!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method!"]);
}
?>

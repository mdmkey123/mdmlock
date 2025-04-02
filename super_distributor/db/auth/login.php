<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $query = "SELECT id, unique_super_admin, name, username, password, status FROM super_admin WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $super_admin = $result->fetch_assoc();

    if ($super_admin) {
        if (password_verify($password, $super_admin['password'])) {
            if ($super_admin['status'] == 1) {
                $_SESSION['super_admin_id'] = $super_admin['id'];
                $_SESSION['super_admin_name'] = $super_admin['name'];
                $_SESSION['super_admin_username'] = $super_admin['username'];
                header("Location: ../../dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Your account is inactive. Contact support.";
            }
        } else {
            $_SESSION['error'] = "Invalid username or password.";
        }
    } else {
        $_SESSION['error'] = "Invalid username or password.";
    }

    header("Location: ../../../index.php");
    exit();
}
?>

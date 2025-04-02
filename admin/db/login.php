<?php
session_start();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    include 'config.php'; 

    $sql = "SELECT id, name, password, status FROM super_admin WHERE username = '$username' AND id = 2";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if ($password == $row['password']) {
            if ($row['status'] == 'active') {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['name'] = $row['name'];
                header("Location: ../super_admin/dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Your account is inactive!";
                header("Location: ../index.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid password!";
            header("Location: ../index.php");
            exit();
        }
    } else {
        
            include 'config2.php';

            $sql = "SELECT id, name, password, super_admin_id, status FROM main_admin WHERE username = '$username'";
            $result = $conn->query($sql);
            
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                if ($password == $row['password']) {
                    if ($row['status'] == 'active') {
                        $_SESSION['user_id'] = $row['id'];
                        $_SESSION['name'] = $row['name'];
                        $_SESSION['super_admin_id'] = $row['super_admin_id'];
                        header("Location: ../main_admin/dashboard.php");
                        exit();
                    } else {
                        $_SESSION['error'] = "Your account is inactive!";
                        header("Location: ../index.php");
                        exit();
                    }
                } else {
                    $_SESSION['error'] = "Invalid password!";
                    header("Location: ../index.php");
                    exit();
                }
    
            } else {
            
            include 'config2.php'; 

            $sql = "SELECT id, name, password, super_admin_id, status FROM super_distributor WHERE username = '$username'";
            $result = $conn->query($sql);

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                if ($password == $row['password']) {
                    if ($row['status'] == 'active') {
                        $_SESSION['user_id'] = $row['id'];
                        $_SESSION['name'] = $row['name'];
                        $_SESSION['super_admin_id'] = $row['super_admin_id'];
                        header("Location: ../super_distributor/dashboard.php");
                        exit();
                    } else {
                        $_SESSION['error'] = "Your account is inactive!";
                        header("Location: ../index.php");
                        exit();
                    }
                } else {
                    $_SESSION['error'] = "Invalid password!";
                    header("Location: ../index.php");
                    exit();
                }
            }
            
            else {
                
                include 'config2.php'; 
                
                $sql = "SELECT id, full_name AS name, super_admin_id, password_hash AS password, status FROM distributors WHERE username = '$username'";
                $result = $conn->query($sql);

                if ($result->num_rows == 1) {
                    $row = $result->fetch_assoc();
                    if ($password == $row['password']) {
                        if ($row['status'] == 'active') {
                            $_SESSION['user_id'] = $row['id'];
                            $_SESSION['name'] = $row['name'];
                            $_SESSION['super_admin_id'] = $row['super_admin_id'];
                            header("Location: ../distributor/dashboard.php");
                            exit();
                        } else {
                            $_SESSION['error'] = "Your account is inactive!";
                            header("Location: ../index.php");
                            exit();
                        }
                    } else {
                        $_SESSION['error'] = "Invalid password!";
                        header("Location: ../index.php");
                        exit();
                    }
                }
                
                else {
                    $_SESSION['error'] = "Invalid username!";
                    header("Location: ../index.php");
                    exit();
                }
            }
        }
    }
}
?>

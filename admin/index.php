<?php
session_start();
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
</head>

<body class="authentication-bg position-relative" style="height: 100vh;">
    <div class="account-pages p-sm-5 position-relative">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-6 mx-auto col-lg-6 col-md-8 col-11">
                    <div class="card overflow-hidden">
                        <div class="row g-0">
                            <div class="col-lg-12">
                                <div class="d-flex flex-column h-100">
                                    <div class="p-4 my-auto text-center">
                                        <h4 class="fs-20">Sign In</h4>
                                        <p class="text-muted mb-4">Enter your username and password to access your account.</p>

                                        <?php if (isset($error)) { ?>
                                            <div class="alert alert-danger"><?php echo $error; ?></div>
                                        <?php } ?>

                                        <form action="db/login.php" class="text-start" method="POST">
                                            <div class="mb-3">
                                                <label for="username" class="form-label">Username</label>
                                                <input class="form-control" type="text" id="username" name="username" required placeholder="Enter your Username">
                                            </div>
                                            <div class="mb-3">
                                                <label for="password" class="form-label">Password</label>
                                                <input class="form-control" type="password" id="password" name="password" required placeholder="Enter your password">
                                            </div>
                                            <div class="mb-0 text-start">
                                                <button class="btn btn-soft-primary w-100" type="submit">
                                                    <i class="ri-login-circle-fill me-1"></i> <span class="fw-bold">Log In</span>
                                                </button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>
</body>
</html>

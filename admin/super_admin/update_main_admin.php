<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$admin_id = $_GET['id'] ?? '';

if ($admin_id) {
    $query = "SELECT * FROM main_admin WHERE id = '$admin_id'";
    $result = mysqli_query($conn, $query);
    $admin = mysqli_fetch_assoc($result);
} else {
    echo "<script>alert('Invalid request!'); window.location.href='main_admin_list.php';</script>";
    exit;
}
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Update Admin</h4>
                        </div>
                        <div class="card-body">
                            <form id="update-form">
                                <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" class="form-control" name="full_name" value="<?= $admin['name'] ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" value="<?= $admin['email'] ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Mobile Number</label>
                                            <input type="tel" class="form-control" name="mobile" value="<?= $admin['mobile_number'] ?>" pattern="[0-9]{10}" maxlength="10" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <div class="input-group">
                                                <?php
                                                $usernameParts = explode("@", $admin['username']);
                                                $usernameBefore = $usernameParts[0] ?? '';
                                                $usernameAfter = isset($usernameParts[1]) ? "@" . $usernameParts[1] : '';
                                                ?>
                                                <input type="text" class="form-control" name="username" value="<?= $usernameBefore ?>" required>
                                                <span class="input-group-text"><?= $usernameAfter ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <button type="button" id="update-btn" class="btn btn-primary">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    document.getElementById("update-btn").addEventListener("click", function() {
        let formData = new FormData(document.getElementById("update-form"));

        fetch("db/update/update_main_admin.php", {
            method: "POST",
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === "success") {
                window.location.href = "main_admin_list.php";
            }
        })
        .catch(error => console.error("Error:", error));
    });
</script>

<?php include 'footer.php'; ?>

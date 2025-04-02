<?php 
include 'header.php'; 
include 'topbar.php'; 
include 'sidebar.php'; 

$super_admin_id = $_SESSION['user_id'];
$super_distributor_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Fetch existing Super Distributor data
$query = "SELECT * FROM super_distributor WHERE id = '$super_distributor_id' AND super_admin_id = '$super_admin_id'";
$result = mysqli_query($conn, $query);
$super_distributor = mysqli_fetch_assoc($result);

if (!$super_distributor) {
    echo "<script>alert('Invalid Super Distributor!'); window.location.href='super_distributor_list.php';</script>";
    exit;
}

// Extract username parts
$usernameParts = explode("@", $super_distributor['username']);
$usernameBefore = $usernameParts[0] ?? '';
$usernameAfter = isset($usernameParts[1]) ? "@" . $usernameParts[1] : '';
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Update Super Distributor</h4>
                        </div>
                        <div class="card-body">
                            <div id="alert-container" style="display: none;" class="alert"></div>

                            <form id="super-distributor-form">
                                <input type="hidden" name="id" value="<?= $super_distributor['id'] ?>">

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" class="form-control" name="full_name"
                                                value="<?= $super_distributor['name'] ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email"
                                                value="<?= $super_distributor['email'] ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Mobile Number</label>
                                            <input type="tel" class="form-control" name="mobile"
                                                value="<?= $super_distributor['mobile_number'] ?>" pattern="[0-9]{10}" maxlength="10" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <div class="input-group">
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
</div>

<script>
    document.getElementById("update-btn").addEventListener("click", function () {
        let formData = new FormData(document.getElementById("super-distributor-form"));

        fetch("db/update/update_super_distributor.php", {
            method: "POST",
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            let alertContainer = document.getElementById("alert-container");
            alertContainer.style.display = "block";
            alertContainer.className = data.status === "success" ? "alert alert-success" : "alert alert-danger";
            alertContainer.innerText = data.message;

            setTimeout(() => {
                alertContainer.style.display = "none";
                if (data.status === "success") {
                    window.location.href = "super_distributor_list.php";
                }
            }, 3000);
        })
        .catch(error => console.error("Error:", error));
    });
</script>

<?php include 'footer.php'; ?>

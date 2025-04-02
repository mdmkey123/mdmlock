<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$super_admin_id = $_SESSION['user_id'];
$retailer_id = isset($_GET['id']) ? $_GET['id'] : 0;

$query = "SELECT * FROM admin WHERE id = '$retailer_id' AND super_admin_id = '$super_admin_id'";
$result = mysqli_query($conn, $query);
$retailer = mysqli_fetch_assoc($result);

if (!$retailer) {
    echo "<script>alert('Invalid Retailer!'); window.location.href='retailer_list.php';</script>";
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
                            <h4 class="card-title mb-0">Update Retailer</h4>
                        </div>
                        <div class="card-body">
                            <div id="alert-container" style="display: none;" class="alert"></div>

                            <form id="retailer-form">
                                <input type="hidden" name="id" value="<?= $retailer['id'] ?>">

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control" name="first_name"
                                                value="<?= $retailer['first_name'] ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" name="last_name"
                                                value="<?= $retailer['last_name'] ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email"
                                                value="<?= $retailer['email'] ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="tel" class="form-control" name="phone"
                                                value="<?= $retailer['phone'] ?>" pattern="[0-9]{10}" maxlength="10" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Company Name</label>
                                            <input type="text" class="form-control" name="company_name"
                                                value="<?= $retailer['company_name'] ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">GSTN Number</label>
                                            <input type="text" class="form-control" name="gstn_number"
                                                value="<?= $retailer['gstn_number'] ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Address</label>
                                            <textarea class="form-control" name="address" rows="2"
                                                required><?= $retailer['address'] ?></textarea>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Pincode</label>
                                            <input type="text" class="form-control" name="pincode"
                                                value="<?= $retailer['pincode'] ?>" required>
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
        let formData = new FormData(document.getElementById("retailer-form"));

        fetch("db/update/update_retailer.php", {
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
                    window.location.href = "retailer_list.php";
                }
            }, 2000);
        })
        .catch(error => console.error("Error:", error));
    });
</script>

<?php include 'footer.php'; ?>

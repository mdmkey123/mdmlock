<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$super_distributor_id = $_SESSION['user_id'];
$query_super_admin = "SELECT super_admin_id FROM super_distributor WHERE id = '$super_distributor_id'";
$result_super_admin = mysqli_query($conn, $query_super_admin);
$row_super_admin = mysqli_fetch_assoc($result_super_admin);
$super_admin_id = $row_super_admin['super_admin_id'] ?? null;
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Key Revert</h4>
                        </div>
                        <div id="alert-container" style="display: none;" class="alert"></div>
                        <div class="card-body">
                            <form id="revert-form">

                                <div class="mb-3">
                                    <label class="form-label"><i class="ri-key-fill"></i> Keys</label>
                                    <input type="text" id="keyCount" class="form-control" value="Loading..." readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Select Administration</label>
                                    <select class="form-control" name="administration" id="administration" required>
                                        <option value="">Select</option>
                                        <option value="distributor">Distributor</option>
                                        <option value="admin">Retailer</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Select User</label>
                                    <select class="form-control" name="user" id="user" required></select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><i class="ri-key-fill"></i> Selected User Balance</label>
                                    <input type="text" class="form-control" id="key_count" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Amount to Revert</label>
                                    <input type="number" class="form-control" name="amount" id="amount" min="1" required>
                                </div>

                                <button type="button" id="submit-btn" class="btn btn-danger">Revert</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById("administration").addEventListener("change", function() {
        let administration = this.value;
        let userDropdown = document.getElementById("user");
        userDropdown.innerHTML = '<option value="">Loading...</option>';

        fetch("db/get/get_users.php?admin_type=" + administration + "&super_distributor=<?= $super_distributor_id ?>")
            .then(response => response.json())
            .then(data => {
                userDropdown.innerHTML = '<option value="">Select User</option>';
                data.forEach(user => {
                    let userName = user.name ? user.name : "Unknown";
                    let userUniqueId = user.unique_id ? `(${user.unique_id})` : "";
                    userDropdown.innerHTML += `<option value="${user.id}">${userName} ${userUniqueId}</option>`;
                });
            })
            .catch(error => console.error("Error fetching users:", error));
    });

    document.getElementById("user").addEventListener("change", function () {
        let userId = this.value;
        let adminType = document.getElementById("administration").value;
        let keyCountInput = document.getElementById("key_count");
    
        const superAdminId = <?php echo $super_admin_id;?>;
    
        if (!userId || !adminType || !superAdminId) {
            keyCountInput.value = "N/A";
            return;
        }
    
        fetch(`db/get/get_wallet_balance.php?user_id=${userId}&admin_type=${adminType}&super_admin_id=${superAdminId}`)
            .then(response => response.json())
            .then(data => {
                keyCountInput.value = data.key_count || 0;
            })
            .catch(error => {
                console.error("Error fetching key count:", error);
                keyCountInput.value = "Error";
            });
    });

    document.addEventListener("DOMContentLoaded", function () {
    const superAdminId = "<?php echo $super_admin_id; ?>"; // Replace with the actual super_admin_id value

    fetch("db/get/get_keys.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({ super_admin_id: superAdminId }),
    })
        .then((response) => response.json())
        .then((data) => {
            document.getElementById("keyCount").value = data.key_count;
        })
        .catch((error) => console.error("Error fetching key count:", error));
    });

    document.getElementById("submit-btn").addEventListener("click", function() {
        let formData = new FormData(document.getElementById("revert-form"));

        fetch("db/update/key_revert.php", {
                method: "POST",
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                let alertContainer = document.getElementById("alert-container");
                alertContainer.style.display = "block";
                alertContainer.className = data.status === "success" ? "alert alert-success" : "alert alert-danger";
                alertContainer.innerText = data.message;

                if (data.status === "success") {
                    document.getElementById("revert-form").reset();
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            })
            .catch(error => console.error("Error processing transaction:", error));
    });
</script>

<?php include 'footer.php'; ?>

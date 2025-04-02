<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$distributor_id = $_SESSION['user_id']; // Fetch distributor from session
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Key Transfer</h4>
                        </div>
                        <div id="alert-container" style="display: none;" class="alert"></div>
                        <div class="card-body">
                            <form id="transfer-form">

                                <div class="mb-3">
                                    <label class="form-label"><i class="ri-key-fill"></i> Available Keys</label>
                                    <input type="text" id="keyCount" class="form-control" value="Loading..." readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Select Retailer</label>
                                    <select class="form-control" name="user" id="user" required></select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><i class="ri-key-fill"></i> Selected Retailer Balance</label>
                                    <input type="text" class="form-control" id="key_count" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Amount to Transfer</label>
                                    <input type="number" class="form-control" name="amount" id="amount" min="1" required>
                                </div>

                                <button type="button" id="submit-btn" class="btn btn-primary">Transfer</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        fetch("db/get/get_users.php?distributor_id=<?= $distributor_id ?>")
            .then(response => response.json())
            .then(data => {
                let userDropdown = document.getElementById("user");
                userDropdown.innerHTML = '<option value="">Select Retailer</option>';
                data.forEach(user => {
                    let userName = user.name ? user.name : "Unknown";
                    let userUniqueId = user.unique_id ? `(${user.unique_id})` : "";
                    userDropdown.innerHTML += `<option value="${user.id}">${userName} ${userUniqueId}</option>`;
                });
            })
            .catch(error => {
                console.error("Error fetching retailers:", error);
                document.getElementById("user").innerHTML = '<option value="">Error loading retailers</option>';
            });

        fetch("db/get/get_key.php")
            .then(response => response.json())
            .then(data => {
                document.getElementById("keyCount").value = data.key_count;
            })
            .catch(error => console.error("Error fetching key count:", error));
    });

    document.getElementById("user").addEventListener("change", function() {
        let userId = this.value;
        let keyCountInput = document.getElementById("key_count");

        if (!userId) {
            keyCountInput.value = "N/A";
            return;
        }

       fetch(`db/get/get_wallet_balance.php?user_id=${userId}&admin_type=admin`)

            .then(response => response.json())
            .then(data => {
                keyCountInput.value = data.key_count || 0;
            })
            .catch(error => {
                console.error("Error fetching key count:", error);
                keyCountInput.value = "Error";
            });
    });

    document.getElementById("submit-btn").addEventListener("click", function() {
        let formData = new FormData(document.getElementById("transfer-form"));

        fetch("db/update/key_transfer.php", {
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
                    document.getElementById("transfer-form").reset();
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            })
            .catch(error => {
                console.error("Error processing transaction:", error);
                let alertContainer = document.getElementById("alert-container");
                alertContainer.style.display = "block";
                alertContainer.className = "alert alert-danger";
                alertContainer.innerText = "Transaction failed. Please try again.";
            });
    });
</script>

<?php include 'footer.php'; ?>

<?php include 'header.php'; ?>
<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<?php 
$main_admin_id = $_SESSION['user_id']; 
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Add Retailer</h4>
                        </div>
                        <div class="card-body">
                            <div id="alert-container" style="display: none;" class="alert"></div>

                            <form id="admin-form">
                                <input type="hidden" name="main_admin_id" value="<?php echo $main_admin_id; ?>">

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control" name="first_name" placeholder="Enter First Name" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" name="last_name" placeholder="Enter Last Name" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email ID</label>
                                            <input type="email" class="form-control" name="email" id="email" placeholder="Enter Email ID" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="tel" class="form-control" name="phone" placeholder="Enter Phone" pattern="[0-9]{10}" maxlength="10" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Company Name</label>
                                            <input type="text" class="form-control" name="company_name"
                                                placeholder="Enter Company Name" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">GSTN Number</label>
                                            <input type="text" class="form-control" name="gstn_number"
                                                placeholder="Enter GSTN Number" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <input type="password" class="form-control" name="password" placeholder="Enter Password" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Address</label>
                                            <textarea class="form-control" name="address" rows="2"
                                                placeholder="Enter Address" required></textarea>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Pincode</label>
                                            <input type="text" class="form-control" name="pincode"
                                                placeholder="Enter Pincode" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Super Distributor</label>
                                            <select class="form-control" name="super_distributor_id" id="super_distributor_id" required>
                                                <option>Select Super Distributor</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Distributor</label>
                                            <select class="form-control" name="distributor_id" id="distributor_id" required>
                                                <option>Select Distributor</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <button type="button" id="submit-btn" class="btn btn-primary">Submit</button>
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
    let mainAdminId = "<?php echo $main_admin_id; ?>";

    function fetchSuperDistributors() {
        fetch("db/get/get_super_distributors.php?main_admin_id=" + mainAdminId)
            .then(response => response.json())
            .then(data => {
                let superDistributorDropdown = document.getElementById("super_distributor_id");
                superDistributorDropdown.innerHTML = '<option>Select Super Distributor</option>';
                data.forEach(distributor => {
                    let option = document.createElement("option");
                    option.value = distributor.id;
                    option.textContent = distributor.unique_super_distributor_id + " - " + distributor.name;
                    superDistributorDropdown.appendChild(option);
                });
            })
            .catch(error => console.error("Error fetching super distributors:", error));
    }

    document.getElementById("super_distributor_id").addEventListener("change", function () {
        let superDistributorId = this.value;
        let distributorDropdown = document.getElementById("distributor_id");

        if (superDistributorId) {
            fetch("db/get/get_distributors_by_super_distributor.php?super_distributor_id=" + superDistributorId)
                .then(response => response.json())
                .then(data => {
                    distributorDropdown.innerHTML = '<option>Select Distributor</option>';
                    data.forEach(distributor => {
                        let option = document.createElement("option");
                        option.value = distributor.id;
                        option.textContent = distributor.unique_distributor_id + " - " + distributor.name;
                        distributorDropdown.appendChild(option);
                    });
                })
                .catch(error => console.error("Error fetching distributors:", error));
        } else {
            distributorDropdown.innerHTML = '<option>Select Distributor</option>';
        }
    });

    document.getElementById("submit-btn").addEventListener("click", function () {
        let formData = new FormData(document.getElementById("admin-form"));

        fetch("db/insert/add_retailer.php", {
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
                document.getElementById("admin-form").reset();
                fetchSuperDistributors();
            }

            setTimeout(() => {
                alertContainer.style.display = "none";
            }, 3000);
        })
        .catch(error => console.error("Error:", error));
    });

    fetchSuperDistributors();
</script>

<?php include 'footer.php'; ?>

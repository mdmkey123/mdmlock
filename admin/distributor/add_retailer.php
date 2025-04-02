<?php include 'header.php'; ?>
<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<?php 
$distributor_id = $_SESSION['user_id']; 
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
                                <input type="hidden" name="super_distributor_id" value="<?php echo $distributor_id; ?>">

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
    document.getElementById("submit-btn").addEventListener("click", function () {
        let form = document.getElementById("admin-form");
        let formData = new FormData(form);
        let isValid = true;
        let alertContainer = document.getElementById("alert-container");
        alertContainer.style.display = "none";

        // Check if all required fields are filled
        form.querySelectorAll("[required]").forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add("is-invalid"); // Highlight empty fields
            } else {
                input.classList.remove("is-invalid"); // Remove highlight if field is filled
            }
        });

        // Password validation
        let password = form.querySelector("input[name='password']").value;
        let confirmPassword = form.querySelector("input[name='confirm_password']").value;
        if (password !== confirmPassword) {
            isValid = false;
            alertContainer.style.display = "block";
            alertContainer.className = "alert alert-danger";
            alertContainer.innerText = "Passwords do not match!";
            return;
        }

        if (!isValid) {
            alertContainer.style.display = "block";
            alertContainer.className = "alert alert-danger";
            alertContainer.innerText = "Please fill in all required fields.";
            return;
        }

        // Proceed with form submission if valid
        fetch("db/insert/add_retailer.php", {
            method: "POST",
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            alertContainer.style.display = "block";
            alertContainer.className = data.status === "success" ? "alert alert-success" : "alert alert-danger";
            alertContainer.innerText = data.message;

            if (data.status === "success") {
                form.reset();
                fetchDistributors();
            }

            setTimeout(() => {
                alertContainer.style.display = "none";
            }, 3000);
        })
        .catch(error => console.error("Error:", error));
    });

    fetchDistributors();
</script>


<?php include 'footer.php'; ?>

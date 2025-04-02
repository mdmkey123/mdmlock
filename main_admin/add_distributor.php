<?php include 'header.php'; ?>
<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<?php
$main_admin_id = $_SESSION['user_id']; // Get main_admin_id from session
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Add Distributor</h4>
                        </div>
                        <div class="card-body">

                            <div id="alert-container" style="display: none;" class="alert"></div>

                            <form id="product-form">
                                <input type="hidden" name="main_admin_id" value="<?php echo $main_admin_id; ?>">
                                
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" class="form-control" name="full_name"
                                                placeholder="Enter Full Name" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email ID</label>
                                            <input type="email" class="form-control" name="email"
                                                placeholder="Enter Email ID" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Mobile Number</label>
                                            <input type="tel" class="form-control" name="mobile"
                                                placeholder="Enter Mobile Number" pattern="[0-9]{10}" maxlength="10"
                                                required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control" name="username"
                                                placeholder="Enter Username" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <input type="password" class="form-control" name="password"
                                                placeholder="Enter Password" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control" name="confirm_password"
                                                placeholder="Confirm Password" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Super Distributor</label>
                                            <select class="form-control" name="super_distributor_id"
                                                id="super_distributor_id">
                                                <option>Select Super Distributor</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">City</label>
                                            <select class="form-control" name="city" id="city">
                                                <option>Select City</option>
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
        document.querySelector("input[name='email']").addEventListener("input", function () {
            let email = this.value;
            let usernameField = document.querySelector("input[name='username']");

            if (email.includes("@")) {
                let username = email.split("@")[0] + "@distributor";
                usernameField.value = username; // Set the generated username in the input field
            } else {
                usernameField.value = ""; // Clear if email is invalid
            }
        });

        function fetchSuperDistributors() {
            let superDistributorDropdown = document.getElementById("super_distributor_id");
            let mainAdminId = "<?php echo $main_admin_id; ?>";

            fetch("db/get/get_super_distributors.php?main_admin_id=" + mainAdminId)
                .then(response => response.json())
                .then(data => {
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
            let cityDropdown = document.getElementById("city");

            if (superDistributorId) {
                fetch("db/get/get_cities_by_state.php?super_distributor_id=" + superDistributorId)
                    .then(response => response.json())
                    .then(data => {
                        cityDropdown.innerHTML = '<option>Select City</option>';
                        data.forEach(city => {
                            let option = document.createElement("option");
                            option.value = city.id;
                            option.textContent = city.city;
                            cityDropdown.appendChild(option);
                        });
                    })
                    .catch(error => console.error("Error fetching cities:", error));
            } else {
                cityDropdown.innerHTML = '<option>Select City</option>';
            }
        });

        document.getElementById("submit-btn").addEventListener("click", function () {
            let formData = new FormData(document.getElementById("product-form"));

            fetch("db/insert/add_distributor.php", {
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
                        document.getElementById("product-form").reset(); // Reset form on success
                    }

                    setTimeout(() => {
                        alertContainer.style.display = "none";
                    }, 3000);
                })
                .catch(error => console.error("Error:", error));
        });

        // Fetch Super Distributors on page load
        fetchSuperDistributors();
    </script>

<?php include 'footer.php'; ?>

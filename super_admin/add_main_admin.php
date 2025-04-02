<?php include 'header.php'; ?>
<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Add Admin</h4>
                        </div>
                        <div class="card-body">
                            <div id="alert-container" style="display: none;" class="alert"></div>

                            <form id="product-form">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" class="form-control" name="full_name" placeholder="Enter Full Name" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" placeholder="Enter Email ID" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Mobile Number</label>
                                            <input type="tel" class="form-control" name="mobile" placeholder="Enter Mobile Number" pattern="[0-9]{10}" maxlength="10" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" readonly class="form-control" name="username" readonly required>
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
    document.querySelector("input[name='email']").addEventListener("input", function() {
        let email = this.value.trim();
        let usernameField = document.querySelector("input[name='username']");
        if (email.includes("@")) {
            let username = email.split("@")[0].toLowerCase() + "@admin";
            usernameField.value = username;
        } else {
            usernameField.value = "";
        }
    });

    document.getElementById("submit-btn").addEventListener("click", function() {
        let formData = new FormData(document.getElementById("product-form"));

        fetch("db/insert/add_main_admin.php", {
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
                document.getElementById("product-form").reset();
            }

            setTimeout(() => {
                alertContainer.style.display = "none";
            }, 3000);
        })
        .catch(error => console.error("Error:", error));
    });
</script>

<?php include 'footer.php'; ?>

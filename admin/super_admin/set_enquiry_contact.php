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
                            <h4 class="card-title mb-0">Update Enquiry Support</h4>
                        </div>
                        <div class="card-body">

                            <div id="alert-container" style="display: none;" class="alert"></div>

                            <form id="admin-form">
                                <div class="row">
                                    <div>
                                    <?php
                                    $query= mysqli_query($conn,"SELECT * FROM settings WHERE id='1'");
                                    $data= $query->fetch_assoc();
                                    ?>
                                    <div><strong>Current Whatsapp Number :  </strong><?php echo $data['whatsapp_number']; ?> </div>
                                    <div><strong>Current Phone Number :  </strong><?php echo $data['phone']; ?> </div>
                                    <div><strong>Current Email :  </strong><?php echo $data['email']; ?> </div>
                                    <br>
                                    <br>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Whatsapp Number</label>
                                            <input type="tel" class="form-control" name="whatsapp_number" placeholder="Enter Whatsapp Number" pattern="[0-9]{10}" maxlength="10" required>
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
                                            <label class="form-label">Email ID</label>
                                            <input type="email" class="form-control" name="email" id="email" placeholder="Enter Email ID" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <button type="button" id="submit-btn" class="btn btn-primary">Update</button>
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
            let formData = new FormData(document.getElementById("admin-form"));

            fetch("db/insert/add_settings.php", {
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
                        location.reload();
                        document.getElementById("admin-form").reset();
                    }

                    setTimeout(() => {
                        alertContainer.style.display = "none";
                    }, 3000);
                })
                .catch(error => console.error("Error:", error));
        });
    </script>

    <?php include 'footer.php'; ?>

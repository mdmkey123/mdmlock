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
                            <h4 class="card-title mb-0">Update FRP-Id</h4>
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
                                    <div><strong>Current Logo: </strong><a href="<?php echo $data['logo_url']; ?>" target="blank"><?php echo $data['logo_url']; ?> </a></div>
                                    <br>
                                </div>
                               
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Logo URL</label>
                                            <input type="text" class="form-control" name="logo_url" placeholder="Enter URL" required>
                                        </div>

                                    <div class="col-lg-12">
                                        <button type="button" id="submit-btn" class="btn btn-primary">UPDATE</button>
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

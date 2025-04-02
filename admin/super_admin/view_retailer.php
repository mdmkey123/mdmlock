<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$super_admin_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    echo "<script>alert('Invalid retailer!'); window.location.href='retailer_list.php';</script>";
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT a.*, 
                 sd.name AS super_distributor_name,
                 ma.name AS main_admin_name, 
                 d.full_name AS distributor_company_name
          FROM admin a
          LEFT JOIN super_distributor sd ON a.super_distributor_id = sd.id
          LEFT JOIN main_admin ma ON a.main_admin_id = ma.id
          LEFT JOIN distributors d ON a.distributor_id = d.id
          WHERE a.id = '$id' AND a.super_admin_id = '$super_admin_id'";

$result = mysqli_query($conn, $query);

if (!$row = mysqli_fetch_assoc($result)) {
    echo "<script>alert('Retailer not found or access denied!'); window.location.href='retailer_list.php';</script>";
    exit;
}

$statusClass = ($row['status'] == 1) ? 'active-status' : 'inactive-status';
$statusText = ($row['status'] == 1) ? 'Active' : 'Inactive';
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="row">

                <div class="row">

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Administration Details</h4>
                            </div>
                            <div class="card-body">
                                <p><strong>Unique ID:</strong> <?= $row['unique_admin_id'] ?></p>
                                <p><strong>Main Admin:</strong> <?= $row['main_admin_name'] ?></p>
                                <p><strong>Super Distributor:</strong> <?= $row['super_distributor_name'] ?></p>
                                <p><strong>Distributor:</strong> <?= $row['distributor_company_name'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Personal Details</h4>
                            </div>
                            <div class="card-body">
                                <p><strong>Full Name:</strong> <?= $row['first_name'] . ' ' . $row['last_name'] ?></p>
                                <p><strong>Email:</strong> <?= $row['email'] ?></p>
                                <p><strong>Phone:</strong> <?= $row['phone'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Company Details</h4>
                            </div>
                            <div class="card-body">
                                <p><strong>Company Name:</strong> <?= $row['company_name']?></p>
                                <p><strong>GST Number:</strong> <?= $row['gstn_number'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Balance & Status</h4>
                            </div>
                            <div class="card-body">
                                <p><strong>Balance:</strong> <?= $row['balance'] ?></p>
                                <p><strong>Status:</strong> <button class="status-btn <?= $statusClass ?>"
                                        onclick="toggleStatus(<?= $row['id'] ?>)" id="status-<?= $row['id'] ?>">
                                        <?= $statusText ?>
                                    </button></p>
                                <p><strong>Delete:</strong> <button class='status-btn inactive-status'
                                        onclick='deleteRetailer(<?= $row['id'] ?>)' title='Delete'>
                                        Delete
                                    </button></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Created Details</h4>
                            </div>
                            <div class="card-body">
                                <p><strong>Created At:</strong> <?= $row['created_at'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Payment-QR</h4>
                            </div>
                            <div class="card-body">
                                <div><strong>QR-URL: </strong><a href="<?php echo $row['payment_qr'];?>" target="_blank"><?php echo $row['payment_qr'];?></a></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 mx-auto">
                        <a href="retailer_list.php" class="btn btn-secondary mt-3">Back to List</a>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script>
        function toggleStatus(id) {
            fetch('db/update/update_retailer_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        let button = document.getElementById('status-' + id);
                        if (data.new_status === 1) {
                            button.classList.remove('inactive-status');
                            button.classList.add('active-status');
                            button.textContent = "Active";
                        } else {
                            button.classList.remove('active-status');
                            button.classList.add('inactive-status');
                            button.textContent = "Inactive";
                        }
                    } else {
                        alert("Failed to update status!");
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function deleteRetailer(id) {
            if (confirm("Are you sure you want to delete this retailer?")) {
                fetch('db/delete/delete_retailer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + id
                })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.status === "success") {
                            location.reload();
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }
    </script>

    <?php include 'footer.php'; ?>

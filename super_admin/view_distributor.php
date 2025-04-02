<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$super_admin_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    echo "<script>alert('Invalid distributor!'); window.location.href='distributor_list.php';</script>";
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT d.*, 
                 ma.name AS main_admin_name, 
                 sd.name AS super_distributor_name, 
                 c.city AS city_name
          FROM distributors d
          LEFT JOIN main_admin ma ON d.main_admin_id = ma.id
          LEFT JOIN super_distributor sd ON d.super_distributor_id = sd.id
          LEFT JOIN cities c ON d.city = c.id
          WHERE d.id = '$id' AND d.super_admin_id = '$super_admin_id'";

$result = mysqli_query($conn, $query);

if (!$row = mysqli_fetch_assoc($result)) {
    echo "<script>alert('Distributor not found or access denied!'); window.location.href='distributor_list.php';</script>";
    exit;
}

$statusClass = ($row['status'] == 'active') ? 'active-status' : 'inactive-status';
$statusText = ucfirst($row['status']);
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
                                <p><strong>Unique ID:</strong> <?= $row['unique_distributor_id'] ?></p>
                                <p><strong>Main Admin:</strong> <?= $row['main_admin_name'] ?></p>
                                <p><strong>Super Distributor:</strong> <?= $row['super_distributor_name'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Personal Details</h4>
                            </div>
                            <div class="card-body">
                                <p><strong>Full Name:</strong> <?= $row['full_name'] ?></p>
                                <p><strong>Email:</strong> <?= $row['email'] ?></p>
                                <p><strong>Mobile Number:</strong> <?= $row['mobile'] ?></p>
                                <p><strong>Username:</strong> <?= $row['username'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Address Details</h4>
                            </div>
                            <div class="card-body">                              
                                <p><strong>City:</strong> <?= $row['city_name'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Wallet Details & Action</h4>
                            </div>
                            <div class="card-body">
                                <p><strong>Wallet:</strong> <?= $row['wallet'] ?></p>
                                <p><strong>Status:</strong> <button class="status-btn <?= $statusClass ?>"
                                        onclick="toggleStatus(<?= $row['id'] ?>)" id="status-<?= $row['id'] ?>">
                                        <?= $statusText ?>
                                    </button></p>
                                <p><strong>Delete:</strong> <button class='status-btn inactive-status'
                                        onclick='deleteDistributor({$row[' id']})' title='Delete'>
                                        Delete
                                    </button></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Created/Updated Details</h4>
                            </div>
                            <div class="card-body">
                                <p><strong>Created At:</strong> <?= $row['created_at'] ?></p>
                                <p><strong>Updated At:</strong> <?= $row['updated_at'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 mx-auto">
                        <a href="distributor_list.php" class="btn btn-secondary mt-3">Back to List</a>
                    </div>
                
                </div>

            </div>
        </div>
    </div>

    <script>
        function toggleStatus(id) {
            fetch('db/update/update_distributor_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        let button = document.getElementById('status-' + id);
                        if (data.new_status === "active") {
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

        function deleteDistributor(id) {
            if (confirm("Are you sure you want to delete this distributor?")) {
                fetch('db/delete/delete_distributor.php', {
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
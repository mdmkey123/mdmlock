<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$super_admin_id = $_SESSION['user_id'];

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$whereClause = "WHERE a.super_admin_id = $super_admin_id";

if (isset($_GET['super_distributor_id']) && $_GET['super_distributor_id'] !== '') {
    $whereClause .= " AND a.super_distributor_id = '" . mysqli_real_escape_string($conn, $_GET['super_distributor_id']) . "'";
}

if (isset($_GET['main_admin_id']) && $_GET['main_admin_id'] !== '') {
    $whereClause .= " AND a.main_admin_id = '" . mysqli_real_escape_string($conn, $_GET['main_admin_id']) . "'";
}

if (isset($_GET['distributor_id']) && $_GET['distributor_id'] !== '') {
    $whereClause .= " AND a.distributor_id = '" . mysqli_real_escape_string($conn, $_GET['distributor_id']) . "'";
}

if (!empty($_GET['name'])) {
    $name = mysqli_real_escape_string($conn, $_GET['name']);
    $whereClause .= " AND (a.first_name LIKE '$name%' OR a.last_name LIKE '$name%')";
}

if (!empty($_GET['email'])) {
    $email = mysqli_real_escape_string($conn, $_GET['email']);
    $whereClause .= " AND a.email LIKE '$email%'";
}

if (!empty($_GET['phone'])) {
    $phone = mysqli_real_escape_string($conn, $_GET['phone']);
    $whereClause .= " AND a.phone LIKE '$phone%'";
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $whereClause .= " AND a.status = '" . mysqli_real_escape_string($conn, $_GET['status']) . "'";
}

if (!empty($_GET['created_from'])) {
    $whereClause .= " AND DATE(a.created_at) >= '" . mysqli_real_escape_string($conn, $_GET['created_from']) . "'";
}

if (!empty($_GET['created_to'])) {
    $whereClause .= " AND DATE(a.created_at) <= '" . mysqli_real_escape_string($conn, $_GET['created_to']) . "'";
}

$query = "SELECT a.*, 
                 ma.name AS main_admin_name,
                 sd.name AS super_distributor_name,
                 d.full_name AS distributor_name
          FROM admin a
          LEFT JOIN main_admin ma ON a.main_admin_id = ma.id
          LEFT JOIN super_distributor sd ON a.super_distributor_id = sd.id
          LEFT JOIN distributors d ON a.distributor_id = d.id
          $whereClause
          LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Count total filtered records for pagination
$countQuery = "SELECT COUNT(*) AS total FROM admin a $whereClause";
$countResult = mysqli_query($conn, $countQuery);
$totalRecords = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRecords / $limit);
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        
                        <div class="card-body">
                            <!-- Filters Section -->
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-3">
                                    <label for="name" class="form-label">Retailer Name</label>
                                    <input type="text" name="name" value="<?= $_GET['name'] ?? '' ?>" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" value="<?= $_GET['email'] ?? '' ?>" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" name="phone" value="<?= $_GET['phone'] ?? '' ?>" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">Select</option>
                                        <option value="1" <?= (isset($_GET['status']) && $_GET['status'] == "1") ? 'selected' : '' ?>>Active</option>
                                        <option value="0" <?= (isset($_GET['status']) && $_GET['status'] == "0") ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="created_from" class="form-label">Created From</label>
                                    <input type="date" name="created_from" value="<?= $_GET['created_from'] ?? '' ?>" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label for="created_to" class="form-label">Created To</label>
                                    <input type="date" name="created_to" value="<?= $_GET['created_to'] ?? '' ?>" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Distributor</label>
                                    <select name="distributor_id" class="form-select">
                                        <option value="">Select</option>
                                        <?php
                                        $dQuery = mysqli_query($conn, "SELECT id, full_name FROM distributors WHERE super_admin_id = $super_admin_id");
                                        while ($dRow = mysqli_fetch_assoc($dQuery)) {
                                            echo "<option value='{$dRow['id']}' " . ($_GET['distributor_id'] == $dRow['id'] ? 'selected' : '') . ">{$dRow['full_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Super Distributor</label>
                                    <select name="super_distributor_id" class="form-select">
                                        <option value="">Select</option>
                                        <?php
                                        $sdQuery = mysqli_query($conn, "SELECT id, name FROM super_distributor WHERE super_admin_id = $super_admin_id");
                                        while ($sdRow = mysqli_fetch_assoc($sdQuery)) {
                                            echo "<option value='{$sdRow['id']}' " . ($_GET['super_distributor_id'] == $sdRow['id'] ? 'selected' : '') . ">{$sdRow['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Main Admin</label>
                                    <select name="main_admin_id" class="form-select">
                                        <option value="">Select</option>
                                        <?php
                                        $maQuery = mysqli_query($conn, "SELECT id, name FROM main_admin WHERE super_admin_id = $super_admin_id");
                                        while ($maRow = mysqli_fetch_assoc($maQuery)) {
                                            echo "<option value='{$maRow['id']}' " . ($_GET['main_admin_id'] == $maRow['id'] ? 'selected' : '') . ">{$maRow['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">Apply</button>
                                    <a href="retailer_list.php" class="btn btn-secondary">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Retailer List</h4>
                            <select id="entriesSelect" class="form-select" style="width: auto;">
                                <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                            </select>
                        </div>
                        <div class="card-body">



                            <!-- Table Data -->
                            <div class="table-container mt-3">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Super Distributor</th>
                                            <th>Main Admin</th>
                                            <th>Distributor</th>
                                            <th>Retailer</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Password</th>
                                            <th>Company Name</th>
                                            <th>GST Number</th>
                                            <th>Keys</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sr_no = $offset + 1;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $retailer_id = $row['id'];
                                            echo "<tr>
                                                    <td>{$sr_no}</td>
                                                    <td>{$row['super_distributor_name']}</td>
                                                    <td>{$row['main_admin_name']}</td>
                                                    <td>{$row['distributor_name']}</td>
                                                    <td>{$row['first_name']} {$row['last_name']}</td>
                                                    <td>{$row['email']}</td>
                                                    <td>{$row['phone']}</td>
                                                    <td>{$row['password']}</td>
                                                    <td>{$row['company_name']}</td>
                                                    <td>{$row['gstn_number']}</td>
                                                    
                                                    ";
                                                    ?>
                                                    <td><i class='ri-key-fill'></i> <span id='key-count-<?= $retailer_id ?>'>Loading...</span></td>
                                                    <?php
                                                    echo "
                                                    <td>" . ($row['status'] == 1 ? 'Active' : 'Inactive') . "</td>
                                                    <td>                                                 
                                                    <a href='update_retailer.php?id={$row['id']}' class='icon-btn'><i class='ri-edit-box-fill'></i></a>
                                                    <button class='icon-btn' onclick='viewRetailer({$row['id']})' title='View'>
                                                        <i class='ri-eye-line'></i>
                                                    </button>
                                                    <button class='icon-btn' onclick='openChangePasswordModal({$row['id']}, {$row['password']})' title='Change Password'>
                                                        <i class='ri-lock-line'></i>
                                                    </button>
                                                </td>
                                                  </tr>";
                                            $sr_no++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="pagination-container">
                                <nav>
                                    <ul class="pagination justify-content-center mt-3">
                                        <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                                            <a href="?page=<?= $i ?>&limit=<?= $limit ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                                        <?php } ?>
                                    </ul>

                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <input type="hidden" id="retailerId" name="id">
                    
                    <div class="mb-3">
                        <label for="oldPassword" class="form-label">Old Password</label>
                        <input type="text" class="form-control" id="oldPassword" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        let keyCountElements = document.querySelectorAll("[id^='key-count-']");
    
        keyCountElements.forEach((element) => {
            let mainAdminId = element.id.replace("key-count-", "");
            fetchKeyCount(mainAdminId);
        });
    });
    
    function fetchKeyCount(mainAdminId) {
        fetch(`db/get/fetch_key_count.php?role=retailer&role_id=${mainAdminId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    document.getElementById(`key-count-${mainAdminId}`).innerText = data.key_count;
                } else {
                    document.getElementById(`key-count-${mainAdminId}`).innerText = "Error";
                    console.error("Error fetching key count:", data.message);
                }
            })
            .catch(error => {
                document.getElementById(`key-count-${mainAdminId}`).innerText = "Error";
                console.error('Error:', error);
            });
    }
    
    document.getElementById('entriesSelect').addEventListener('change', function() {
        window.location.href = "?limit=" + this.value + "&page=1";
    });

    document.getElementById('entriesSelect').addEventListener('change', function() {
        window.location.href = "?limit=" + this.value + "&page=1";
    });

    function loadData(page) {
        let search = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
        let limit = document.getElementById('entriesPerPage').value;
        window.location.href = `retailer_list.php?page=${page}&limit=${limit}&search=${search}`;
    }

    function resetSearch() {
        if (document.getElementById('searchInput')) {
            document.getElementById('searchInput').value = '';
        }
        loadData(1);
    }

    function viewRetailer(id) {
        window.location.href = 'view_retailer.php?id=' + id;
    }

    function openChangePasswordModal(id, password) {
        document.getElementById('retailerId').value = id;
        document.getElementById('oldPassword').value = password; // Show old password (read-only)
        document.getElementById('newPassword').value = ''; // Clear new password field
        document.getElementById('confirmPassword').value = ''; // Clear confirm password field
        $('#changePasswordModal').modal('show');
    }



    document.getElementById('changePasswordForm').addEventListener('submit', function(event) {
        event.preventDefault();

        let formData = new FormData(this);
        fetch('db/update/update_retailer_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert(data.message);
                    $('#changePasswordModal').modal('hide');
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    });

    function toggleStatus(id) {
        fetch('db/update/update_retailer_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    let button = document.getElementById('status-' + id);
                    button.classList.toggle('active-status', data.new_status === 1);
                    button.classList.toggle('inactive-status', data.new_status === 0);
                    button.textContent = data.new_status === 1 ? "Active" : "Inactive";
                } else {
                    alert("Failed to update status!");
                }
            });
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
                });
        }
    }
</script>

<?php include 'footer.php'; ?>
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
          WHERE d.id = '$id' AND d.main_admin_id = '$super_admin_id'";

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
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header text-center">
                            <h3 class="card-title">Distributor Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <?php
                                $sections = [
                                    "Administration Details" => [
                                        "Unique ID" => $row['unique_distributor_id'],
                                        "Main Admin" => $row['main_admin_name'],
                                        "Super Distributor" => $row['super_distributor_name']
                                    ],
                                    "Personal Details" => [
                                        "Full Name" => $row['full_name'],
                                        "Email" => $row['email'],
                                        "Mobile Number" => $row['mobile'],
                                        "Username" => $row['username']
                                    ],
                                    
                                    "Address Details" => [
                                        
                                        "City" => $row['city_name']
                                     
                                    ],
                                    "Keys & Actions" => [
                                        "Keys" => "<i class='ri-key-fill'></i> {$row['wallet']}",
                                        "Status" => "<button class='status-btn {$statusClass}' onclick='toggleStatus({$row['id']})' id='status-{$row['id']}'> {$statusText} </button>",
                                        "Delete" => "<button class='status-btn inactive-status' onclick='deleteDistributor({$row['id']})' title='Delete'>Delete</button>"
                                    ],
                                    "Created/Updated Details" => [
                                        "Created At" => $row['created_at'],
                                        "Updated At" => $row['updated_at']
                                    ]
                                ];

                                foreach ($sections as $title => $fields) {
                                    echo "<div class='col-lg-4'>
                                            <div class='card shadow-sm'>
                                                <div class='card-header bg-secondary text-white'>
                                                    <h5 class='mb-0'>{$title}</h5>
                                                </div>
                                                <div class='card-body'>";
                                    foreach ($fields as $key => $value) {
                                        echo "<p><strong>{$key}:</strong> {$value}</p>";
                                    }
                                    echo "  </div>
                                            </div>
                                          </div>";
                                }
                                ?>

                                <div class="col-lg-3 mx-auto text-center mt-4">
                                    <a href="distributor_list.php" class="btn btn-secondary">Back to List</a>
                                </div>

                            </div>
                        </div> 
                    </div> 
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
            button.classList.toggle('active-status', data.new_status === "active");
            button.classList.toggle('inactive-status', data.new_status === "inactive");
            button.textContent = data.new_status === "active" ? "Active" : "Inactive";
        } else {
            alert("Failed to update status!");
        }
    });
}

function deleteDistributor(id) {
    if (confirm("Are you sure you want to delete this distributor?")) {
        fetch('db/delete/delete_distributor.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === "success") {
                window.location.href = 'distributor_list.php';
            }
        });
    }
}
</script>

<?php include 'footer.php'; ?>

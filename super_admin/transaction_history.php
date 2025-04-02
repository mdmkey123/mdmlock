<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$super_admin_id = $_SESSION['user_id'];


?>
<!-- TableExport CDN -->
<!--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tableexport@5.2.0/dist/css/tableexport.min.css">-->
<!--<script src="https://cdn.jsdelivr.net/npm/tableexport@5.2.0/dist/js/tableexport.min.js"></script>-->

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <style>
                        body {
                            font-family: Arial, sans-serif;
                            background-color: #f4f7fa;
                            margin: 0;
                            /*padding: 20px;*/
                        }

                        .tabs {
                            display: flex;
                            justify-content: center;
                            margin-bottom: 20px;
                        }

                        .tab {
                            padding: 10px 20px;
                            cursor: pointer;
                            background-color: #fff;
                            border: 1px solid #ccc;
                            margin-right: 5px;
                            border-radius: 5px;
                            transition: background-color 0.3s ease;
                        }

                        .tab:hover {
                            background-color: #f0f0f0;
                        }

                        .active-tab {
                            background-color: #007bff;
                            color: white;
                            border-color: #007bff;
                        }

                        .cards-container {
                            display: flex;
                            justify-content: space-around;
                            flex-wrap: wrap;
                        }

                        .card {
                            background-color: #fff;
                            padding: 20px;
                            margin: 10px;
                            border-radius: 10px;
                            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                            width: 100%;
                            text-align: center;
                        }

                        .card h4 {
                            margin-top: 0;
                            font-size: 18px;
                        }

                        .card p {
                            font-size: 14px;
                            color: #555;
                        }

                        table {
                            width: 100%;
                            margin-top: 30px;
                            border-collapse: collapse;
                        }

                        table th,
                        table td {
                            padding: 12px;
                            text-align: left;
                            border-bottom: 1px solid #ddd;
                        }

                        table th {
                            background-color: #f8f9fa;
                        }

                        .table-container {
                            margin-top: 20px;
                        }

                        /* Make the charts section look better */
                        .chart-container {
                            margin-top: 20px;
                        }

                        .chart-container h3 {
                            text-align: center;
                            margin-bottom: 15px;
                        }
                        </style>
                        <body>
                        <?php 
                                    // Ensure the period is set or defaults to 'daily'
                                    $period = isset($_GET['period']) ? $_GET['period'] : 'daily';
                                
                                    // Set the date filter based on the selected period
                                    switch ($period) {
                                        case 'daily':
                                            // Filter for today's data
                                            $date_filter = "DATE(ma.created_at) = CURDATE()"; // Corrected here to use CURDATE()
                                            $date_filter2 = "DATE(retailer.created_at) =  CURDATE()"; // Corrected here to use CURDATE()
                                            // $date_filter2 = "DATE_FORMAT(retailer.created_at, '%Y-%m-%d') = '2025-02-24'"; // Corrected here to use CURDATE()
                                            break;
                                
                                        case 'weekly':
                                            // Filter for the current week (same week of the year)
                                            $date_filter = "YEARWEEK(ma.created_at, 1) = YEARWEEK(CURDATE(), 1)"; // Corrected to use YEARWEEK
                                            $date_filter2 = "YEARWEEK(retailer.created_at, 1) = YEARWEEK(CURDATE(), 1)"; // Corrected to use YEARWEEK
                                            break;
                                
                                        case 'monthly':
                                            // Filter for the current month and year
                                            $date_filter = "MONTH(ma.created_at) = MONTH(CURDATE()) AND YEAR(ma.created_at) = YEAR(CURDATE())";
                                            $date_filter2 = "MONTH(retailer.created_at) = MONTH(CURDATE()) AND YEAR(retailer.created_at) = YEAR(CURDATE())";
                                            break;
                                
                                        default:
                                            // Default case for 'daily' if no valid period is provided
                                            $date_filter = "DATE(ma.created_at) = CURDATE()"; // Default case
                                            $date_filter2 = "DATE(retailer.created_at) = CURDATE()"; // Default case
                                            break;
                                    }
                            ?>

                            <!-- Period Selection Form -->
                            <form method="GET" action="">
                                <button type="submit" name="period" value="daily"
                                    <?php if ($period == 'daily') echo 'class="selected"'; ?>>Daily</button>
                                <button type="submit" name="period" value="weekly"
                                    <?php if ($period == 'weekly') echo 'class="selected"'; ?>>Weekly</button>
                                <button type="submit" name="period" value="monthly"
                                    <?php if ($period == 'monthly') echo 'class="selected"'; ?>>Monthly</button>
                            </form>

                            <!-- Admin Details Table -->
                            <div class="table-container" id="admin-details">
                                <h3>Admin Details</h3>
                                 <button id="download" style="float:right; margin:4px;">Download Excel</button>
                                 <table id="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Unique ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Mobile Number</th>
                                            <th>Keys</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                // Ensure the super_admin_id is properly sanitized before using it in the query
                $super_admin_id = mysqli_real_escape_string($conn, $super_admin_id);

                // Define the query to fetch data for the main admin
                $query = "SELECT ma.* 
                          FROM main_admin ma 
                          WHERE ma.super_admin_id = '$super_admin_id' 
                          AND $date_filter";
                
                // Execute the query
                $result = mysqli_query($conn, $query);

                // Check if there are any results
                if (mysqli_num_rows($result) > 0) {
                    // Loop through each row in the result
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Fetch data from the current row
                        $main_admin_id = $row['id'];
                        $admin_id = $row['unique_main_admin_id'];  // Adjust the column names as per your table
                        $admin_name = $row['name'];
                        $admin_email = $row['email'];
                        $mobile_number = $row['mobile_number'];

                        // Output the data inside a table row
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($admin_id) . "</td>";
                        echo "<td>" . htmlspecialchars($admin_name) . "</td>";
                        echo "<td>" . htmlspecialchars($admin_email) . "</td>";
                        echo "<td>" . htmlspecialchars($mobile_number) . "</td>";
                        echo "<td><i class='ri-key-fill'></i> <span id='key-count-" . htmlspecialchars($main_admin_id) . "'>Loading...</span></td>";
                        echo "</tr>";
                    }
                } else {
                    // If no data is found, display a message
                    echo "<tr><td colspan='5'>No admins found.</td></tr>";
                }
            ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="table-container" id="superDistributor-details">
                                <h3>Super Destributor Details</h3>
                                <button id="download2" style="float:right; margin:4px;">Download Excel</button>
                                <table id="superDistributor-details">
                                    <thead>
                                        <tr>
                                            <th>Unique ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Mobile Number</th>
                                            <th>Keys</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                                                // Define the query to fetch data for the main admin
                                                                $query = "SELECT ma.* 
                                                                          FROM super_distributor ma
                                                                          WHERE ma.super_admin_id = $super_admin_id AND $date_filter";
                                                                
                                                                // Execute the query
                                                                $result = mysqli_query($conn, $query);
                                                                
                                                                // Check if there are any results
                                                                if (mysqli_num_rows($result) > 0) {
                                                                    // Loop through each row in the result
                                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                                    // getting key -----
                                                                        $main_admin_id = $row['id'];
                                                                   
                                                                    // Fetch data from the current row
                                                                    $admin_id = $row['unique_super_distributor_id'];  // Adjust the column names as per your table
                                                                    $admin_name = $row['name'];
                                                                    $admin_email = $row['email'];
                                                                    $mobile_number = $row['mobile_number'];
                                                            
                                                                    // Output the data inside a table row
                                                                                        echo "<tr>";
                                                                                echo "<td>" . htmlspecialchars($admin_id) . "</td>";
                                                                                echo "<td>" . htmlspecialchars($admin_name) . "</td>";
                                                                                echo "<td>" . htmlspecialchars($admin_email) . "</td>";
                                                                                echo "<td>" . htmlspecialchars($mobile_number) . "</td>";
                                                                                // echo "<td>" . htmlspecialchars($key_count) . "</td>"; // Output the key count
                                                                                echo "<td><i class='ri-key-fill'></i> <span id='key-count2-" . htmlspecialchars($main_admin_id) . "'>Loading...</span></td>";
                                                                                echo "</tr>";
                                                                                
                                                                                    }
                                                                                } else {
                                                                                    // If no data is found, display a message
                                                                                    echo "<tr><td colspan='5'>No admins found.</td></tr>"; // Make sure the colspan matches the number of columns
                                                                                }
                                                            ?>

                                    </tbody>

                                </table>
                            </div>
                            <div class="table-container" id="distibutor-details">
                                <h3>Distributor Details</h3>
                                <button id="download3" style="float:right; margin:4px;">Download Excel</button>
                                <table id="distibutor-details">
                                    <thead>
                                        <tr>
                                            <th>Unique ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Mobile Number</th>
                                            <th>Keys</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            // Define the query to fetch data for the main admin
                                            $query = "SELECT ma.* 
                                                      FROM distributors ma 
                                                      WHERE ma.super_admin_id = $super_admin_id AND $date_filter";
                                            
                                            // Execute the query
                                            $result = mysqli_query($conn, $query);
                                            
                                            // Check if there are any results
                                            if (mysqli_num_rows($result) > 0) {
                                                // Loop through each row in the result
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                // getting key -----
                                                    $main_admin_id = $row['id'];
                                                // Fetch data from the current row
                                                $admin_id = $row['unique_distributor_id'];  // Adjust the column names as per your table
                                                $admin_name = $row['full_name'];
                                                $admin_email = $row['email'];
                                                $mobile_number = $row['mobile'];
                                        
                                                // Output the data inside a table row
                                                                    echo "<tr>";
                                                            echo "<td>" . htmlspecialchars($admin_id) . "</td>";
                                                            echo "<td>" . htmlspecialchars($admin_name) . "</td>";
                                                            echo "<td>" . htmlspecialchars($admin_email) . "</td>";
                                                            echo "<td>" . htmlspecialchars($mobile_number) . "</td>";
                                                            // echo "<td>" . htmlspecialchars($key_count) . "</td>"; // Output the key count
                                                            echo "<td><i class='ri-key-fill'></i> <span id='key-count3-" . htmlspecialchars($main_admin_id) . "'>Loading...</span></td>";
                                                            echo "</tr>";
                                                            
                                                                }
                                                            } else {
                                                                // If no data is found, display a message
                                                                echo "<tr><td colspan='5'>No admins found.</td></tr>"; // Make sure the colspan matches the number of columns
                                                            }
                                                                ?>

                                    </tbody>

                                </table>
                            </div>
                            <div class="table-container" id="Keytransaction-details">
                                <h3>Key transaction history Details</h3>
                                <button id="download4" style="float:right; margin:4px;">Download Excel</button>
                                <table id="Keytransaction-details">
                                    <thead>
                                        <tr>
                                            <th>Transaction ID</th>
                                            <th>Transaction Type</th>
                                            <th>Credited/Debited To</th>
                                            <th>Keys</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        //   $whereClauses = ["second_user_type = 'super_admin'"];
                                            // Define the query to fetch data for the main admin
                                            $query = "SELECT ma.* FROM transaction_history ma WHERE ma.second_user_type = 'super_admin' AND $date_filter";
                                                      
                                            
                                            // Execute the query
                                            $result = mysqli_query($conn, $query);
                                            
                                            // Check if there are any results
                                            if (mysqli_num_rows($result) > 0) {
                                                // Loop through each row in the result
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                $type_color = ($row['type'] === 'credit') ? 'success' : 'danger';
                                                // getting key -----
                                                    // $main_admin_id = $row['id'];
                                                // Fetch data from the current row
                                                $transaction_id = $row['transaction_id'];  // Adjust the column names as per your table
                                                $type = $row['type'];
                                                $user_type = $row['user_type'];
                                                $keys = $row['number_of_keys'];
                                                $created_at = $row['created_at'];
                                        
                                                // Output the data inside a table row
                                                                    echo "<tr>";
                                                            echo "<td>" . htmlspecialchars($transaction_id) . "</td>";
                                                            echo "<td><span class='badge bg-{$type_color}'>{$type}</span></td>";
                                                            // echo "<td>" . htmlspecialchars($type) . "</td>";
                                                            echo "<td>" . htmlspecialchars($user_type) . "</td>";
                                                            echo "<td>" . htmlspecialchars($keys) . "</td>";
                                                            echo "<td>" . htmlspecialchars($created_at) . "</td>";
                                                            // echo "<td>" . htmlspecialchars($key_count) . "</td>"; // Output the key count
                                                            // echo "<td><i class='ri-key-fill'></i> <span id='key-count3-" . htmlspecialchars($main_admin_id) . "'>Loading...</span></td>";
                                                            echo "</tr>";
                                                            
                                                                }
                                                            } else {
                                                                // If no data is found, display a message
                                                                echo "<tr><td colspan='5'>No admins found.</td></tr>"; // Make sure the colspan matches the number of columns
                                                            }
                                                                ?>

                                    </tbody>

                                </table>
                            </div>
                            <div class="table-container" id="retailer-details">
                                <h3>Retailer Details</h3>
                                <button id="download5" style="float:right; margin:4px;">Download Excel</button>
                                <table id="retailer-details">
                                    <thead>
                                        <tr>
                                            <th>Unique ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Mobile Number</th>
                                            <th>Keys</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            // Define the query to fetch data for the main admin
                                            $query = "SELECT ma.* 
                                                      FROM admin ma 
                                                      WHERE ma.super_admin_id = $super_admin_id AND $date_filter";
                                            
                                            // Execute the query
                                            $result = mysqli_query($conn, $query);
                                            
                                            // Check if there are any results
                                            if (mysqli_num_rows($result) > 0) {
                                                // Loop through each row in the result
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                // getting key -----
                                                    $main_admin_id = $row['id'];
                                               
                                                // Fetch data from the current row
                                                $admin_id = $row['unique_admin_id'];  // Adjust the column names as per your table
                                                $admin_name = $row['first_name'].'-'.$row['last_name'];
                                                $admin_email = $row['email'];
                                                $mobile_number = $row['phone'];
                                        
                                                // Output the data inside a table row
                                                                    echo "<tr>";
                                                            echo "<td>" . htmlspecialchars($admin_id) . "</td>";
                                                            echo "<td>" . htmlspecialchars($admin_name) . "</td>";
                                                            echo "<td>" . htmlspecialchars($admin_email) . "</td>";
                                                            echo "<td>" . htmlspecialchars($mobile_number) . "</td>";
                                                            // echo "<td>" . htmlspecialchars($key_count) . "</td>"; // Output the key count
                                                            echo "<td><i class='ri-key-fill'></i> <span id='key-count4-" . htmlspecialchars($main_admin_id) . "'>Loading...</span></td>";
                                                            echo "</tr>";
                                                            
                                                                }
                                                            } else {
                                                                // If no data is found, display a message
                                                                echo "<tr><td colspan='5'>No admins found.</td></tr>"; // Make sure the colspan matches the number of columns
                                                            }
                                                                ?>

                                    </tbody>

                                </table>
                            </div>
                            <div class="table-container6" id="user-details">
                                <h3>User Details</h3>
                                <button id="download6" style="float:right; margin:4px;">Download Excel</button>
                                <table id="user-details">
                                    <thead>
                                        <tr>
                                            <!--<th>Unique ID</th>-->
                                            <th>Customer Name</th>
                                            <th>Loan Id</th>
                                            <th>Phone</th>
                                            <th>Device Name</th>
                                            <th>Product Price(₹)</th>
                                            <th>Latest EMI(₹)</th>
                                            <th>Device Status</th>
                                            <th>Lock Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            // Define the query to fetch data for the main admin
                                            $query = "SELECT 
                                                customers.id AS customer_id, customers.name, customers.phone, customers.device_id,
                                                devices.brand, devices.model, devices.admin_id AS retailer_id,
                                                devices.status AS device_status, devices.locked AS device_locked,  -- Added these fields
                                                emi_details.product_price, emi_details.loan_id,
                                                
                                                (SELECT emis.amount 
                                                 FROM emis 
                                                 WHERE emis.emi_details_id = emi_details.id 
                                                 ORDER BY emis.emi_date DESC LIMIT 1) AS latest_emi,
                                    
                                                (SELECT emis.emi_date 
                                                 FROM emis 
                                                 WHERE emis.emi_details_id = emi_details.id 
                                                 ORDER BY emis.emi_date DESC LIMIT 1) AS emi_date,
                                    
                                                retailer.first_name AS retailer_first_name, retailer.last_name AS retailer_last_name, retailer.created_at,
                                                retailer.distributor_id, retailer.super_distributor_id, retailer.super_admin_id,
                                    
                                                distributor.full_name AS distributor_name,
                                                super_distributor.name AS super_distributor_name
                                              
                                              FROM customers 
                                              LEFT JOIN devices ON customers.device_id = devices.id 
                                              LEFT JOIN emi_details ON devices.id = emi_details.device_id
                                              LEFT JOIN admin AS retailer ON devices.admin_id = retailer.id
                                              LEFT JOIN distributors AS distributor ON retailer.distributor_id = distributor.id
                                              LEFT JOIN super_distributor ON retailer.super_distributor_id = super_distributor.id
                                              WHERE retailer.super_admin_id = '$super_admin_id' AND $date_filter2";

                                            
                                            // Execute the query
                                            $result = mysqli_query($conn, $query);
                                            
                                            // print_r(mysqli_fetch_assoc($result));die;
                                            // Check if there are any results
                                            if (mysqli_num_rows($result) > 0) {
                                                
                                                // Loop through each row in the result
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                // $sr_no = 1;
                                                // getting key -----
                                                // $main_admin_id = $row['id'];
                                               
                                                // Fetch data from the current row
                                                // $admin_id = $row['unique_main_admin_id'];  // Adjust the column names as per your table
                                                $name = $row['name'];
                                                $loan_id = $row['loan_id'] ?? 'Emi Not Generated';
                                                $phone = $row['phone'];
                                                $device_model = $row['brand'].'-'.$row['model'];;
                                                $product_price = $row['product_price'] ?? 'Emi Not Generated';
                                                $latest_emi = $row['latest_emi'] ?? 'Emi Not Generated';
                                                $device_status = $row['device_status'];
                                                $device_locked = ($row['device_locked'] == 1)
                                                    ? "<span class='lock locked'>Locked</span>"
                                                    : "<span class='lock unlocked'>Unlocked</span>";
                                        
                                                // Output the data inside a table row
                                                                    echo "<tr>";
                                                            // echo "<td>" . htmlspecialchars($sr_no) . "</td>";
                                                            echo "<td>" . htmlspecialchars($name) . "</td>";
                                                            echo "<td>" . htmlspecialchars($loan_id) . "</td>";
                                                            echo "<td>" . htmlspecialchars($phone) . "</td>";
                                                            echo "<td>" . htmlspecialchars($device_model) . "</td>";
                                                            echo "<td>" . htmlspecialchars($product_price) . "</td>";
                                                            echo "<td>" . htmlspecialchars($latest_emi) . "</td>";
                                                            echo "<td>" . htmlspecialchars($device_status) . "</td>";
                                                            echo "<td>" . $device_locked . "</td>";
                                                            // echo "<td>" . htmlspecialchars($key_count) . "</td>"; // Output the key count
                                                            // echo "<td><i class='ri-key-fill'></i> <span id='key-count5-" . htmlspecialchars($main_admin_id) . "'>Loading...</span></td>";
                                                            echo "</tr>";
                                                            
                                                                }
                                                            } else {
                                                                // If no data is found, display a message
                                                                echo "<tr><td colspan='5'>No admins found.</td></tr>"; // Make sure the colspan matches the number of columns
                                                            }
                                                                ?>

                                    </tbody>

                                </table>
                            </div>
                        </body>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- SheetJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
document.getElementById('download').addEventListener('click', function () {
    const table = document.getElementById('admin-table');
    const workbook = XLSX.utils.table_to_book(table, { sheet: "AdminDetails" });
    XLSX.writeFile(workbook, 'admin_details.xlsx');
});
document.getElementById('download2').addEventListener('click', function () {
    const table = document.getElementById('superDistributor-details');
    const workbook = XLSX.utils.table_to_book(table, { sheet: "AdminDetails" });
    XLSX.writeFile(workbook, 'super_distributor_details.xlsx');
});
document.getElementById('download3').addEventListener('click', function () {
    const table = document.getElementById('distibutor-details');
    const workbook = XLSX.utils.table_to_book(table, { sheet: "AdminDetails" });
    XLSX.writeFile(workbook, 'distributor_details.xlsx');
});
document.getElementById('download4').addEventListener('click', function () {
    const table = document.getElementById('Keytransaction-details');
    const workbook = XLSX.utils.table_to_book(table, { sheet: "AdminDetails" });
    XLSX.writeFile(workbook, 'key_trans_details.xlsx');
});
document.getElementById('download5').addEventListener('click', function () {
    const table = document.getElementById('retailer-details');
    const workbook = XLSX.utils.table_to_book(table, { sheet: "AdminDetails" });
    XLSX.writeFile(workbook, 'retailer_details.xlsx');
});
document.getElementById('download6').addEventListener('click', function () {
    const table = document.getElementById('user-details');
    const workbook = XLSX.utils.table_to_book(table, { sheet: "AdminDetails" });
    XLSX.writeFile(workbook, 'user_details.xlsx');
});
</script>


<script>
document.addEventListener("DOMContentLoaded", function() {
    let keyCountElements = document.querySelectorAll("[id^='key-count-']");

    keyCountElements.forEach((element) => {
        let mainAdminId = element.id.replace("key-count-", "");
        fetchKeyCount(mainAdminId);
    });
});

document.addEventListener("DOMContentLoaded", function() {
    let keyCountElements = document.querySelectorAll("[id^='key-count2-']");

    keyCountElements.forEach((element) => {
        let mainAdminId = element.id.replace("key-count2-", "");
        fetchKeyCount2(mainAdminId);
    });
});
document.addEventListener("DOMContentLoaded", function() {
    let keyCountElements = document.querySelectorAll("[id^='key-count3-']");

    keyCountElements.forEach((element) => {
        let mainAdminId = element.id.replace("key-count3-", "");
        fetchKeyCount3(mainAdminId);
    });
});
document.addEventListener("DOMContentLoaded", function() {
    let keyCountElements = document.querySelectorAll("[id^='key-count4-']");

    keyCountElements.forEach((element) => {
        let mainAdminId = element.id.replace("key-count4-", "");
        fetchKeyCount4(mainAdminId);
    });
});
document.addEventListener("DOMContentLoaded", function() {
    let keyCountElements = document.querySelectorAll("[id^='key-count2-']");

    keyCountElements.forEach((element) => {
        let mainAdminId = element.id.replace("key-count5-", "");
        fetchKeyCount5(mainAdminId);
    });
});


function fetchKeyCount(mainAdminId) {
    fetch(`db/get/fetch_key_count.php?role=main_admin&role_id=${mainAdminId}`)
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


function fetchKeyCount2(mainAdminId) {
    fetch(`db/get/fetch_key_count.php?role=super_distributor&role_id=${mainAdminId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                document.getElementById(`key-count2-${mainAdminId}`).innerText = data.key_count;
            } else {
                document.getElementById(`key-count2-${mainAdminId}`).innerText = "Error";
                console.error("Error fetching key count:", data.message);
            }
        })
        .catch(error => {
            document.getElementById(`key-count2-${mainAdminId}`).innerText = "Error";
            console.error('Error:', error);
        });
}

function fetchKeyCount3(mainAdminId) {
    fetch(`db/get/fetch_key_count.php?role=distributor&role_id=${mainAdminId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                document.getElementById(`key-count3-${mainAdminId}`).innerText = data.key_count;
            } else {
                document.getElementById(`key-count3-${mainAdminId}`).innerText = "Error";
                console.error("Error fetching key count:", data.message);
            }
        })
        .catch(error => {
            document.getElementById(`key-count-${mainAdminId}`).innerText = "Error";
            console.error('Error:', error);
        });
}

function fetchKeyCount4(mainAdminId) {
    fetch(`db/get/fetch_key_count.php?role=retailer&role_id=${mainAdminId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                document.getElementById(`key-count4-${mainAdminId}`).innerText = data.key_count;
            } else {
                document.getElementById(`key-count4-${mainAdminId}`).innerText = "Error";
                console.error("Error fetching key count:", data.message);
            }
        })
        .catch(error => {
            document.getElementById(`key-count-${mainAdminId}`).innerText = "Error";
            console.error('Error:', error);
        });
}

function fetchKeyCount5(mainAdminId) {
    fetch(`db/get/fetch_key_count.php?role=super_distributor&role_id=${mainAdminId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                document.getElementById(`key-count2-${mainAdminId}`).innerText = data.key_count;
            } else {
                document.getElementById(`key-count2-${mainAdminId}`).innerText = "Error";
                console.error("Error fetching key count:", data.message);
            }
        })
        .catch(error => {
            document.getElementById(`key-count2-${mainAdminId}`).innerText = "Error";
            console.error('Error:', error);
        });
}
</script>

<?php include 'footer.php'; ?>
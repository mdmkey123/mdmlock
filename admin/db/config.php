<?php
$master_servername = "localhost";
$master_username = "u743628887_zyntro_master";
$master_password = "Zyntro!@#2025";
$master_dbname = "u743628887_zyntro_master";

$conn = mysqli_connect($master_servername, $master_username, $master_password, $master_dbname);

if (!$conn) {
    die("Master Database Connection Failed: " . mysqli_connect_error());
}
?>

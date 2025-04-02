<?php
session_start();

if (!isset($_SESSION['user_id'])) {
	header("Location: ../index.php");
	exit();
}

$user_id = $_SESSION['user_id'];

include 'db/config.php';

$query = "SELECT state_id FROM super_distributor WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$state_id = $row['state_id'];

?>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title>Dashboard | Distributor</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- App favicon -->
	<link rel="shortcut icon" href="assets/images/favicon.ico">

	<!-- Daterangepicker css -->
	<link rel="stylesheet" href="assets/vendor/daterangepicker/daterangepicker.css">

	<!-- Vector Map css -->
	<link rel="stylesheet" href="assets/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css">

	<!-- Theme Config Js -->
	<script src="assets/js/config.js"></script>

	<!-- App css -->
	<link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

	<!-- Icons css -->
	<link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

	<style>
		.table th {
			white-space: nowrap;
		}

		.custom-table {
			border-collapse: collapse;
			width: 100%;
			background-color: #ffffff;
			box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
			overflow: hidden;
		}

		.custom-table thead {
			background-color: #343a40;
			color: #ffffff;
			text-align: center;
		}

		.custom-table th {
			padding: 12px;
			white-space: nowrap;
		}

		.custom-table td {
			padding: 20px;
			border-bottom: 1px solid #ddd;
			white-space: nowrap;
			position: relative;
		}

		.custom-table tbody tr:nth-child(even) {
			background-color: #f8f9fa;
		}

		.custom-table tbody tr:hover {
			background-color: #f1f1f1;
			transition: 0.3s;
		}

		.table-container {
			overflow-x: auto;
			padding: 10px;
			/* Space around the table */
			position: relative;
			margin-bottom: 10px;
			/* Creates space below table */
		}

		.table td {
			white-space: nowrap;
		}

		/* Status Button */
		.status-btn {
			border: none;
			padding: 6px 15px;
			font-size: 14px;
			border-radius: 20px;
			cursor: pointer;
			color: white;
		}

		.active-status {
			background: #28a745;
		}

		.inactive-status {
			background: #dc3545;
		}

		/* Icon Buttons */
		.icon-btn {
			background-color: transparent;
			border: none;
			cursor: pointer;
			padding: 5px;
			color: #007bff;
			font-size: 18px;
		}

		.icon-btn:hover {
			color: #0056b3;
			background-color: #f1f1f1;
			border-radius: 5px;
		}

		/* Editable Balance */
		.editable-balance {
			cursor: pointer;
			color: #007bff;
			font-weight: bold;
		}

		.editable-balance:focus {
			border: 1px solid #007bff;
			outline: none;
		}

		.editable-balance-input {
			width: 100px;
			border: 1px solid #007bff;
			padding: 5px;
			border-radius: 5px;
		}

		/* Custom scrollbar for modern look */
		.table-container::-webkit-scrollbar {
			width: 8px;
			/* Thin scrollbar */
			height: 8px;
		}

		.table-container::-webkit-scrollbar-track {
			background: #f1f1f1;
			border-radius: 5px;
			margin: 5px;
			/* Creates space between scrollbar and content */
		}

		.table-container::-webkit-scrollbar-thumb {
			background: #888;
			border-radius: 5px;
		}

		.table-container::-webkit-scrollbar-thumb:hover {
			background: #555;
		}
	</style>
</head>

<body>
	<!-- Begin page -->
	<div class="wrapper">
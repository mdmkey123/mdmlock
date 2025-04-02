<?php
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $requestId= $_POST['id'];
    $approverId= $_POST['user_id'];
    $approverRole= $_POST['user_role'];
    
    $updateQuery= mysqli_query($conn,"UPDATE activation_requests SET status='Approved', approved_by='$approverRole', approver='$approverId' WHERE request_id='$requestId'");
    
    if($updateQuery){
        $deviceQuery=mysqli_query($conn,"SELECT customers.device_id FROM activation_requests INNER JOIN customers ON activation_requests.customer_id=customers.id WHERE activation_requests.request_id='$requestId'");
        $device= $deviceQuery->fetch_assoc();
        $deviceId=$device['device_id'];
        mysqli_query($conn,"UPDATE devices SET status='active' WHERE id='$deviceId'");
        echo json_encode(["status"=>"success","message"=>"Device Activation Approved Successfully.","data"=>$device]);
    }else{
        echo json_encode(["status"=>"error","message"=>"failed in approving this device, Pls contact your technical support."]);
    }
}
?>
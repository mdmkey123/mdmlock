<?php
include '../config.php';

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Content-Type: application/json');
     header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
     
     if ($_SERVER['REQUEST_METHOD'] =='POST'){
         $data= $_POST;
             if (!empty($data)){
                 
                 $table = "settings";
                 foreach ($data as $column => $value) {
                        // Escape single quotes and set values directly in the query
                        $setClause .= "$column = '$value', ";
                    }
                 $setClause = rtrim($setClause, ", "); 
                 $sql = "UPDATE $table SET $setClause WHERE id = '1'";    
                 
                 $updateQuery= mysqli_query($conn,$sql);
                 if($updateQuery){
                     echo json_encode(["status"=>"success","message"=>"Settings updated successfully."]);
                 }else{
                     echo json_encode(["status"=>"error","message"=>"failed to update your entered data, Pls contact your technical support."]);
                 }
             }else {
                echo json_encode(["status" => "error", "message" => "No data provided"]);
                }
         
     }else{
         echo json_encode(array('message' => 'Invalid request method', 'status' => "error"));
     }
?>     
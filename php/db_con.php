<?php
$db_conx = mysqli_connect("localhost", "root", "root", "smart_meter_reading","8888");
// Evaluate the connection
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
} 
?>
<?php
$db_conx = mysqli_connect("localhost:8888", "root", "root", "change36_wop");
// Evaluate the connection
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
} 
?>
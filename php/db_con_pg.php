<?php
$servername='localhost';
$user = 'root';  
$pswd = 'root';  
$db = 'smart_meter_reading';
$port ='8888';

/* set out document type to text/javascript instead of text/html */
header("Content-type: text/javascript");

/* our multidimentional php array to pass back to javascript via ajax */
// $con = "host=localhost port=5432 dbname='change36_energy00' user=change36_pguser001 password=pgaogMa84#aig01g";
//        $db = pg_connect($con) or die('connection failed');
$conn = mysqli_connect($servername, $user, $pswd, $db, $port); 
if($conn->connect_error){
    die("Connection failed: ". $conn->connect_error);
    echo 'fail to connect.';
} 
else 
{
    echo "successful." ;
}

        //echo 'Connected to: ', pg_dbname($db);
?>
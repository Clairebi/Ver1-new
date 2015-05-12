<?php

echo "start"; 
//session_start();
/* set out document type to text/javascript instead of text/html */
// header("Content-type: text/javascript");

/* our multidimentional php array to pass back to javascript via ajax */
//$con = "host=localhost:8888  dbname='smart_meter_reading' user=root password=root";
//        $db = pg_connect($con) or die('connection failed');



//$servername='localhost:8888'
$user = 'root';  
$pswd = 'root';  
$db = 'smart_meter_reading';  

$conn = mysql_connect($servername, $user, $pswd);  
mysql_select_db($db, $conn);

/*$conn = new mysqli("localhost:8888", $user, $pswd, $db); 
if($conn->connect_error){
    die("Connection failed: ". $conn->connect_error);
    echo 'fail to connect.';
} 
else 
{
    echo "successful" ;
}
*/

/*
     //   echo 'Connected to: ';
        
        $u = $_SESSION["username"];
        
        //test **********************
        $u='calplug'; //test only
        
        //echo $u;

        $u_daily = $u."_daily";
        $query = "SELECT time_day, daily_sum AS sum, daily_budget AS budget, daily_cost AS cost FROM $u_daily ORDER BY time_day DESC LIMIT 1";
        //$result1 = pg_query($query);
        $result = mysql_query($query);
        //$daily_row = pg_fetch_array($result1, 0, PGSQL_ASSOC);
        $daily_row = mysql_fetch_array($result1, 0, PGSQL_ASSOC);
        
        $u_cycle = $u."_cycle";
        $query = "SELECT cycle_start, cycle_sum AS sum, cycle_budget AS budget, cycle_cost AS cost FROM $u_cycle ORDER BY cycle_start DESC LIMIT 1";
        $result2 = pg_query($query);
        $cycle_row = pg_fetch_array($result2, 0, PGSQL_ASSOC);

        $u_week = $u."_week";
        $query = "SELECT weekday_sum AS weekdays, weekend_sum AS weekends FROM $u_week ORDER BY week_start DESC LIMIT 8";
        $result3 = pg_query($query);
        $week_row = pg_fetch_all($result3);
   
        $u_hour = $u."_hour";
        $query = "SELECT hourly_sum AS sum, hour_budget AS budget FROM $u_hour ORDER BY time_hour DESC LIMIT 6";
        $result4 = pg_query($query);
        $hour_row = pg_fetch_all($result4);
     
        $umeter = $u."meter";
        $query = "SELECT timestamp, delta_conspt AS conspt, currentpower FROM $umeter ORDER BY timestamp DESC LIMIT 1";
        $result5 = pg_query($query);
        $power_row = pg_fetch_array($result5, 0, PGSQL_ASSOC);
       
        //$result = array_merge($daily_row, $cycle_row, $hour_row, $power_row);
        
        
 $arr = array ($daily_row, $cycle_row, $week_row, $hour_row, $power_row);       

echo json_encode($arr);
*/
?>
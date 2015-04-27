<?php
include_once("db_con_pg.php");
$query = "SELECT username, init_conspt AS conspt, cycle_endpoint AS ce FROM users WHERE username = '$u'";
$result = pg_query($query);
$user_row = pg_fetch_array($result, 0, PGSQL_NUM);
//echo $user_row[0]; echo " "; echo $user_row[1]; echo " "; echo $user_row[2];

$init = "SELECT init('$user_row[0]', $user_row[1], date '$user_row[2]')";
$result11 = pg_query($init);
?>
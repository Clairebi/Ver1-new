<?php
/* set out document type to text/javascript instead of text/html */
header("Content-type: text/javascript");

/* our multidimentional php array to pass back to javascript via ajax */
$con = "host=localhost port=5432 dbname='change36_energy' user=change36_pguser001 password=pgaogMa84#aig01g";
        $db = pg_connect($con) or die('connection failed');
        echo 'Connected to: ', pg_dbname($db) .'<br />';
        
        
        $pgsql = "SELECT * FROM DeviceUser ";
$user_query = pg_query($db,$pgsql);
// Now make sure that user exists in the table
$numrows = pg_num_rows($user_query); 
pg_close($db);
if($numrows < 1){
	//echo "That user does not exist or is not yet activated, press back";
    echo "That user does not exist! press back";
    exit();	
}
echo $numrows .' records were found in table device_component <br />';
?>


<html>
    <head>
        <meta charset="UTF-8">
      
        <title></title>
    </head>
    <body>
    Test the postgre sQL.
    </body>
</html>
      
<?php
/* set out document type to text/javascript instead of text/html */
header("Content-type: text/javascript");

/* our multidimentional php array to pass back to javascript via ajax */
$con = "host=localhost port=5432 dbname='change36_energy00' user=change36_pguser001 password=pgaogMa84#aig01g";
        $db = pg_connect($con) or die('connection failed');
        //echo 'Connected to: ', pg_dbname($db);
?>
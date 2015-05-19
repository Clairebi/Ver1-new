<?php
session_start();
include_once("db_con_pg.php");
// Files that inculde this file at the very top would NOT require 
// connection to database or session_start(), be careful.
// Initialize some vars
$user_ok = 0;
$log_id = "";
$log_username = "";
$log_password = "";//echo $user_ok; echo "!!!0";
//User Verify function
function evalLoggedUser($db,$id,$u,$p) {
	//$sql = "SELECT ip FROM users WHERE id='$id' AND username='$u' AND password='$p' AND activated='1' LIMIT 1";
    $mysql = "SELECT ip FROM users WHERE id='$id' AND username='$u' AND password='$p' ";
    $query = mysqli_query($mysql);
    $numrows = mysqli_num_rows($query); 

    echo $numrows;
	if($numrows > 0){
		return true;
	}
}
if(isset($_SESSION["userid"]) && isset($_SESSION["username"]) && isset($_SESSION["password"])) {
	$log_id = preg_replace('#[^0-9]#', '', $_SESSION['userid']);
	$log_username = preg_replace('#[^a-z0-9]#i', '', $_SESSION['username']);
	$log_password = preg_replace('#[^a-z0-9]#i', '', $_SESSION['password']);
	// Verify the user
	$user_ok = evalLoggedUser($db,$log_id,$log_username,$log_password);
    //$user_ok = 1;
} else if(isset($_COOKIE["id"]) && isset($_COOKIE["user"]) && isset($_COOKIE["pass"])){
	$_SESSION['userid'] = preg_replace('#[^0-9]#', '', $_COOKIE['id']);
    $_SESSION['username'] = preg_replace('#[^a-z0-9]#i', '', $_COOKIE['user']);
    $_SESSION['password'] = preg_replace('#[^a-z0-9]#i', '', $_COOKIE['pass']);
	$log_id = $_SESSION['userid'];
	$log_username = $_SESSION['username'];
	$log_password = $_SESSION['password'];
	// Verify the user
	$user_ok = evalLoggedUser($db,$log_id,$log_username,$log_password);
    //$user_ok = 1;
	if($user_ok == true){
		// Update their lastlogin datetime field
		$mysql = "UPDATE users SET lastlogin=now() WHERE id='$log_id'";
        $query = mysqli_query($mysql);
	}
}
//echo $user_ok;echo "!!!";
$u = "";
$isOwner = "no";

// Make sure the _GET username is set, and sanitize it
if(isset($_GET["u"])){
	$u = preg_replace('#[^a-z0-9]#i', '', $_GET['u']);
    //echo $u;
} else {
    header("location: http://www.calit2.uci.edu/");
    exit();	
}
// Select the member from the users table
//$sql = "SELECT * FROM users WHERE username='$u' AND activated='1' LIMIT 1";
$mysql = "SELECT * FROM users WHERE username='$u'";
$user_query = mysqli_query($mysql);
// Now make sure that user exists in the table
$numrows = mysqli_num_rows($user_query); 
if($numrows < 1){
	//echo "That user does not exist or is not yet activated, press back";
    echo "That user does not exist! press back";
    exit();	
}
// Check to see if the viewer is the account owner

if($u == $log_username && $user_ok == true){
	$isOwner = "yes";
}

if($isOwner == "no"){
    header("location: http://www.calit2.uci.edu/");
    exit();
} else {
    header("location: ../index.php?u=".$_SESSION["username"]);
}
?>
<?php
session_start();
// If user is logged in, header them away
if(isset($_SESSION["username"])){
	header("location: index.php?u=".$_SESSION["username"]);
    exit();
}
?><?php
// Ajax calls this NAME CHECK code to execute
if(isset($_POST["usernamecheck"])){
	include_once("php/db_con_pg.php");
	$username = preg_replace('#[^a-z0-9]#i', '', $_POST['usernamecheck']);
	$pgsql = "SELECT id FROM users WHERE username='$username' LIMIT 1";
    $query = pg_query($pgsql); 
    $uname_check = pg_num_rows($query);
    if (strlen($username) < 3 || strlen($username) > 16) {
	    echo '<strong style="color:#F00;">3 - 16 characters please</strong>';
	    exit();
    }
	if (is_numeric($username[0])) {
	    echo '<strong style="color:#F00;">Usernames must begin with a letter</strong>';
	    exit();
    }
    if ($uname_check < 1) {
	    echo '<strong style="color:#009900;">' . $username . ' is OK</strong>';
	    exit();
    } else {
	    echo '<strong style="color:#F00;">' . $username . ' is taken</strong>';
	    exit();
    }
}
?><?php
// Ajax calls this REGISTRATION code to execute
if(isset($_POST["u"])){
	// CONNECT TO THE DATABASE
	include_once("php/db_con_pg.php");
	// GATHER THE POSTED DATA INTO LOCAL VARIABLES
	$u = preg_replace('#[^a-z0-9]#i', '', $_POST['u']);
	$e = pg_escape_string($_POST['e']);
	$p = $_POST['p'];
	$addr = $_POST['addr'];
    //$addr = preg_replace('#[^a-z0-9]#', '', $_POST['addr']);
    $city = preg_replace('#[^a-z ]#i', '', $_POST['city']);
    $s = preg_replace('#[^a-z ]#i', '', $_POST['s']);
	$c = preg_replace('#[^a-z ]#i', '', $_POST['c']);
    
    $conspt = preg_replace('#[^0-9 ]#i', '', $_POST['conspt']);
	
    $ce = preg_replace('#[a-z ]#i', ' ', $_POST['ce']);
	// GET USER IP ADDRESS
    $ip = preg_replace('#[^0-9.]#', '', getenv('REMOTE_ADDR'));
	// DUPLICATE DATA CHECKS FOR USERNAME AND EMAIL
	$pgsql = "SELECT id FROM users WHERE username='$u' LIMIT 1";
    $query = pg_query($pgsql); 
	$u_check = pg_num_rows($query);
	// -------------------------------------------
	$pgsql = "SELECT id FROM users WHERE email='$e' LIMIT 1";
    $query = pg_query($pgsql); 
	$e_check = pg_num_rows($query);
	// FORM DATA ERROR HANDLING
	if($u == "" || $e == "" || $p == "" || $addr == "" || $city == "" || $s == "" || $c == "" || $conspt == "" || $ce == ""){
		echo $p;echo $addr;echo $city;echo $s;echo $c;echo $conspt;echo $ce;
        echo "The form submission is missing values.";
        exit();
	} else if ($u_check > 0){ 
        echo "The username you entered is alreay taken";
        exit();
	} else if ($e_check > 0){ 
        echo "That email address is already in use in the system";
        exit();
	} else if (strlen($u) < 3 || strlen($u) > 16) {
        echo "Username must be between 3 and 16 characters";
        exit(); 
    } else if (is_numeric($u[0])) {
        echo 'Username cannot begin with a number';
        exit();
    } else {
	// END FORM DATA ERROR HANDLING
	    // Begin Insertion of data into the database
		// Hash the password and apply your own mysterious unique salt
		$cryptpass = crypt($p);
		//include_once ("php/randStrGen.php");
		$p_hash = md5($p);
		// Add user info into the database table for the main site table
		$pgsql = "INSERT INTO users (username, email, password, address1, city, state, country, init_conspt, cycle_endpoint, ip, signup, lastlogin, notescheck)       
		        VALUES('$u','$e','$p_hash','$addr','$city','$s','$c','$conspt','$ce','$ip',now(),now(),now())";
		$query = pg_query($pgsql); 
		//$uid = mysqli_insert_id($db_conx);
		// Establish their row in the useroptions table
		//$sql = "INSERT INTO useroptions (id, username, background) VALUES ('$uid','$u','original')";
		//$query = mysqli_query($db_conx, $sql);
        
        // Create this new user's tables
        include_once ("php/create_pg.php");
		// Create directory(folder) to hold each user's files(pics, MP3s, etc.)
		if (!file_exists("user/$u")) {
			mkdir("user/$u", 0755);
		}
		// Email the user their activation link
/*		$to = "$e";							 
		$from = "zhentao1989@gmail.com";
		$subject = 'yoursitename Account Activation';
		$message = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>yoursitename Message</title></head><body style="margin:0px; font-family:Tahoma, Geneva, sans-serif;"><div style="padding:10px; background:#333; font-size:24px; color:#CCC;"><a href="http://www.yoursitename.com"><img src="http://www.yoursitename.com/images/logo.png" width="36" height="30" alt="yoursitename" style="border:none; float:left;"></a>yoursitename Account Activation</div><div style="padding:24px; font-size:17px;">Hello '.$u.',<br /><br />Click the link below to activate your account when ready:<br /><br /><a href="http://www.yoursitename.com/activation.php?id='.$uid.'&u='.$u.'&e='.$e.'&p='.$p_hash.'">Click here to activate your account now</a><br /><br />Login after successful activation using your:<br />* E-mail Address: <b>'.$e.'</b></div></body></html>';
		$headers = "From: $from\n";
        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\n";
		mail($to, $subject, $message, $headers);*/
		echo "signup_success";
        include_once ("php/init.php");
		exit();
	}
	exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Sign Up</title>
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="style/style.css">
<style type="text/css">
#signupform{
	margin-top:24px;	
}
#signupform > div {
	margin-top: 12px;	
}
#signupform > input,select {
	width: 200px;
	padding: 3px;
	background: #F3F9DD;
}
#signupbtn, #login {
	font-size:18px;
	padding: 12px;
}
#terms {
	border:#CCC 1px solid;
	background: #F5F5F5;
	padding: 12px;
}
</style>
<script src="js/main.js"></script>
<script src="js/ajax.js"></script>
<script>
function restrict(elem){
	var tf = _(elem);
	var rx = new RegExp;
	if(elem == "email"){
		rx = /[' "]/gi;
	} else if(elem == "username"){
		rx = /[^a-z0-9]/gi;
	}
	tf.value = tf.value.replace(rx, "");
}
function emptyElement(x){
	_(x).innerHTML = "";
}
function checkusername(){
	var u = _("username").value;
	if(u != ""){
		_("unamestatus").innerHTML = 'checking ...';
		var ajax = ajaxObj("POST", "signup.php");
        ajax.onreadystatechange = function() {
	        if(ajaxReturn(ajax) == true) {
	            _("unamestatus").innerHTML = ajax.responseText;
	        }
        }
        ajax.send("usernamecheck="+u);
	}
}
function signup(){
	var u = _("username").value;
	var e = _("email").value;
	var p1 = _("pass1").value;
	var p2 = _("pass2").value;
    var addr1 = _("addr1").value;
    var addr2 = _("addr2").value;
    var city = _("city").value;
    var s = _("state").value;
	var c = _("country").value;
    
    var conspt = _("init_conspt").value;
    var ce = _("cycle_endpoint").value;
	
	var status = _("status");
	if(u == "" || e == "" || p1 == "" || p2 == "" || addr1 == "" || city == "" || s=="" || c=="" || conspt=="" || ce==""){
		status.innerHTML = "Fill out all of the form data";
	} else if(p1 != p2){
		status.innerHTML = "Your password fields do not match";
	} else if( _("terms").style.display == "none"){
		status.innerHTML = "Please view the terms of use";
	} else {
		_("signupbtn").style.display = "none";
		status.innerHTML = 'please wait ...';
		var ajax = ajaxObj("POST", "signup.php");
        ajax.onreadystatechange = function() {
	        if(ajaxReturn(ajax) == true) {
	            if(ajax.responseText != "signup_success"){
					status.innerHTML = ajax.responseText;
					_("signupbtn").style.display = "block";
				} else {
					window.scrollTo(0,0);
					_("pageMiddle").innerHTML = "<h3>Congratulations "+u+", signup completed!  </h3><a href = 'login.php'>login here</a>";
				}
	        }
        }
        ajax.send("u="+u+"&e="+e+"&p="+p1+"&addr="+addr1+" "+addr2+"&city="+city+"&s="+s+"&c="+c+"&conspt="+conspt+"&ce="+ce+":00");
	}
}
function openTerms(){
	_("terms").style.display = "block";
	emptyElement("status");
}
/* function addEvents(){
	_("elemID").addEventListener("click", func, false);
}
window.onload = addEvents; */
</script>
</head>
<body>
<?php //include_once("template_pageTop.php"); ?>
<div id="pageMiddle">
  <h3>Sign Up Here</h3>
  <form name="signupform" id="signupform" onsubmit="return false;">
    <div>Username: </div>
    <input id="username" type="text" onblur="checkusername()" onkeyup="restrict('username')" maxlength="16">
    <span id="unamestatus"></span>
    <div>Email Address:</div>
    <input id="email" type="text" onfocus="emptyElement('status')" onkeyup="restrict('email')" maxlength="88">
    <div>Create Password:</div>
    <input id="pass1" type="password" onfocus="emptyElement('status')" maxlength="16">
    <div>Confirm Password:</div>
    <input id="pass2" type="password" onfocus="emptyElement('status')" maxlength="16">
    
    <div>Street Address:</div>
    <input id="addr1" type="text" onfocus="emptyElement('status')" maxlength="100">
    <div>Address line 2:</div>
    <input id="addr2" type="text" maxlength="100">
    <div>City:</div>
    <input id="city" type="text" onfocus="emptyElement('status')" maxlength="100">
    <div>State:</div>
    <select id="state" onfocus="emptyElement('status')">
        <option value="AL">Alabama</option>
        <option value="AK">Alaska</option>
        <option value="AZ">Arizona</option>
        <option value="AR">Arkansas</option>
        <option value="CA">California</option>
        <option value="CO">Colorado</option>
        <option value="CT">Connecticut</option>
        <option value="DE">Delaware</option>
        <option value="DC">District Of Columbia</option>
        <option value="FL">Florida</option>
        <option value="GA">Georgia</option>
        <option value="HI">Hawaii</option>
        <option value="ID">Idaho</option>
        <option value="IL">Illinois</option>
        <option value="IN">Indiana</option>
        <option value="IA">Iowa</option>
        <option value="KS">Kansas</option>
        <option value="KY">Kentucky</option>
        <option value="LA">Louisiana</option>
        <option value="ME">Maine</option>
        <option value="MD">Maryland</option>
        <option value="MA">Massachusetts</option>
        <option value="MI">Michigan</option>
        <option value="MN">Minnesota</option>
        <option value="MS">Mississippi</option>
        <option value="MO">Missouri</option>
        <option value="MT">Montana</option>
        <option value="NE">Nebraska</option>
        <option value="NV">Nevada</option>
        <option value="NH">New Hampshire</option>
        <option value="NJ">New Jersey</option>
        <option value="NM">New Mexico</option>
        <option value="NY">New York</option>
        <option value="NC">North Carolina</option>
        <option value="ND">North Dakota</option>
        <option value="OH">Ohio</option>
        <option value="OK">Oklahoma</option>
        <option value="OR">Oregon</option>
        <option value="PA">Pennsylvania</option>
        <option value="RI">Rhode Island</option>
        <option value="SC">South Carolina</option>
        <option value="SD">South Dakota</option>
        <option value="TN">Tennessee</option>
        <option value="TX">Texas</option>
        <option value="UT">Utah</option>
        <option value="VT">Vermont</option>
        <option value="VA">Virginia</option>
        <option value="WA">Washington</option>
        <option value="WV">West Virginia</option>
        <option value="WI">Wisconsin</option>
        <option value="WY">Wyoming</option>
    </select>
    <div>Country:</div>
    <input id="country" type="text" onfocus="emptyElement('status')">
      
    <div>Total Consumption So Far:</div>
    <input id="init_conspt" type="text" onfocus="emptyElement('status')" maxlength="50">
    <div>Billing Cycle Starts On:</div>
    <input id="cycle_endpoint" type="datetime-local" onfocus="emptyElement('status')" maxlength="50">  
    <!--<select id="country" onfocus="emptyElement('status')">
      <?php //include_once("template_country_list.php"); ?>
    </select>-->
    <div>
      <a href="#" onclick="return false" onmousedown="openTerms()">
        View the Terms Of Use
      </a>
    </div>
    <div id="terms" style="display:none;">
      <h3>Web Intersect Terms Of Use</h3>
      <p>1. Play nice here.</p>
      <p>2. Take a bath before you visit.</p>
      <p>3. Brush your teeth before bed.</p>
    </div>
    <br /><br />
    <button id="signupbtn" onclick="signup()">Create Account</button>
    <button id="login" onclick="location.href='login.php'">Log In</button>
    <span id="status"></span>
  </form>
</div>
<?php //include_once("template_pageBottom.php"); ?>
</body>
</html>

<?php
error_reporting(E_ALL);
$user = strtoupper($_GET['recovername']);

$email = "";

if(isset($_SERVER['SERVER_NAME'])) { 
	if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false){ 
		$mysqli = new mysqli("ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com", "user", "password", "ntrader") or exit("Error connecting to database");     
	} else { 
		$mysqli = new mysqli("localhost", "root", null, "ntrader");     
	} 
}
//Obtain email and validation
$stmt = $mysqli->prepare("SELECT Email from `m0003_user_desc` where UCASE(username)=?");
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->bind_result($emaildata);
while ($stmt->fetch()){
	$email = $emaildata;
}
$stmt->close();

if($email == ""){
	echo "FAIL1";
	exit;
}

//initiate request   
$stmt = $mysqli->prepare("SHOW TABLE STATUS LIKE 'm0003_pass_recover'");
$stmt->execute();
$stmt->bind_result($col1, $col2, $col3, $col4, $col5, $col6, $col7, $col8, $col9, $col10, $col11, $col12, $col13, $col14, $col15, $col16, $col17, $col18);
while ($stmt->fetch()){
	$refnum = hash("sha512", $col11); 
}
$stmt->close();
echo "PASS1<br/>";

//submit request
$result = $mysqli->query("INSERT into `m0003_pass_recover` VALUES ('', '$user', '0','$refnum');");
if($result){
	echo "PASS2";
	//send mail
	$message = 'Click link to recover password: <br/> http://ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com/chgpass.php?Ref='.$refnum;
	
	$headers .= "Reply-To: nTrader Administrator <Admin@nTrader.com>\r\n";
	$headers .= "Return-Path: nTrader Administrator <Admin@nTrader.com>\r\n";
	$headers .= "From: nTrader Administrator <Admin@nTrader.com>\r\n";
	$headers .= "Organization: nTrader\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "X-Priority: 3\r\n";
	$headers .= "X-Mailer: PHP". phpversion() ."\r\n";
	
	
	mail($email, 'nTrader Password Recovery', $message, $headers,'-f admin@ntrader.com');
	echo $email;
	echo "SENT";
}else{
	echo $user;
	echo $refnum;
}

$mysqli->close();

?>
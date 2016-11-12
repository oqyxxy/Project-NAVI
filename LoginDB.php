<?php 

$check = false;

$source = $_GET['sourcedata'];
$userid = $_GET['useriddata'];
$password = $_GET['passdata'];
$password = hash("sha512", $password);

if(isset($_SERVER['SERVER_NAME'])) { 
// we assume if url does not contain "localhost", it must be your 0fees or zymic database connection 
	if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false){ 
		$mysqli = new mysqli("ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com", "user", "password", "ntrader") or exit("Error connecting to database");     
	} else { 
		// must be localhost database connection 
		$mysqli = new mysqli("localhost", "root", null, "ntrader");     
	} 
}   
$stmt = $mysqli->prepare("select UCASE(username), password, Name from `m0003_user_desc`");
$stmt->execute();
$stmt->bind_result($userdata, $passworddata, $namedata);
while ($stmt->fetch()){
	if(strtoupper($userid) == $userdata){
		if($password == $passworddata){
			$nameid = $namedata;
			$check = true;
		}
	}
}
$stmt->close();
$mysqli->close();

if($check == true){
	echo "LoginTransfer.php?username=".$userid."&realname=".$nameid."&sourcedata=".$source;
}
?>
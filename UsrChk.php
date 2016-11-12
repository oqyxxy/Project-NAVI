<?php

	$user = strtoupper($_GET['username']);
	$chker = false;

	if(isset($_SERVER['SERVER_NAME'])) { 
	// we assume if url does not contain "localhost", it must be your 0fees or zymic database connection 
		if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false){ 
			$mysqli = new mysqli("ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com", "user", "password", "ntrader") or exit("Error connecting to database");     
		} else { 
			// must be localhost database connection 
			$mysqli = new mysqli("localhost", "root", null, "ntrader");     
		} 
	}   
	$stmt = $mysqli->prepare("select UCASE(username)from `m0003_user_desc` where UCASE(username)=?");
	$stmt->bind_param("s", $user);
	$stmt->execute();
	$stmt->bind_result($userdata);
	while ($stmt->fetch()){
		if ($userdata != ""){
			$chker = true;
		}
	}
	$stmt->close();
	$mysqli->close();
	
	if ($chker == true) {
		echo "PASS";
	}
?>
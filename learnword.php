<?php

SESSION_START();

$username = $_SESSION['userid'];
$word = $_GET['worddata'];

if($username != "" && $word != ""){
	if(isset($_SERVER['SERVER_NAME'])) {
	// we assume if url does not contain "localhost", it must be your 0fees or zymic database connection
		if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false){
			$mysqli = new mysqli("ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com", "user", "password", "ntrader") or exit("Error connecting to database");
		} else {
			// must be localhost database connection
			$mysqli = new mysqli("localhost", "root", null, "ntrader");
		}
	}
	$result = $mysqli->query("CALL `P004_LEARN_WORD`('$username', '$word')");
	
	if ($result){
		echo "'".$word."' added to your word learner!";
	}else{
		echo 'Error!';
		//printf("Error: %s\n", $mysqli->error);
	}
}else{
	echo 'Fail to start!';
}

?>
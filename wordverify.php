<?php

$wordpart = $_GET['wordpart'];

if($wordpart == ""){
	exit;	
}

if(isset($_SERVER['SERVER_NAME'])) { 
// we assume if url does not contain "localhost", it must be your 0fees or zymic database connection 
	if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false){ 
		$mysqli = new mysqli("ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com", "user", "password", "ntrader") or exit("Error connecting to database");     
	} else { 
		// must be localhost database connection 
		$mysqli = new mysqli("localhost", "root", null, "ntrader");
	} 
}

$wordexist = false;
$stmt = $mysqli->prepare("SELECT c.WID FROM `m0002_financial_lexicon` a LEFT JOIN `m0002_headword_relation` b ON a.Headword = b.Headword LEFT JOIN `m0002_financial_lexicon` c ON b.Relation = c.Headword WHERE UCASE(a.Headword)=?");
$stmt->bind_param("s", $wordpart);
$stmt->execute();
$stmt->bind_result($WIDdata);
while ($stmt->fetch()){
	$wordexist = true;
	if(!is_null($WIDdata)){
		$WIDexception = $WIDexception.",".$WIDdata;
	}
}

if($wordexist){
	echo "unhide1";
}else{
	echo "unhide2";
}

$stmt->close();
$mysqli->close();

?>
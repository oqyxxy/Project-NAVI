<?php

if(isset($_POST['UserID'])){
	$username = trim($_POST['UserID']);
	if(empty($username)||strlen($username)<6){
	    echo "BAD NRIC or data! Please try again.";
	    exit();
	}
}
if(isset($_POST['registerpassword'])){
	$password = trim($_POST['registerpassword']);
	if(empty($password)||strlen($password)<8){
	    echo "BAD NRIC or data! Please try again.";
	    exit();
	}
	$password = hash("sha512", $password);
}
if(isset($_POST['name'])){
	$name = trim($_POST['name']);
	if(empty($name)){
	    echo "BAD NRIC or data! Please try again.";
	    exit();
	}
}
if(isset($_POST['email'])){
	$email = trim($_POST['email']);
	if(empty($email)){
	    echo "BAD NRIC or data! Please try again.";
	    exit();
	}
}
if(isset($_POST['contact'])){
	$contnum = trim($_POST['contact']);
	if(empty($contnum)){
	    echo "BAD NRIC or data! Please try again.";
	    exit();
	}
}
if(isset($_POST['nric'])){
	$nric = trim($_POST['nric']);
	if(empty($nric)){
	    echo "BAD NRIC or data! Please try again.";
	    exit();
	}
}
if(isset($_POST['address'])){
	$address = trim($_POST['address']);
	if(empty($address)){
	    echo "BAD NRIC or data! Please try again.";
	    exit();
	}
}
if(isset($_POST['postalcode'])){
	$postcode = trim($_POST['postalcode']);
	if(empty($postcode)){
	    echo "BAD NRIC or data! Please try again.";
	    exit();
	}
}
if(isset($_POST['gender'])){
	$gender = trim($_POST['gender']);
	if(empty($gender)){
	    echo "BAD NRIC or data! Please try again.";
	    exit();
	}
}
if(isset($_POST['dob'])){
	$DOB = trim($_POST['dob']);
	if(empty($DOB)){
	    echo "BAD NRIC or data! Please try again.";
	    exit();
	}
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
$result = $mysqli->query("CALL `P001_REGISTER_USER`('$username', '$password', '$name', '$email', '$contnum', '$nric', '$address', '$postcode', '$gender', '$DOB')");
//echo "CALL `P001_REGISTER_USER`('$username', '$password', '$name', '$email', '$contnum', '$nric', '$address', '$postcode', '$gender', '$DOB')";

//$stmt->bind_param("ssssississ", $username, $password, $name, $email, $contnum, $nric, $address, $postcode, $gender, $DOB);
//$result = $stmt->execute();
if ($result){
		echo '<script type="text/javascript">
    	alert("Thank you for registering.");
    	window.location="index.php";
		</script>';
}else{
	echo "BAD NRIC or data! Please try again.";
	//printf("Error: %s\n", $mysqli->error);
	//echo "CALL `P001_REGISTER_USER`('$username', '$password', '$name', '$email', '$contnum', '$nric', '$address', '$postcode', '$gender', '$DOB')";
	//echo "FAIL REGISTER!</br>";
	//echo $username."</br>";
	//echo $password."</br>";
	//echo $name."</br>";
	//echo $email."</br>";
	//echo $contnum."</br>";
	//echo $nric."</br>";
	//echo $address."</br>";
	//echo $postcode."</br>";
	//echo $gender."</br>";
	//echo $DOB."</br>";
}
//$stmt->close();
//$mysqli->close();

?>
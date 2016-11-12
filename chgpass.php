<?php

$reachbyPOST = isset($_POST['submit']);

if ($reachbyPOST == false){
	if(isset($_GET['Ref'])==false){
		exit;
	}
	$Refnum = $_GET['Ref'];
	if(isset($_SERVER['SERVER_NAME'])) { 
	// we assume if url does not contain "localhost", it must be your 0fees or zymic database connection 
		if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false){ 
			$mysqli = new mysqli("ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com", "user", "password", "ntrader") or exit("Error connecting to database");     
		} else { 
			// must be localhost database connection 
			$mysqli = new mysqli("localhost", "root", null, "ntrader");     
		} 
	}   
	$stmt = $mysqli->prepare("select Username from `m0003_pass_recover` where Ref_Num=? and status='0';");
	$stmt->bind_param("s", $Refnum);
	$stmt->execute();
	$stmt->bind_result($userdata);
	while ($stmt->fetch()){
		if($userdata != ""){
			$user = $userdata;
		}
	}
	$stmt->close();
	$mysqli->close();
	
	if(isset($user)){
		echo '<!DOCTYPE html>
		<html>
		<body>
		
		<form action="chgpass.php" method="post">
		<p>Please set a new password '.$user.'</p>
		<p>New Password: <input type="password" name="pass"/></p>
		<p>Confirm new Password: <input type="password" name="confirmpass"/></p>
		<input type="submit" name="submit" value="Change Password!"/>
		<input type="hidden" name="user" value="'.$user.'"/>
		<input type="hidden" name="refnum" value="'.$Refnum.'"/>
		</form>
		</body>
		</html>';
	}else{
		echo "BAD LINK!";
	}
}else{
	$pass = $_POST['pass'];
	$confirmpass = $_POST['confirmpass'];
	$user = $_POST['user'];
	$Refnum = $_POST['refnum'];
	
	if($pass != $confirmpass){
		$errmsg = "Your password does not match!";
	}
	if(strlen($pass) < 8){
		$errmsg = "Password must be at least 8 characters long!";
	}
	if ($errmsg != ""){
		echo '<!DOCTYPE html>
		<html>
		<body>
		<p><font color="red">'.$errmsg.'</font></p>
		<form action="chgpass.php" method="post">
		<p>Please set a new password '.$user.'</p>
		<p>New Password: <input type="password" name="pass"/></p>
		<p>Confirm new Password: <input type="password" name="confirmpass"/></p>
		<input type="submit" name="submit" value="Change Password!"/>
		<input type="hidden" name="user" value="'.$user.'"/>
		<input type="hidden" name="refnum" value="'.$Refnum.'"/>
		</form>
		</body>
		</html>';
	}else{
		$pass = hash("sha512", $pass); 
		
		
		if(isset($_SERVER['SERVER_NAME'])) { 
		// we assume if url does not contain "localhost", it must be your 0fees or zymic database connection 
			if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false){ 
				$mysqli = new mysqli("ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com", "user", "password", "ntrader") or exit("Error connecting to database");     
			} else { 
				// must be localhost database connection 
				$mysqli = new mysqli("localhost", "root", null, "ntrader");     
			} 
		}   
		$result = $mysqli->query("CALL `P003_CHANGE_PASSWORD`('$user','$pass','$Refnum')");
		if ($result){
				echo '<script type="text/javascript">
					    	alert("Password change request completed! Your password has been successfuly changed.");
					    	window.location="http://ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com/"
						</script>';
			//header("Location:http://ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com/");
		}else{
			printf("Error: %s\n", $mysqli->error);
			echo "CALL ` P003_CHANGE_PASSWORD `('$user','$pass','$Refnum')";
			echo $user."</br>";
			echo $pass."</br>";
			echo $Refnum."</br>";
		}
	}
}
?>

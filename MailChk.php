<?php

/**
Validate an email address.
Provide email address (raw input)
Returns true if the email address has the email 
address format and the domain exists.
*/
function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if
(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}





	$mail = strtoupper($_GET['mail']);
	$pretest = validEmail($mail);
	if ($pretest == false){
		echo "FAIL";
		exit;
	}
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
	$stmt = $mysqli->prepare("select UCASE(Email)from `m0003_user_desc` where UCASE(Email)=?");
	$stmt->bind_param("s", $mail);
	$stmt->execute();
	$stmt->bind_result($maildata);
	while ($stmt->fetch()){
		if ($maildata != ""){
			$chker = true;
		}
	}
	$stmt->close();
	$mysqli->close();
	
	if ($chker == true) {
		echo "PASS";
	}
?>
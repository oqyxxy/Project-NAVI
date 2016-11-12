<?php 
session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<!-- ==================== AJAX to loginDB.php ==================== -->
	<script>
	function DBvalidate(userinfo,passinfo)
	{
		var xmlhttp;
		var sourcedata = document.getElementById('sourceloc').value
		var url = "LoginDB.php?useriddata="+ userinfo +"&passdata=" + passinfo +"&sourcedata=" + sourcedata;

		if (userinfo == "" || passinfo == ""){
		  document.getElementById("DBvalResult").innerHTML= '<div id="Validation">Please complete both fields</div>';
		  return;
		}
		
		if (window.XMLHttpRequest)
		  {// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		  }
		else
		  {// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		  }
		  
		xmlhttp.onreadystatechange=function()
		  {
		  if (xmlhttp.readyState==4 && xmlhttp.status==200)
		    {
			var DBresult = xmlhttp.responseText;
		    if (DBresult != "")
				{
		    	window.location = DBresult
		    	}
		    else
			    {
		    	document.getElementById("DBvalResult").innerHTML = '<div id="Validation">Wrong Username or Password</div>';
		    	}	
		    }
		  }
		xmlhttp.open("GET",url,true);
		xmlhttp.send();
	}
	</script>

	<script type="text/javascript">
	function ResetLogin()
	{
		document.getElementById("username").value = "";
		document.getElementById("password").value = "";
		document.getElementById("DBvalResult").innerHTML = "";
	}
	</script>

	<title>Register</title>
	<meta http-equiv="description" content="" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="Educate yourself with nTrader's dynamic Jargon translation utility, 
and the dynamics of the financial market place."/>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" />
    	<script src="http://code.jquery.com/jquery-1.8.3.js"></script>
    	<script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
    	<link rel="stylesheet" href="CSS/standard.css" type="text/css" />
	<link rel="stylesheet" href="CSS/register.css" type="text/css" />
	<link rel="stylesheet" href="CSS/tableblock.css" type="text/css"/>
	<link rel="image_src" href="Images/Logo_42x42.png" />
	<link rel="shortcut icon" href="Images/browser.ico" />

</head>
<body onLoad="StartMask(); DisabledPersonalTB(); DisabledConfirmTB();">



<!-- ==================== Menu ==================== -->
<div id='cssmenu' style="position: relative;">
   <a href="index.php"><img id="logo" src="Images/Logo.png"/></a>
<ul>
   	<li><a class = "fNiv" href='index.php'><span>Home</span></a></li>
   	<li><a class = "fNiv" href='AboutUs.php'><span>About Us</span></a></li>
	<li><a class = "fNiv" href='products.php'><span>Our Products</span></a></li>
   	<?php
	if(isset($_SESSION['username'])){
		echo "<li><a class = 'fNiv' href='home.php'><span>Go to nConsole</span></a></li>";
	}
	if(!isset($_SESSION['username'])){
		echo "<li class='active'><a class = 'fNiv' href='register.php' onclick=".'"'."ResetLogin();document.getElementById('sourceloc').value='index'; Disappear()".'"'.";><span>Register</span></a></li>";
	}
	if(!isset($_SESSION['username'])){
		echo "<li><a class = 'login-window' href='#login-box' onclick=".'"'."ResetLogin();document.getElementById('sourceloc').value='homepage'; Disappear()".'"'.";><span>Login&nbsp;/&nbsp;nConsole</span></a></li>";
	}
	?>
</ul>
	<?php
	if(isset($_SESSION['username'])){

		echo	'<div id="Header_info">
			<table id ="Header"><tr><td id="HeaderCol" style="font-weight:bold; font-family:arial; text-align:right;">Welcome, '.$_SESSION['username'].'</td></tr>
			<tr><td style="text-align: right;">
			<div id="user-controls" style="float: right;">
			<a href="#" class="button icon AccSettings"><span>Settings</span></a>
			<a href="logout.php" class="button icon logout-btn"><span>Logout</span></a>
			</div>
			</td></tr></table>
			</div>';

	}
	?>
<input type="hidden" id="sourceloc" name="sourceloc" />
</div>


<!-- ==================== Login Popup (http://www.alessioatzeni.com/blog/login-box-modal-dialog-window-with-css-and-jquery/) ==================== -->
<div id="login-box" class="login-popup" id="login-popup">
        <a href="#" class="close"><img src="Images/Standard/close.png" class="btn_close" title="Close Window" alt="Close" /></a>
	<form method="post" class="signin" id="signin" action="">
		<b id="PopupTitle">Log In</b>
                <fieldset class="textbox">
                <div id='DBvalResult'></div>
            	<label class="username">
                <span id= "TBLabel">Username or email</span>
                <input id="username" name="username" value="" type="text" autocomplete="on" placeholder="Username">
                </label>
                <label class="password">
                <span id= "TBLabel">Password</span>
                <input id="password" name="password" value="" type="password" placeholder="Password">
                </label>
                <button class="submit button" type="button" onclick="DBvalidate(username.value, password.value)">Sign in</button>
                <p>
		<button  type="button" id="forget" onclick="ClearThis(); RemoveWarnning();">Forgot your password?</button>
		<br>
		<a href="register.php"><button  type="button" id="notmember" onclick="ClearThis(); RemoveWarnning();">Not yet a member?</button></a>
                </p>        
                </fieldset>
	</form>

	<form method="post" class="forgetpw" id="forgetpw" action="sendmail.php">
		<b id="PopupTitle">Forget Password</b>
                <fieldset class="textbox">
		<!-- Password reset instructions will be sent to the email address associated with your account.</br> -->
            	<label class="putusername">
                <span id= "TBLabel">Username</span>
                <input id="recovername" name="recovername" value="" type="text" autocomplete="on" placeholder="Username">
                </label>
                <button class="send button" type="button" onclick="sendmail(recovername.value);">Send</button>
		<p>
		<button type="button" id="forget" onclick="Disappear();">Back to login</button>
		</p>      
                </fieldset>
         </form>
</div>


<?php 
if(isset($_SESSION['username'])){
	echo '<script type="text/javascript">
    	alert("You are already registered!");
    	history.back();
	</script>';
}

?>

<!-- ==================== Register (Put Warning message in id="warning") ==================== -->
	<div id="All">
	<form id="wholeform" name="wholeform" method="post" action="regconfirm.php">
	<table id = "wholetable">
	<tr>
		<td id = "wholetabletd" ALIGN="center" VALIGN="TOP">
			<!-- ==================== Account Info ==================== -->
			<div class="table-foreground">
    				<img id= "accountmask" src="Images/Register/table-foreground.png" />
			</div>
			<table id = "accountinfo">
			<tr>
				<tr><td id = "Session" align="left" colspan="2" VALIGN="TOP"><div id="step"> 1.</div></br><div id="stepDesc"> Choose a username and password.</div><br/><br/></td></tr>				
			</tr>
			<tr><td colspan="2"><h5 id="UsrWarn" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td>Username: </td>
				<td><input type="text" name="UserID" id="UserID" onblur="ValUser(this.value)" title="Enter at least 6 characters "/></td>
				
			</tr>
			<tr><td colspan="2"><h5 id="MailWarn" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td>Email: </td>
				<td><input type="text" name="email" id="email" onblur="ValMail(this.value)" title="Enter your email address here"/></td>
				
			</tr>
			<tr><td colspan="2"><h5 id="PassWarn" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td>Password: </td>
						<td><input type="password" name="registerpassword" id="registerpassword" onblur="ValPass(this.value)" title="Enter at least 8 characters"/></td>
			</tr>
			<tr><td colspan="2"><h5 id="CPassWarn" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td>Confirmed Password: </td>
				<td><input type="password" name="confirmedpassword" id="confirmedpassword" disabled onblur="ValCPass(this.value,registerpassword.value)"  title="Retype your password"/></td>
			</tr>
			<tr>
				<td align="right" colspan="2" VALIGN="TOP"></br><button type="button" id="Val1button" onclick='setTimeout("ValStep1()",750);';>Next</button></td>
			</tr>
			<tr>
				<td id="OKLogo1" align="right" colspan="2" VALIGN="TOP" style="text-align:center;"></td>
			</tr>
			</table></br>
		</td>
		<td id = "wholetabletd" ALIGN="center" VALIGN="TOP">
			<!-- ==================== Personal Info ==================== -->
			<div class="table-foreground">
    				<img id= "personalmask" src="Images/Register/table-foreground.png" />
			</div>
			<table id = "personalinfo"> <!-- style = "display:none" --> 
			<tr>
				<tr><td id = "Session" align="left" colspan="2" VALIGN="TOP"><div id="step"> 2.</div></br><div id="stepDesc"> Enter your personal particulars</div></br><br/><br/></td></tr>
			</tr>
			<tr><td colspan="2"><h5 id="NameWarn" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td id = tbname>Name: </td>
				<td><input type="text" name="name" id="name" title="Enter your name"/></td>
			</tr>
			<tr><td colspan="2"><h5 id="NRICWarn" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td id = tbname>NRIC: </td>
				<td><input type="text" name="nric" id="nric" title="Retype your NRIC"/></td>
			</tr>
			<tr><td colspan="2"><h5 id="genderWarn" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td id = tbname>Gender: </td>
				<td>
				<select name="gender" id="gender" title="Enter your gender">
				  <option value=""></option>
				  <option value="M">Male</option>
				  <option value="F">Female</option>
				  <option value="U">Others</option>
				</select>
				</td>
			</tr>
			<tr><td colspan="2"><h5 id="dobWarn" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td id = tbname>Date of Birth: </td>
				<td><input type="text" readonly="readonly" name="dob" id="dob" placeholder="yyyy-mm-dd" title="Enter your date of birth"/></td>
			</tr>
			<tr><td colspan="2"><h5 id="AddressWarn" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td id = tbname>Address: </td>
				<td><input type="text" name="address" id="address" title="Enter your address"/></td>
			</tr>
			<tr><td colspan="2"><h5 id="PCWarn" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td id = tbname>Postal Code: </td>
				<td><input type="text" name="postalcode" id="postalcode" title="Enter above address's postal code"/></td>
			</tr>
			<tr><td colspan="2"><h5 id="CNumWarn" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td id = tbname>Contact Number: </td>
				<td><input type="text" name="contact" id="contact" title="Enter your contact number"/></td>
			</tr>
			<tr>
				<td VALIGN="TOP" align="left"></br><button type="button" onclick='MaskThis("personalmask"); UnMaskPrevious("accountmask"); UnMaskPrevious("OKLogo1");'>Back</button></td>
				<td VALIGN="TOP" align="right"></br><button  type="button" id="personalb" onclick='ValStep2();'>Next</button></td>
			</tr>
			<tr>
				<td id="OKLogo2" align="right" colspan="2" VALIGN="TOP" style="text-align:center;"></td>
			</tr>
			</table>
		</td>
		<td id = "wholetabletd" ALIGN="left" VALIGN="TOP">
			<!-- ==================== Confirmation ==================== -->
			<div class="table-foreground">
    				<img id= "confirmmask" src="Images/Register/table-foreground1.png" />
			</div>
			<table id = "confirm">  <!-- style = "display:none" --> 
			<tr>
				<tr><td id = "Session" align="left" colspan="2" VALIGN="TOP"><div id="step"> 3.</div></br><div id="stepDesc"> Confirm & Register.</div><br/><br/><br/></td></tr>	
			</tr>
			<tr><td colspan="2"><h5 id="warning" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td  colspan="2"  id = "tnc"><input type="checkbox" name="tnc" id="tncreal">&nbsp I have read the Terms and Conditions<br></td>
			</tr>
			<tr>
				<td VALIGN="TOP" align="left"></br><button type="button" onclick='MaskThis("confirmmask"); UnMaskPrevious("personalmask"); UnMaskPrevious("OKLogo2");'>Back</button></td>
				<td align="right" colspan="2" VALIGN="TOP"></br><button type="button" id="confirmb" onclick="ValStep3();">Register</button></td>
			</tr>
			<tr>
				<td id="OKLogo3" align="right" colspan="2" VALIGN="TOP" style="text-align:center;"></td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
	</form>
	</div>

<!-- ==================== DATE JQUERY ==================== -->
    <script>
    $(function() {
        $( "#dob" ).datepicker({
        	maxDate: "+0D",
        	defaultDate: "-20Y",
            showOn: "button",
            buttonImage: "Images/Standard/Menu/calendar.gif",
            buttonImageOnly: true,
            changeMonth: true,
            changeYear: true,
        });
        $( "#dob" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
    });
    </script>
    
<!-- ==================== Login Popup ==================== -->
	<script type="text/javascript">
  	$(document).ready(function() {
		$('a.login-window').click(function() {
		
                	//Getting the variable's value from a link 
			var loginBox = $(this).attr('href');

			//Fade in the Popup
			$(loginBox).fadeIn(300);
		
			//Set the center alignment padding + border see css style
			var popMargTop = ($(loginBox).height() + 24) / 2; 
			var popMargLeft = ($(loginBox).width() + 24) / 2; 
		
			$(loginBox).css({ 
				'margin-top' : -popMargTop,
				'margin-left' : -popMargLeft
			});
		
			// Add the mask to body
			$('body').append('<div id="mask"></div>');
			$('#mask').fadeIn(300);
		
			return false;
		});
	
		// When clicking on the button close or the mask layer the popup closed
		$('a.close, #mask').live('click', function() { 
	  		$('#mask , .login-popup').fadeOut(300 , function() {
				$('#mask').remove();  
			}); 
			return false;
		});
	});
	</script>

<!-- ==================== Login Popup 2 ==================== -->
	<script type="text/javascript">
		function Disappear()
		{	
			document.getElementById("signin").style.display = 'block';
			document.getElementById("forgetpw").style.display = 'none';
		}

		function ClearThis()
		{	
			document.getElementById("signin").style.display = 'none';
			document.getElementById("forgetpw").style.display = 'block';
		}
		
		function RemoveWarnning()
		{	
			document.getElementById("Validation").style.display = 'none';
		}
		
		function AddImage()
		{
  			document.getElementById("table-foreground").innerHTML = "<img id='myimage' src='Images/Register/table-foreground.png'>";
			document.getElementById("myimage").ondragstart = function() { return false; };
		}

		function sendmail(recovername){
			
        	$.get('sendmail.php?recovername='+recovername, {}, function(){
            	//successful ajax request
              	alert ('A password recovery request has been sent! Please check your mail.');
          	}).error(function(){
            	alert('error... ohh no!');
          	});
	    }

		function logoutnow(){
        	window.location = "http://ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com/logout.php"
        }
	</script>

<!-- ==================== JAVA (Register) ==================== -->
	<script>
		function AddOK(Cell)
		{
			document.getElementById(Cell).innerHTML = "<img src='Images/Register/OK.png'/>";
		}		

		function DisplayNext(NextSection)
		{	
			document.getElementById(NextSection).style.display = '';	
		}
		
		function AddImage()
		{
  			document.getElementById("table-foreground").innerHTML = "<img id='myimage' src='Images/Register/table-foreground.png'>";
			document.getElementById("myimage").ondragstart = function() { return false; };
		}
		
		function MaskThis(ThisSection)
		{
			document.getElementById(ThisSection).style.display = 'block';
		}
		
		function UnMaskPrevious(PreviousSection)
		{	
			document.getElementById(PreviousSection).style.display = 'none';
		}
		
		function StartMask()
		{
			document.getElementById("accountmask").style.display = 'none';
			document.getElementById("personalmask").ondragstart = function() { return false; };
			document.getElementById("confirmmask").ondragstart = function() { return false; };
		}
		
		function DisabledPersonalTB()
		{
			document.getElementById("name").disabled = true;
			document.getElementById("gender").disabled = true;
			document.getElementById("nric").disabled = true;
			document.getElementById("dob").disabled = true;
			document.getElementById("address").disabled = true;
			document.getElementById("postalcode").disabled = true;
			document.getElementById("contact").disabled = true;
			document.getElementById("personalb").disabled = true;
		}
		
		function EnabledPersonalTB()
		{
			document.getElementById("name").disabled = false;
			document.getElementById("gender").disabled = false;
			document.getElementById("nric").disabled = false;
			document.getElementById("dob").disabled = false;
			document.getElementById("address").disabled = false;
			document.getElementById("postalcode").disabled = false;
			document.getElementById("contact").disabled = false;
			document.getElementById("personalb").disabled = false;
		}

		function DisabledConfirmTB()
		{
			document.getElementById("tnc").disabled = true;
			document.getElementById("confirmb").disabled = true;
		}
		
		function EnabledConfirmTB()
		{
			document.getElementById("tnc").disabled = false;
			document.getElementById("confirmb").disabled = false;
		}

		function ValUser(userinfo)
		{
			document.getElementById("UsrWarn").innerHTML = "";
			var xmlhttp;
			var url = "UsrChk.php?username="+ userinfo;

			if (userinfo == ""){
			  return;
			}

			if (userinfo.length <= 5){
				document.getElementById("UsrWarn").innerHTML = "<div id='Validation'>Username must be at least 6 characters long!</div>";
				return;
			}
			
			if (window.XMLHttpRequest)
			  {// code for IE7+, Firefox, Chrome, Opera, Safari
			  xmlhttp=new XMLHttpRequest();
			  }
			else
			  {// code for IE6, IE5
			  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			  }
			  
			xmlhttp.onreadystatechange=function(){
				if (xmlhttp.readyState==4 && xmlhttp.status==200){
					var DBresult = xmlhttp.responseText;
			    	if (DBresult != ""){
			    		document.getElementById("UsrWarn").innerHTML = "<div id='Validation'>Username has been taken.</div>";
			    	}else{
			    		document.getElementById("UsrWarn").innerHTML = "";
			    	}	
				}
			}
			xmlhttp.open("GET",url,true);
			xmlhttp.send();
		}

		function ValMail(Mailinfo)
		{
			document.getElementById("MailWarn").innerHTML = "";
			var xmlhttp;
			var url = "MailChk.php?mail="+ Mailinfo;

			if (Mailinfo == ""){
				return;
			}
			
			if (window.XMLHttpRequest)
			  {// code for IE7+, Firefox, Chrome, Opera, Safari
			  xmlhttp=new XMLHttpRequest();
			  }
			else
			  {// code for IE6, IE5
			  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			  }
			  
			xmlhttp.onreadystatechange=function(){
				if (xmlhttp.readyState==4 && xmlhttp.status==200){
					var DBresult = xmlhttp.responseText;
			    	if (DBresult != ""){
				    	if (DBresult == "FAIL")
				    	{
				    		document.getElementById("MailWarn").innerHTML = "<div id='Validation'>This Email is not valid!</div>";
				    	}else if (DBresult == "PASS")
				    	{
				    		document.getElementById("MailWarn").innerHTML = "<div id='Validation'>This Email is already used by another user!</div>";
				    	}else
				    	{
				    		document.getElementById("MailWarn").innerHTML = "<div id='Validation'>Unexpected error, contact admin.</div>";
				    	}
			    	}else{
			    		document.getElementById("MailWarn").innerHTML = "";
			    	}	
				}
			}
			xmlhttp.open("GET",url,true);
			xmlhttp.send();
		}

		function ValPass(passinfo)
		{
			document.getElementById("confirmedpassword").setAttribute("disabled",true); 
			ValCPass(document.getElementById("confirmedpassword").value,passinfo)
			document.getElementById("PassWarn").innerHTML = "";
			if (passinfo == "")
			{
				return;
			}
			
			if (passinfo.length <= 7)
			{
				document.getElementById("PassWarn").innerHTML = "<div id='Validation'>Password must be at least 8 characters!</div>";
			}else
			{
				document.getElementById("confirmedpassword").removeAttribute("disabled"); 
			}
		}

		function ValCPass(Cpassinfo,passinfo)
		{	
			if (Cpassinfo == "")
			{
				document.getElementById("confirmedpassword").setAttribute("disabled",true); 
				document.getElementById("CPassWarn").innerHTML ="";
				return;
			}else{
				document.getElementById("confirmedpassword").removeAttribute("disabled"); 
			}
			
			document.getElementById("CPassWarn").innerHTML = "";
			if (Cpassinfo != passinfo)
			{
				document.getElementById("CPassWarn").innerHTML = "<div id='Validation'>Password does not match!</div>";
			}
		}

		function ValStep1()
		{				
			if(document.getElementById("UsrWarn").innerHTML == "" && document.getElementById("MailWarn").innerHTML == "" &&	document.getElementById("PassWarn").innerHTML == "" && document.getElementById("CPassWarn").innerHTML == "")
			{
				var chker = true;
				if(document.getElementById("UserID").value == "")
				{
					chker = false;
				}

				if(document.getElementById("email").value == "")
				{
					chker = false;
				}

				if(document.getElementById("registerpassword").value == "")
				{
					chker = false;
				}

				if(document.getElementById("confirmedpassword").value == "")
				{
					chker = false;
				}

				if(chker == true)
				{
					AddOK("OKLogo1");
					MaskThis("accountmask");
					document.getElementById('personalmask').style.display = 'none';
					EnabledPersonalTB();
				}else
				{
					alert("Please complete all fields before proceeding to the next step!");
				}
			}else
			{
				alert("There are existing errors in step 1!");
			}
		}

		function ValStep2()
		{
			var chker = true
			document.getElementById('NameWarn').innerHTML = "";
			document.getElementById('NRICWarn').innerHTML = "";
			document.getElementById('dobWarn').innerHTML = "";
			document.getElementById('AddressWarn').innerHTML = "";
			document.getElementById('PCWarn').innerHTML = "";
			document.getElementById('CNumWarn').innerHTML = "";
			document.getElementById('genderWarn').innerHTML = "";
			
			if (document.getElementById('name').value == "")
			{
				chker = false
				document.getElementById('NameWarn').innerHTML = "<div id='Validation'>Please specify your name.</div>";
			}

			if (document.getElementById('nric').value != "")
			{
				var nricval = document.getElementById('nric').value
				if (nricval.length != 9)
				{
					chker = false
					document.getElementById('NRICWarn').innerHTML = "<div id='Validation'>Invalid NRIC.</div>";
				}					
			}else
			{
				chker = false
				document.getElementById('NRICWarn').innerHTML = "<div id='Validation'>Please specify your NRIC.</div>";
			}

			if (document.getElementById('gender').value == "")
			{
				chker = false
				document.getElementById('genderWarn').innerHTML = "<div id='Validation'>Please specify your gender.</div>";
			}
			
			if (document.getElementById('dob').value == "")
			{
				chker = false
				document.getElementById('dobWarn').innerHTML = "<div id='Validation'>Please specify your date of birth.</div>";
			}
			
			if (document.getElementById('address').value == "")
			{
				chker = false
				document.getElementById('AddressWarn').innerHTML = "<div id='Validation'>Please specify your Address.</div>";
			}

			if (document.getElementById('postalcode').value != "")
			{
				var PCnumber = document.getElementById('postalcode').value;
				if (PCnumber.length != 6 || isNaN(PCnumber) == true)
				{
					chker = false
					document.getElementById('PCWarn').innerHTML = "<div id='Validation'>Invalid Postal Code.</div>";	
				}
			}else
			{
				chker = false
				document.getElementById('PCWarn').innerHTML = "<div id='Validation'>Please specify your postal code.</div>";
			}

			if (document.getElementById('contact').value != "")
			{
				var CNumber = document.getElementById('contact').value;
				if (CNumber.length != 8 || isNaN(CNumber) == true)
				{
					chker = false
					document.getElementById('CNumWarn').innerHTML = "<div id='Validation'>Invalid Contact Number.</div>";	
				}
			}else
			{
				chker = false
				document.getElementById('CNumWarn').innerHTML = "<div id='Validation'>Please specify your contact number.</div>";
			}

			if(chker == true)
			{
				AddOK("OKLogo2");
				MaskThis("personalmask");
				document.getElementById('confirmmask').style.display = 'none'; 
				EnabledConfirmTB();
			}else
			{
				alert("There are empty or invalid fields in Step 2!");
			}
		}
				
		function ValStep3()
		{
			if(document.getElementById('tncreal').checked) 
			{
				AddOK("OKLogo3");
				MaskThis("confirmmask");
				document.forms["wholeform"].submit();				
			}else
			{
				alert("Please read the terms and conditions before completing registration.");
			}
		}
		
	</script>
    <div class="nfooter">
	<div>
	<span style="float:left;">nTrader Console Project 2012. Temasek Polytechnic IIT School.</span>
	<span style="float: right;">This website is best viewed with Google Chrome</span>
	<span><a href="acknowledge.php">Acknowledgement</a></span> | <span><a href="contact-us.php">Contact us</a></span> | <span><a href="feedback.php">Feedback</a></span>
	</div>
    </div>
</body>
</html>

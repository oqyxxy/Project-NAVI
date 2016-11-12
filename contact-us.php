<?php 
session_start();

if(isset($_POST['fullname']) && isset($_POST['email']) && isset($_POST['subject']) && isset($_POST['content'])){
	$user = $_POST['fullname'];
	$mail = $_POST['email'];
	$subject = $_POST['subject'];
	$content = $_POST['content'];
	
	if(isset($_SERVER['SERVER_NAME'])) { 
	// we assume if url does not contain "localhost", it must be your 0fees or zymic database connection 
		if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false){ 
			$mysqli = new mysqli("ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com", "user", "password", "ntrader") or exit("Error connecting to database");     
		} else { 
			// must be localhost database connection 
			$mysqli = new mysqli("localhost", "root", null, "ntrader");     
		} 
	}   
	$result = $mysqli->query("CALL `P005_SUBMIT_FEEDBACK`('$user', '$mail', '$subject', '$content')");

	if ($result){
			echo 'received';
			exit();
	}else{
		echo "BAD data!";
		printf("Error: %s\n", $mysqli->error);
		//echo $user."</br>";
		//echo $mail."</br>";
		//echo $subject."</br>";
		//echo $content."</br>";
		exit();
	}
}

?>

<html>
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
	
	
	<title>Contact Us</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
	<meta name="description" content="Educate yourself with nTrader's dynamic Jargon translation utility, 
and the dynamics of the financial market place.">
	<link rel="image_src" href="Images/Logo_42x42.png">
	<link rel="stylesheet" href="CSS/standard.css" type="text/css">
	<link rel="stylesheet" href="CSS/contact-us.css" type="text/css">
	<link rel="shortcut icon" href="Images/browser.ico" >

</head>
<body>

	<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="Home/JAVA/mootools.1.2.3.js"></script>
	<script type="text/javascript" src="Home/JAVA/VerticalSlider.js"></script>
	
<div id="wrapper">

<!-- ==================== Menu ==================== -->
<div id='cssmenu' style="position: relative;">
   <a href="index.php"><img id="logo" src="Images/Logo.png"/></a>
<ul>
   	<li><a class = "fNiv" href='index.php'><span>Main</span></a></li>
   	<li class='active'><a class = "fNiv" href='AboutUs.php'><span>About Us</span></a></li>
	<li><a class = "fNiv" href='products.php'><span>Our Products</span></a></li>
   	<?php
	if(isset($_SESSION['username'])){
		echo "<li><a class = 'fNiv' href='home.php'><span>Go to nConsole</span></a></li>";
	}
	if(!isset($_SESSION['username'])){
		echo "<li><a class = 'fNiv' href='register.php' onclick=".'"'."ResetLogin();document.getElementById('sourceloc').value='index'; Disappear()".'"'.";><span>Register</span></a></li>";
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

<div style="width:100%;">
    <table width=100% height=100%>
	<tr>
	    <td width=270px valign=top style="border-right:1px solid;padding-top:55px;" >
		<ul>
		    <li class="Generalitem">
			<span><a href="contact-us.php">Contact Us</span>
		    </li>
		    <li class="Generalitem">
			</span><a href="acknowledge.php">Acknowledgement</a></span>
		    </li>
		    <li class="Generalitem">
			<span><a href="feedback.php">Feedback</a></span>
		    </li>
		</ul>
	    </td>
	    <td width=auto valign=top align=center>
		<div id="content-type">
		    <div id="content-title"><h2>Contact Us</h2></div>
		    <div id="comments">
			<table id="generalcontact" width=766px>
			    <tr>
				<td id="Title" colspan=2 align=center>
				    Headquarters
				</td>				
			    </tr>
			    <tr>
				<td valign=top width=239px>
				    nTrader Project 2012<br>
				    Singapore 529757, <br>
				    21 Tampines Avenue 1 <br>
				    Tel: +65 6788 2000				    
				</td>
				<td valign=top width=518px>
				    For general Enquiries: enquiries@ntrader.com<br>
				    For corporate affairs: ntradings@ntrader.com
				</td>
			    </tr>
			</table>
		    </div>
		    <br>
		    <br>
		    <br>
		    <div id="enquiries-form">
			<form>
			<table id="enquiries-tbl">
			    <tr>
				<td id="Title" width=230px style="padding:8px;">Full name</td>
				<td width=500px><input type="text" id="fullname" name="fullname" /></td>				
			    </tr>
			    <tr>
				<td id="Title" width=230px style="padding:8px;">Email</td>
				<td width=500px><input type="text" id="email" name="email" /></td>
			    </tr>
			    <tr>
				<td id="Title" width=230px style="padding:8px;">Subject</td>
				<td width=500px><input type="text" id="subject" name="subject" style="width:100%;"/></td>
			    </tr>
			    <tr>
				<td id="Title" valign=top height=300px>Content</td>
				<td width=500px><textarea rows="18" cols="60" id="content" name="content" style="resize: none;"></textarea></td>
			    </tr>
			    <tr>
				<td id="Title">Contact nTrader NOW!</td>
				<td width=500px><input type="submit" name="submittext" value="Submit to nTrader Admin" onclick="submitadmin();return false;" style="background-color: #0079b2;padding: 6px;font-weight: bold;color: white;"/> <input type="reset" value="Reset!" style="background-color: #0079b2;padding: 6px;font-weight: bold;color: white;"> </td>
			    </tr>
			</table>
			</form>
		    </div>
		</div>
<!-- bottom padding -->
<br><br><br><br><br><br><br><br><br>
	    </td>
	</tr>
    </table>
</div>


<!-- ==================== JAVA (Slider) ==================== -->
	<script type="text/javascript" src="jquery/jquery.js"></script>
	<script type="text/javascript" src="jquery/jMenu.jquery.js"></script>


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
	
	<script type="text/javascript">
		function submitadmin(){
			var usrname = document.getElementById("fullname").value;
			var usrmail = document.getElementById("email").value;
			var contentsub = document.getElementById("subject").value;
			var content = document.getElementById("content").value;

			if(usrname == "" || usrmail == "" || contentsub == "" || content == ""){
				alert("Please complete all fields before submitting.")
			}else{
                $.ajax({  
                    url: '',  
                    data: {'fullname': usrname, 'email': usrmail, 'subject': contentsub, 'content': content},
                    type: 'post',
                    success: function(AJAX_RESP){                                               
						if(AJAX_RESP == "received"){
							alert("The administrator have received your message");
							window.location = "http://ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com/contact-us.php";
						}else{
							return
						}
                    },
                    error: function() {  
                        
                    },  
                    complete: function() {
                        
                    }
                });
			}
		}
	
	</script>
		
</div>
    <div align="top"><img src="Images/white.png" class="bg"></div>
    <div class="nfooter">
	<div>
	<span style="float:left;">nTrader Console Project 2012. Temasek Polytechnic IIT School.</span>
	<span style="float: right;">This website is best viewed with Google Chrome</span>
	<span><a href="acknowledge.php">Acknowledgement</a></span> | <span><a href="contact-us.php">Contact us</a></span> | <span><a href="feedback.php">Feedback</a></span>
	</div>
    </div>
</body>
</html>
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

	<title>About Us</title>
	<meta http-equiv="description" content="" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="Educate yourself with nTrader's dynamic Jargon translation utility, 
and the dynamics of the financial market place."/>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" />
    	<script src="http://code.jquery.com/jquery-1.8.3.js"></script>
    	<script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
    	<link rel="stylesheet" href="CSS/standard.css" type="text/css" />
	<link rel="stylesheet" href="CSS/about.css" type="text/css" />
	<link rel="image_src" href="Images/Logo_42x42.png" />
	<link rel="shortcut icon" href="Images/browser.ico" />

</head>
<body  onLoad="Default();">



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



<!-- ==================== Product Blocks ==================== -->
	<a href="contact-us.php"><img src="Images/About/ContactUs.png" id="ContactUs"></a>
	<div class="wrapper">
            <!-- <h1 class="title">nTrader</h1> -->
            <div id="v-nav">
                <ul>
                    <li tab="tab1" class="first current">Who are we?</li>
                    <li tab="tab2">Announcement</li>
                    <li tab="tab3" class="last">Updates</li>
                </ul>

                <div class="tab-content">
                    	<h4>
                        	nTrader
                    	</h4>
			<p>The NTrader is a non-commercial web based financial resource platform catered specifically to aspiring and retail investors. It was created by four students from Temasek Polytechnic students who have a keen interest in finance and technology. By taking on the challenge of learning industry leading methods of investment analytics, this group of students aims to bring investment related tools that are normally owned by only professionals to the general public. </p>
				</br><p>The NTrader has three core features:</p>
				<li>nTranslator</li>
				<li>nIndicator</li>
				<li>nIntel</li>
				</br><p>Each of these features serves to eliminate problems or gaps faced by retail or novice investors. If you are not clear of how to use these features, kindly refer to the tutorial section where there are examples to showcase the benefits that you can get by using them.</p>
				</br></br><h4>Our Mission</h4>
				<p>To provide an innovative, visually captivating and fun platform where investors are able to utilize industry leading tools to aid in their investment decisions and acquire financial literacy.</p>
                		</br></br><h4>Our Vision</h4>
				<p>To be the leading provider of innovative investment tools and resources for retail investors.</p>
                </div>

                <div class="tab-content">
                    	<h4>
                        	Announcement
                    	</h4>
                </div>

                <div class="tab-content">
                    <h4>
                        Updates
                    </h4>
                </div>
            </div>
        </div>
	

        <!-- Include JavaScript -->
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>        
        <script type="text/javascript" src="http://benalman.com/code/projects/jquery-hashchange/jquery.ba-hashchange.js"></script>
        <script type="text/javascript" src="jquery/VerticalTab.js"></script>
	<script>
		$(function () {
    			var items = $('#v-nav>ul>li').each(function () {
        			$(this).click(function () {
					//remove previous class and add it to clicked tab
					items.removeClass('current');
					$(this).addClass('current');

					//hide all content divs and show current one
					$('#v-nav>div.tab-content').hide().eq(items.index($(this))).show('fast');
					window.location.hash = $(this).attr('tab');
				});
			});

    			if (location.hash) {
				showTab(location.hash);
			}else {
				showTab("tab1");
			}

    			function showTab(tab) {
        			$("#v-nav ul li:[tab*=" + tab + "]").click();
   			 }

    			// Bind the event hashchange, using jquery-hashchange-plugin
    			$(window).hashchange(function () {
				showTab(location.hash.replace("#", ""));
			})

    			// Trigger the event hashchange on page load, using jquery-hashchange-plugin
   			$(window).hashchange();

		});
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

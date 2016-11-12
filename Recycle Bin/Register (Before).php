<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>nTrader</title>
	<meta http-equiv="description" content="" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="CSS/standard.css" type="text/css" />
</head>
<body onLoad="StartMask(); DisabledPersonalTB(); DisabledConfirmTB();">

<h1>nTrader</h1>

<ul id="jMenu">
	<li><a class="fNiv">Homepage</a></li>
	
	<li><a class="fNiv">About Us</a></li>
	
	<li><a class="fNiv">nTrader</a></li>
	
	<li><a class="fNiv">Subscript</a></li>
	
	<li><a class="login-window" href="#login-box">Sign In</a></li>
	
</ul>



<!-- ==================== Login Popup (http://www.alessioatzeni.com/blog/login-box-modal-dialog-window-with-css-and-jquery/) ==================== -->
<div id="login-box" class="login-popup">
        <a href="#" class="close"><img src="close_pop.png" class="btn_close" title="Close Window" alt="Close" /></a>
          <form method="post" class="signin" action="#">
                <fieldset class="textbox">
            	<label class="username">
                <span>Username or email</span>
                <input id="username" name="username" value="" type="text" autocomplete="on" placeholder="Username">
                </label>
                <label class="password">
                <span>Password</span>
                <input id="password" name="password" value="" type="password" placeholder="Password">
                </label>
                <button class="submit button" type="button">Sign in</button>
                <p>
                <a class="forgot" href="#">Forgot your password?</a>
                </p>        
                </fieldset>
          </form>
</div>



<!-- ==================== Register (Put Warning message in id="warning") ==================== -->
	<table id = "wholetable">
	<tr>
		<td id = "wholetabletd" ALIGN="center" VALIGN="TOP">
			<!-- ==================== Account Info ==================== -->
			<table id = "accountinfo">
			<tr>
				<tr><td id = "Session" align="left" colspan="2" VALIGN="TOP">Step 1 </td></tr>				
			</tr>
			<tr><td><h5 id="warning" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td>Username: </td>
				<td><input type="text" name="email" id="email"/></td>
				
			</tr>
			<tr><td><h5 id="warning" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td>Email: </td>
				<td><input type="text" name="email" id="email"/></td>
				
			</tr>
			<tr><td><h5 id="warning" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td>Password: </td>
						<td><input type="text" name="password" id="password"/></td>
			</tr>
			<tr><td><h5 id="warning" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td>Confirmed Password: </td>
				<td><input type="text" name="confirmedpassword" id="confirmedpassword"/></td>
			</tr>
			<tr>
				<td align="right" colspan="2" VALIGN="TOP"></br><button onclick="document.getElementById('personalmask').style.display = 'none'; EnabledPersonalTB();";>Next</button></td>
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
				<tr><td id = "Session" align="left" colspan="2" VALIGN="TOP">Step 2 </td></tr>	
			</tr>
			<tr><td><h5 id="warning" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td id = tbname>Name: </td>
				<td><input type="text" name="name" id="name""/></td>
			</tr>
			<tr><td><h5 id="warning" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td id = tbname>NRIC: </td>
				<td><input type="text" name="nric" id="nric"/></td>
			</tr>
			<tr><td><h5 id="warning" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td id = tbname>Date of Birth: </td>
				<td><input type="text" name="dob" id="dob"/></td>
			</tr>
			<tr><td><h5 id="warning" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td id = tbname>Address: </td>
				<td><input type="text" name="address" id="address"/></td>
			</tr>
			<tr><td><h5 id="warning" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td id = tbname>Postal Code: </td>
				<td><input type="text" name="postalcode" id="postalcode"/></td>
			</tr>
			<tr><td><h5 id="warning" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td id = tbname>Contact Number: </td>
				<td><input type="text" name="contact" id="contact"/></td>
			</tr>
			<tr>
				<td align="right" colspan="2" VALIGN="TOP"></br><button id="personalb" onclick="document.getElementById('confirmmask').style.display = 'none'; EnabledConfirmTB();">Next</button></td>
			</tr>
			</table>
		</td>
		<td id = "wholetabletd" ALIGN="center" VALIGN="TOP">
			<!-- ==================== Confirmation ==================== -->
			<div class="table-foreground">
    				<img id= "confirmmask" src="Images/Register/table-foreground.png" />
			</div>
			<table id = "confirm">  <!-- style = "display:none" --> 
			<tr>
				<tr><td id = "Session" align="left" colspan="2" VALIGN="TOP">Step 3 </td></tr>	
			</tr>
			<tr><td><h5 id="warning" style="display: inline; color: red;"></h5></td></tr>
			<tr>
				<td id = "tnc"><input type="checkbox" name="tnc" id="tnc">&nbsp I have read the Terms and Conditions<br></td>
			</tr>
			<tr>
				<td align="right" colspan="2" VALIGN="TOP"></br><button id="confirmb">Register</button></td>
			</tr>
			</table>
		</td>
	</tr>
	</table>



<!-- ==================== JAVE (Menu) ==================== -->
	<script type="text/javascript" src="jquery/jquery.js"></script>
	<script type="text/javascript" src="jquery/jMenu.jquery.js"></script>
	<script type="text/javascript">
  		$(document).ready(function(){
			$("#jMenu").jMenu({
				openClick : true,
				ulWidth : 'auto',
				effects : {
					effectSpeedOpen : 200,
					effectSpeedClose : 200,
					effectTypeOpen : 'slide',
					effectTypeClose : 'slide',
					effectOpen : 'linear',
					effectClose : 'linear'
				},
				TimeBeforeOpening : 100,
				TimeBeforeClosing : 100,
				animatedText : false,
				paddingLeft: 1
			});
		})

	</script>



<!-- ==================== JAVA (Login Popup) ==================== -->
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



<!-- ==================== JAVA (Register) ==================== -->
	<script>
		function DisplayNext(NextSection)
		{	
			document.getElementById(NextSection).style.display = '';	
		}
		
		function AddImage()
		{
  			document.getElementById("table-foreground").innerHTML = "<img id='myimage' src='Images/Register/table-foreground.png'>";
			document.getElementById("myimage").ondragstart = function() { return false; };
		}
		
		function StartMask()
		{
			document.getElementById("personalmask").ondragstart = function() { return false; };
			document.getElementById("confirmmask").ondragstart = function() { return false; };
		}
		
		function DisabledPersonalTB()
		{
			document.getElementById("name").disabled = true;
			document.getElementById("nric").disabled = true;
			document.getElementById("dob").disabled = true;
			document.getElementById("address").disabled = true;
			document.getElementById("postalcode").disabled = true;
			document.getElementById("contact").disabled = true;
			document.getElementById("contact").disabled = true;
			document.getElementById("personalb").disabled = true;
		}
		
		function EnabledPersonalTB()
		{
			document.getElementById("name").disabled = false;
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
	</script>

</body>
</html>

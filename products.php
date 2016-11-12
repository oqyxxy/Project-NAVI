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

	<title>Our Products</title>
	<meta http-equiv="description" content="" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="Educate yourself with nTrader's dynamic Jargon translation utility, 
and the dynamics of the financial market place."/>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" />
    	<script src="http://code.jquery.com/jquery-1.8.3.js"></script>
    	<script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
    	<link rel="stylesheet" href="CSS/standard.css" type="text/css" />
	<link rel="stylesheet" href="CSS/products.css" type="text/css" />
	<link rel="image_src" href="Images/Logo_42x42.png" />
	<link rel="shortcut icon" href="Images/browser.ico" />

</head>
<body  onLoad="Default();">



<!-- ==================== Menu ==================== -->
<div id='cssmenu' style="position: relative;">
   <a href="index.php"><img id="logo" src="Images/Logo.png"/></a>
<ul>
   	<li><a class = "fNiv" href='index.php'><span>Home</span></a></li>
   	<li><a class = "fNiv" href='AboutUs.php'><span>About Us</span></a></li>
	<li class='active'><a class = "fNiv" href='products.php'><span>Our Products</span></a></li>
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
	<table id="ProductDesc">
		<tr>
			<td colspan="3">
				<p>As mentioned in the <i>Who are we?</i> section, the nConsole comprise of four components which makes up the tools and applications within the nTrader. These components will seek to tackle the following issues:
<li>Spending too much effort trying to search for relevant and accurate investment information.</li>
<li>Wasting too much time trying to source out different types of investment information to conduct analysis <br> 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<b>E.g</b> economic indicators, financial news, financial market data)</li>
<li>Do not have the ability to comprehend investment information</li>
<p>A brief introduction of each component will be given in this section. More details will be given in the respective component tabs.</p></p>
			</td>
		</tr>
		<tr>
			<td></td>
			<td id="Summary">
				<p>The nIndictor is a set of proprietary nTrader algorithms that is used to derive news sentiment. These algorithms are based upon various news analytics research literature and will be subjected to continuous upgrades.</p>
				<div id="MoreContent1"></div>
				<p id="p_more1"></p>
				<a href="home.php"><img id="StartNow" src="Images/Products/Start Now Button.png" alt="some_text"></a>
			</td>
			<td id="screenshot">
				<img src="Images/Products/nConsole.png" alt="some_text">
			</td>
		</tr>
		<tr>
			<td></td>
			<td id="Summary">
				<p> nIntel is a set of tools which provides insights into the global economy. Such insights will include the performances of regional markets, sentiment ratio of various economies as computed from our nIndicator algorithms, and country specific news.</p>
				<div id="MoreContent2"></div>
				<p id="p_more2"></p>
				<a href="home.php"><img id="StartNow" src="Images/Products/Start Now Button.png" alt="some_text"></a>
			</td>
			<td id="screenshot">
				<img src="Images/Products/nIntel2.png" alt="some_text">
			</td>
		</tr>
		<tr>
			<td></td>
			<td id="Summary">
				<p>The nTranslator is a feature of nTrader which translates all financial jargons within the financial news into simpler terms.</p>
				<div id="MoreContent3"></div>
				<p id="p_more3"></p>
			</td>
			<td>
				<img src="Images/Products/nTranslator.png" alt="some_text">
			</td>
		</tr>
		<tr>
			<td></td>
			<td id="Summary">
				<p>The nLearner is the educational component of nTrader where fun games on financial literacy are introduced.</p>
				<div id="MoreContent4"></div>
				<p id="p_more4"></p>
				<a href="wordlearner.php"><img id="StartNow" src="Images/Products/Start Now Button.png" alt="some_text"></a>
			</td>
			<td>
				<img src="Images/Products/nLearner.png" alt="some_text">
			</td>
		</tr>
	</table>
	
	<script>
		function Default()
		{	
			document.getElementById("p_more1").innerHTML = "<a onclick=" + "" + "showhidden('1');" + "" + ">Read more...</a>";
			document.getElementById("p_more2").innerHTML = "<a onclick=" + "" + "showhidden('2');" + "" + ">Read more...</a>";
			document.getElementById("p_more3").innerHTML = "<a onclick=" + "" + "showhidden('3');" + "" + ">Read more...</a>";
			document.getElementById("p_more4").innerHTML = "<a onclick=" + "" + "showhidden('4');" + "" + ">Read more...</a>";
		}

		function showhidden(section)
		{	
			var Content;
			if (section==1) {
				Content = "<p>Most retail investors have a day job and just do not have the luxury of time and effort to read and digest daily financial news. Financial news serves as an important source of financial information for many as investment decisions are derived from it. Therefore to provide convenience to this group of investors, the NIndicator will capture the informational context of the news, whether the news is indicating a positive, negative or a neutral sentiment on a financial entity.</p><p>With these sentiment ratings you are able to quickly browse through headlines of news articles without having to spend the effort of reading through the whole news article. Furthermore these sentiments are consolidated and compiled into a global sentiment map which will be further described in NIntel. This map chart will provide a macro view of the news sentiments of countries all over the world.</p><p>To ensure the accuracy of our news sentiment, we will conduct regular back testing. The back testing will consist of the various global and regional indexes and its respective sentiment index. After which both indexes will be mapped together and their correlation will be calculated. All correlation scores are updated daily and available in the NIndicator section.</p>";
			}else if (section==2) {
				Content = "<p>The NIntel is a business intelligence dashboard that delivers a three-in-one view of the global economy. The three elements are:</p><li>Global News Sentiment Map Chart</li><li>Global Economic data</li><li>Global and Regional Indexes</li><p>This macro view of the global economy is able to aid in the investment decision making process of investors who adopts the top-down approach of investing or otherwise known as the E-I-C (Economy-Industry-Company) investment strategy.</p><p>The Global News Sentiment Map Chart is generated through NTrader's sentiment analytics as mentioned in section NIndicator. What it shows is the sentiment ratio of various global economies/countries where sentiment ratio is derived through the following equation -number of positive news/number of negative news.</p>";
			}else if (section==3) {
				Content = "<p>Reading huge chunks of financial news information can be a pain especially for a novice investor who is not familiar with many of the mind-boggling financial terms and jargons. The problem here is clearly financial literacy. Therefore leveraging on a built in and ever-growing financial dictionary database, the NTrader's very own NTranslator will be able to locate and decipher the definition and meaning of any financial terms and jargons within the financial news content.</p><p>To experience the benefits of the NTranslator, all you have to do is access a financial news article and hover your mouse cursor over the pre-located financial terms and jargons (refer to the screenshot above).</p><p>To enhance the purpose of the NTranslator, we have plans to have the add-on feature, NStorage, which any individual will be entitled to upon registering an account with NTrader. The NStorage acts like a depositary for financial terms and jargons. NTrader clients are able to save financial terms and jargons that they have read into their respective NStorage so as to review and revise them whenever necessary.</p>";
			}else if (section==4) {
				Content = "";
			}

			document.getElementById("p_more" + section).innerHTML = "<a onclick=" + "" + "hidecontent('" + section + "');" + "" + ">Read less...</a>";
			document.getElementById("MoreContent" + section).innerHTML = Content;
			document.getElementById("MoreContent" + section).style.display = 'block';
		}

		function hidecontent(section)
		{	
			document.getElementById("p_more" + section).innerHTML = "<a onclick=" + "" + "showhidden('" + section + "');" + "" + ">Read more...</a>"
			document.getElementById("MoreContent" + section).style.display = 'none';
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

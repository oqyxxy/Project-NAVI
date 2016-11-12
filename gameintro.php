<?php
SESSION_START();
if(!isset($_SESSION['username'])){
	header("Location:index.php");
}
?>

<html>
<head>

<title>Game Introduction</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="description" content="Educate yourself with nTrader's dynamic Jargon translation utility, 
and the dynamics of the financial market place."/>
</head>

<body>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="Home/JAVA/mootools.1.2.3.js"></script>
<script type="text/javascript" src="Home/JAVA/VerticalSlider.js"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.pack.js"></script>
<link rel="stylesheet" href="Home/CSS/gameintro.css" type="text/css"/>

<a href=""><img style="position: absolute; margin-left: 3px;" src="Home/Images/nLearner.png"/></a>
<p align="right"><a href="wordlearner.php" id="BackTo"><br>Back to WordLearn HQ</a></p>
<div align="center" id="All">
	<table>
		<tr>
			<td><div id="div_IntroImage"></div></td>
			<td id="td_DiffSetting"><fieldset id="DiffSetting">
				<legend><b>Difficulties Setting</b></legend>
				<p><u>Choose a game mode :</u></p>
				<div id='gamechoice'></div>
				<p align="right"><input type='button' value='START!' id='startgame' onclick='startthegame()'/></p>
			</fieldset><p id="Extra"></p></td>
		</tr>
	</table>
</div>

<script type="text/javascript">
var gametype = getUrlVars()["game"];

if(gametype == "hangword"){
	document.getElementById("div_IntroImage").innerHTML = "<img id='IntroImage' src='Home/Images/Jargon_Find/HangwordIntro.png'>";
	document.getElementById("gamechoice").innerHTML = "<input type='radio' name='gamediff' value='Invincible'>&nbsp;&nbsp;Invincible (The man will not be affected by your failures)<br/><input type='radio' name='gamediff' value='Normal'>&nbsp;&nbsp;Normal (7 failures and the man is dead)<br/><input type='radio' name='gamediff' value='Expert'>&nbsp;&nbsp;Expert (3 failures and the man is dead)<br/><input type='radio' name='gamediff' value='Sudden Death'>&nbsp;&nbsp;Sudden Death (Don't even try...)<br/>";
}else if(gametype == "jargonhunt"){
	document.getElementById("div_IntroImage").innerHTML = "<img id='IntroImage' src='Home/Images/Jargon_Find/Scroll.png'>";
	document.getElementById("gamechoice").innerHTML = "<input type='radio' name='gamediff' value='Beginner'/>&nbsp;&nbsp;Beginner (150% life, 50% Completion)<br/><input type='radio' name='gamediff' value='Advanced' />&nbsp;&nbsp;Advanced (125% life, 75% Completion)<br/><input type='radio' name='gamediff' value='Expert' />&nbsp;&nbsp;Expert (No assistance, Full Completion)<br/><input type='radio' name='gamediff' value='YOLO' />&nbsp;&nbsp;YOLO mode (You only live once...)<br/>";
	document.getElementById("Extra").innerHTML = "* Note that this game doesn&#146;t recognised abbreviations and inflected form. Clicking these words will result in losing of life."
}else{
	alert("Invalid method of entry. Proceeding to kick")
	window.location = "http://ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com"
}

function getUrlVars() {
		var vars = {};
		var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
			vars[key] = value;
		});
		return vars;
}

function startthegame(){
	var gametype = getUrlVars()["game"];
	var radios = document.getElementsByName('gamediff');
	var diffsetting

	for (var i = 0, length = radios.length; i < length; i++) {
	    if (radios[i].checked) {
	        diffsetting = radios[i].value;
	    }
	}

	if(typeof(diffsetting) == "undefined"){
		alert("Please select a game mode before proceeding!");
	}else{
		var url = ""
		if(gametype == "hangword"){
			url = "http://ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com/hangword.php?gametype=" + diffsetting;
		}else if(gametype == "jargonhunt"){
			url = "http://ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com/jargon-find.php?gametype=" + diffsetting;
		} 
		window.location = url;
	}
}

</script>

</body>
</html>
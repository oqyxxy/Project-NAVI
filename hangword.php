<?php
SESSION_START();
if(!isset($_SESSION['username'])){
	header("Location:index.php");
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
$stmt = $mysqli->prepare("SELECT a.Headword, a.Definition FROM `m0002_financial_lexicon` a WHERE a.Headword not in (SELECT Headword FROM `m0002_headword_relation` UNION SELECT Relation from `m0002_headword_relation`) ORDER BY RAND() LIMIT 1");
$stmt->execute();
$stmt->bind_result($headworddata, $defdata);
while ($stmt->fetch()){
	$headword = $headworddata;
	$definition = $defdata;
}
$stmt->close();
$mysqli->close();

unset($_SESSION['headwordarr']);
$headwordsplit = str_split($headword);
$_SESSION['headwordarr'] = $headwordsplit;
?>

<html>
<head>

<title>nTrader Jargon Translator</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="description" content="Educate yourself with nTrader's dynamic Jargon translation utility, 
and the dynamics of the financial market place."/>
</head>

<body>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="Home/JAVA/mootools.1.2.3.js"></script>
<script type="text/javascript" src="Home/JAVA/VerticalSlider.js"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.pack.js"></script>
<link rel="stylesheet" href="Home/CSS/hangword.css" type="text/css"/>

<script type="text/javascript">
function verifyABC(ABCinput){
    $.ajax({  
        url: 'hangwordverify.php',  
        data: {'alphakey': ABCinput},
        type: 'get',
        success: function(AJAX_RESP){                                               
            var veriresult = eval(AJAX_RESP);
            if(veriresult[0] == false){
                //loselife
                var currpath = document.getElementById('gameimgsrc').value;
                if((document.getElementById('lpscore').value) != "10000"){
                    document.getElementById('lpscore').value = document.getElementById('lpscore').value - 1;
                    document.getElementById('manimage').src = currpath + (document.getElementById('lpscore').value) + ".png";
                    if((document.getElementById('lpscore').value) <= 0){
    					alert("YOU HAVE FAILED TO SAVE THE MAN....");
    					window.location = "http://ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com/wordlearner.php";
    				}
                }
                //alert("WARNING YOU ARE DIENG!");
            }else{
                //survive
                var positionarr = veriresult[1];
                var charcount = document.getElementById('totchar').value;
				for (x in positionarr){
					var templol = positionarr[x];
					var cmproperator = document.getElementById(templol).innerHTML
					if((!isNaN(templol)) && ((cmproperator == "_") || (cmproperator == " "))){ //retarded code thinks that integers are strings and not numbers
						 document.getElementById(templol).innerHTML = ABCinput.toUpperCase();
						 document.getElementById('totchar').value = document.getElementById('totchar').value - 1;
						 //ENDGAME SCENE
						 if(document.getElementById('totchar').value <= 0){
							alert("Hooray! The man is saved!");
							window.location = "http://ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com/wordlearner.php";
						 }
					};
	            }		
            }
        },
        error: function() {  
			alert("epic fail");
        },  
        complete: function() {

        }
    });
    document.getElementById("wordinput").value = "";
}

</script>

<a href=""><img style="position: absolute; margin-left: 3px;" src="Home/Images/nLearner.png"/></a>
<p align="right"><a href="wordlearner.php" id="BackTo"><br>Back to WordLearn HQ</a></p>
<div id="All" align='center'>
	<h1 style="min-width: 200px;">Guess the financial term before the man dies!</h1>
	<?php 
	$gamemode = $_GET['gametype'];
	
	if($gamemode == "Invincible"){
		$imgpath = "/HWIMG/Invincible/";
		$life = 10000;
	}elseif($gamemode == "Normal"){
		$imgpath = "/HWIMG/Normal/";
		$life = 7;
	}elseif($gamemode == "Expert"){
		$imgpath = "/HWIMG/Expert/";
		$life = 3;
	}elseif($gamemode == "Sudden Death"){
		$imgpath = "/HWIMG/SD/";
		$life = 1;
	}else{
		echo '<script type="text/javascript">
    	alert("Bad entrance!!!");
    	history.back();
		</script>';
	}
	
	echo "<p><img class ='Stickman'"." src='".$imgpath.$life.".png"."' title='THE MAN!' alt='' id='manimage' /></p>";
	
	$idcounter = 1;
	$sidecounter = 0;
	$Underlines = "";
	foreach ($headwordsplit as $value){
		if($value != " "){
			$Underlines = $Underlines."<span id='".$idcounter."'>_</span> &nbsp";
			$sidecounter = $sidecounter + 1; 
		}else{
			$Underlines = $Underlines."<span id='".$idcounter."'>&nbsp;&nbsp;</span> &nbsp";
		}
		$idcounter = $idcounter + 1;
	}

	function left($str, $length) {
     		return substr($str, 0, $length);
	}

	echo "<table id='Underlines' align='center'><tr><td>".left($Underlines, strlen($Underlines) - 5)."</td></tr></table>";
	echo "<input type='hidden' id='totchar' value='".($sidecounter)."' />";
	//echo "<input type='hidden' id='totchar' value='".($headword)."' />";
	echo "<input type='hidden' id='gameimgsrc' value='".($imgpath)."' />";
	echo "<input type='hidden' id='lpscore' value='".($life)."' />";
	
	echo "<table id='Table_Definition' align='center'><tr><td id='Title'>Definition: </td><td id='Definition'>".$definition."</td></tr></table>";
	?>
	
	<p><b><br>Your guess :</b><input id='wordinput' type='text' maxlength="1" onkeydown="if (event.keyCode == 13) document.getElementById('verifyclick').click();"/><input type='button' id='verifyclick' value='Save the man' onclick='verifyABC(wordinput.value);'/></p>
</div>

</body>
</html>
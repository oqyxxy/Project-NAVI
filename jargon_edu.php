<?php
	session_start();

	if(!isset($_SESSION['username'])){
		header("Location:index.php");
	}

	$content_url = $_GET["content_url"];
	$signal = $_GET["signal"];
	$date = $_GET["date"];
	$category = $_GET["category"];
	$affinstr = $_GET["affinstr"];

	if ($signal > 0)
  	{
  		$SignalWord = "Bull";
  	}
	elseif ($signal < 0)
  	{
  		$SignalWord = "Bear";
  	}
	else
  	{
  		$SignalWord = "Neutral";
  	}
?>
<html>
<head>


<title>nTrader Jargon Translator</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="description" content="Educate yourself with nTrader's dynamic Jargon translation utility, 
and the dynamics of the financial market place."/>
<link rel="stylesheet" href="Home/CSS/jargon_edu.css">
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.pack.js"></script>
<a href=""><img style="position: absolute; margin-left: 3px;" src="Home/Images/nTranslator.png"/></a>
<a href="home.php" id="BackTo"><br>Back to News</a>
</head>
<body>
<!-- ==================== AJAX to addword ==================== -->
<script>
function wordlearn_ajax(imgsrc){
    
    var clickval = document.getElementById("clickword").value
    var userval = document.getElementById("trueuserid").value
    var imgtype = imgsrc.substring(91,92)
    var xmlhttp;
	var url = "learnword.php?useriddata="+ userval +"&worddata=" + clickval;
    
    if(imgtype == "2"){
    	document.getElementById("wordlearn_alert").innerHTML = "Already added to your word learner.";
    }else{
		//alert('die');
		if(window.XMLHttpRequest){
    		// code for IE7+, Firefox, Chrome, Opera, Safari
		  	xmlhttp=new XMLHttpRequest();
		}else{
    		// code for IE6, IE5
		  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		
		xmlhttp.onreadystatechange=function(){
			if (xmlhttp.readyState==4 && xmlhttp.status==200){
				var DBresult = xmlhttp.responseText;
				document.getElementById("wordlearn_alert").innerHTML = DBresult;
				if(DBresult == ("'" + clickval + "' added to your word learner!")){
					var currlist = document.getElementById("learnlist").value
					document.getElementById("learnlist").value = currlist+ "||*+*+*||" + clickval
					document.getElementById("favbuttonimg").src ="Images/Jargon/favourites-button2.png"
				}
		    }
		}
		xmlhttp.open("GET",url,true);
		xmlhttp.send();
    }    
}

$(document).ready(function() {

	//grab all span tag with rel set to jargon-trans
	$('span[class=jargon-trans], #jargon-trans-box').live("click", function() {		//mouseenter
		//get the height, top and calculate the left value for the sharebox
                
		var height = $(this).height();
		var top = $(this).offset().top;
		
		//get the left and find the center value
		var left = $(this).offset().left + ($(this).width() /2) - ($('#jargon-trans-box').width() / 2);		
		if(left<0){
                    left=0;
                }else if(typeof(document.body.clientWidth) == 'number'){
                    if(($('#jargon-trans-box').width() + left) > document.body.clientWidth){
                       left = document.body.clientWidth - $('#jargon-trans-box').width()                        
                    }
                }

		//grab the title value and explode the bar symbol to grab the url and title
		//the content should be in this format url|title
		var value = $(this).attr('title').split('|');
                
		//assign the value to variables and encode it to url friendly
		var field = value[0];
		var url = encodeURIComponent(value[0]);
		var title = encodeURIComponent(value[1]);
		
		//assign the height for the header, so that the link is cover
		$('#jargon-trans-header').height(height);
		
		//display the box
		$('#jargon-trans-box').show();
		
		//set the position, the box should appear under the link and centered
		$('#jargon-trans-box').css({'top':top, 'left':left});
		
		//assign the url to the textfield
		$('#jargon-trans-field').val(field);    ///////////////////	

		//Reset warning message
		document.getElementById("wordlearn_alert").innerHTML = "";
		
		//onclick determine if item is added in wordlearner
		var favlist =  document.getElementById("learnlist").value;
		var favchk = false;
		if(favlist != ""){
			var favarr = favlist.split("||*+*+*||");
			for (var key in favarr){
				if(favarr[key] == $(this).text()){
					favchk = true;
				}
			}
		}
		document.getElementById("clickword").value = $(this).text();
		if(favchk == true){
			document.getElementById("favbuttonimg").src ="Images/Jargon/favourites-button2.png"
		}else{
			document.getElementById("favbuttonimg").src ="Images/Jargon/favourites-button1.png"
		}			
                               
	});

	//onmouse out hide the jargon-trans box
	$('#jargon-trans-box').mouseleave(function () {
		$('#jargon-trans-field').val('');
		$(this).hide();
	});
        
	//hightlight the textfield on click event
	$('#jargon-trans-field').click(function () {
		$('#jargon-trans-field').val('');
		$(this).hide();
	});
});


</script>

<?php

include_once 'Libraries/pos_tagger.php';

//START OF USER INFO EXTRACTION//

$userid = $_SESSION['userid'];

if(isset($_SERVER['SERVER_NAME'])) { 
	if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false){ 
		$mysqli = new mysqli("ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com", "user", "password", "ntrader") or exit("Error connecting to database");     
	} else { 
		// must be localhost database connection 
		$mysqli = new mysqli("localhost", "root", null, "ntrader");     
	} 
}

$stmt = $mysqli->prepare("SELECT b.Headword FROM `m0006_User_Word_relation` a,`m0002_financial_lexicon` b WHERE a.WID = b.WID AND a.user=?");
$stmt->bind_param("s", $userid);
$stmt->execute();
$stmt->bind_result($worddata);
while ($stmt->fetch()){
	$wordlist = $wordlist."||*+*+*||".$worddata;
}
$stmt->close();
$mysqli->close();

echo '<p id="p_learnlist"><input type="hidden" name="learnlist" id="learnlist" value="'.$wordlist.'"/></p><p id="p_trueuserid"><input type="hidden" name="trueuserid" id="trueuserid" value="'.$userid.'"/></p><br/>';

//END OF USER INFO EXTRACTION//

$Jargon_Reference = apc_fetch('HEADWORD_ARR');
$pos_object = apc_fetch('POS_TAG_OBJECT');

//$Direction_Keys = apc_fetch('DIRECTION_POSITION_ARRAY');
$Subject_pattern = "/" . apc_fetch('SUBJECT_PATTERN') . "/";
$Direction_pattern = "/" . apc_fetch('DIRECTION_PATTERN') . "/i";

if(isset($_GET['content_url'])){
    $news_url = $_GET['content_url'];
}else{
    exit();
}

$bloomberg_news_data = GetNewsContent($news_url);
$news_headline = $bloomberg_news_data[0];
$news_content = $bloomberg_news_data[1];
$news_duplicate = $news_content;

//BLOCK #1 ---------------------------  Get sentence of interests from array sentences e.g. Jargons, sentence for analysis
$sentences = Sentence_Splitter($news_duplicate);
$ArraySubRelEvt = array();

    //Sentences for analysis --> Sentences which satisfy requirement for directional keys and subject instrument
    $Analysis_Sentences = preg_grep($Direction_pattern, $sentences);
    $Analysis_Sentences = preg_grep($Subject_pattern, $Analysis_Sentences);
    foreach($Analysis_Sentences as $oSentence_analysis){
        
        if(preg_match('/as|on|after|amid/i', preg_replace('/\bsuch\b\s+\bas\b/i', '', $oSentence_analysis))){

            $tags = $pos_object->tag($oSentence_analysis);
            $SubRelEvt = $pos_object->Chunk_taggedString($tags, $oSentence_analysis, $Direction_pattern);

	    if(count($SubRelEvt)> 0){
		$ArraySubRelEvt[] = $SubRelEvt;
	    }

        }
        //analytical keywords for investors
        
    }

//BLOCK #2 --------------------------  Perform jargon translation on news content
    foreach ($Jargon_Reference as $Jargon_Set){
        $news_content = preg_replace("/".$Jargon_Set['Pattern']."/i", "ooYYoo".preg_replace('/\s/', '_', $Jargon_Set['Headword'])."ooYYoo", $news_content);
    }

    if(preg_match('/ooYYoo\S+?ooYYoo/', $news_content)){
        preg_match_all('/ooYYoo(\S+?)ooYYoo/', $news_content, $Jargon_Plc, PREG_SET_ORDER);
        foreach($Jargon_Plc as $Jargon_Expr){
            $news_content = preg_replace("/".$Jargon_Expr[0]."/", "<span title=\"". $Jargon_Reference[str_replace('_', ' ', $Jargon_Expr[1])]['Definition'] ."\" class=\"jargon-trans\" >" . str_replace('_', ' ', $Jargon_Expr[1]) . "</span>" , $news_content);
            //$Jargon_Reference[str_replace('_', ' ', $Jargon_Expr[1])]['Definition']; //jargon definition            
            //echo $Jargon_Expr[1]. '->' . $Jargon_Reference[str_replace('_', ' ', $Jargon_Expr[1])]['Definition'] . "<br>";
        }
    }

Function Sentence_Splitter($text_input){

    $sentences = array();
    $sentences_matches = array();
    $quoted_sentence_match = array();
    $tmpString = "";
    
    if(preg_match('/<p>[\s\S]+?<\/p>/', $text_input)){
        preg_match_all('/(?<=<p>)[\s\S]+?(?=<\/p>)/', $text_input, $paragraphs);
    }else{
        preg_match_all('/[\s\S]+/', $text_input, $paragraphs);
    }
        
        foreach($paragraphs[0] as $one_para){
        
                //Substitute common abbreviation mistaken as sentences by program with placeholder
                $para = preg_replace('/(\bJan|\bFeb|\bMar|\bApr|\bMay|\bJun|\bJul|\bAug|\bSep|\bOct|\bNov|\bDec|\bLtd|\bCorp|\bvs|\bJr|\bMr|\bMrs|\bDr)(?:\.)(?=\s+|$)/i', '$1##DOT##', $one_para);    
                preg_match_all('/[\s\S]+?(?:\.|\?|\!)(?=\s+[^a-z\(]|$)/', $para, $sentences_matches, PREG_SET_ORDER);

                //�[\s\S]+?\.�      quoted sentences    
                foreach($sentences_matches as $string_sentence){
                    if(preg_match('/“[\s\S]+?\.”/', $string_sentence[0])){
                        preg_match_all('/“[\s\S]+?\.”/', $string_sentence[0], $quoted_sentence_match, PREG_SET_ORDER);
                        $tmpString = preg_replace('/“[\s\S]+?\.”/', '', $string_sentence[0]);
                        foreach($quoted_sentence_match as $quoted_sentence){
                            $sentences[] = str_replace('##DOT##', '.', $quoted_sentence[0]);
                        }
                        $sentences[] = str_replace('##DOT##', '.', $tmpString);
                    }else{        
                    $sentences[] = str_replace('##DOT##', '.', $string_sentence[0]);
                    }

                }
        }
    
    
    return $sentences;
}

Function GetNewsContent($newsURL){    
    include_once('Libraries/simple_html_dom.php');
    $Content = "";
    $Headline = "";
    $html=file_get_html($newsURL);
    foreach ($html->find('div[id=story_display]') as $div){
        foreach($div->find('p') as $p){
	    $Content .= '<p>'.$p->plaintext.'</p>';
        }
    }

    foreach ($html->find('div[id=disqus_title]') as $div){
        $Headline = $div->plaintext;
    }    

return array($Headline, $Content);
}              

?>

<script type="text/javascript">
    function show_hide_jargons(hidden_state){
        if(hidden_state){
            document.getElementById('news-house').innerHTML=<?php echo json_encode($news_content); ?>;
        }else{
            document.getElementById('news-house').innerHTML=<?php echo json_encode($news_duplicate); ?>;
        }
    }

	function AddDelFlagChart(){
    		if(document.getElementById('JargonControl').checked){
        		show_hide_jargons(true);
    		}else{
        		show_hide_jargons(false);
    		}
	}
</script>

	<div id="news-head">
		<h2><?php echo $news_headline; ?></h2>
	</div>
	<div id="content">
	<div id="news-house">
    		<?php echo $news_duplicate; ?>
	</div>
	<br><table style="border-spacing:15px;" id="Summary">
			<tr style="background-color: #0079b2; color: white;">
				<td id="Title" colspan="4" style="text-align: center;"><p><b>Summary </b></td>
			</tr>
			<tr>
				<td id="Title"><p><b>No.</b></td><td id="Title"><p><b>Subject </b></td><td id="Title"><p><b>Direction </b></td><td id="Title"><p><b>Event </b></td>
			</tr>
			<?php for ($i=0; $i < count($ArraySubRelEvt); $i++) {
				$List = $i + 1;
				echo "<tr><td style='width: 10px;'>".$List."</td><td>".$ArraySubRelEvt[$i]["Subject"]."</td><td>".$ArraySubRelEvt[$i]["Direction"]."</td><td>".$ArraySubRelEvt[$i]["Event"]."</td></tr>";} ?>
	</table>
	</div>

<!-- echo "<li>&nbsp;&nbsp;&nbsp;".$ArraySubRelEvt[1]["Subject"].$ArraySubRelEvt[$i]["Direction"].$ArraySubRelEvt[$i]["Event"]."</li>"; }?><br></td> -->

<div id="jargon-trans-box" onmouseout="hide_tooltip();">
	<div id="jargon-trans-header"></div>
	<div id="jargon-trans-body">
		<div id="jargon-trans-blank"></div>
                <div id="div_definition"><strong>Definition</strong></div>
		<div id="jargon-trans-url"><textarea name="jargon-trans-field" id="jargon-trans-field" class="field" rows="3" cols="50" readonly disabled="disabled"></textarea></div>
		<table align="right" style="margin-top: 10px; margin-right: 10px;">
			<tr>
				<td>
					<div id="wordlearn_alert"></div>
					<div id="div_favbuttonimg" align="right">
				</td>
				<td style="vertical-align: top;">
					<p><img id="favbuttonimg" src="" onclick="wordlearn_ajax(this.src);">
				</td>
			</tr>
		</table>
                    
                </div>
	</div>
</div>

	<table id="News_Details">
		<tr>
			<td>
				<b>Signal:</b> <b id=<?php echo $SignalWord; ?>><?php echo $SignalWord; ?></b><br><br>
				<b>Published on:</b> <?php echo $date; ?><br><br>
				<b>News category:</b> <?php echo $category; ?><br><br>
				<b>Affected Instrument(s):</b> <?php echo $affinstr; ?><br><br>
				<ul class='star-rating'>
					<li class='current-rating'> style='width:105px;' Currently 3.5/5 Stars.</li>
					<li><a href='#' title='1 star out of 5' class='one-star'>1</a></li>
					<li><a href='#' title='2 stars out of 5' class='two-stars'>2</a></li>
					<li><a href='#' title='3 stars out of 5' class='three-stars'>3</a></li>
					<li><a href='#' title='4 stars out of 5' class='four-stars'>4</a></li>
					<li><a href='#' title='5 stars out of 5' class='five-stars'>5</a></li>
				</ul>
				<div id="clickword" name="clickword"/>
					<br><input id="JargonControl" type="checkbox" onchange="AddDelFlagChart()"> Show Jargon
				</div>
			</td>
		</tr>
	</table>

</body>
</html>
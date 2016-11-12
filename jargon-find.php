<?php

SESSION_START();
if(!isset($_SESSION['username'])){
	header("Location:index.php");
}

    if(isset($_SERVER['SERVER_NAME'])) {
                if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false){
                        $hostname = 'ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com';
                        $username = 'user';
                        $password = 'password';
                        $dbName = 'ntrader';
                } else {
                        // must be localhost database connection
                        $hostname = 'localhost';
                        $username = 'root';
                        $password = NULL;
                        $dbName = 'nTrader';
                }
        }

    $PASSAGE_QUERY = "SELECT News_Content
                      FROM ntrader.m0004_news_archive
                      ORDER BY RAND() LIMIT 1;";

    $link = mysql_connect($hostname, $username, $password);
    mysql_select_db($dbName, $link);
    $result = mysql_query($PASSAGE_QUERY, $link);
        
    while($row = mysql_fetch_assoc($result)) {
        $PASSAGE_QUERY = $row['News_Content'];
    }


$Jargon_Reference = apc_fetch('HEADWORD_ARR');
$Jargon_Reference = array_change_key_case($Jargon_Reference,CASE_LOWER);

$news_data = $PASSAGE_QUERY;

$mod_jargon_passage = All_News_Jargon($news_data, $Jargon_Reference);

Function All_News_Jargon($news_content, $Jargon_Reference){
    
    $jargons = array();
    $j_placeholder = array();
    $Jargon_Plc = array();

    foreach ($Jargon_Reference as $Jargon_Set){

        if(preg_match('/'.$Jargon_Set['Pattern'].'/i', $news_content)){
            $news_content = preg_replace("/".$Jargon_Set['Pattern']."/i", "ooYYoo".preg_replace('/\s/', '_', $Jargon_Set['Headword'])."ooYYoo", $news_content);
            $jargons[$Jargon_Set['Headword']] = $Jargon_Set['Headword'];
        }

    }

    if(preg_match('/ooYYoo\S+?ooYYoo/', $news_content)){        
        
        preg_match_all('/ooYYoo(\S+?)ooYYoo/', $news_content, $Jargon_Plc, PREG_SET_ORDER);
        foreach($Jargon_Plc as $Jargon_Expr){
            $news_content = preg_replace("/".$Jargon_Expr[0]."/", '####', $news_content, 1);            
            $j_placeholder[] = $Jargon_Expr[0];
        }
    }   
    
    $news_content = str_replace('<p>', '%%%%', $news_content);
    $news_content = str_replace('</p>', '****', $news_content);
    $news_content = preg_replace('/(([\w\d\&\:-]+)(’s|\'s)?)/', '<span class="hide $1">$1</span>', $news_content);
    
    $plc_count = 0;
    
    while (strpos($news_content,'####') !== false) {

        $jargon_var_ = str_replace('ooYYoo', '', $j_placeholder[$plc_count]);
        $jargon_var = str_replace('_', ' ', $jargon_var_);
        
        $news_content = preg_replace('/####/', '<span class="hide '.$jargon_var_.'">'.$jargon_var.'</span>', $news_content, 1);
        
        $plc_count++;        
    }
    
    $news_content = str_replace('%%%%', '<p>', $news_content);
    $news_content = str_replace('****', '</p>', $news_content);    
        
    return array($news_content, $jargons);  //embedded news + jargon lists
    
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
    foreach ($html->find('div[id=story_head]') as $div){
        $Headline = $div->plaintext;
    }    
return array($Headline, $Content);
}

?>

<html>
    <head>
    
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
        <script src="http://code.jquery.com/jquery-latest.pack.js"></script>
        <style>
            .unhide1{
                background-color: #7CFC00; 
                border: 2px solid #CDD6DB;
                border-radius: 8px;
            }
            
             .unhide2{
                background-color: #DFCCCC;
                border: 2px solid #CDD6DB;
                border-radius: 8px;
            }
            
        </style>        
        <script type="text/javascript">        
            $(document).ready(function() {
                
                $('.hide').live('click', function(){
                    
                    if($(this).hasClass("hide")) {
                        
                        var orig_spanid = $(this).attr('class');
                        var spanid = '.' + orig_spanid;
                        var currspanid = spanid.replace(' ', '.');
                        var actualword = ($(this).text()).toUpperCase();
                        var classprefix = truthmoment(actualword);

                        classprefix.success(function (finalprefix){
                            if(finalprefix == "unhide1"){
                                var newsWC = document.getElementById('wcdisplay').innerHTML -1
                                var compscore = document.getElementById('compdisplay').innerHTML -1
                                if(compscore <= 0){
									alert("Oh...you won...Congrats!");
									window.location = "http://ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com/wordlearner.php"
                                }
                            	document.getElementById('wcdisplay').innerHTML =  newsWC;
                            	document.getElementById('compdisplay').innerHTML = compscore;
                            }else{
                                var UserLP = document.getElementById('lpdisplay').innerHTML -1
                                if(UserLP <= 0){
									alert("GAME OVER. Try harder next time");
									window.location = "http://ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com/wordlearner.php"
                                }
                            	document.getElementById('lpdisplay').innerHTML = UserLP;
                            }
	                        $(currspanid).removeClass(orig_spanid).addClass(finalprefix + orig_spanid.substr(4));//.addClass(newspanid); //should be current id                                                   
                        });
                    }
                });
                 
            });

            function truthmoment(wordinput){
                 return $.ajax({  
                	url: 'wordverify.php',  
                    data: {'wordpart': wordinput},
                    type: 'get'
                 });
            }
        </script>

	<a href=""><img style="position: absolute; margin-left: 3px;" src="Home/Images/nLearner.png"/></a>
	<a href="wordlearner.php" id="BackTo"><br>Back to WordLearn HQ</a>        
    </head>
    <body>
	<link rel="stylesheet" href="Home/CSS/jargon_find.css">
    
	<!-- news output PHP -->    
	<table id="Game_Info">
		<tr>
			<td id="icon">
				<img src="Home/Images/Jargon_Find/Win.png">
			</td>
			<td>
				<p>No. of Words left to win the game: </p>
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<h3><b id="compdisplay"></b></h3>
			</td>
		</tr>
		<tr>
			<td id="icon">
				<img src="Home/Images/Jargon_Find/Find.png">
			</td>
			<td>
				<p>Unique financial terms not found: </p>
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<h3><b id="wcdisplay"></b></h3>
			</td>
		</tr>
		<tr>
			<td id="icon">
				<img src="Home/Images/Jargon_Find/HP.jpg">
			</td>
			<td>
				<p>Life Points: </p>
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<h3><b id="lpdisplay"></b></h3>
			</td>
		</tr>
	</table>
	

	<script>
	
<?php 
	$gamemode = $_GET['gametype'];

	if($gamemode == "Beginner"){
		$compval = 0.5 * (count($mod_jargon_passage[1]));
		$compval = round($compval) -1;
		$life = 75;
	}elseif($gamemode == "Advanced"){
		$compval = 0.75 * (count($mod_jargon_passage[1]));
		$compval = round($compval) - 1;
		$life = 60;
	}elseif($gamemode == "Expert"){
		$compval = count($mod_jargon_passage[1]);
		$life = 50;
	}elseif($gamemode == "YOLO"){
		$compval = count($mod_jargon_passage[1]);
		$life = 1;
	}else{
		echo '<script type="text/javascript">
    	alert("Bad entrance!!!");
    	history.back();
		</script>';
	}

?>
		
		document.getElementById('compdisplay').innerHTML = <?php echo $compval;?>
		
		document.getElementById('wcdisplay').innerHTML = <?php echo count($mod_jargon_passage[1]);?>
		
		document.getElementById('lpdisplay').innerHTML = <?php echo $life;?>
	</script>
	<div id="news-house"><?php 
		echo $mod_jargon_passage[0];
	?></div>
    </body>
</html>
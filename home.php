<?php

SESSION_START();
if(!isset($_SESSION['username'])){
	header("Location:index.php");
}

error_reporting(E_ALL ^ (E_NOTICE));
$country_iso = apc_fetch('COUNTRY_ISO');

if(isset($_GET['start'])) {
    /* spit out the posts within the desired range */  
    echo get_posts($_GET['start'],$_GET['desiredPosts']);
    $_SESSION['posts_start']+= $_GET['desiredPosts'];  
    /* kill the page */  
    exit();
}

    if(isset($_GET['news_type'])&&isset($_GET['term'])&&isset($_GET['by_content'])&&isset($_GET['regex_search'])&&isset($_GET['country_search'])&&isset($_GET['search_start_date'])&&isset($_GET['search_end_date'])&&isset($_GET['goodnews'])&&isset($_GET['badnews'])&&isset($_GET['neutralnews'])){
        
        $search_start = $_GET['search_start'];
        $desired_num_post = $_GET['desired_num_post'];
	$byAffectInstr = $_GET['affected_instr'];
        $news_type = $_GET['news_type'];
        $term = $_GET['term'];
        if($_GET['by_content']=='false'){$by_content = false;}else{$by_content = true;}
        if($_GET['regex_search']=='false'){$regex_search = false;}else{$regex_search = true;}
        $country_search = $_GET['country_search'];
        $search_start_date = $_GET['search_start_date'];
        $search_end_date = $_GET['search_end_date'];
        if($_GET['goodnews']=='false'){$goodnews = false;}else{$goodnews = true;}
        if($_GET['badnews']=='false'){$badnews = false;}else{$badnews = true;}        
        if($_GET['neutralnews']=='false'){$neutralnews = false;}else{$neutralnews = true;}         
        
        $bySQLString = array();        
        
        if(isset($_GET['refresh_flag'])&&$_GET['refresh_flag']){
            $search_start = 0;
            $desired_num_post = 5;            
            $load_result = $_GET['refresh_flag'];
            $qry_news_srh = load_search_news($search_start,$desired_num_post,$news_type,$term,$by_content,$regex_search,$country_search,$search_start_date,$search_end_date,$goodnews,$badnews,$neutralnews,$byAffectInstr,$bySQLString);
        }else{
            $load_result = false;
            echo json_encode(load_search_news($search_start,$desired_num_post,$news_type,$term,$by_content,$regex_search,$country_search,$search_start_date,$search_end_date,$goodnews,$badnews,$neutralnews,$byAffectInstr,$bySQLString));                    
        }        
        $_SESSION['posts_start'] = $search_start;
        $number_of_posts = $desired_num_post;
        if(!$load_result){
            exit();            
        }

    }
    if(isset($_POST['start_searchpost_count'])&&isset($_POST['end_searchpost_count'])&&isset($_POST['SQLstring'])){
        echo json_encode(load_search_news($_POST['start_searchpost_count'],$_POST['end_searchpost_count'],'','','','','','','','','','','',$_POST['SQLstring']));
        $_SESSION['posts_start']+= $_POST['end_searchpost_count'];
        exit();
    }

Function load_search_news($start_post = 0, $num_of_news = 10, $news_type = '', $search_term = '', $by_content = false, $by_regex = false, $by_country = '', $start_date = '', $end_date = '', $goodnews = false, $badnews = false, $neutralnews = false, $Affected_Instr = '', $search_settings = array()){

        if(isset($_SERVER['SERVER_NAME'])) { 
                    if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false){ 
                            $hostname = 'ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com';
                            $username = 'nTrader';
                            $password = '';
                            $dbName = 'ntrader';
                    } else {
                            // must be localhost database connection 
                            $hostname = 'localhost';
                            $username = 'root';
                            $password = NULL;
                            $dbName = 'nTrader';
                    } 
            }                    

if(count($search_settings)==0){
    $search_settings = array($news_type,$search_term,$by_content,$by_regex,$by_country,$start_date,$end_date,$goodnews,$badnews,$neutralnews,$Affected_Instr);
}

$news_type = $search_settings[0];
$search_term = $search_settings[1];
$by_content = $search_settings[2];
$by_regex = $search_settings[3];
$by_country = $search_settings[4];
$start_date = $search_settings[5];
$end_date = $search_settings[6];
$goodnews = $search_settings[7];
$badnews = $search_settings[8];
$neutralnews = $search_settings[9];
$Affected_Instr = $search_settings[10];
            
        $searched_news = array();
        $number_of_results = 0;
        $sql_string = "";
        //number of search results in pages?
        //news array
        //sql string

                    $search_date = "";
                    $search_content = "";
                    $search_by_term = "";
                    $search_country = "";
                    $search_by_regex = "";
                    $search_category = "";
                    $search_regex_content = "";
                    $search_affected_instrument = "";
                    $sentiment_query = "";

                    if($goodnews){
                        if($sentiment_query==""){
                            $sentiment_query .= " AND (t1.NegPos_Ind > 0 ";
                        }else{
                            $sentiment_query .= " OR t1.NegPos_Ind > 0 ";            
                        }
                    }
                    if($badnews){
                        if($sentiment_query==""){
                            $sentiment_query .= " AND (t1.NegPos_Ind < 0 ";
                        }else{
                            $sentiment_query .= " OR t1.NegPos_Ind < 0 ";            
                        }
                    } 
                    if($neutralnews){
                        if($sentiment_query==""){
                            $sentiment_query .= " AND (t1.NegPos_Ind = 0 ";
                        }else{
                            $sentiment_query .= " OR t1.NegPos_Ind = 0 ";            
                        }
                    } 
                    
                    if($sentiment_query!=""){
                        $sentiment_query = $sentiment_query . " ) ";
                    }
                    
                    if(trim($news_type)==''){
                        $search_category = " -- ";
                    }    

                    if(trim($start_date)==''||trim($end_date)==''){     //date values or ''
                        $search_date = " -- ";
                    }
                    if(trim($search_term)!='' && !$by_regex){
                        if(!$by_content){
                        $search_content = ") -- ";
                        }
                        
                        $search_by_regex = " -- ";
                    }else{
                        $search_by_term = " -- ";
                    }
                    if(trim($search_term)!='' && $by_regex){
                        if(!$by_content){
                        $search_regex_content = ") -- ";
                        }
                        $search_term = FormatRegexPtrn($search_term);
                        $search_by_term = " -- ";
                    }else{
                        $search_by_regex = " -- ";
                    }
                    if(trim($by_country)==''){
                        $search_country = " -- ";
                    }
                    if(trim($Affected_Instr)==''){
                        $search_affected_instrument = " -- ";                        
                    }

                    $news_query = "SELECT SQL_CALC_FOUND_ROWS t1.ID, t1.Date, t1.Headline, t1.News_Category, t1.Affected_Instr, t1.NegPos_Ind, t1.News_URL, t1.Release_date, t1.Intro_Paragraph \r\n
                                   FROM m0004_news_archive t1, m0001_subject_instrument t2 \r\n
                                   WHERE t1.Affected_Instr = t2.Instrument_Code \r\n
                                   $search_country AND t2.Subject = '$by_country' \r\n
                                   $search_category AND t1.News_Category = '$news_type' \r\n
                                   $search_affected_instrument AND t1.Affected_Instr = '$Affected_Instr' \r\n
                                   $search_by_term AND (t1.Headline LIKE '%$search_term%' $search_content OR t1.News_Content LIKE '%$search_term%' ) \r\n
                                   $search_by_regex AND (t1.Headline REGEXP '$search_term' $search_regex_content OR t1.News_Content REGEXP '$search_term' ) \r\n
                                   $search_date AND t1.Date between '$start_date' and '$end_date' \r\n
                                   $sentiment_query";

                    $sql_string = $news_query;
                    $news_query .= " ORDER BY t1.Release_date DESC LIMIT $start_post, " . $num_of_news.";";                    
                    
        
        $link = mysql_connect($hostname, $username, $password);
        mysql_select_db($dbName, $link);
        $result = mysql_query($news_query, $link);        
        
        while($row = mysql_fetch_assoc($result)) {
            $searched_news[] = $row;
        }        
        
        $num_rows = mysql_fetch_assoc(mysql_query("SELECT FOUND_ROWS() as num_of_results;", $link));    
        $number_of_results = $num_rows['num_of_results'];

        mysql_close($link);

        $DB_SEARCH_NEWS = array($searched_news, $number_of_results, $search_settings);
        
        return $DB_SEARCH_NEWS;
    }
Function get_posts($start = 0, $number_of_posts = 5) {

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

    /* connect to and select the db */  
    $connection = mysql_connect($hostname,$username,$password ); //hostname, username, password  
    mysql_select_db($dbName,$connection);  
    /* create the posts array */  
    $posts = array();  
    /* get the posts */  
    $query = "SELECT ID, Date, Headline, News_Category, Affected_Instr, NegPos_Ind, News_URL, Release_date, Intro_Paragraph 
              FROM m0004_news_archive
              ORDER BY Release_date DESC LIMIT $start,$number_of_posts"; /* */
    
    $result = mysql_query($query);
    while($row = mysql_fetch_assoc($result)) {
	
        $posts[] = $row;  
    }
    /* return the posts in the JSON format */  
    return json_encode($posts);
}

$number_of_posts = 5; //5 posts will load at a time
//$_SESSION['posts_start'] = isset($_SESSION['posts_start']) ? $_SESSION['posts_start'] : 
$number_of_posts;
$_SESSION['posts_start'] = $number_of_posts;
?>

<html> 
<head>
	<title>News</title>
	<meta name="description" content="Educate yourself with nTrader's dynamic Jargon translation utility, 
and the dynamics of the financial market place."/>
	<link rel="shortcut icon" href="Images/browser.ico" />
	<link rel="stylesheet" href="Home/CSS/home.css">
	<link rel="stylesheet" href="Home/CSS/standard.css">
</head>
<body>
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="Home/JAVA/mootools.1.2.3.js"></script>
	<script type="text/javascript" src="Home/JAVA/VerticalSlider.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
    			$(".trigger").click(function(){
        			$(".panel").toggle("fast");
        			$(this).toggleClass("active");
        			return false;
    			});

		});
	</script>

	<script type="text/javascript">
		function AddImage()
		{
                    document.getElementById("table-foreground").innerHTML = "<img id='myimage' src='Home/Images/table-foreground.png'>";
                    document.getElementById("myimage").ondragstart = function() { return false; };
		}
	</script>

<div class="wrap">

	<div id="Header_info">
	<a href="home.php"><img style="position: absolute; margin-left: 10px;" src="Home/Images/Logo.png"/></a>
	<table id ='Header'><tr><td id='HeaderCol' style="font-weight:bold; font-family:arial; text-align:right;">Welcome, <?php echo $_SESSION['username']; ?></td></tr>
	<tr><td style="text-align: right;">

	<!--
	<button  type='button' id='AccSettings' onclick=''>Settings</button>
	<button  type='button' id='logout-btn' onclick='logoutnow();'>Logout</button>
	-->
	<div id="user-controls" style="float: right;">
	<a href="#" class="button icon AccSettings"><span>Settings</span></a>
	<a href="logout.php" class="button icon logout-btn"><span>Logout</span></a>
	</div>
	</td></tr></table>
	</div>
	<div id='cssmenu'>
		<ul>
   			<li class='active'><a href='home.php'><span><img id="Icon" src="Home/Images/nIndicateIcon.png">&nbsp;&nbsp;&nbsp;&nbsp;News</span></a></li>
      			<li class='has-sub '><a href='bi_1.php'><span>nIntel</span></a>
      			<ul>
         			<li><a href='bi_1.php'><span>Graph</span></a></li>
         			<li><a href='bi_2.php'><span>Map</span></a></li>
      			</ul>
   			</li>
   			<li><a href='wordlearner.php'><span>WordLearn HQ</span></a></li>
   			<li><a href='#'><span>Help</span></a></li>
			<li><a href='index.php'><span>Back to Main</span></a></li>
		</ul>
	</div>	

	<script type="text/javascript">
		var slider2=new accordion.slider("slider2");
		slider2.init("slider2",0,"open");
	</script>


            <script type="text/javascript">

var Searched_SQL = [];
    
        <?php


if(!$load_result){

$bloomberg_URL_main = "http://www.bloomberg.com";
$bloomberg_URL_news = "http://www.bloomberg.com/archive/news/";

//Constant pattern
$News_filter = "/" . "stocks" . "/i";

//APC fetch constant variables
$Reason_pattern = apc_fetch('REASON_PATTERN');
$Subject_pattern = "/" . apc_fetch('SUBJECT_PATTERN') . "/";
$Direction_pattern = "/" . apc_fetch('DIRECTION_PATTERN') . "/i";	
$Instr_Code = apc_fetch('INSTRUMENT_POSITION_ARRAY');
$Dir_Keys = apc_fetch('DIRECTION_POSITION_ARRAY');
$Dir_Keys_Type = apc_fetch('DIRECTION_KEY_TYPES');
$Todays_News = array();

$ins_values_str = '';

for($xyz=1;$xyz<=1;$xyz++){

$news_date = date("Y-m-d");

//matches all headline stories with hyperlink to full article & headline title
preg_match_all('/<a\s+href=[\'"]?([^\s\>\'"]*)[\'"\>]\s*>([\s\S]+?)<\/a>/i', GetBloombergHeadlines($bloomberg_URL_news . $news_date), $matches, PREG_SET_ORDER);

//printing, will get only news with the filter in part of headlines, but not part of reason
for($i=0;$i<count($matches);$i++){        
    if(preg_match($News_filter, preg_replace($Reason_pattern, '', $matches[$i][2]))){

        $headline = trim($matches[$i][2]);
        $URL = $bloomberg_URL_main . $matches[$i][1];
        $newsParts = explode(';', trim($matches[$i][2]));
        foreach($newsParts as $newsItem){
            $Trading_Indicator_Instrument_Code = GenerateTradingSignal($newsItem, $matches[$i][1]);
            $Trading_Ind = $Trading_Indicator_Instrument_Code[0];//trading indicator
            $Instrument_Code_Arr = $Trading_Indicator_Instrument_Code[1];//Instrument code list
            if(count($Instrument_Code_Arr)>0){
                foreach($Instrument_Code_Arr as $Instr_Code_val){
                    //create insert values                    
                    $news_date;
                    $URL;
                    $headline;
                    $News_Cat = $News_filter;

                    $News_Cat[strlen($News_filter)-1] = '';
                    $News_Cat[strlen($News_filter)-2] = '';
                    $News_Cat[0] = '';
                    
                    $Trading_Ind;
                    
                    $Todays_News[] = array('ID'=>$news_date . count($Todays_News), 'Date'=>$news_date,'Headline'=>$headline,'News_Category'=>$News_Cat,'Affected_Instr'=>$Instr_Code_val,'NegPos_Ind'=>$Trading_Ind,'News_URL'=>$URL, 'Release_date'=>'Today\'s News', 'Intro_Paragraph'=>'Current day introduction unavailable');

                }
            }            
        }
    }
}

}

}

Function GenerateTradingSignal($Headline, $HeadlineURL){

    $Direction_Applied = false;	
    
    global $Reason_pattern, $Subject_pattern, $Direction_pattern, $Instr_Code, $Dir_Keys,$News_filter, $Dir_Keys_Type;
    $newsSubject = preg_replace($Reason_pattern, '', $Headline);
    
    preg_match_all($Subject_pattern, $newsSubject, $Subject_Matches, PREG_SET_ORDER);
    preg_match_all($Direction_pattern, $newsSubject, $Direction_Matches, PREG_SET_ORDER);

	foreach($Subject_Matches as $i => $value){
		$Subject_Pos = array_keys(preg_grep('/^\s*$/', $Subject_Matches[$i], PREG_GREP_INVERT));
        foreach($Subject_Pos as $POS){
            if($POS != 0){
                //currently only match for macro stocks pattern
                if($News_filter == "/" . "stocks" . "/i"){
                    if(preg_match('/'.RegexWordMatch(FormatRegexPtrn($Subject_Matches[$i][$POS])).'\s+\b[sS]tocks\b/',$newsSubject)){
                        $Instr_Code_Arr[] = $Instr_Code[$POS];
                                                
                    }elseif(preg_match('/^\b[sS]tocks\b/',$newsSubject)){
                        //this news is a global stock news
                    }
                }
            }
        }        
	}
    
    //Initializing BullBear_Indicator
    if(count($Direction_Matches)>=1){
    $BullBear_Ind = 1;
    }else{
    $BullBear_Ind = 0;     
    }
	
	foreach($Direction_Matches as $i => $value){
		$Direction_Pos = array_keys(preg_grep('/^\s*$/', $Direction_Matches[$i], PREG_GREP_INVERT));

            foreach($Direction_Pos as $POS){

                if($POS!=0){

		    if(($Dir_Keys_Type[$POS]=='Direction' && !$Direction_Applied)||$Dir_Keys_Type[$POS]=='Modification'){
		    
                    $BullBear_Ind = $BullBear_Ind * $Dir_Keys[$POS];

			if($Dir_Keys_Type[$POS]=='Direction'){
			    $Direction_Applied = true;
			}
		    }
                }
            }
	}

    return array($BullBear_Ind, $Instr_Code_Arr);    //True or False Value -> True means Bull, False means Bear
}

Function GetBloombergHeadlines($url) {
//This function get bloomberg headline stories in string format with the HTML <a> tag remains

    //get bloomberg archive stories as html anchor tag string
    $context = stream_context_create(array('http' => array('header'=>'Connection: close ')));
    $URLsource =  file_get_contents($url,false, $context);    
    preg_match ("/<ul class=\"stories\">([\s\S]+?)<\/ul>/i",$URLsource,$matches);
    $HTML_HeadLines = preg_replace("/<li>|<\/li>/i","",$matches[1]);
    
    return($HTML_HeadLines);    
}

Function RegexWordMatch($RegexPattern){
    if(trim($RegexPattern) != ""){
        if(preg_match('/\W/',$RegexPattern[0])){
            $RegexPattern = "\B".$RegexPattern; 
        }else{
            $RegexPattern = '\b'.$RegexPattern;             
        }        
        if(preg_match('/\W/',$RegexPattern[strlen($RegexPattern)-1])){
            $RegexPattern = $RegexPattern.'\B'; 
        }else{
            $RegexPattern = $RegexPattern.'\b';             
        }        
    }    
    Return $RegexPattern;
}

Function FormatRegexPtrn($RegexPattern){
    $pattern = '/\\|\.|\$|\+|\*|\?|\[|\]|\(|\)|\/|\||\{|\}|\'|\#/';
    
    preg_match_all($pattern, $RegexPattern, $matches, PREG_SET_ORDER);
    $matches = array_unique($matches);
    foreach($matches as $match){
        $RegexPattern = preg_replace("/\\".$match[0]."/", "\\\\".$match[0], $RegexPattern);
    }    
    return $RegexPattern;
}

Function GetNewsContent($newsURL){
//Get news content with URL

    include_once('/Libraries/simple_html_dom.php');
    $Content = "";	

    $html=file_get_html($newsURL);
    foreach ($html->find('div[id=story_display]') as $div){
        foreach($div->find('p') as $p){
	    $Content .= '<p>'.$p->plaintext.'</p>';
        }
    }

return $Content;

}
        
?>        

	$(function() {

            var baseDomain = 'http://localhost/nTrader/Home/';
            var postHandler = function(postsJSON, scrollingFlag) {  
                $.each(postsJSON,function(i,post) {

                    var ID = post['ID'];
                    var news_url = post['News_URL'];
                    var news_headline = post['Headline'];
                    var news_date = post['Date'];
                    var news_category = post['News_Category'];
                    var affected_instr = post['Affected_Instr'];
                    var NegPos_Ind = post['NegPos_Ind'];
                    var Release_date = post['Release_date'];
                    var intro_para = post['Intro_Paragraph'] + '<br/>';

                    news_url = news_url + '&signal=' + NegPos_Ind + '&date=' + Release_date + '&category=' + news_category + '&affinstr=' + affected_instr 
                    
                    if(NegPos_Ind>0){
                        var NegPosNeu = "Bull";
                    }else if(NegPos_Ind<0){
                        var NegPosNeu = "Bear";
                    }else{
                        var NegPosNeu = "Neutral";
                    }
                    
                    if(i==(postsJSON.length-1)){
                        var divObj = $('<div style="border-bottom-style: dashed;border-bottom-color: 2px solid #ccc;"></div>');
                    }else{
                        var divObj = $('<div></div>');
                    }
                    divObj.addClass('post');
                    divObj.attr('id','News-'+ID);
                    divObj.html('<table style="width: 100%;"><tr>\n\
					<td class="' + NegPosNeu + '">' + NegPosNeu + '</td>\n\
                                        <td class="newscell" style="padding-right: 10px;">\n\
                                            <a href="jargon_edu.php?content_url=' + news_url+ '" target="_blank" class="post-title">' + news_headline + '</a>&nbsp;&nbsp;<a class="Release_date">' + Release_date + '</a>\n\
                                            <p class="post-content">' + intro_para + '<a href="jargon_edu.php?content_url=' + news_url+ '" target="_blank" class="post-more">Read more...</a></p>\n\
                                        </td>\n\
                                        <td class="post-info">\n\
                                            <p><span class="news_category">News category: </span> \n\
                                                ' + news_category + '<br><p>\n\
                                                <span class="affected_instr">Affected Instrument(s): </span> \n\
                                                <a href="bi_1.php?query_instr='+ affected_instr + '">' + affected_instr + '</a>\n\
                                        </td>\n\
                                        </tr>\n\
                                </table>'); 
                    divObj.click(function() {
                        //window.open(news_url,'popUpWindow','height=700,width=800,left=10,top=10,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes');
                        //bi_1.php?query_instr=affected_instr
                    })
                    divObj.hide();
                    divObj.slideDown(250,function() {
                    });
                    divObj.appendTo($('#posts'));
                        if(scrollingFlag){
                            if(i == 0) {
                                var scroll = function() {  
                                    new Fx.Scroll(window).toElement('News-' + ID);  
                                };
                                scroll.delay(300);
                            }  
                        }
                });  
            };
 
            var load_result = <?php echo json_encode($load_result); ?>;
            var start = <?php echo $_SESSION['posts_start']; ?>;
            var desiredPosts = <?php echo $number_of_posts; ?>;             

            if(load_result){
                
                var searched_news_array = eval(<?php echo json_encode($qry_news_srh); ?>);
                var searched_news = searched_news_array[0];
                var num_results_searched = searched_news_array[1];
                Searched_SQL = searched_news_array[2];                                

                postHandler(searched_news,false);                

            }else{
                var todays_posts = <?php echo json_encode($Todays_News); ?>;            
                if(todays_posts.length>desiredPosts){
                    start = start - desiredPosts;
                    postHandler(todays_posts,false);
                }else{
                    var initialPosts = <?php echo get_posts(0,$_SESSION['posts_start']); ?>;
                    postHandler(todays_posts,false);
                    postHandler(initialPosts,false);
                }            
            }
            
            var loadMore = $('#load-more');  
            var narrow_search_submit = $('#narrow_search_submit');
            var simple_search_text = $('#simple_search_text');
            
            simple_search_text.keypress(function(event) {
                if(event.which==13){    //if enter key was press
                    var term = document.getElementById('simple_search_text').value;
                    var news_type = '';
                    var by_content = true;    
                    var regex_search = false;
                    var country_search = '';
                    var search_start_date = '';
                    var search_end_date = '';
                    var goodnews = false;
                    var badnews = false;
                    var neutralnews = false;

                    start = 0;
                    desiredPosts = 5;

                    $.ajax({  
                        url: '',  
                        data: {'search_start': start, 'desired_num_post': desiredPosts, 'news_type': news_type, 'term': term, 'by_content': by_content, 'regex_search': regex_search, 'country_search': country_search, 'search_start_date': search_start_date, 'search_end_date': search_end_date, 'goodnews': goodnews, 'badnews': badnews, 'neutralnews': neutralnews},
                        type: 'get',
                        success: function(AJAX_RESP){                                               

                            $('#posts').empty();
                            var searched_news_array = eval(AJAX_RESP);
                            var searched_news = searched_news_array[0];
                            var num_results_searched = searched_news_array[1];
                            Searched_SQL = searched_news_array[2];
			    document.getElementById('results-obtained').innerHTML= num_results_searched + " results found!";
			    $("#results-obtained").css("visibility", "visible");
                            postHandler(searched_news,true);
                        },
                        error: function() {  
			    
                        },  
                        complete: function() {
			    
                        }
                    });                                    
                }
            });
            
            narrow_search_submit.click(function(){

                var news_type = document.getElementById('news_type').value;
                var term = document.getElementById('term').value;
                var by_content = (document.getElementById('by_content').checked);    
                var regex_search = (document.getElementById('regex_search').checked);
                var country_search = document.getElementById('country_search').value;
                var search_start_date = document.getElementById('search_start_date').value;
                var search_end_date = document.getElementById('search_end_date').value;
                var goodnews = (document.getElementById('goodnews').checked);
                var badnews = (document.getElementById('badnews').checked);
                var neutralnews = (document.getElementById('neutralnews').checked);

                start = 0;
                desiredPosts = 5;

                $.ajax({  
                    url: '',  
                    data: {'search_start': start, 'desired_num_post': desiredPosts, 'news_type': news_type, 'term': term, 'by_content': by_content, 'regex_search': regex_search, 'country_search': country_search, 'search_start_date': search_start_date, 'search_end_date': search_end_date, 'goodnews': goodnews, 'badnews': badnews, 'neutralnews': neutralnews},
                    type: 'get',
                    success: function(AJAX_RESP){                                               
                        
                        $('#posts').empty();
                        var searched_news_array = eval(AJAX_RESP);
                        var searched_news = searched_news_array[0];
                        var num_results_searched = searched_news_array[1];
                        Searched_SQL = searched_news_array[2];
			document.getElementById('results-obtained').innerHTML= num_results_searched + " results found!";
			$("#results-obtained").css("visibility", "visible");
                        postHandler(searched_news,true);
                    },
                    error: function() {  

                    },  
                    complete: function() {

                    }
                });                
            });           
            
            loadMore.click(function(){  
                //move this code//////
                if(Searched_SQL.length==0){
                    //add the activate class and change the message
                    loadMore.addClass('activate').text('Loading...');
                    //begin the ajax attempt
                    $.ajax({  
                        url: '',  
                        data: {'start': start, 'desiredPosts': desiredPosts},
                        type: 'get',
                        success: function(AJAX_RESP) {

                            //increment the current status
                            start += desiredPosts;
                            archived_news = eval(AJAX_RESP);

			    if(archived_news.length==0){
                            	loadMore.text('Nothing To Load');
			    }else{
                            	loadMore.text('Load More');				
			    }

                            postHandler(archived_news,true);
                        },
                        //failure class  
                        error: function() {  
                            //reset the message  
                            loadMore.text('Oops! Try Again.');  
                        },  
                        complete: function() {
                            //remove the spinner  
                            loadMore.removeClass('activate');  
                        }
                    });
                }else{
                    //add the activate class and change the message
                    loadMore.addClass('activate').text('Loading...');
                    //begin the ajax attempt
                    $.ajax({
                        url: '',
                        data: {'start_searchpost_count': start, 'end_searchpost_count': desiredPosts, 'SQLstring': Searched_SQL},
                        type: 'post',
                        success: function(AJAX_RESP) {

                            start += desiredPosts;                                                 
      

                            var searched_news_array = eval(AJAX_RESP);
                            var searched_news = searched_news_array[0];
                            var num_results_searched = searched_news_array[1];
                            Searched_SQL = searched_news_array[2];
			    document.getElementById('results-obtained').innerHTML= num_results_searched + " results found!";
			    $("#results-obtained").css("visibility", "visible");
			    if(searched_news.length==0){
                            	loadMore.text('Nothing To Load');
			    }else{
                            	loadMore.text('Load More');				
			    }
                            
                            postHandler(searched_news,true);
                            
                        },
                        //failure class  
                        error: function() {  
                            //reset the message  
                            loadMore.text('Oops! Try Again.');  
                        },  
                        complete: function() {
                            //remove the spinner  
                            loadMore.removeClass('activate');  
                        }
                    });                    
                }
                //move this code/////////
            });              
});

            </script>
            <script>
            function logoutnow(){
            	window.location = "http://ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com/logout.php"
            }
            </script>

<div id="body-news-content">
	<div id="results-obtained" valign=middle style="position: absolute;top:24px;width:300px;margin-left:-150px;border:1px solid #F0C36D;left:50%;padding:5px;border-radius:5px;text-align:center;background-color:#F9EDBE;font-weight:bold;visibility:hidden;font-size: 12px;">
	</div>
	<img src="Home/Images/Refresh.png" id="Refresh" onClick="document.location.reload(true)"/>
	<div class="lighter">
		<input type="text" id="simple_search_text" class="search rounded" placeholder="Search news">             
	</div>
	<div id="accordion2">
		<dl class="accordion2" id="slider2">
			<dt>Advance Search</dt>
			<dd><br>
				<table id="Filter">
				<tr>
					<td VALIGN="TOP" id="Title">Keywords:</td>
					<td>
					<input id="term" class="standard-textinput" type="text" size="20" placeholder="E.g. Stocks"><br><br>
					</td>
				</tr>
				<tr><td VALIGN="TOP" id="Title">Search:&nbsp;</td>
					<td>
                                    	<label><input id="by_content" class="standard-checkbox" type="checkbox" name="Content">&nbsp;Content</label><br>
					<label><input id="regex_search" class="standard-checkbox" type="checkbox" name="Regex">&nbsp;Regular Expression</label><br><br><br>
					</td>
				</tr>
				<tr><td VALIGN="TOP" id="Title">Type:</td>
				<td>



				<label/><select class="standard-select" id="news_type" name="type" >
  						<option value="">--All Instruments--</option>
  						<option value="Stocks">Stocks</option>
  					</select><br><br><br>

				</td></tr>
				<tr><td VALIGN="TOP" id="Title">Country:</td>
				<td>
				<label/><select class="standard-select" id="country_search" name="country" >
  						<option value="">--All Countries--</option>
                                                <?php
                                                foreach($country_iso as $country_nm){
                                                    echo '<option value="'.$country_nm.'">'.$country_nm.'</option>';
                                                }?>
  					</select><br><br><br>
				</td></tr>
				<tr><td VALIGN="TOP" id="Title">Date:</td>
				<td>
				<br><br>
				</td>
				</tr>
				<tr>
				<td style="padding-left:20px;">
				From
				</td>
				<td>
				<input id="search_start_date" class="standard-date" type="date">										
				</td>
				<tr>
				<td style="padding-left:20px;">To</td>
                                <td><input id="search_end_date" class="standard-date" type="date"></td>
				</tr>
				<tr><td colspan="2"><br><br></td></tr>
				</tr>
				<tr><td VALIGN="TOP" id="Title">Indicators:</td>
				<td>
					<label><input id="goodnews" class="standard-checkbox" type="checkbox" name="Bull">&nbsp;Bull</label><br>
					<label><input id="badnews" class="standard-checkbox" type="checkbox" name="Bear">&nbsp;Bear</label><br>
					<label><input id="neutralnews" class="standard-checkbox" type="checkbox" name="Neutral">&nbsp;Neutral</label>
				</td></tr>
				<tr>
				<td colspan="2">
				<p id="FilterSubmit"><br><input class="FilterSubmit" type="submit" value="Search" id="narrow_search_submit" ><br><br><p>
				</td>
				</tr>
				</table>
			</dd>
			<!-- <dt>Type</dt>
			<dd>
				<span></span>
			</dd> -->
		</dl>
	</div>
        <div id="posts-container" onclick="">
            <!-- Posts go inside this DIV -->  
            <div id="posts"></div>  
            <!-- Load More "Link" -->  
            <div id="load-more">Load More</div>
        </div>
    </div>	
</div>
    </body>
</html>
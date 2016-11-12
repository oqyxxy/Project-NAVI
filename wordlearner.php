<?php

SESSION_START();

if(!isset($_SESSION['username'])){
	header("Location:index.php");
}

if(isset($_POST['search'])&&isset($_POST['escape'])&&isset($_POST['wildmatch'])&&isset($_POST['numeric'])){
    
    if($_POST['escape_string']=='true'){$escape_string = true;}else{$escape_string = false;}
    if($_POST['wildmatch']=='true'){$wildmatch = true;}else{$wildmatch = false;}
    if($_POST['numeric']=='true'){$numeric = true;}else{$numeric = false;}
        
    $search_results = search_financial_lexicon($_POST['search'], $escape_string, $wildmatch, $numeric);

    echo json_encode($search_results);
    
    exit();
    
}

if(isset($_POST['settings'])&&isset($_POST['start'])){

    $searchsettings = $_POST['settings'];
    
    $search_term = $searchsettings[0];
    $escape_string = $searchsettings[1];
    $wildmatch = $searchsettings[2];
    $num_beginning = $searchsettings[3];    
    
    if($escape_string=='true'){$escape_string = true;}else{$escape_string = false;}
    if($wildmatch=='true'){$wildmatch = true;}else{$wildmatch = false;}
    if($num_beginning=='true'){$numeric_var = true;}else{$numeric_var = false;}
    
    echo json_encode(search_lexicon_pages(array($search_term,$escape_string,$wildmatch,$numeric_var), $_POST['start']));
    exit();
}

        if(isset($_SERVER['SERVER_NAME'])){
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

$favourite_words = array();
$word_of_the_day = array();

        $query = "SELECT t2.Headword as headword, COUNT( * ) AS times_favourite, t2.Ref_URL
                  FROM m0006_User_Word_relation t1, m0002_financial_lexicon t2
                  WHERE t1.WID = t2.WID
                  GROUP BY t1.WID
                  ORDER BY 2 DESC 
                  LIMIT 10;";

          $conn = mysql_connect($hostname, $username, $password);
          mysql_select_db($dbName, $conn);

          $result = mysql_query($query, $conn);                
          while($row = mysql_fetch_assoc($result)) {
              $favourite_words[] = $row;
          }

        $query = "SELECT t1.WID AS WID, t1.Word_of_day as Word, 
                  CASE
                      WHEN t2.Relation = 0 THEN
                      t2.Definition
                      ELSE
                     (SELECT Headword FROM m0002_financial_lexicon Where WID = t2.Relation)
                  END as Definition
                  FROM m0007_daily_word t1, m0002_financial_lexicon t2
                  WHERE t1.ID = (
                  SELECT MAX( ID )
                  FROM m0007_daily_word )
                  AND t1.WID = t2.WID";

          $result = mysql_query($query, $conn);                
          while($row = mysql_fetch_assoc($result)) {
              $word_of_the_day = $row;
          }

        mysql_close($conn);

Function search_lexicon_pages($searchsettings, $startresult){
    
    $search_term = $searchsettings[0];
    $escape_string = $searchsettings[1];
    $wildmatch = $searchsettings[2];
    $num_beginning = $searchsettings[3];
    
    if(isset($_SERVER['SERVER_NAME'])) { 
                if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false){ 
                        $hostname = 'ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com';
                        $username = 'nTrader';
                        $password = '';
                        $dbName = 'ntrader';
                } else {
                        $hostname = 'localhost';
                        $username = 'root';
                        $password = NULL;
                        $dbName = 'nTrader';
                }
    }    

    if($escape_string){
        $mod_string = str_replace('%', '\\%', $search_term);
        $mod_string = str_replace('_', '\\_', $search_term);
    }else{
        $mod_string = $search_term;
    }
    
    if($wildmatch){
        $mod_string = "%" . $mod_string . "%";
    }
    
    $query = "SELECT SQL_CALC_FOUND_ROWS Headword,
                     CASE
                     WHEN t1.Relation <> 0 THEN
                        (SELECT t2.Definition
                        From m0002_financial_lexicon t2 
                        WHERE WID=t1.Relation)
                     ELSE
                        t1.Definition
                     END AS Definition,
                     Ref_URL AS Source,
		     t1.WID AS WID
              FROM m0002_financial_lexicon t1
              WHERE t1.Headword LIKE '" . $mod_string ."'
              ORDER BY 1
              LIMIT " . "$startresult" . ", 10;";
    
    if($num_beginning){
        $query = "SELECT SQL_CALC_FOUND_ROWS Headword,
                         CASE
                         WHEN t1.Relation <> 0 THEN
                            (SELECT t2.Definition
                            From m0002_financial_lexicon t2 
                            WHERE WID=t1.Relation)
                         ELSE
                            t1.Definition
                         END AS Definition,
                         Ref_URL AS Source,
			 t1.WID AS WID
                  FROM m0002_financial_lexicon t1
                  WHERE t1.Headword REGEXP '[#0-9].*'
                  LIMIT $startresult, 10;";
        
    }
    
    $conn = mysql_connect($hostname, $username, $password);
    mysql_select_db($dbName, $conn);
    
    $result = mysql_query($query, $conn);
    while($row = mysql_fetch_assoc($result)) {
        $search_results[] = $row;
    }
    
    $num_rows = mysql_fetch_assoc(mysql_query("SELECT FOUND_ROWS() as num_of_results;", $conn));
    $num_of_results = $num_rows['num_of_results'];
    
    mysql_close($conn);
    
    return array($search_results,$num_of_results, $query);    
    
}
        
Function search_financial_lexicon($search_term, $escape_string = false, $wildmatch = false, $num_beginning = false){
    
    $search_results = array();
    $num_of_results = 0;
    $search_settings = array();
    $mod_string = "";
    
    $search_settings[0] = $search_term;
    $search_settings[1] = $escape_string;
    $search_settings[2] = $wildmatch;
    $search_settings[3] = $num_beginning;
    
    if(trim($search_term)==""){
        return array($search_results,$num_of_results, $search_settings);
    }    
    
    if(isset($_SERVER['SERVER_NAME'])) { 
                if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false){ 
                        $hostname = 'ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com';
                        $username = 'nTrader';
                        $password = '';
                        $dbName = 'ntrader';
                } else {
                        $hostname = 'localhost';
                        $username = 'root';
                        $password = NULL;
                        $dbName = 'nTrader';
                }
    }
    
    
    //escape mysql wildcard
    if($escape_string){
        $mod_string = str_replace('%', '\\%', $search_term);
        $mod_string = str_replace('_', '\\_', $search_term);
    }else{
        $mod_string = $search_term;
    }
    
    if($wildmatch){
        $mod_string = "%" . $mod_string . "%";
    }
    
    
    $query = "SELECT SQL_CALC_FOUND_ROWS Headword,
                     CASE
                     WHEN t1.Relation <> 0 THEN
                        (SELECT t2.Definition
                        From m0002_financial_lexicon t2 
                        WHERE WID=t1.Relation)
                     ELSE
                        t1.Definition
                     END AS Definition,
                     Ref_URL AS Source,
		     t1.WID AS WID
              FROM m0002_financial_lexicon t1
              WHERE t1.Headword LIKE '" . $mod_string ."'
              ORDER BY 1
              LIMIT 0, 10;";
    
    if($num_beginning){
        $query = "SELECT SQL_CALC_FOUND_ROWS Headword,
                         CASE
                         WHEN t1.Relation <> 0 THEN
                            (SELECT t2.Definition
                            From m0002_financial_lexicon t2 
                            WHERE WID=t1.Relation)
                         ELSE
                            t1.Definition
                         END AS Definition,
                         Ref_URL AS Source,
			 t1.WID AS WID
                  FROM m0002_financial_lexicon t1
                  WHERE t1.Headword REGEXP '[#0-9].*'
                  LIMIT 0, 10;";
        
    }
    
    $conn = mysql_connect($hostname, $username, $password);
    mysql_select_db($dbName, $conn);
    
    $result = mysql_query($query, $conn);
    while($row = mysql_fetch_assoc($result)) {
        $search_results[] = $row;
    }
    
    $num_rows = mysql_fetch_assoc(mysql_query("SELECT FOUND_ROWS() as num_of_results;", $conn));
    $num_of_results = $num_rows['num_of_results'];
    
    mysql_close($conn);
    
    return array($search_results,$num_of_results,$search_settings, $query);
    
}        

?>

<html> 
<head>
	<title>Home</title>
	<meta name="description" content="Educate yourself with nTrader's dynamic Jargon translation utility, 
and the dynamics of the financial market place."/>
	<link rel="shortcut icon" href="Images/browser.ico" />
	<link rel="stylesheet" href="Home/CSS/home.css">
	<link rel="stylesheet" href="Home/CSS/standard.css">
	<link rel="stylesheet" href="Home/CSS/wordlearn.css">
	<script type='text/javascript' src='http://code.jquery.com/jquery-latest.min.js'></script>
	<script type='text/javascript' src='http://www.wizzud.com/jqdock_examples_folder/jquery.jqDock.min.js'></script>
    	<script type="text/javascript" src="Home/JAVA/mootools.1.2.3.js"></script>
	<script type="text/javascript" src="Home/JAVA/VerticalSlider.js"></script>
	<script type="text/javascript">
            
                var search_settings = [];
                var MaxPage = 0;
                
		$(document).ready(function(){

    			$(".add-jargon-btn").live('click', function(){
				var orig_spanid = $(this).attr('id');
				var jargon_to_add = orig_spanid.replace(/\d+?-/, '');

                                $.ajax({
                                    url: 'learnword.php',
                                    data: {'worddata': jargon_to_add},
                                    type: 'get',
                                    success: function(AJAX_RESP) {

                                        alert(AJAX_RESP);
                    					var currlist = document.getElementById("learnlist").value
                    					document.getElementById("learnlist").value = currlist+ "||*+*+*||" + jargon_to_add
                                        favouriteCheck();

                                    },
                                    error: function() {  

                                    },
                                    complete: function() {

                                    }
                                });

    			});

    			$(".done-jargon-btn").live('click', function(){
						alert("You have already added this jargon into your favourites. Go to jargon manager to remove it from favourites");
        			});
                        
                        $('#page-number span').live('click', function(){
                              
                              var desiredPage = 1;
                              var pageClick = "";
                                                            
                              if(!$(this).hasClass('active_page')){
                                    pageClick = $(this).text();
                                    if(isNaN(pageClick)){
                                        if(pageClick==">>"){
                                            desiredPage = parseInt($('#page-number span.active_page').text());
                                            desiredPage = desiredPage + 1
                                        }else if(pageClick=="<<"){
                                            desiredPage = parseInt($('#page-number span.active_page').text());
                                            desiredPage = desiredPage - 1
                                        }                   
                                    }else{
                                    desiredPage = pageClick;
                                    }
                                    var startCounter = ((desiredPage-1)*10);

$('#results-container').empty();
var showloadhtml = $('<tr></tr>');
showloadhtml.html('<tr><td colspan=3><div class="show-loading" style="position: relative; height:305px; background-color:#DBC9C9;"><div style="position: absolute;left: 390px;bottom: 135px;font-weight: bold;color: grey;">Loading...</div></div></td></tr>'); 
showloadhtml.appendTo($('#results-container'));

$.ajax({
url: '',
data: {'settings': search_settings, 'start': startCounter},
type: 'post',
success: function(AJAX_RESP) {

                    var searched_news_array = eval(AJAX_RESP);
                    var searched_jargons = searched_news_array[0];
                    var num_results_searched = searched_news_array[1];
                    
                    document.getElementById('num_results_display').innerHTML= num_results_searched + " results found!";
		    
		    if((parseInt(startCounter) + 10)>= num_results_searched ){
	                document.getElementById('results-page').innerHTML = "Showing " + (parseInt(startCounter) + 1) + " to " + num_results_searched + " of " + num_results_searched + " results";
		    }else{			
	                document.getElementById('results-page').innerHTML = "Showing " + (parseInt(startCounter) + 1) + " to " + (parseInt(startCounter) + 10) + " of " + num_results_searched + " results";
		    }                    

                    $('#results-container').empty();
                    $('#page-number').empty();

                    SearchHandler(searched_jargons);
                    updatePagination(desiredPage, num_results_searched);
                    favouriteCheck();

},
error: function() {
                    $('#results-container').empty();
                    var showloadhtml = $('<tr></tr>');                    		
                    showloadhtml.html('<tr><td colspan=3><div class="show-loading" style="position: relative; height:305px; background-color:#DBC9C9;"><div style="position: absolute;left: 390px;bottom: 135px;font-weight: bold;color: grey;">Oops something went wrong...</div></div></td></tr>'); 
                    showloadhtml.appendTo($('#results-container'));
},
complete: function() {

}
});


                                    
                              }
                              
                        });
                        
		});

		function AddImage()
		{
                    document.getElementById("table-foreground").innerHTML = "<img id='myimage' src='Home/Images/table-foreground.png'>";
                    document.getElementById("myimage").ondragstart = function() { return false; };
		}

                function updatePagination(NewActivePage, numOfResults){
                    MaxPage = Math.ceil(numOfResults / 10);
                    var page_per_time = 5;  //5 pages will show at a time

                    if(MaxPage==0){
                        return;
                    }
                    
                    var minpage = 1;
                    var tempMinpage = NewActivePage - 2;
                    var tempMaxpage = parseInt(NewActivePage) + 2;
                    var trueMin = 0;
                    var trueMax = 0;
                    var PreviousFlag = false;
                    var NextFlag = false;

                    
                    if(tempMinpage==minpage&&tempMaxpage==MaxPage){
                        trueMin = minpage;
                        trueMax = MaxPage;                        
                    }else if(tempMinpage<minpage&&tempMaxpage>MaxPage){
                        //will take from min page to max page, no previous after flag
                        trueMin = minpage;
                        trueMax = MaxPage;
                    }else if(MaxPage<=page_per_time&&tempMinpage<=minpage){
			//no previous after flag
                        trueMin = minpage;
                        trueMax = MaxPage;
		    }else if(tempMaxpage>MaxPage){
                        //will take from max page and pass remainder to min, will have previous flag
                        trueMax = MaxPage;
                        trueMin = tempMinpage - (tempMaxpage - MaxPage);
                        PreviousFlag = true;
                    }else if(tempMinpage<minpage){
                        //will take from min page and pass remainder to max, will have after flag
                        trueMin = minpage;
                        trueMax = (minpage - tempMinpage) + tempMaxpage;
                        NextFlag = true;
                    }else if(tempMinpage>minpage&&tempMaxpage<MaxPage){
                        //will have both previous after flag
                        trueMin = tempMinpage;
                        trueMax = tempMaxpage;
                        NextFlag = true;
                        PreviousFlag = true;
                    }else if(tempMaxpage<MaxPage){
                        //will have after flag
                        trueMax = tempMaxpage;
                        trueMin = tempMaxpage - 4;
                        NextFlag = true;
                    }else if(tempMinpage>minpage){
                        //will have previous flag
                        trueMin = tempMinpage;
                        trueMax = tempMinpage + 4;
                        PreviousFlag = true;
                    }
                    
			if(trueMin<1){
			    trueMin = 1;
			}
			if(trueMax>MaxPage){
			    trueMax = MaxPage;
			}
			
			if(MaxPage<=5){
			    PreviousFlag = false;
			    NextFlag = false;
			}

                        //create previous flag
                        if(PreviousFlag){
                            var span_page = $('<span class="previous"></span>');
                            span_page.html('<<');
                            span_page.appendTo($('#page-number'));
                        }                        
                        for(i=trueMin;i<=trueMax;i++){
                            if(i==NewActivePage){var span_page = $('<span class="active_page"></span>');}else{var span_page = $('<span></span>');}
                            span_page.html(i);
                            span_page.appendTo($('#page-number'));
                        }                    
                        if(NextFlag){
                            var span_page = $('<span class="next"></span>');
                            span_page.html('>>');
                            span_page.appendTo($('#page-number'));
                        }
                }

		function SearchHandler(SearchedJSON){
			$.each(SearchedJSON,function(i,results) {
				var headword = results['Headword'];
				var definition = results['Definition'];
				var url = results['Source'];
				var wid = results['WID'];
                        	var TBLrowOBJ = $('<tr></tr>');
                    		TBLrowOBJ.addClass('result-content');
				TBLrowOBJ.attr('id','Jargon-'+wid);
				TBLrowOBJ.html('<td style="text-transform:capitalize;">' + headword + '</td><td style="line-height: 1.4;">' + definition +'<p style="padding-bottom: 2px;"></p><a href="' + url +'" target="_blank"><font size=2>See full definition</font></a></td><td style="vertical-align: middle;" align=center><span class="add-jargon-btn" id="' + wid + '-' + headword + '">Favourite</span></td>'); 
                    		TBLrowOBJ.appendTo($('#results-container'));
			});
		};
		
		function PaginationHandler(ResultsNumber){
                    
                    MaxPage = Math.ceil(ResultsNumber / 10);
                    var page_per_time = 5;  //5 pages will show at a time

                    $('#page-number').empty();                    
                    if(MaxPage==0){
                        return;
                    }

                    if(MaxPage>page_per_time){
                        for(i=1;i<=5;i++){
                            if(i==1){var span_page = $('<span class="active_page"></span>');}else{var span_page = $('<span></span>');}
                            span_page.html(i);
                            span_page.appendTo($('#page-number'));
                        }
                        var span_page = $('<span class="next"></span>');
                        span_page.html('>>');
                        span_page.appendTo($('#page-number'));
                    }else{
                        for(i=1;i<=MaxPage;i++){
                            if(i==1){
                        	var span_page = $('<span class="active_page"></span>');                                
                            }else{
                        	var span_page = $('<span></span>');
                            }
                        span_page.html(i);
                        span_page.appendTo($('#page-number'));
                        }                                                                    
                    }                    
                        
		}
	
		function Search_Jargon(searchText, escape_string, numeric_b){

			if(searchText == undefined && escape_string == undefined && numeric_b == undefined){
                            //must be clicked by search input
                            searchText = document.getElementById('jargon-search-input').value;
                            escape_string = true;
                            wildmatch = true;
                            numeric_b = false;                            
			}else{
                            //must be click by the href url
                            wildmatch = false;
                            escape_string = false;
                            if(numeric_b==undefined){
                                numeric_b = false
                            }                                         
			}

			$('#results-container').empty();
                        var showloadhtml = $('<tr></tr>');                    		
			showloadhtml.html('<tr><td colspan=3><div class="show-loading" style="position: relative; height:305px; background-color:#DBC9C9;"><div style="position: absolute;left: 390px;bottom: 135px;font-weight: bold;color: grey;">Loading...</div></div></td></tr>'); 
                    	showloadhtml.appendTo($('#results-container'));

                        $.ajax({
                            url: '',
                            data: {'search': searchText, 'escape': escape_string, 'wildmatch': wildmatch, 'numeric': numeric_b},
                            type: 'post',
                            success: function(AJAX_RESP) {

                                var searched_news_array = eval(AJAX_RESP);
                                var searched_jargons = searched_news_array[0];
                                var num_results_searched = searched_news_array[1];
                                search_settings = searched_news_array[2];
				
				document.getElementById('num_results_display').innerHTML= num_results_searched + " results found!";

				if(num_results_searched>=10){
					document.getElementById('results-page').innerHTML = "Showing 1 to 10 of " + num_results_searched + " results";
				}else if(num_results_searched>0){
					document.getElementById('results-page').innerHTML = "Showing 1 to " + num_results_searched + " results";					
				}else{
					document.getElementById('results-page').innerHTML = "";
				}

				$('#results-container').empty();
				SearchHandler(searched_jargons);
				PaginationHandler(num_results_searched);
				favouriteCheck();

                            },
                            error: function() {

				$('#results-container').empty();
                        	var showloadhtml = $('<tr></tr>');                    		
				showloadhtml.html('<tr><td colspan=3><div class="show-loading" style="position: relative; height:305px; background-color:#DBC9C9;"><div style="position: absolute;left: 390px;bottom: 135px;font-weight: bold;color: grey;">Oops something went wrong...</div></div></td></tr>'); 
                    		showloadhtml.appendTo($('#results-container'));
				
                            },
                            complete: function() {

                            }
                        });
                        
		}

		function favouriteCheck(){
			//reset code
			$('.done-jargon-btn').each(function() {
				$(this).removeClass('done-jargon-btn').addClass('add-jargon-btn');
				$(this).html( $(this).html().replace('Stashed','Favourite') );
			});

			//set code
			var favlist =  document.getElementById("learnlist").value;
			
			if(favlist != ""){
				var favarr = favlist.split("||*+*+*||");
			}
			
			$('.add-jargon-btn').each(function() {
				var favchk = false;
				var tmpreader = $(this).attr('id');
				tmpreader = tmpreader.replace(/^[\d]+\-/, "");
				
				for (var key in favarr){
					if(favarr[key] == tmpreader){
						favchk = true;
					}
				}

				if(favchk == true){
					$(this).removeClass('add-jargon-btn').addClass('done-jargon-btn');
					$(this).html( $(this).html().replace('Favourite','Stashed') );
				}
			});
			
		}

		jQuery(document).ready(function($){

			var dockOptions =
			{ align: 'top' // horizontal menu, with expansion DOWN from a fixed TOP edge
				, labels: true  // add labels (defaults to 'br')
				, size: 75
			};
			$('#menu').jqDock(dockOptions);
			$('#menu img').click(function () {
				//alert('test');
			});

		});

	</script>
</head>
<body>

<?php 
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

echo '<p id="p_learnlist"><input type="hidden" name="learnlist" id="learnlist" value="'.$wordlist.'"/></p><p id="p_trueuserid"><input type="hidden" name="trueuserid" id="trueuserid" value="'.$userid.'"/></p>';

//END OF USER INFO EXTRACTION//

?>





<div class="wrap">

	<div id="Header_info">
	<a href="home.php"><img style="position: absolute; margin-left: 10px;" src="Home/Images/Logo.png"/></a>
	<table id ='Header'><tr><td id='HeaderCol' style="font-weight:bold; font-family:arial; text-align:right;">Welcome, <?php echo $_SESSION['username']; ?></td></tr>
	<tr><td style="text-align: right;">

	<div id="user-controls" style="float: right;">
	<a href="#" class="button icon AccSettings"><span>Settings</span></a>
	<a href="logout.php" class="button icon logout-btn"><span>Logout</span></a>
	</div>
	</td></tr></table>
	</div>

	<div id='cssmenu'>
		<ul>
   			<li><a href='home.php'><span>News</span></a></li>
      			<li class='has-sub '><a href='bi_1.php'><span>nIntel</span></a>
      			<ul>
         			<li><a href='bi_1.php'><span>Graph</span></a></li>
         			<li><a href='bi_2.php'><span>Map</span></a></li>
      			</ul>
   			</li>
   			<li class='active '><a href='#'><span><img id="Icon" src="Home/Images/nLearnerIcon.png">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;WordLearn HQ</span></a></li>
   			<li><a href='#'><span>Help</span></a></li>
			<li><a href='index.php'><span>Back to Main</span></a></li>
		</ul>
	</div>
	<div id="jargon-hq-learn">

		<div id='hq-user-tabs'>
			<div id='header-tabs' style="font-size: 0;">
				<a href='wordlearner.php'><span class="active_tab">Jargons Headquarter</span></a>
				<a href='my-jargon.php'><span>My Jargons</span></a>
			</div>
		</div>

		<div id='game-nav-menu'>
			<div id='game-header' align="left">
			Financial Word Games <font size=1 style="vertical-align: top;">(Newly launched**)</font>
			</div>
			<div id='menu'>
				<a href="gameintro.php?game=jargonhunt" target="_blank"><img src='Home/CSS/Images/jargon-passage-game.png' title='Find the Jargons' alt='' /></a>
				<a href="gameintro.php?game=hangword" target="_blank"><img src='Home/CSS/Images/guess-jargon-game.png' title='Guess the Jargon' alt='' /></a>
				<img src='Home/CSS/Images/financial-mcq-game.png' title='Financial Literacy Proficiency Test' alt='' />
				<img src='Home/CSS/Images/match-jargon-game.png' title='Mix & Match' alt='' />
				<img src='Home/CSS/Images/jargon-puzzle-game.png' title='Puzzling Jargon' alt='' />
			</div>
			<div id='AllGames' align="center">
				<span><a href="#">All Games</a></span>
			</div>
			
		</div>
		
		<div id='jargon-summary'>
			<div id='lexicon-header'>Financial Jargons In A Page</div>
			<div id='lexicon-intro'>
			    <table width=100%>
				<tr style="vertical-align: top;">
				    <td width=65% style="background-color: #DBD4D4;">
					<div id='term-of-the-day'>
					    <table>
						<!-- Term of the day info goes here -->
						<tr><td style="color:#362F2D; padding-bottom:5px; padding-top:5px;"><h2>Term of the day</h2></td></tr>
						<tr><td style="padding-bottom:5px; color:#5F5353;"><h4><?php echo ucfirst($word_of_the_day['Word']); ?></h4></td></tr>						
						<tr><td style="padding-bottom: 5px;"><?php echo $word_of_the_day['Definition']; ?></td></tr>
					    </table>
					</div>
					<div>
						<!-- function to add today's word -->
						<span id='<?php echo $word_of_the_day['WID'].'-'.$word_of_the_day['Word']; ?>' class='add-jargon-btn' style="float:right; margin-top:8px; margin-right:20px; margin-bottom:10px;">Favourite</span>
					</div>
				    </td>
				    <td width=35%>
					<div id='jargon-top-definition' style='margin-top: 8px; margin-left: 5px;'>
					    <table width=100% style="color: brown;">
						<tr>
						    <td style="font-weight: bold; font-size: 15; border-bottom: 3px solid brown;" colspan="2">Top 10 Favourited Jargons</td>
						</tr>
						<!-- get most favourited words from server -->
						<?php
						    for($i=0;$i<count($favourite_words);$i++){
							echo "<tr>";
        						echo "<td width=10%>".($i+1).".</td>"."<td width=90% style=\"border-bottom:dotted 1px; padding-bottom: 2px; padding-top: 2px;\"><a href=\"".$favourite_words[$i]['Ref_URL']."\" target=\"_blank\" style=\"text-decoration: none;\">".ucfirst($favourite_words[$i]['headword'])."</a></td>";
							echo "</tr>";
						    }
						?>						


					    </table>
					</div>
				    </td>
				</tr>
				<tr>
				    <td colspan=2 style="background-color: blanchedAlmond;">
					<div id='jargon-intro-search' style="border-top: 1px solid #CCC;">
					    <!-- search form goes here -->
					    <table id='search-form'>
						<tr>
						    <!-- Search form title -->
						    <td style="padding-bottom: 20px;"><h2><font color="#362F2D">Search and Browse Financial Jargons</font></h2>
						    <p style="margin-bottom: 10px;"></p>
						    Search to browse, add, or review financial words. Enter a search term to get started!</td>
						</tr>
                                                <tr>
						   <td>
							<form>
							<input id='jargon-search-input' type='text'>
							<!-- Search Button -->
							<button  id='jargon-search-btn' style="margin-left:3px;" value='' onclick="Search_Jargon(); return false;"><span></span></button>
							</form>
						   </td>
                                                </tr>
						<tr>
						   <td style="padding-top: 5px;">
							<font style="letter-spacing: 3px;">
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('#0-9',false,true)">#0-9</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('A%',false,false)">A</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('B%',false,false)">B</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('C%',false,false)">C</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('D%',false,false)">D</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('E%',false,false)">E</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('F%',false,false)">F</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('G%',false,false)">G</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('H%',false,false)">H</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('I%',false,false)">I</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('J%',false,false)">J</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('K%',false,false)">K</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('L%',false,false)">L</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('M%',false,false)">M</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('N%',false,false)">N</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('O%',false,false)">O</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('P%',false,false)">P</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('Q%',false,false)">Q</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('R%',false,false)">R</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('S%',false,false)">S</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('T%',false,false)">T</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('U%',false,false)">U</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('V%',false,false)">V</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('W%',false,false)">W</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('X%',false,false)">X</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('Y%',false,false)">Y</a>
                                                            <a href="javascript:void(0);" onclick="Search_Jargon('Z%',false,false)">Z</a>
							</font>
						   </td>
						</tr>
						<tr>
							<!-- x number of results found -->
						    <td style="padding-top:15px; padding-bottom: 10px;">
							<font id='num_results_display' color=#999 size=3></font>
						    </td>
						</tr>
					    </table>
					    <table id='search-results' width=100% style="border-spacing: 0;">
						<tr id='result-header'>
						    <th width=30%>Jargon</th>
						    <th width=50%>Definition</th>
						    <th width=20% align=center>Favourite</th>
						</tr>
						<!-- Search results will be injected in this container -->
						<tbody id="results-container">
						</tbody>
					    </table>
					</div>
                                            <div id="pagination" align="center">
                                                <!-- Result set Page number will be displayed here -->
                                                <div id="page-number">      
                                                </div>
                                                <!--Result Set page number status will be shown here -->
						<div id="results-page" style="margin-top:18px">
                                                </div>
                                            </div>
				    </td>
				<tr>
			    </table>
			</div>
		</div>
	</div>


        </div>
        
       <script>
       	favouriteCheck();
       </script>
    </body>
</html>
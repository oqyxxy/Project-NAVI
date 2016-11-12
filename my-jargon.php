<?php

SESSION_START();

if(!isset($_SESSION['username'])){
	header("Location:index.php");
}else{
	$userid = $_SESSION['userid'];
}

if(isset($_POST['worddata'])){

    $wordtodel = $_POST['worddata'];
    $userid = $_SESSION['userid'];

	if(isset($_SERVER['SERVER_NAME'])){
		if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false){
	    	$hostname = 'ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com';
	        $username = 'SJJ';
	        $password = 'SJJ';
	        $dbName = 'ntrader';
	    } else {
	    	// must be localhost database connection 
	        $hostname = 'localhost';
	        $username = 'root';
	        $password = NULL;
	        $dbName = 'nTrader';
	    }
	}

    $query = "CALL P006_DELETE_WORD('$userid', '$wordtodel');";

    $conn = mysql_connect($hostname, $username, $password);
    mysql_select_db($dbName, $conn);	
    $result = mysql_query($query, $conn);
    
    if($result){
	echo "pass";
    }else{
	echo "fail";
    }    

exit();
	
}

if(isset($_POST['startnum'])){
	echo json_encode(load_user_favorite($userid, $_POST['startnum']));
	exit();
}

Function load_user_favorite($userinfo, $startresult){
	
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
	
	$user_words = array();
	
	$query = "SELECT SQL_CALC_FOUND_ROWS b.headword as Headword, b.definition as Definition, b.source as Source, b.WID as WID
				FROM `m0006_User_Word_relation` a
				LEFT JOIN (SELECT Headword,
				                    CASE
				                    WHEN t1.Relation <> 0 THEN
				                    (SELECT t2.Definition
				                     From `m0002_financial_lexicon` t2 
				                     WHERE WID=t1.Relation
				                    )
				                    ELSE
				                    t1.Definition
				                    END AS Definition,
				                    Ref_URL AS Source,
				                    t1.WID AS WID
				            FROM `m0002_financial_lexicon` t1) b
				ON a.WID = b.WID
				WHERE a.user = '" . $userinfo ."'
				ORDER BY Headword
				LIMIT " . "$startresult" . ", 10;";
	
	$conn = mysql_connect($hostname, $username, $password);
	mysql_select_db($dbName, $conn);
	
	$result = mysql_query($query, $conn);
	while($row = mysql_fetch_assoc($result)) {
		$user_words[] = $row;
	}

	$num_rows = mysql_fetch_assoc(mysql_query("SELECT FOUND_ROWS() as num_of_results;", $conn));
        $num_of_results = $num_rows['num_of_results'];
	
	mysql_close($conn);
	return array($user_words, $num_of_results);
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
	<link rel="stylesheet" href="Home/CSS/my-jargon.css">
	<script type='text/javascript' src='http://code.jquery.com/jquery-latest.min.js'></script>
	<script type='text/javascript' src='http://www.wizzud.com/jqdock_examples_folder/jquery.jqDock.min.js'></script>
    	<script type="text/javascript" src="Home/JAVA/mootools.1.2.3.js"></script>
	<script type="text/javascript" src="Home/JAVA/VerticalSlider.js"></script>
	<script type="text/javascript">

	$(document).ready(function(){

		function AddImage()
		{
                    document.getElementById("table-foreground").innerHTML = "<img id='myimage' src='Home/Images/table-foreground.png'>";
                    document.getElementById("myimage").ondragstart = function() { return false; };
		}

		$(".del-jargon-btn").live('click', function(){
		var orig_spanid = $(this).attr('id');
		var jargon_to_add = orig_spanid.replace(/\d+?-/, '');

                        $.ajax({
                            url: '',
                            data: {'worddata': jargon_to_add},
                            type: 'post',
                            success: function(AJAX_RESP) {
                                successType = AJAX_RESP;
				if(successType = 'pass'){
				    removeID = orig_spanid.replace(/-.*$/,'');				    
				    $('#Jargon-' + removeID).hide('slow', function(){ $('#Jargon-' + removeID).remove(); });
				}else{
				    alert('Error removing jargon! Please try again!');
				}				

                            },
                            error: function() {  

                            },
                            complete: function() {

                            }
                        });


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
data: {'startnum': startCounter},
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
                
})

		function load_fav(DBstartval){
			$.ajax({
				url: '',
				data: {'startnum': DBstartval},
				type: 'post',
				success: function(AJAX_RESP) {
					var fav_array = eval(AJAX_RESP);
					var usr_words = fav_array[0];
					var rowcount = fav_array[1];
					$('#results-container').empty();
					SearchHandler(usr_words);
					updatePagination(1, rowcount)

				},
				error: function() {
					alert("FAIL");
				},
				complete: function() {

				}
				});
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
				TBLrowOBJ.html('<td style="text-transform:capitalize;">' + headword + '</td><td style="line-height: 1.4;">' + definition +'<p style="padding-bottom: 2px;"></p><a href="' + url +'"><font size=2>See full definition</font></a></td><td style="vertical-align: middle;" align=center><span class="del-jargon-btn" id="' + wid + '-' + headword + '">Remove</span></td>'); 
                    		TBLrowOBJ.appendTo($('#results-container'));
			});
		};

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

	</script>
</head>
<body>

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
				<a href='wordlearner.php'><span>Jargons Headquarter</span></a>
				<a href='my-jargon.php'><span class="active_tab">My Jargons</span></a>
			</div>
		</div>

		
		
		
		<tr>
			<td colspan=2 style="background-color: blanchedAlmond;">
				<div id='jargon-intro-search' style="border-top: 1px solid #CCC;">
					    
					<!-- search form goes here -->
					<table id='search-form'>
						<tr>
							<!-- Search form title -->
						    <td style="padding-bottom: 20px;"><h2><font color="#362F2D">Jargon Manager</font></h2>
						    <p style="margin-bottom: 10px;"></p>
						    Manage all your jargons here.
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
						    <th width=20% align=center>Remove favourite</th>
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
		
		
		<script type="text/javascript">
			load_fav(0);
		</script>

    </div>
</body>
</html>
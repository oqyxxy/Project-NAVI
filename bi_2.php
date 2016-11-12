<?php
session_start();

if(!isset($_SESSION['username'])){
	header("Location:index.php");
}

require_once 'charting_data.php';

$ISO_REFERENCE = apc_fetch('COUNTRY_ISO');
    
    if(isset($_POST['Geomap_SD'])||isset($_POST['Geomap_ED'])){

        echo json_encode(GeoMap_Sentiment_data($_POST['Geomap_SD'], $_POST['Geomap_ED'], false));	
	
        exit;
    }
    
    if(isset($_GET['country_name'])||isset($_GET['year_period'])||isset($_GET['begin_date'])||isset($_GET['close_date'])){
        echo load_economic_indicators($_GET['country_name'], $_GET['year_period']);
        echo "###"; //array delimiter
        echo preg_replace('/\"/','',(Sentiment_charting_data($_GET['country_name'], $_GET['begin_date'], $_GET['close_date'])));        
        exit;
    }

?>

<html>
<head>
	<title>nIntel - Map</title> 
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="Home/CSS/standard.css">
	<link rel="stylesheet" href="Home/CSS/map.css">
	<link rel="shortcut icon" href="Images/browser.ico" />
	<script type="text/javascript" src="http://jqueryjs.googlecode.com/files/jquery-1.3.2.js"></script>
	<script type="text/javascript" src="Home/JAVA/BI/jquery.min.js"></script>
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>

	<script type="text/javascript">
		function AddImage()
		{
  			document.getElementById("table-foreground").innerHTML = "<img id='myimage' src='Home/Images/table-foreground.png'>";
			document.getElementById("myimage").ondragstart = function() { return false; };
		}
	</script>


	
	<!-- ==================== BI ================== -->
                <script type="text/javascript">
                //php variables to javascript variables
                var geomap_data = <?php echo json_encode(GeoMap_Sentiment_data('null', 'null', true)); ?>;
                var country_iso = <?php echo json_encode($ISO_REFERENCE); ?>;
                var sentiment_chart_data = [];
		var econs_ind = [];
                </script>
		<script type="text/javascript">
                google.load('visualization', '1', {packages: ['geomap']});

function draw_country_sentiment_chart(data, country_code, country_name){

    var groupingUnits = [['week',[1]], ['month',[1, 2, 3, 4, 6]]];

		// create the chart
		sent_chart = new Highcharts.StockChart({
		    chart: {
		        renderTo: 'div_country_sentiment_chart',
		        alignTicks: false
		    },
		    exporting: {
         		enabled: false
		    },
		    rangeSelector: {
		        selected: 1
		    },
		    title: {
		        text: country_name + ' Sentiment Chart'
		    },
            	    navigator : {
                    	enabled : false
                    },
		    yAxis: [{
		        title: {
		            text: 'SENTIMENT',
                        style: {
                            color: '#AA4643'
                            }
		        },
                        labels: {
                            formatter: function() {
                                return this.value +'%';
                            },
                            style: {
                                color: '#AA4643'
                            }
                        },
		        lineWidth: 2,
                        tickInterval: 20,
                        min: -120,
                        max: 120
		    }],		    
		    series: [{
		        type: 'line',
		        name: country_code,
                        color: '#4572A7',
		        data: data,
		        dataGrouping: {
                            units: groupingUnits
		        },
                        tooltip: {
                            valueDecimals: 2
                        }
		    }]
		});

}
function draw_geomap_chart(){
    
    var Geomap_data_array = [];    
    var datalength = geomap_data.length;
    var sentiment_val = 0;
    Geomap_data_array.push(['Country', 'Sentiment ratio']);
    for(i=0;i<datalength;i++){
//        sentiment_val = Math.round(1/geomap_data[i][1]*100)/100;
	  sentiment_val = Math.round(geomap_data[i][1]*100)/100;
        Geomap_data_array.push([geomap_data[i][0], sentiment_val]);
    }
    
    var options=[];
    options['width'] = '40%';
    options['height'] = '45%';
    options['colors'] = [0xFF0000, 0xFFFF00, 0x006600]; //#FF0000, #FF8000, #FFFF00, #80FF00, #00FF00

    var GeomapDataTable = google.visualization.arrayToDataTable(Geomap_data_array);
    for(i=0;i<datalength;i++){
//        sentiment_val = Math.round(1/geomap_data[i][1]*100)/100;
	  sentiment_val = Math.round(geomap_data[i][1]*100)/100;
        GeomapDataTable.setCell(i, 1, sentiment_val, sentiment_val + '\nPos News: ' + geomap_data[i][2] + '\nNeg News: ' + geomap_data[i][3]);
    }
    
    var geomap = new google.visualization.GeoMap(document.getElementById('div_geomap'));
    geomap.draw(GeomapDataTable, options);
    
    google.visualization.events.addListener(
        geomap, 'regionClick', function(e) {

            if(document.getElementById('Geomap_End_date').value == ""){
                var currentTime = new Date();                
            }else{
                var currentTime = new Date(document.getElementById('Geomap_End_date').value);
            }
            var econs_year = currentTime.getFullYear();
            var begin_date = document.getElementById('Geomap_Start_date').value
            var close_date = document.getElementById('Geomap_End_date').value

            $.ajax({
                url: '',  
                data: {'country_name': country_iso[e["region"]], 'year_period': econs_year, 'begin_date': begin_date, 'close_date': close_date},
                type: 'get',
                success: function(AJAX_RESP) {
                    var AJAX_RESP_ARR = AJAX_RESP.split('###');                                   
                    econs_ind = eval(AJAX_RESP_ARR[0]);
                    sentiment_chart_data = eval(AJAX_RESP_ARR[1]);
			$("#EcoInd").empty();

			
			var table = document.getElementById('EcoInd');
			var textToInsert = '<thead class="fixedHeader"><tr><th><a href="#">Year</a></th><th><a href="#">Economic Indicator</a></th><th><a href="#">Value</a></th><th><a href="#">Indicator Scale</a></th><th><a href="#">Indicator Unit</a></th></tr></thead><tbody class="scrollContent">';

			for(i=1;i<=econs_ind.length - 1;i++){
				if( econs_ind[i][3] !== "" ) {
					if (econs_ind[i][5] > 0) {
						econs_ind[i][5] = "Estimate";
					} else {
						econs_ind[i][5] = "Historical";
					}
 
    					textToInsert  += '<tr><td>' + econs_ind[i][0] + '</td><td>' + econs_ind[i][1] + '</td><td>' + econs_ind[i][3] + '</td><td>' + econs_ind[i][2] + '</td><td>' + econs_ind[i][4] + '</td><td>' + econs_ind[i][5] + '</td></tr>';
				}
			}
			textToInsert  += '</tbody>';
			$("#EcoInd").append(textToInsert);

                    draw_country_sentiment_chart(sentiment_chart_data,e["region"],country_iso[e["region"]]);

                },
                error: function() {  
                },  
                complete: function() {
                }
            });
            
        //show the lists of indicators
        
        
//        urlToQry = 'http://www.lmgtfy.com/?q=' + country_iso[e["region"]];
//        window.open(urlToQry, '_target');
    });  
    
}

$(function() {
    google.setOnLoadCallback(draw_geomap_chart);    
});

function AJAX_ReloadGeoMap(){
        //RESET ALL CHART INDICATORS CHECKBOXES
    
        var ajaxRequest;    
        try{
            // Opera 8.0+, Firefox, Safari
            ajaxRequest = new XMLHttpRequest();
        } catch (e){
            // Internet Explorer Browsers
            try{
                ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                try{
                    ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (e){
                    // Something went wrong
                    alert("Your browser do not support ajax!");
                    return false;
                }
            }
        }

        var geomap_SD = document.getElementById('Geomap_Start_date').value;
        var geomap_ED = document.getElementById('Geomap_End_date').value;        

        var query = "Geomap_SD=" + geomap_SD + '&Geomap_ED=' + geomap_ED;
        ajaxRequest.open("POST", "", true);
        
        ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxRequest.setRequestHeader("Content-length", query.length);
        ajaxRequest.setRequestHeader("Connection", "close");                
        ajaxRequest.send(query);
        
        ajaxRequest.onreadystatechange = function() {//Call a function when the state changes.
            if(ajaxRequest.readyState == 4 && ajaxRequest.status == 200) {
                    var AJAX_RESP_TEXT = ajaxRequest.responseText;
						
			geomap_data = eval(AJAX_RESP_TEXT);
                    	draw_geomap_chart();       

            }                
        }
}

		</script>
<script src="Home/JAVA/BI/highstock.js"></script>
<script src="Home/JAVA/BI/modules/exporting.js"></script>
<script type="text/javascript">
function logoutnow(){
	window.location = "http://ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com/logout.php"
}

</script>
 
</head>
<body>

<div class="wrap">

	<div id="Header_info">
	<a href="bi_2.php"><img style="position: absolute; margin-left: 10px;" src="Home/Images/Logo.png"/></a>
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
      			<li class='active has-sub '><a href='bi_1.php'><span><img id="Icon" src="Home/Images/nIntelIcon.png">&nbsp;&nbsp;&nbsp;&nbsp;nIntel</span></a>
      			<ul>
         			<li><a href='bi_1.php'><span>Graph</span></a></li>
         			<li class='active'><a href='bi_2.php'><span>Map</span></a></li>
      			</ul>
   			</li>
   			<li><a href='wordlearner.php'><span>WordLearn HQ</span></a></li>
   			<li><a href='#'><span>Help</span></a></li>
			<li><a href='index.php'><span>Back to Main</span></a></li>
		</ul>
	</div>
	
	
	
	<!-- ==================== Hidden (http://spyrestudios.com/how-to-create-a-sexy-vertical-sliding-panel-using-jquery-and-css3/) ==================== -->
	<div class="panel">
    		<p>My Keywords</p>
		
		<div style="clear:both;"></div>

		<div class="columns">
			<div class="colleft">
		
			</div>
	
			<div class="colright">
				
			</div>
		</div>
	</div>
	<div style="clear:both;"></div>



	<!-- ==================== BI ==================== -->

	<div id="map">
		<div id="mapcontrol">
			<input id="Geomap_Start_date" type="date" placeholder="YYYY/MM/DD"> to
			<input id="Geomap_End_date" type="date" placeholder="YYYY/MM/DD">
			<input name="a_submit" type="submit" onclick="AJAX_ReloadGeoMap();">
		</div>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<div id="div_geomap"></div>
	</div>
	
	<div id="country">
		<div id="div_country_sentiment_chart"></div>
		<div id="tableContainer" class="tableContainer"><table border="0" cellpadding="0" cellspacing="0" width="100%" id="EcoInd"></table></div>
	</div>
</body>
</html>
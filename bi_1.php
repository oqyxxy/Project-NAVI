<?php

session_start();

if(!isset($_SESSION['username'])){
	header("Location:index.php");
}

require_once 'charting_data.php';

$INSTRUMENT_CODE_NAME_ARR = apc_fetch('INSTRUMENT_NAME_CODE_ARR');

    //set important AJAX variables
    if(isset($_POST['my_instrument'])){
        //set important variables as session
        
        $_SESSION['Price_Data'] = preg_replace('/\"/','',load_price_data($_POST['my_instrument'], false));               
        $_SESSION['Instrument_Code'] = json_encode($_POST['my_instrument']);
        $_SESSION['Instrument_Name'] = json_encode($INSTRUMENT_CODE_NAME_ARR[$_POST['my_instrument']]['NAME']);        
        $_SESSION['Chart_Type'] = json_encode('spline');                
        if($_SESSION['Chart_Type'] == 'spline'||$_SESSION['Chart_Type'] == 'line'){
            $_SESSION['OHLC_FLAG'] = json_encode(false);
        }{
            $_SESSION['OHLC_FLAG'] = json_encode(true);
        }                
        $_SESSION['Instrument_Headlines'] = json_encode(Search_Headlines($_POST['my_instrument']));
        
        echo $_SESSION['Price_Data'].'###'.$_SESSION['Instrument_Code'].'###'.$_SESSION['Instrument_Name'].'###'.$_SESSION['Chart_Type'].'###'.$_SESSION['OHLC_FLAG'].'###'.$_SESSION['Instrument_Headlines'];                
        
        exit;
    }

    //initializing default dashboard variables
        //OHLC flag customizable to true or false   TRUE will enable if OHLC or Candlestick
        //                                          FALSE will enable if spline chart selected
        //        $chart_type = "ohlc";
        //        $chart_type = "candlestick";
        $chart_type = 'spline';
        if($chart_type == 'spline'||$chart_type == 'line'){
           $OHLC_data_flg = false;
        }else{
            $OHLC_data_flg = true;
        }
        if(isset($_GET['query_instr'])){
            $select_instrument = $_GET['query_instr'];
        }else{
            $select_instrument = 'MXAP:IND';
        }
        
        $instrument_name = $INSTRUMENT_CODE_NAME_ARR[$select_instrument]['NAME'];
        $intraday_flg = false;
        $_SESSION['Price_Data'] = preg_replace('/\"/','',load_price_data($select_instrument, false));
        $_SESSION['Instrument_Code'] = $select_instrument;
        $_SESSION['Instrument_Name'] = $instrument_name;
        $_SESSION['Chart_Type'] = $chart_type;        
        $_SESSION['OHLC_FLAG'] = $OHLC_data_flg;
        $_SESSION['Instrument_Headlines'] = json_encode(Search_Headlines($select_instrument));
        
?>

<html>
<head>
	<title>nIntel - Graph</title> 
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="Home/CSS/standard.css">
	<link rel="stylesheet" href="Home/CSS/graph.css">
	<link rel="shortcut icon" href="Images/browser.ico" />
	<script type="text/javascript" src="http://jqueryjs.googlecode.com/files/jquery-1.3.2.js"></script>
	<script type="text/javascript" src="Home/JAVA/BI/jquery.min.js"></script>
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

		function logoutnow(){
			window.location = "http://ec2-54-251-8-97.ap-southeast-1.compute.amazonaws.com/logout.php"
		}
	</script>


	
	<!-- ==================== BI ================== -->
                <script type="text/javascript">                
		var data = <?php echo $_SESSION['Price_Data']; ?>;
                var instr_code = <?php echo json_encode($_SESSION['Instrument_Code']); ?>;
                var instr_name = <?php echo json_encode($_SESSION['Instrument_Name']); ?>;
                var chart_type = <?php echo json_encode($_SESSION['Chart_Type']); ?>;
                var ohlc_data_flg = <?php echo json_encode($_SESSION['OHLC_FLAG']); ?>;
                var news_headlines = <?php echo $_SESSION['Instrument_Headlines']; ?>;
                </script>
		<script type="text/javascript">

function draw_stock_chart(data, instr_code, instr_name, chart_type, ohlc_data_flg){
		// split the data set into ohlc and volume
		var ohlc = [],
			volume = [],
			dataLength = data.length;
			
    for (i = 0; i < dataLength; i++) {
        if(ohlc_data_flg){
            ohlc.push([data[i][0],data[i][1],data[i][2],data[i][3],data[i][4]]);    //date, OHLC
        }else{
            ohlc.push([data[i][0],data[i][4]]);                //date, closing price
        }
        volume.push([data[i][0],data[i][5]])    //date, volume
    }

		// set the allowed units for data grouping
		var groupingUnits = [[
			'week',                         // unit name
			[1]                             // allowed multiples
		], [
			'month',
			[1, 2, 3, 4, 6]
		]];

		// create the chart
		chart = new Highcharts.StockChart({
		    exporting: { enabled: false },

		    chart: {
		        renderTo: 'div_stock_chart',
		        alignTicks: false
		    },

		    rangeSelector: {
		        selected: 1
		    },

		    title: {
		        text: instr_name
		    },

		    yAxis: [{
		        title: {
		            text: 'PRICE'
		        },
		        height: 200,
		        lineWidth: 2
		    }, {
		        title: {
		            text: 'VOLUME'
		        },
		        top: 300,
		        height: 100,
		        offset: 0,
		        lineWidth: 2
		    },                                        
                    { // Secondary yAxis
                gridLineWidth: 0,
                title: {
                    text: 'INDICATORS',
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
                opposite: true,
                top: 300,
                height: 100,
                offset: 0,
                lineWidth: 2,
                gridLineWidth: 0,
                tickInterval: 25,
                min: 0,
                max: 100
            }
                    
                    ],
		    
		    series: [{
		        type: chart_type,
		        name: instr_code,
                        color: '#4572A7',
		        data: ohlc,
		        dataGrouping: {
					units: groupingUnits
		        }
		    }, {
                        type: 'column',
		        name: 'Volume',
                        color: '#AA4643',
		        data: volume,
		        yAxis: 1,
		        dataGrouping: {
					units: groupingUnits
		        }
		    }]
		});    
}

$(function() {
    draw_stock_chart(data, instr_code, instr_name, chart_type, ohlc_data_flg);
});

function AJAX_ReloadStockChart(){
    
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
        
        var instrument_code = document.getElementById('my_instrument').value;
//        var chart_type = document.getElementById('my_instrument').value;
        
        var query = "my_instrument=" + instrument_code;
        ajaxRequest.open("POST", "", true);
        
        ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxRequest.setRequestHeader("Content-length", query.length);
        ajaxRequest.setRequestHeader("Connection", "close");                
        ajaxRequest.send(query);

        ajaxRequest.onreadystatechange = function() {//Call a function when the state changes.
            if(ajaxRequest.readyState == 4 && ajaxRequest.status == 200) {
                    var AJAX_RESP_TEXT = ajaxRequest.responseText;
                    
                    var AJAX_RESP_ARR = AJAX_RESP_TEXT.split('###');
                    data = eval(AJAX_RESP_ARR[0]);
                    instr_code = eval(AJAX_RESP_ARR[1]);                
                    instr_name = eval(AJAX_RESP_ARR[2]);
                    chart_type = eval(AJAX_RESP_ARR[3]);
                    ohlc_data_flg = eval(AJAX_RESP_ARR[4]);
                    news_headlines = eval(AJAX_RESP_ARR[5]);
                    chart_type = document.getElementById('chart_type').value;
                    
             draw_stock_chart(data, instr_code, instr_name, chart_type, ohlc_data_flg);               

            }                
        }
}
function add_news_flag_chart(){
    
    var event_flags = news_headlines;
    var flag_data = [];
    
    var urlToQry = '';
    var querydate = '';

    for (i = 0; i < event_flags.length; i++) {

	querydate = event_flags[i][5];

        urlToQry = 'home.php?search_start_date=&search_end_date=&news_type=&term=&by_content=&regex_search=&country_search=&goodnews=&badnews=&neutralnews=&refresh_flag=1&affected_instr=';
	urlToQry = urlToQry.replace("search_start_date=", "search_start_date="+querydate);
	urlToQry = urlToQry.replace("search_end_date=", "search_end_date="+querydate);
	urlToQry = urlToQry.replace("affected_instr=", "affected_instr="+instr_code);



        flag_data.push({x:Date.UTC(event_flags[i][0], event_flags[i][1], event_flags[i][2]), title: event_flags[i][4], text:event_flags[i][3],events:{click: (function(urlToQry) {
     return function(){
        window.open(urlToQry, '_blank');
    }
})(urlToQry)}});}




        chart.addSeries({
            name: 'flag_event',
            type: 'flags',
            onSeries : 'dataseries',
            shape : 'circlepin',
            width : 16,
            data: flag_data
        });
}

function AddDelFlagChart(){
    if(document.getElementById('NewsOverlay').checked){
        add_news_flag_chart();
    }else{
        clearchart('flag_event');
    }
}

function clearchart(chartname){
    for(i=0;i<chart.series.length;i++){       
        if(chart.series[i]['name']==chartname){
            chart.series[i].remove();
            break;
        }
    }
}

function change_chart_type(){
    
    chart_type = document.getElementById('chart_type').value;
    
    for(i=0;i<chart.series.length;i++){       
        if(chart.series[i]['type']=='ohlc'||chart.series[i]['type']=='candlestick'||chart.series[i]['type']=='spline'){
            if(chart.series[i]['type']==chart_type){
                break;
            }else{
                chart.series[i].remove();
                var ohlc = [];
		var groupingUnits = [['week',[1]], ['month',[1, 2, 3, 4, 6]]];                
                dataLength = data.length;                
		if(chart_type=='ohlc'||chart_type=='candlestick'){
                    ohlc_data_flg = true;
                }else{
                    ohlc_data_flg = false;
                }
                for (i = 0; i < dataLength; i++) {
                    if(ohlc_data_flg){
                        ohlc.push([data[i][0],data[i][1],data[i][2],data[i][3],data[i][4]]);    //date, OHLC
                    }else{
                        ohlc.push([data[i][0],data[i][4]]);                //date, closing price
                    }
                }                
                chart.addSeries({
		        type: chart_type,
		        name: instr_code,
                        color: '#4572A7',
		        data: ohlc,
		        dataGrouping: {
					units: groupingUnits
		        }
                });
                
                break;                
            }
        }
    }
}

function Add_SMA(){
    //data - Date, open, high, low, close, volume
    //15 day SMA will consist of date and 15 day average price    
////////////////////////////////////////////////////////////    
//    clearchart('MXAP:IND');
//    
    SMA_period = 30;
    max_datapoint = data.length-1;
    SMA_maxpoint = SMA_period - 1;
    SMA_minpoint = 0;
    var SMA_data = [];
    var SMA_period_total = 0;
    var SMA_period_average = 0;
    var groupingUnits = [['week',[1]], ['month',[1, 2, 3, 4, 6]]];                
    //get SMA first data set
    for(datapoint=SMA_minpoint;datapoint<=SMA_maxpoint;datapoint++){
//        if(datapoint<SMA_maxpoint){
//        SMA_data.push([data[datapoint][0],0]);    //fill array with blank data
//        }
        SMA_period_total += data[datapoint][4];
    }
    SMA_period_average = SMA_period_total/SMA_period;
    //SMA_data[SMA_data.length-1]=[data[SMA_maxpoint][0], SMA_period_average]    
    SMA_data.push([data[SMA_maxpoint][0], Math.round(SMA_period_average*100)/100]);
    
    SMA_maxpoint++;
    SMA_minpoint++;
    
    //for subsequent data set
    for(datapoint=SMA_maxpoint;datapoint<=max_datapoint;datapoint++){
        SMA_period_total = (SMA_period_total - data[SMA_minpoint-1][4] + data[datapoint][4]);
        SMA_period_average = SMA_period_total/SMA_period;
        SMA_data.push([data[datapoint][0], Math.round(SMA_period_average*100)/100]);
        SMA_minpoint++;
    }

                chart.addSeries({
		        type: 'line',
		        name: 'SMA'+' '+SMA_period,
		        data: SMA_data,
		        dataGrouping: {
					units: groupingUnits
		        }
                });

}
function Del_SMA(){
    for(i=0;i<chart.series.length;i++){
        chartname = chart.series[i]['name'];
        if(chartname.substring(0,4)=='SMA '){
            chart.series[i].remove();
            break;
        }
    }
}
function AddDel_SMA(){
    if(document.getElementById('SMA').checked){
        Add_SMA();
    }else{
        Del_SMA();
    }
}

function Add_RSI(){

    RSI_period = 14;
    RSI_maxpoint = RSI_period;
    max_datapoint = data.length-1;
    RSI_minpoint = 1;       //index 0 to index 1
    var RSI_data = [];
    var groupingUnits = [['week',[1]], ['month',[1, 2, 3, 4, 6]]];
    var RSI_period_gain =0;
    var RSI_period_loss = 0;
    var RSI_1day_gain_loss = 0;
    var period_RSI =0;

    //get RSI first data set    
    for(datapoint=RSI_minpoint;datapoint<=RSI_maxpoint;datapoint++){
        RSI_1day_gain_loss = data[datapoint][4] - data[datapoint-1][4]
        if(RSI_1day_gain_loss<0){
            RSI_period_loss+=(RSI_1day_gain_loss*-1);
        }else if(RSI_1day_gain_loss>0){
            RSI_period_gain+=RSI_1day_gain_loss;
        }        
        if(datapoint<RSI_maxpoint){
            RSI_data.push([data[datapoint][0], 0]);     //insert blank data
        }
    }
    
    period_RSI =(RSI_period_gain/RSI_period)/(RSI_period_loss/RSI_period);
    period_RSI = 100-(100*(1/(1+period_RSI)));    
    RSI_data.push([data[RSI_maxpoint][0], Math.round(period_RSI*100)/100]);    
    
    RSI_maxpoint++;
    RSI_minpoint++;
    
    //for subsequent data set
    for(datapoint=RSI_maxpoint;datapoint<=max_datapoint;datapoint++){
        RSI_1day_gain_loss = data[datapoint][4]-data[datapoint-1][4];
        if(RSI_1day_gain_loss<0){
            RSI_period_loss = (RSI_period_loss * (RSI_period-1) + (-1*RSI_1day_gain_loss))/RSI_period;
            RSI_period_gain = (RSI_period_gain * (RSI_period-1) + 0)/RSI_period;
        }else if(RSI_1day_gain_loss>0){
            RSI_period_loss = (RSI_period_loss * (RSI_period-1) + 0)/RSI_period;
            RSI_period_gain = (RSI_period_gain * (RSI_period-1) + RSI_1day_gain_loss)/RSI_period;
        }else{
            RSI_period_loss = (RSI_period_loss * (RSI_period-1) + 0)/RSI_period;            
            RSI_period_gain = (RSI_period_gain * (RSI_period-1) + 0)/RSI_period;                        
        }
        period_RSI =(RSI_period_gain/RSI_period)/(RSI_period_loss/RSI_period);
        period_RSI = 100-(100*(1/(1+period_RSI)));
        RSI_data.push([data[datapoint][0], Math.round(period_RSI*100)/100]);                
    }
                chart.addSeries({
		        type: 'spline',
		        name: 'RSI'+' '+RSI_period,
		        data: RSI_data,
                        yAxis: 2,
		        dataGrouping: {
					units: groupingUnits
		        }
                });
}
function Del_RSI(){
    for(i=0;i<chart.series.length;i++){
        chartname = chart.series[i]['name'];
        if(chartname.substring(0,4)=='RSI '){
            chart.series[i].remove();
            break;
        }
    }    
}
function AddDel_RSI(){
    if(document.getElementById('RSI').checked){
        Add_RSI();
    }else{
        Del_RSI();
    }
}

function Add_Stochastic(){
    
    var stochastic_period = 14 - 1;     //VAL 14 CAN BE CHANGE
    var stochastic_k = [];
    var stochastic_d = [];
    var max_datapoint = data.length-1;
    var Highest_High = [];
    var Lowest_Low = [];
    var High_var = 0;
    var Low_var = 0;
    var stoch_d_EMA_period = 3;     //CAN BE CHANGE
    var stoch_d_total = 0;
    var stoch_k_val = 0;
    var groupingUnits = [['week',[1]], ['month',[1, 2, 3, 4, 6]]];
    
    //get Stochastic first data set
    for(datapoint=0;datapoint<stochastic_period;datapoint++){
        stochastic_k.push([data[datapoint][0],0]);        //insert first 13 set blank data        
        Highest_High.push(data[datapoint][2]);
        Lowest_Low.push(data[datapoint][3]);
    }

    //insert first non-blank data set (14th data point)
    Highest_High.push(data[stochastic_period][2]);
    Lowest_Low.push(data[stochastic_period][3]);
    Low_var = Math.min.apply(Math, Lowest_Low);
    High_var = Math.max.apply(Math, Highest_High);
    
    stoch_k_val=Math.round(((data[stochastic_period][4] - Low_var)/(High_var - Low_var)*100)*100)/100;
    stochastic_k.push([data[stochastic_period][0],Math.round(stoch_k_val*100)/100]);
    stoch_d_total += stoch_k_val;
    
    stochastic_period = stochastic_period + 2
    for(datapoint=stochastic_period;datapoint<=max_datapoint;datapoint++){
            Highest_High.shift();
            Lowest_Low.shift();
            Highest_High.push(data[datapoint][2]);
            Lowest_Low.push(data[datapoint][3]);
            Low_var = Math.min.apply(Math, Lowest_Low);
            High_var = Math.max.apply(Math, Highest_High);
            stoch_k_val=((data[datapoint][4] - Low_var)/(High_var - Low_var)*100);
            stochastic_k.push([data[datapoint][0],Math.round(stoch_k_val*100)/100]);
        if(datapoint-stochastic_period>=stoch_d_EMA_period-1){
            stochastic_d.push([data[datapoint][0], Math.round(stoch_d_total/stoch_d_EMA_period*100)/100]);
            stoch_d_total += stoch_k_val;
            stoch_d_total -= stochastic_k[datapoint-stoch_d_EMA_period-1][1];
        }else{
            stoch_d_total += stoch_k_val;
        }        
    }

chart.addSeries({
        type: 'spline',
        name: 'Stoch %K',
        data: stochastic_k,
        color: '#4572A7',
        yAxis: 2,
        dataGrouping: {
                        units: groupingUnits
        }
});

chart.addSeries({
        type: 'spline',
        name: 'Stoch %D',
        data: stochastic_d,
        color: '#89A54E',
        yAxis: 2,
        dataGrouping: {
                        units: groupingUnits
        }        
});

}
function Del_Stochastic(){
    for(i=0;i<chart.series.length;i++){
        chartname = chart.series[i]['name'];
        if(chartname=='Stoch %K'){
            chart.series[i].remove();
            break;
        }
    }        
    for(i=0;i<chart.series.length;i++){
        chartname = chart.series[i]['name'];
        if(chartname=='Stoch %D'){
            chart.series[i].remove();
            break;
        }
    }    
}
function AddDel_Stochastic(){
    if(document.getElementById('Stoch').checked){
        Add_Stochastic();
    }else{
        Del_Stochastic();
    }    
}

function checkInd_checkbox(){
	AddDelFlagChart();
	AddDel_SMA();
	AddDel_RSI();
	AddDel_Stochastic();
}

		</script>
<script src="Home/JAVA/BI/highstock.js"></script>
<script src="Home/JAVA/BI/modules/exporting.js"></script>


 
</head>
<body onload="checkInd_checkbox();">

<div class="wrap">

	<div id="Header_info">
	<a href="bi_1.php"><img style="position: absolute; margin-left: 10px;" src="Home/Images/Logo.png"/></a>
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
         			<li class='active'><a href='bi_1.php'><span>Graph</span></a></li>
         			<li><a href='bi_2.php'><span>Map</span></a></li>
      			</ul>
   			</li>
   			<li><a href='wordlearner.php'><span>WordLearn HQ</span></a></li>
   			<li><a href='#'><span>Help</span></a></li>
			<li><a href='index.php'><span>Back to Main</span></a></li>
		</ul>
	</div>		
	
<div id="TechnicalAnalysis">
	<table>
		<tr style="vertical-align: middle;">
			<td>
				Instrument: <input id="my_instrument" type="text" list="autocomplete_instr" onchange="AJAX_ReloadStockChart()" placeholder="Double Click to show list">
				Chart Type: <select id="chart_type" onchange="change_chart_type()">
            				<option value="spline">SPLINE</option>
            				<option value="line">LINE</option>
            				<option value="candlestick">CANDLE STICK</option>
            				<option value="ohlc">OHLC CHART</option>            
            			</select>
				<datalist id="autocomplete_instr">
    				<?php
        				foreach($INSTRUMENT_CODE_NAME_ARR as $code_name => $full_name){
            					echo "<option value=\"$code_name\">". $full_name['NAME'] ."</option>";
        				}
    				?>
				</datalist>
			</td>			
		</tr>
	</table>
</div>

<div id="graph">
<div id="div_stock_chart"></div>
</div>

<div id="Indicators">

			<label><input id="NewsOverlay" type="checkbox" onchange="AddDelFlagChart()"><text>30 Days News Overlay</text></label>
			<label><input id="SMA" type="checkbox" onchange="AddDel_SMA()"><text>30 Days Simple Moving Average</text></label>
			<label><input id="RSI" type="checkbox" onchange="AddDel_RSI()"><text>14 Days RSI</text></label>
			<label><input id="Stoch" type="checkbox" onchange="AddDel_Stochastic()"><text>Stochastic Oscillator</text></label>

</div>
</body>
</html>
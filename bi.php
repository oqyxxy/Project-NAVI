<?php
session_start();
require_once 'charting_data.php';

$INSTRUMENT_CODE_NAME_ARR = apc_fetch('INSTRUMENT_NAME_CODE_ARR');
$ISO_REFERENCE = apc_fetch('COUNTRY_ISO');

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
        $select_instrument = 'MXAP:IND';
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
	<title>Home</title> 
	<link rel="stylesheet" href="Home/CSS/standard.css">
	<script type="text/javascript" src="http://jqueryjs.googlecode.com/files/jquery-1.3.2.js"></script>
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


	
	<!-- ==================== BI ================== -->
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Highstock Example</title>

		<script type="text/javascript" src="Home/JAVA/BI/jquery.min.js"></script>
                <script type="text/javascript" src="https://www.google.com/jsapi"></script>
                <script type="text/javascript">
                //php variables to javascript variables
                
		var data = <?php echo $_SESSION['Price_Data']; ?>;
                var instr_code = <?php echo json_encode($_SESSION['Instrument_Code']); ?>;
                var instr_name = <?php echo json_encode($_SESSION['Instrument_Name']); ?>;
                var chart_type = <?php echo json_encode($_SESSION['Chart_Type']); ?>;
                var ohlc_data_flg = <?php echo json_encode($_SESSION['OHLC_FLAG']); ?>;
                var news_headlines = <?php echo $_SESSION['Instrument_Headlines']; ?>;
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
                        tickInterval: 25,
                        min: -100,
                        max: 100
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
    options['width'] = '800';
    options['height'] = '500';

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

//			for(i=0;i<46;i++){
//	                    alert(econs_ind[i][1]);
//			}

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
    draw_stock_chart(data, instr_code, instr_name, chart_type, ohlc_data_flg);
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
    
    var urlToQry = 'http://www.lmgtfy.com/?q=';
    
    for (i = 0; i < event_flags.length; i++) {
        urlToQry = 'http://www.lmgtfy.com/?q='+ (event_flags[i][0] < 10 ? '0' : '') + event_flags[i][0] + '-' + (event_flags[i][1] < 10 ? '0' : '') + event_flags[i][1] + '-' + (event_flags[i][2] < 10 ? '0' : '') + event_flags[i][2];
        flag_data.push({x:Date.UTC(event_flags[i][0], event_flags[i][1], event_flags[i][2]), title: event_flags[i][4], text:event_flags[i][3],events:{click: function(event){window.open(urlToQry, '_target');}}});        
    }
    //'http://www.lmgtfy.com/?q=' + event_flags[i][0] + '-' + event_flags[i][1]+ '-' + event_flags[i][2]
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

		</script>
<script src="Home/JAVA/BI/highstock.js"></script>
<script src="Home/JAVA/BI/modules/exporting.js"></script>


 
</head>
<body>
	<table id ="Header">
		<tr>	
			<td id="logo"><img src="Home/Images/Logo.png"/></td>
			<td>Welcome, Leung KY</br>
			<div id="AccSettings">Account Settings&nbsp&nbsp&nbspLogout</div>
			<script type="text/javascript" src="Home/JAVA/VerticalSlider.js"></script>
			</td>
		</tr>
	</table>
	
	</br><div id='cssmenu'>
		<ul>
   			<li><a href='home.php'><span>Home</span></a></li>
  			<li class='active has-sub '><a href='#'><span>Business Intelligence</span></a>
      			<ul>
         			<li class='active '><a href='bi.php'><span>Map</span></a></li>
         			<li><a href='#'><span>Graph</span></a></li>
      			</ul>
   			</li>
   			<li><a href='#'><span>About</span></a></li>
			<li><a href='index.php'><span>Back to Home</span></a></li>
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
	<a class="trigger" href="#"></a>	



	<!-- ==================== BI ==================== -->
	

<div style="position: absolute; top: 110px; left: 10px;">
Instrument: <input id="my_instrument" type="text" onchange="AJAX_ReloadStockChart()">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Chart Type: <select id="chart_type" onchange="change_chart_type()">
            <option value="spline">SPLINE</option>
            <option value="line">LINE</option>
            <option value="candlestick">CANDLE STICK</option>
            <option value="ohlc">OHLC CHART</option>            
            </select>

<div id="div_stock_chart" style="height: 500px; width: 80.5%;"></div>
30 Days News Overlay: <input id="NewsOverlay" type="checkbox" onclick="AddDelFlagChart()">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
30 Days Simple Moving Average: <input id="SMA" type="checkbox" onclick="AddDel_SMA()">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
14 Days RSI: <input id="RSI" type="checkbox" onclick="AddDel_RSI()">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Stochastic Oscillator: <input id="Stoch" type="checkbox" onclick="AddDel_Stochastic()">

<div id="div_geomap" style="height: 500px; width: 1600px"></div>
<input id="Geomap_Start_date" type="date"> to
<input id="Geomap_End_date" type="date">
<input name="a_submit" type="submit" onclick="AJAX_ReloadGeoMap();">
<div id="div_country_sentiment_chart" style="height: 200px; width: 500px"></div>
</div>
</body>
</html>






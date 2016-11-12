<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

<?php

error_reporting(E_ALL ^ (E_NOTICE));

$bloomberg_URL_main = "http://www.bloomberg.com";
$bloomberg_URL_news = "http://www.bloomberg.com/archive/news/";
$news_date = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d")-1,   date("Y")));

//Constant pattern
$News_filter = "/" . "stocks" . "/i";

//APC fetch constant variables
$Reason_pattern = apc_fetch('REASON_PATTERN');
$Subject_pattern = "/" . apc_fetch('SUBJECT_PATTERN') . "/";
$Direction_pattern = "/" . apc_fetch('DIRECTION_PATTERN') . "/i";	
$Instr_Code = apc_fetch('INSTRUMENT_POSITION_ARRAY');
$Dir_Keys = apc_fetch('DIRECTION_POSITION_ARRAY');
$Dir_Keys_Type = apc_fetch('DIRECTION_KEY_TYPES');

$ins_values_str = '';

for($xyz=1;$xyz<=1;$xyz++){

$news_date = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d")-1,   date("Y")));

//matches all headline stories with hyperlink to full article & headline title
preg_match_all('/<a\s+href=[\'"]?([^\s\>\'"]*)[\'"\>]\s*>([\s\S]+?)<\/a>/i', GetBloombergHeadlines($bloomberg_URL_news . $news_date), 
$matches, PREG_SET_ORDER);

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

                    $ins_values_str = $ins_values_str . "('$news_date','$headline','$News_Cat','$Instr_Code_val','$Trading_Ind','$URL'),\n";

                }
            }            
        }
    }
}

//insert to database the archives
if($ins_values_str != ''){
    $ins_values_str = preg_replace('/\s*,\s*$/', ';', $ins_values_str);    
    $ins_values_str = "INSERT INTO `m0004_news_archive`(`Date`, `Headline`, `News_Category`, `Affected_Instr`, `NegPos_Ind`, `News_URL`) VALUES".$ins_values_str;
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
            $mysqli = new mysqli($hostname, $username, $password, $dbName) or exit("Error connecting to database");         
            $result = $mysqli->query($ins_values_str);

echo $ins_values_str."<br>";
            
            if($result){
                echo "success";
            }else{
                echo "fail";
            }                            
}

$ins_values_str = "";

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
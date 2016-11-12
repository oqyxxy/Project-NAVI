<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

<?php

error_reporting(E_ALL ^ (E_NOTICE));

$bloomberg_URL_main = "http://www.bloomberg.com";
$bloomberg_URL_news = "http://www.bloomberg.com/archive/news/";

$news_date = date("Y-m-d");

//Constant pattern
$News_filter = "/" . "stocks" . "/i";

//APC fetch constant variables
$Reason_pattern = apc_fetch('REASON_PATTERN');
$Subject_pattern = "/" . apc_fetch('SUBJECT_PATTERN') . "/";
$Direction_pattern = "/" . apc_fetch('DIRECTION_PATTERN') . "/i";	
$Instr_Code = apc_fetch('INSTRUMENT_POSITION_ARRAY');
$Dir_Keys = apc_fetch('DIRECTION_POSITION_ARRAY');

//matches all headline stories with hyperlink to full article & headline title
preg_match_all('/<a\s+href=[\'"]?([^\s\>\'"]*)[\'"\>]\s*>([\s\S]+?)<\/a>/i', GetBloombergHeadlines($bloomberg_URL_news . $news_date), 
$matches, PREG_SET_ORDER);

//printing, will get only news with the filter in part of headlines, but not part of reason
for($i=0;$i<count($matches);$i++){        
    if(preg_match($News_filter, preg_replace($Reason_pattern, '', $matches[$i][2]))){
        echo "Headline: " . trim($matches[$i][2]) . "<br>";
        echo "URL: " . $bloomberg_URL_main . $matches[$i][1] . "<br>";
        $newsParts = explode(';', trim($matches[$i][2]));
        foreach($newsParts as $newsItem){
            $Trading_Indicator = GenerateTradingSignal($newsItem, $matches[$i][1]);
            echo "Indicator: ";
            if($Trading_Indicator==1){
                echo "Bull<br>";
            }elseif($Trading_Indicator==-1){
                echo "Bear<br>";
            }elseif($Trading_Indicator==0){
                echo "Neutral<br>";
            }elseif($Trading_Indicator<1 AND $Trading_Indicator>0){
                echo "Slightly bullish<br>";
            }elseif($Trading_Indicator<0 AND $Trading_Indicator>-1){
                echo "Slightly bearish<br>";
            }                       
        }
        echo "---------------------------------------------------------------------<br>";
    }
}

Function GenerateTradingSignal($Headline, $HeadlineURL){
    
    global $Reason_pattern, $Subject_pattern, $Direction_pattern, $Instr_Code, $Dir_Keys,$News_filter;
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
                            echo "Related Instrument Code: ".$Instr_Code[$POS]."<br>";
                            $hasStockSubject = true;
                        }
                    }
                }
            }        
	}    
        
    if(!$hasStockSubject){
	//check if is global stock
	if(preg_match('/\b[sS]tocks\b/',$newsSubject)){
	    //it is a global stock
	    $newsSubject = preg_replace('/\b[sS]tocks\b/', 'Global Stocks', $newsSubject);
    	    preg_match_all($Subject_pattern, $newsSubject, $Subject_Matches, PREG_SET_ORDER);
	    $Subject_Pos = array_keys(preg_grep('/^\s*$/', $Subject_Matches[array_search('Global', $Subject_Matches)], PREG_GREP_INVERT));
            foreach($Subject_Pos as $POS){
                if($POS != 0){
                    //currently only match for macro stocks pattern
                    if($News_filter == "/" . "stocks" . "/i"){
                        if(preg_match('/'.RegexWordMatch(FormatRegexPtrn($Subject_Matches[$i][$POS])).'\s+\b[sS]tocks\b/',$newsSubject)){
                            echo "Related Instrument Code: ".$Instr_Code[$POS]."<br>";
			    $hasStockSubject = true;
                        }
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
    
    echo $BullBear_Ind."<br>";
	
	foreach($Direction_Matches as $i => $value){
		$Direction_Pos = array_keys(preg_grep('/^\s*$/', $Direction_Matches[$i], PREG_GREP_INVERT));

        foreach($Direction_Pos as $POS){
            if($POS!=0){
				$BullBear_Ind = $BullBear_Ind * $Dir_Keys[$POS];                
                echo "dir:".$Dir_Keys[$POS]."<br>";
                echo "pos:".$POS."<br>";
            }
        }
	}
    
    echo "Breakdown:".$newsSubject . "<br>";        
    
    return $BullBear_Ind;    //True or False Value -> True means Bull, False means Bear
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
    preg_match_all($pattern, $RegexPattern, $matches);
    $matches = array_unique($matches);    
    foreach($matches as $match){
        preg_replace($match, "\\".$match, $RegexPattern);
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
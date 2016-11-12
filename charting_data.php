<?php

function load_price_data($instr_index, $intraday_request_flg){
    
    $price_data_arr = array();
    
    $data_period = '&TimePeriod=6Y';             //6Y    
    $historical_url_query = '&Outfields=HDATE,PR006-H,PR007-H,PR008-H,PR005-H,PR013-H';            
    $intraday_url_query = '&Outfields=ITIME,PR005-I';
    $price_query_url = 'http://www.bloomberg.com/apps/data?pid=webpxta&Securities=';

    if(!$intraday_request_flg){
        $query_string = $price_query_url.$instr_index.$data_period.$historical_url_query;
    }else{
        $query_string = $price_query_url.$instr_index.$data_period.$intraday_url_query;    
    }
    
    $price_data_string = file_get_contents($query_string);
    
    preg_match_all('/^\s*(\d+)"(\S+?)"(\S+?)"(\S+?)"(\S+?)"(\S+?)\s*$/m', $price_data_string, $price_data_arr, PREG_SET_ORDER);

    for($i=0;$i<count($price_data_arr);$i++){
        array_shift($price_data_arr[$i]);
        $price_data_arr[$i][0] = strtotime(substr($price_data_arr[$i][0], 0, -4).'-'.substr($price_data_arr[$i][0], 4, -2).'-'.substr($price_data_arr[$i][0], -2))+3600 . "000";        
//1. convert using str to time, add 3600, concatenate 3 000 digits                

    }    
    
return json_encode($price_data_arr);
    
}

function Search_Headlines($Instrument_Code){

    $SESSION_GROUP_CONCAT = 'SET SESSION group_concat_max_len = 10000000;';
    $HEADLINE_QUERY =   'SELECT DATE,  GROUP_CONCAT(Headline SEPARATOR  \'<br>\' ),        
                        CASE
                             WHEN (Avg(NegPos_Ind) > 0) THEN \'+\'
                             WHEN (Avg(NegPos_Ind) < 0) THEN \'-\'
                             WHEN (Avg(NegPos_Ind) = 0) THEN \'o\'
                        END
                        FROM `m0004_news_archive` 
                        WHERE Affected_Instr = \''.$Instrument_Code.'\' '.
                        'GROUP BY Date
                        ORDER BY Date DESC
                        LIMIT 30;';    
    
    $Headline_Archive = array();
    
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
        
        $stmt=$mysqli->prepare($SESSION_GROUP_CONCAT);
        $stmt->execute();        
        $stmt=$mysqli->prepare($HEADLINE_QUERY);
        $stmt->execute();
        $stmt->bind_result($Date, $Headlines, $POS_Ind);
        $i = 0;
        while($stmt->fetch()){                    
            $date_elmt = explode('-', $Date);                    
            $Headline_Archive[$i][0] = $date_elmt[0];//YEAR
            $Headline_Archive[$i][1] = $date_elmt[1]-1;//MONTH
            $Headline_Archive[$i][2] = $date_elmt[2];//DATE                    
            $Headline_Archive[$i][3] = $Headlines;  //HEADLINES
            $Headline_Archive[$i][4] = $POS_Ind;    //POSNEG_IND
            $Headline_Archive[$i][5] = $date_elmt;    //DateMonthYear	
            $i++;
        }
        $stmt->close();
        $mysqli->close();
        
        return $Headline_Archive; 
        
}

function GeoMap_Sentiment_data($Start_period, $End_period, $flagAll){

    $GEOMAP_data = array();
    
    if($flagAll){
        $flagAll = '--';
    }else{
        $flagAll = '';        
        if(strtotime($Start_period) > strtotime($End_period)){
            echo "test";            
            return $GEOMAP_data;
        }
    }

$SENTIMENT_QUERY  = "SELECT pos.Subject_ISO_Code, (pos.Pos_Count-neg.Neg_Count)/(pos.Pos_Count+neg.Neg_Count) as Sentiment, Pos_Count, Neg_Count FROM (\r\n";
$SENTIMENT_QUERY .= "SELECT Subject_ISO_Code, Count(*) as Pos_Count FROM(\r\n";
$SENTIMENT_QUERY .= "SELECT t3.Subject_ISO_Code, t1.NegPos_Ind\r\n";
$SENTIMENT_QUERY .= "FROM m0004_news_archive t1, m0001_subject_instrument t2, m0001_news_subject t3\r\n";
$SENTIMENT_QUERY .= "WHERE t1.Affected_Instr=t2.Instrument_Code\r\n";
$SENTIMENT_QUERY .= "AND t3.Subject = t2.Subject\r\n";
$SENTIMENT_QUERY .= "AND t3.Classification = 'Country'\r\n";
$SENTIMENT_QUERY .= $flagAll." AND t1.Date between "."'$Start_period'"." and "."'$End_period'"."\r\n";
$SENTIMENT_QUERY .= "UNION ALL\r\n";
$SENTIMENT_QUERY .= "SELECT t4.Subject_ISO_Code, t1.NegPos_Ind\r\n";
$SENTIMENT_QUERY .= "FROM m0004_news_archive t1, m0001_subject_instrument t2, m0001_news_subject t3, m0001_country_region t4\r\n";
$SENTIMENT_QUERY .= "WHERE t1.Affected_Instr=t2.Instrument_Code\r\n";
$SENTIMENT_QUERY .= "AND t3.Subject = t2.Subject\r\n";
$SENTIMENT_QUERY .= "AND t3.Classification = 'Region'\r\n";
$SENTIMENT_QUERY .= "AND t3.Subject = t4.Region\r\n";
$SENTIMENT_QUERY .= $flagAll." AND t1.Date between "."'$Start_period'"." and "."'$End_period'"."\r\n";
$SENTIMENT_QUERY .= ") a\r\n";
$SENTIMENT_QUERY .= "WHERE NegPos_Ind > 0\r\n";
$SENTIMENT_QUERY .= "GROUP BY Subject_ISO_Code\r\n";
$SENTIMENT_QUERY .= "ORDER BY 1) pos,\r\n";
$SENTIMENT_QUERY .= "(SELECT Subject_ISO_Code, Count(*) as Neg_Count FROM(\r\n";
$SENTIMENT_QUERY .= "SELECT t3.Subject_ISO_Code, t1.NegPos_Ind\r\n";
$SENTIMENT_QUERY .= "FROM m0004_news_archive t1, m0001_subject_instrument t2, m0001_news_subject t3\r\n";
$SENTIMENT_QUERY .= "WHERE t1.Affected_Instr=t2.Instrument_Code\r\n";
$SENTIMENT_QUERY .= "AND t3.Subject = t2.Subject\r\n";
$SENTIMENT_QUERY .= "AND t3.Classification = 'Country'\r\n";
$SENTIMENT_QUERY .= $flagAll." AND t1.Date between "."'$Start_period'"." and "."'$End_period'"."\r\n";
$SENTIMENT_QUERY .= "UNION ALL\r\n";
$SENTIMENT_QUERY .= "SELECT t4.Subject_ISO_Code, t1.NegPos_Ind\r\n";
$SENTIMENT_QUERY .= "FROM m0004_news_archive t1, m0001_subject_instrument t2, m0001_news_subject t3, m0001_country_region t4\r\n";
$SENTIMENT_QUERY .= "WHERE t1.Affected_Instr=t2.Instrument_Code\r\n";
$SENTIMENT_QUERY .= "AND t3.Subject = t2.Subject\r\n";
$SENTIMENT_QUERY .= "AND t3.Classification = 'Region'\r\n";
$SENTIMENT_QUERY .= "AND t3.Subject = t4.Region\r\n";
$SENTIMENT_QUERY .= $flagAll." AND t1.Date between "."'$Start_period'"." and "."'$End_period'"."\r\n";
$SENTIMENT_QUERY .= ") a\r\n";
$SENTIMENT_QUERY .= "WHERE NegPos_Ind < 0\r\n";
$SENTIMENT_QUERY .= "GROUP BY Subject_ISO_Code\r\n";
$SENTIMENT_QUERY .= "ORDER BY 1) neg\r\n";
$SENTIMENT_QUERY .= "WHERE pos.Subject_ISO_Code = neg.Subject_ISO_Code;";

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
        
        $stmt=$mysqli->prepare($SENTIMENT_QUERY);
        $stmt->execute();
        $stmt->bind_result($Subj, $Ind, $Pos_Count, $Neg_Count);
        $i = 0;
        while($stmt->fetch()){
            $GEOMAP_data[$i][0] = $Subj;
            $GEOMAP_data[$i][1] = $Ind;
            $GEOMAP_data[$i][2] = $Pos_Count;
            $GEOMAP_data[$i][3] = $Neg_Count;
            $i++;
        }
        $stmt->close();
        $mysqli->close();    
    
    return $GEOMAP_data;

}

Function load_economic_indicators($Country_Name, $Period){

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
    
    $country_ind_lists = array();
    
    $QUERY = "SELECT ind_year, economic_ind, ind_scale, ind_value, indicator_units, estimation_flag
              FROM m0005_economic_ind
              WHERE country_name = '$Country_Name'
              AND ind_year = '$Period';";    

        $mysqli = new mysqli($hostname, $username, $password, $dbName) or exit("Error connecting to database");        
        $stmt=$mysqli->prepare($QUERY);
        $stmt->execute();
        $stmt->bind_result($year, $ind_name, $ind_scale, $ind_value, $ind_units, $estimate_flag);
        $i = 0;
        while($stmt->fetch()){
            $country_ind_lists[$i][0] = $year;
            $country_ind_lists[$i][1] = $ind_name;
            $country_ind_lists[$i][2] = $ind_scale;
            $country_ind_lists[$i][3] = $ind_value;
            $country_ind_lists[$i][4] = $ind_units;
            $country_ind_lists[$i][5] = $estimate_flag;
            $i++;
        }
        $stmt->close();
        $mysqli->close();
    
    return json_encode($country_ind_lists);
        
}

Function Sentiment_charting_data($country_Name, $Start_Date = '', $End_Date = ''){
    
    $FlagDate = false;
    if($Start_Date==''||$End_Date==''){
        $FlagDate = true;
    }    
    $Flagall= "";
    if($FlagDate){
        $Flagall= " -- ";
    }    
    
    $country_sentiment_data = array();
    
    $QUERY  = "SELECT Date, SUM(NegPos_Ind) FROM \r\n";
    $QUERY .= "(SELECT t1.ID, t1.Date, t1.Headline, t3.Subject_ISO_Code, t1.NegPos_Ind \r\n";
    $QUERY .= "FROM m0004_news_archive t1, m0001_subject_instrument t2, m0001_news_subject t3 \r\n";
    $QUERY .= "WHERE t1.Affected_Instr=t2.Instrument_Code \r\n";
    $QUERY .= "AND t3.Subject = t2.Subject \r\n";
    $QUERY .= "AND t3.Classification = 'Country' \r\n";
    $QUERY .= "UNION ALL \r\n";
    $QUERY .= "SELECT t1.ID, t1.Date, t1.Headline, t4.Subject_ISO_Code, t1.NegPos_Ind \r\n";
    $QUERY .= "FROM m0004_news_archive t1, m0001_subject_instrument t2, m0001_news_subject t3, m0001_country_region t4 \r\n";
    $QUERY .= "WHERE t1.Affected_Instr=t2.Instrument_Code \r\n";
    $QUERY .= "AND t3.Subject = t2.Subject \r\n";
    $QUERY .= "AND t3.Classification = 'Region' \r\n";
    $QUERY .= "AND t3.Subject = t4.Region \r\n";
    $QUERY .= ") A \r\n";
    $QUERY .= "WHERE Subject_ISO_Code = '$country_Name' \r\n";
    $QUERY .= "$Flagall AND Date between '$Start_Date' AND '$End_Date' \r\n";
    $QUERY .= "GROUP BY Date \r\n";
    $QUERY .= "ORDER BY Date; \r\n";
    
    $tmp_date = "";
    $tmp_Ind = 0;
    
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
    $stmt=$mysqli->prepare($QUERY);
    $stmt->execute();
    $stmt->bind_result($News_Date, $NegPos_Ind);
        $i = 0;
        while($stmt->fetch()){ 
            if($i!=0){
                $country_sentiment_data[$i-1][0] = strtotime($News_Date)+3600 . "000";
                if((abs($tmp_Ind)+abs($NegPos_Ind)!=0)){
                    $country_sentiment_data[$i-1][1] = round(($tmp_Ind+$NegPos_Ind)/(abs($tmp_Ind)+abs($NegPos_Ind))*100,2);                    
                }else{
                    $country_sentiment_data[$i-1][1] = 0;                    
                }
            }
            $tmp_date = $News_Date;
            $tmp_Ind = $NegPos_Ind;            
            $i++;
        }
        $stmt->close();
        $mysqli->close();
        
    return json_encode($country_sentiment_data);
        
}

?>
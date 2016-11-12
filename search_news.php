<?php

//, $by_country, $start_date, $end_date, $by_sentiment

load_search_news(0, 5, '' ,'China', true, true);

function load_search_news($start_post, $end_post, $news_type = '', $search_term = '', $by_content = false, $by_regex = false, $by_country = '', $start_date = '', $end_date = '', $by_sentiment = '', $bySQLQuery = ''){

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
    
    $searched_news = array();
    $number_of_results = 0;
    $sql_string = "";
    //number of search results in pages?
    //news array
    //sql string
    
    $search_sentiment = "";     
    $search_date = "";
    $search_content = "";
    $search_by_term = "";
    $search_country = "";
    $search_by_regex = "";
    $search_category = "";
    $search_regex_content = "";
    
    if(trim($news_type)==''){
        $search_category = " -- ";
    }    
    if(trim($by_sentiment)==''){        //acceptable value < or > or = or ''
        $search_sentiment = " -- ";
    }
    if(trim($start_date)==''||trim($end_date)==''){     //date values or ''
        $search_date = " -- ";
    }
    if(trim($search_term)!='' && !$by_regex){
        if(!$by_content){
        $search_content = " -- ";
        }
        $search_by_regex = " -- ";
    }else{
        $search_by_term = " -- ";
    }
    if(trim($search_term)!='' && $by_regex){
        if(!$by_content){
        $search_regex_content = " -- ";
        }
        $search_term = FormatRegexPtrn($search_term);
        $search_by_term = " -- ";
    }else{
        $search_by_regex = " -- ";
    }
    if(trim($by_country)==''){
        $search_country = " -- ";
    }

    $news_query = "SELECT SQL_CALC_FOUND_ROWS t1.ID, t1.Date, t1.Headline, t1.News_Category, t1.Affected_Instr, t1.NegPos_Ind, t1.News_URL \r\n
                   FROM m0004_news_archive t1, m0001_subject_instrument t2 \r\n
                   WHERE t1.Affected_Instr = t2.Instrument_Code \r\n
                   $search_country WHERE t2.Subject = $by_country;
                   $search_category WHERE t1.News_Category = $news_type \r\n
                   $search_by_term AND t1.Headline LIKE '%$search_term%' $search_content OR t1.News_Content LIKE '%$search_term%' \r\n
                   $search_by_regex AND t1.Headline REGEXP '$search_term' $search_regex_content OR t1.News_Content REGEXP '$search_term' \r\n
                   $search_date AND t1.Date between $start_date and $end_date \r\n
                   $search_sentiment AND t1.NegPos_Ind $by_sentiment 0 \r\n
                   LIMIT $start_post, $end_post;";

    $link = mysql_connect($hostname, $username, $password);
    mysql_select_db($dbName, $link);
    $result = mysql_query($news_query, $link);
    
    while($row = mysql_fetch_assoc($result)) {
        $searched_news[] = $row;
    }    
    
    
    $num_rows = mysql_fetch_assoc(mysql_query("SELECT FOUND_ROWS() as num_of_results;", $link));
    
    $number_of_results = $num_rows['num_of_results'];    
    $sql_string = $news_query;

print $number_of_results."<br>";
print $sql_string."<br>";
print_r($searched_news);
    
    mysql_close($link);
    
    //$num_rows = mysql_query("SELECT FOUND_ROWS();", $link);
    //$number_of_results;
    //print_r(mysql_fetch_assoc($num_rows));
    
    //search by type of news - stocks, bonds, commodities
    //search inside headlines and/or news content
    //search by news mentioning the related instrument
    //search by news base on date
    //search by positive news or negative news or neutral news
    
    return $searched_news;
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

?>
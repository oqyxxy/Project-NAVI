<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<?php

$db_url_arr = array();

    if(isset($_SERVER['SERVER_NAME'])) { 
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

    $mysqli = new mysqli($hostname, $username, $password, $dbName) or exit("Error connecting to database");

    $stmt=$mysqli->prepare("SELECT News_URL FROM m0004_news_archive WHERE News_Content='' ORDER BY DATE DESC;");
    $stmt->execute();
    $stmt->bind_result($URL);
    while($stmt->fetch()){
        $db_url_arr[] = $URL;
    }

for($i=0;$i<count($db_url_arr);$i++){
    
    $news_content_data = GetNewsContent($db_url_arr[$i]);

    $newsContent = "";
    $release_time = "";
    $intro = "";	

    $newsContent = str_replace('\\', "\\\\", $news_content_data[0]);    
    $newsContent = str_replace("'", "''", $newsContent);
    $release_time = $news_content_data[1];
    $release_time = str_replace('\\', "\\\\", $release_time);    
    $release_time = str_replace("'", "''", $release_time);    
    $intro = $news_content_data[2];
    $intro = str_replace('\\', "\\\\", $intro);    
    $intro = str_replace("'", "''", $intro);        
    
    $result = $mysqli->query("UPDATE m0004_news_archive
                                SET News_Content = '".$newsContent."', Release_date = '".$release_time."', Intro_Paragraph = '".$intro."'
                                WHERE News_Content = ''
                                AND News_URL = '".$db_url_arr[$i]."';");
   
}            
    
Function GetNewsContent($newsURL){
//Get news content with URL
    include_once('simple_html_dom.php');
    $Content = "";
    $Timestamp = "";
    $intro = "";
    $html=file_get_html($newsURL);
    foreach ($html->find('div[id=story_display]') as $div){
        foreach($div->find('p') as $p){
	    $Content .= '<p>'.$p->plaintext.'</p>';
        }
    }
    foreach ($html->find('span[class=datestamp]') as $div){
        $Timestamp = $div->plaintext;
    }
    
    preg_match('/<p>([\s\S]+?)<\/p>/i', $Content, $matches);
    $intro = $matches[1];    
    
return array($Content, $Timestamp, $intro);
}
    
?>
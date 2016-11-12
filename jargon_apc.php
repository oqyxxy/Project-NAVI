<!--<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>-->
<?php

        $Headword_Pattern_Arr = array();

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
        
        $stmt=$mysqli->prepare("SET NAMES utf8;");
        $stmt->execute();
        
        $stmt=$mysqli->prepare("SELECT Headword, wordcount, regex_pattern, definition FROM (
                                SELECT t2.Relation as Headword,
                                       length(t2.Relation) - length(replace(t2.Relation, ' ', '')) + 1 as wordcount,
                                       (SELECT t3.regex_pattern FROM m0002_financial_lexicon t3 WHERE Headword = t2.Relation) as regex_pattern, t1.definition
                                FROM m0002_financial_lexicon t1, m0002_headword_relation t2
                                WHERE t2.Headword = t1.Headword
                                UNION ALL
                                SELECT t1.Headword, length(t1.Headword) - length(replace(t1.Headword, ' ', '')) + 1 as wordcount, t1.regex_pattern, t1.definition
                                FROM m0002_financial_lexicon t1
                                LEFT JOIN
                                m0002_headword_relation t2
                                ON t1.Headword = t2.Headword
                                WHERE t2.Relation is null ) TBL
                                ORDER BY wordcount DESC;");
        
        $stmt->execute();
        $stmt->bind_result($Headword, $Wordcount, $Pattern, $Definition);
            while($stmt->fetch()){
                
                $Headword_Pattern_Arr[$Headword]['Headword'] = $Headword;
                $Headword_Pattern_Arr[$Headword]['Pattern'] = $Pattern;
                $Headword_Pattern_Arr[$Headword]['Definition'] = $Definition;
                
                echo $Definition;
                
/*                $Headword_Pos_Arr[$Headword_Pos]['Headword'] = $Headword;
                $Headword_Pos_Arr[$Headword_Pos]['POS'] = $POS_type;
                $Headword_Pos_Arr[$Headword_Pos]['Definition'] = $Definition;
                $Headword_Pos_Arr[$Headword_Pos]['Pattern'] = $Headword_Pattern;
                $Headword_Regex_Str = $Headword_Regex_Str . '|' . $Headword_Pattern;*/
            }
            
/*        if(count($Headword_Regex_Str)>0){
            $Headword_Regex_Str = substr($Headword_Regex_Str, 1);
        }
        
        echo $Headword_Regex_Str;
*/        
        $stmt->close();
        $mysqli->close();
        
        apc_store('HEADWORD_ARR', $Headword_Pattern_Arr);
//        apc_store('HEADWORD_PATTERN', $Headword_Regex_Str);        

?>
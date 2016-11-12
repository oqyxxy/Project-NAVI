<?php

require_once 'Libraries/pos_tagger.php';

		//To create logic to insert new keywords
		
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
        
        //To get position of subject keywords submatches in regex pattern written txt file and 
        //corresponding index code
        $stmt=$mysqli->prepare("SELECT  @curRow := @curRow + 1 AS Position_No,
                                GROUP_CONCAT(subjInstr.Instrument_Code SEPARATOR  ',' ) Instrument_Code
                                FROM    m0001_news_subject newsSubj, m0001_subject_instrument subjInstr
                                JOIN    (SELECT @curRow := 0) r
                                WHERE subjInstr.Subject = newsSubj.Subject
                                GROUP BY newsSubj.Subject;");
        $stmt->execute();
		$stmt->bind_result($Pos_No, $Instr);
		while($stmt->fetch()){
				$Instr_Code[$Pos_No] = $Instr;
			}
			
			//To get position of direction keys keywords submatches in regex pattern written txt file 
			//and corresponding negative-positive indicator        
		$stmt=$mysqli->prepare("SELECT  @curRow := @curRow + 1 AS Position_No,
									PosNeg_Ind
									FROM    m0001_direction_keys DirKeys
									JOIN    (SELECT @curRow := 0) r;");
			$stmt->execute();
		$stmt->bind_result($Pos_No, $Instr);        
		while($stmt->fetch()){
				$Dir_Keys[$Pos_No] = $Instr;
			}                
		

		$stmt=$mysqli->prepare("SELECT  @curRow := @curRow + 1 AS Position_No,
									Key_Classification
									FROM    m0001_direction_keys DirKeys
									JOIN    (SELECT @curRow := 0) r;");		
			$stmt->execute();
		$stmt->bind_result($Pos_No, $Key_Types);        
		while($stmt->fetch()){
				$Dir_Keys_Type[$Pos_No] = $Key_Types;
			}                

		//Store to Apache PHP Cache as constant variables
		apc_store('INSTRUMENT_POSITION_ARRAY', $Instr_Code);
		apc_store('DIRECTION_POSITION_ARRAY', $Dir_Keys);
		apc_store('DIRECTION_KEY_TYPES', $Dir_Keys_Type);
		
        $stmt=$mysqli->prepare("SET SESSION group_concat_max_len = 10000000;");
        $stmt->execute();        
        
        $stmt=$mysqli->prepare("SELECT GROUP_CONCAT( CONCAT(  '(', Abbreviation,  ')' )
                                SEPARATOR  '|' ) Abbreviation, r.Alternative_Key
                                FROM m0001_news_subject
                                join
                                (SELECT GROUP_CONCAT( CONCAT(  '(', Alternative_Key,  ')' )
                                SEPARATOR  '|' ) Alternative_Key
                                FROM m0001_direction_keys) r;");
        $stmt->execute();
        $stmt->bind_result($Subject_pattern, $Direction_pattern);
	while($stmt->fetch()){
               echo $Subject_pattern;
               echo $Direction_pattern;
        }

        $stmt=$mysqli->prepare("SELECT instrument_code, instrument_full_name
				FROM m0001_subject_instrument;");
        $stmt->execute();
        $stmt->bind_result($SUBJECT_INSTRUMENT, $INSTR_FULL_NAME);
	while($stmt->fetch()){
		$Instr_Name_Code[$SUBJECT_INSTRUMENT]['CODE'] = $SUBJECT_INSTRUMENT;
		$Instr_Name_Code[$SUBJECT_INSTRUMENT]['NAME'] = $INSTR_FULL_NAME;
		echo $INSTR_FULL_NAME;		
        }

////////////
	
	$ISO_Reference = array();
        $stmt=$mysqli->prepare("SELECT Country_Name, Country_Code 
				FROM m0005_charting_ISO;");
        $stmt->execute();
        $stmt->bind_result($ISO_Country_Name, $ISO_Code);
	while($stmt->fetch()){
		$ISO_Reference[$ISO_Code] = $ISO_Country_Name;
        }

///////////	
     
	$stmt->close();
	$mysqli->close();		

        $tagger = new PosTagger('http://phpir.com/user/files/text/lexicon.txt');
        apc_store('POS_TAG_OBJECT', $tagger);
	apc_store('COUNTRY_ISO', $ISO_Reference);	
	apc_store('INSTRUMENT_NAME_CODE_ARR', $Instr_Name_Code);
	apc_store('SUBJECT_PATTERN', $Subject_pattern);
	apc_store('DIRECTION_PATTERN', $Direction_pattern);
        apc_store('REASON_PATTERN', '/(^|\s)\b(on|as|amid|after|to|in|with|ahead|before)\b(\s|$)[\S\s]*/i');
        
		
?>
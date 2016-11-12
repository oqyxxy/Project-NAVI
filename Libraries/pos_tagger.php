<?php

class PosTagger {

        private $dict;
        
        public function __construct($lexicon) {
                $fh = fopen($lexicon, 'r');
                while($line = fgets($fh)) {
                        $tags = explode(' ', $line);
                        $this->dict[strtolower(array_shift($tags))] = $tags;
                }
                fclose($fh);
        }
        
        public function tag($text) {
            //'/([\w\d\.\&\:-]+|,)(’s|\'s)?/'
                preg_match_all('/([\w\d\.\&\:-]+((,\d+)+([\.\d*]+)?)?|,)(’s|\'s)?/', $text, $matches);
                $nouns = array('NN', 'NNS');
                
                $return = array();
                $i = 0;
                foreach($matches[0] as $token) {                                                                  
                    
                        // default to a common noun
                        $return[$i] = array('token' => $token, 'tag' => 'NN');  
                        
                        // remove trailing full stops
                        if(substr($token, -1) == '.') {
                                $token = preg_replace('/\.+$/', '', $token);
                        }
                        
                        // get from dict if set
                        if(isset($this->dict[strtolower($token)])) {
                                $return[$i]['tag'] = $this->dict[strtolower($token)][0];
                        }       
                        
                        // Converts verbs after 'the' to nouns
                        if($i > 0) {
                                if($return[$i - 1]['tag'] == 'DT' &&
                                        in_array($return[$i]['tag'], 
                                                        array('VBD', 'VBP', 'VB'))) {
                                        $return[$i]['tag'] = 'NN';
                                }
                        }
                        
                        // Convert noun to number if . appears
                        if($return[$i]['tag'][0] == 'N' && strpos($token, '.') !== false && !preg_match('/[a-z]/i' ,$token)) {
                                $return[$i]['tag'] = 'CD';
                        }                                                
                        
                        // Convert noun to past particile if ends with 'ed'
                        if($return[$i]['tag'][0] == 'N' && substr($token, -2) == 'ed') {
                                $return[$i]['tag'] = 'VBN';
                        }
                        
                        // Anything that ends 'ly' is an adverb
                        if(substr($token, -2) == 'ly') {
                                $return[$i]['tag'] = 'RB';
                        }
                        
                        // Common noun to adjective if it ends with al
                        if(in_array($return[$i]['tag'], $nouns) 
                                                && substr($token, -2) == 'al') {
                                $return[$i]['tag'] = 'JJ';
                        }
                        
                        // Noun to verb if the word before is 'would'
                        if($i > 0) {
                                if($return[$i]['tag'] == 'NN' 
                                        && strtolower($return[$i-1]['token']) == 'would') {
                                        $return[$i]['tag'] = 'VB';
                                }
                        }                        
                        
                        // Convert noun to plural if it ends with an s
                        if($return[$i]['tag'] == 'NN' && substr($token, -1) == 's') {
                                $return[$i]['tag'] = 'NNS';
                        }
                        
                        // Convert common noun to gerund
                        if(in_array($return[$i]['tag'], $nouns) 
                                        && substr($token, -3) == 'ing') {
                                $return[$i]['tag'] = 'VBG';
                        }
                        
                        // If we get noun noun, and the second can be a verb, convert to verb
                        if($i > 0) {
                                if(in_array($return[$i]['tag'], $nouns) 
                                                && in_array($return[$i-1]['tag'], $nouns) 
                                                && isset($this->dict[strtolower($token)])) {
                                        if(in_array('VBN', $this->dict[strtolower($token)])) {
                                                $return[$i]['tag'] = 'VBN';
                                        } else if(in_array('VBZ', 
                                                        $this->dict[strtolower($token)])) {
                                                $return[$i]['tag'] = 'VBZ';
                                        }
                                }
                        }                       
                        
                        //Convert to number if previous token is numeric and current tag is noun
                        if($i > 0){
                            if($return[$i]['tag']=='NN' || $return[$i]['tag']=='NNS' || $return[$i]['tag']=='NP' || $return[$i]['tag']=='NPS'){
                                if(is_numeric($return[$i-1]['token'])){
                                    $return[$i-1]['tag'] = 'CD';
                                }
                            }
                        }

                        if($i > 0){                            
                            if(strtoupper(substr($return[$i]['token'], 0, 1)) == substr($return[$i]['token'], 0, 1) && ctype_alpha(substr($return[$i]['token'], 0, 1))){
                                $return[$i]['tag'] = 'NN';
                            }
                        }

                        if($i > 0){
                            if(substr($return[$i-1]['tag'], -1) == '$'){
                                $return[$i]['tag'] = 'NN';                                
                            }
                        }

                        if(preg_match('/\'s|’s/',$token)){
                                $return[$i]['tag'] = 'NN$';
                        }                        
                        
                        $i++;
                }
                
                return $return;
        }
        
        public function GetTaggedText($tags) {
            $TaggedSentence = "";    
            foreach($tags as $t) {
                $TaggedSentence = $TaggedSentence . $t['token'] . "/" . $t['tag'] .  " ";
            }
            return $TaggedSentence;
        }
        
        public function Chunk_taggedString($tags, $original_sentence, $dir_pattern){

            $PhraseExprArr = array();
            $tagged_string = " ".$this->GetTaggedText($tags);
            $Passive_Phrase_Ind = false;
            $Passive_Regex_Pattern = '/((will|would|has|had|(will|would)\/\S+\s+have)\/\S+\s+be(en)?\/\S+\s+being\/\S+|(will|would)\/\S+\s+have\/\S+\s+been\/\S+|ha[sd]\/\S+\s+been\/\S+|(is|was)\/\S+\s+being\/\S+|would\/\S+\s+be\/\S+|will\/\S+\s+be\/\S+|was\/\S+|is\/\S+|to\/\S+\s+be\/\S+)(\s+\S+\/(VBN))/';            
            
            $simple_tag_string = $tagged_string;                        
            
            $token_pattern = '/(\S+\/(DT|POS|WP$|PP$))?(\s+\S+\/(CD))?(\s+\S+\/(JJ|JJS))*(\s+\S+\/(NNP|NNS|NNPS|NN\$?|PRP))((\s+\S+\/(VBG))(\S+\/(DT|POS|WP$|PP$))?(\s+\S+\/(CD))?(\s+\S+\/(JJ|JJS))*(\s+\S+\/(NNP|NN\$?|NNS|NNPS|PRP)))?/';    
        //1. Simplify and replace tagged string with noun phrase chunk tokens
            while(preg_match($token_pattern, $tagged_string, $matches)){
                $tagged_string = preg_replace($token_pattern, " NNP##".count($PhraseExprArr), $tagged_string, 1);        
                $PhraseExprArr["NNP##".count($PhraseExprArr)] = trim($matches[0]);
            }

            $token_pattern = '/NNP##\d+(\s+(of\/[A-Z]+\s+)?NNP##\d+)+/';
            while(preg_match($token_pattern, $tagged_string, $matches)){
                $tagged_string = preg_replace($token_pattern, " NNP##".count($PhraseExprArr), $tagged_string, 1);        
                $PhraseExprArr["NNP##".count($PhraseExprArr)] = trim($matches[0]);
            }

            $token_pattern = '/\S+\/JJR\s+than\/IN\s+NNP##\d+/';
            while(preg_match($token_pattern, $tagged_string, $matches)){
                $tagged_string = preg_replace($token_pattern, "NNP##".count($PhraseExprArr), $tagged_string, 1);
                $PhraseExprArr["NNP##".count($PhraseExprArr)] = trim($matches[0]);
            }                
            
            //Handle scenario for e.g. The maker of chips for computers and mobile phones
            $token_pattern = '/NNP##\d+\s+for\/\S+\s+NNP##\d+((,?(\s+NNP##\d+,)+?)?(\s+and\/CC\s+NNP##\d+)+)?/';
            while(preg_match($token_pattern, $tagged_string, $matches)){
                $tagged_string = preg_replace($token_pattern, "NNP##".count($PhraseExprArr), $tagged_string, 1);
                $PhraseExprArr["NNP##".count($PhraseExprArr)] = trim($matches[0]);
            }
            // for/IN NNP##2 and/CC NNP##3                        
            
        //2. Simplifies and replace tagged string with verb phrase chunk tokens
            //$token_pattern = '/(\S+\/MD)?(\s+\S+\/(VB[DZPNG]?))/';
            $token_pattern = '/(\S+\/MD)?(\s+\S+\/(VB[DZPNG]?))(\s+\S+\/RB)?/';
            while(preg_match($token_pattern, $tagged_string, $matches)){
                $tagged_string = preg_replace($token_pattern, " VBP##".count($PhraseExprArr), $tagged_string, 1);
                $PhraseExprArr["VBP##".count($PhraseExprArr)] = trim($matches[0]);
            }

            $token_pattern = '/VBP##\d+(\s+VBP##\d+)+/';
            while(preg_match($token_pattern, $tagged_string, $matches)){
                $tagged_string = preg_replace($token_pattern, " VBP##".count($PhraseExprArr), $tagged_string, 1);        
                $PhraseExprArr["VBP##".count($PhraseExprArr)] = trim($matches[0]);
            }

        //3. Simplifies and replace tagged string with prepositions chunk tokens
            $token_pattern = '/(\S+\/(IN))/';
            while(preg_match($token_pattern, $tagged_string, $matches)){
                $tagged_string = preg_replace($token_pattern, "INP##".count($PhraseExprArr), $tagged_string, 1);
                $PhraseExprArr["INP##".count($PhraseExprArr)] = $matches[0];
            }

        //4. Assign event tags to verbs and corresponding arguments
            $event_string = $tagged_string;
            $eventExprArr = array();
            $token_pattern = '/(NNP##\d+)?\s+(?:,\/,\s+NNP##\d+\s+,\/,\s+)?(VBP##\d+)([\s\S]*?)\s+(?=((,\/,|\S+\/CC)[\S\s]+?VBP##\d+|\s*$))/';
            //$token_pattern = '/(NNP##\d+)?\s+(VBP##\d+)([\s\S]*?)\s+(?=((,\/,|\S+\/CC)[\S\s]+?VBP##\d+|\s*$))/';

            $temp_expanded_string = "";

            while(preg_match($token_pattern, $event_string, $matches)){
                $event_string = preg_replace($token_pattern, " Evt##".count($eventExprArr)." ", $event_string,1);
                $Event_Arr_Index = "Evt##".count($eventExprArr);
                $Event_Arr_Num = count($eventExprArr);

                $temp_expanded_string = $this->ReplaceSubExpression('/(NNP|VBP|INP)##\d+/', trim($matches[0]), $PhraseExprArr);

                $eventExprArr[$Event_Arr_Index]['Expr'] = trim($matches[0]);        //string of the event
                $eventExprArr[$Event_Arr_Index]['Subject'] = $matches[1];           //logic will inherit previous phrase subject if current phrase have none
                if($matches[1]!=null){
                $eventExprArr[$Event_Arr_Index]['Subject'] = $matches[1];
                }else{
                    if(($Event_Arr_Num - 1)>=0){
                        if($eventExprArr["Evt##".($Event_Arr_Num - 1)]['Subject']!=null){
                            $eventExprArr[$Event_Arr_Index]['Subject'] = $eventExprArr["Evt##".($Event_Arr_Num - 1)]['Subject'];
                        }else{
                            $eventExprArr[$Event_Arr_Index]['Subject'] = $matches[1];
                        }
                    }
                }
                $eventExprArr[$Event_Arr_Index]['Event'] = $matches[2];             //Event verbs                
                

                if(preg_match('/^NNP##\d+/', trim($matches[3]), $target_match)){
                    $eventExprArr[$Event_Arr_Index]['Target'] = $target_match[0];
                    $matches[3] = preg_replace('/^NNP##\d+\s+/', '', trim($matches[3]), 1);
                }else{
                    $eventExprArr[$Event_Arr_Index]['Target'] = null;            
                }
                $eventExprArr[$Event_Arr_Index]['Others'] = trim($matches[3]);

                if(preg_match($Passive_Regex_Pattern, " ".$temp_expanded_string)){
                    $temp_expanded_string = $eventExprArr[$Event_Arr_Index]['Subject']; //Let temp_expanded_string be transfer string
                    $eventExprArr[$Event_Arr_Index]['Subject'] = $eventExprArr[$Event_Arr_Index]['Target'];
                    $eventExprArr[$Event_Arr_Index]['Target'] = $temp_expanded_string;
                }                                                
                
                $s_expression = $this->remove_string_tags($this->ReplaceSubExpression('/(NNP|VBP|INP)##\d+/', $eventExprArr[$Event_Arr_Index]['Expr'], $PhraseExprArr));
                $s_subject = $this->remove_string_tags($this->ReplaceSubExpression('/(NNP|VBP|INP)##\d+/', $eventExprArr[$Event_Arr_Index]['Subject'], $PhraseExprArr));    //customize to remove DT
                $s_target = $this->remove_string_tags($this->ReplaceSubExpression('/(NNP|VBP|INP)##\d+/', $eventExprArr[$Event_Arr_Index]['Target'], $PhraseExprArr));
                $s_event = $this->remove_string_tags($this->ReplaceSubExpression('/(NNP|VBP|INP)##\d+/', $eventExprArr[$Event_Arr_Index]['Event'], $PhraseExprArr));
                $s_other = $this->remove_string_tags($this->ReplaceSubExpression('/(NNP|VBP|INP)##\d+/', $eventExprArr[$Event_Arr_Index]['Others'], $PhraseExprArr));
                
                if(preg_match($dir_pattern,$s_event)){
                    $subject_rel = $s_subject;
                    $subject_dir = $s_event;
                }

            }

            $event_reason = "";
            
            if(preg_match('/(?<!such)\s+\bas\b(?:\s+(?!of))([\s\S]+?),[\s\S]+?,([\s\S]+?)(,\s+|$)/i', $original_sentence,$event_reason_match)){
                $event_reason = $event_reason_match[1] . $event_reason_match[2];
            }elseif(preg_match('/(?<!such)\s*\bas\b(?:\s+(?!of))([\s\S]+?)(,\s+|$)/i', $original_sentence, $event_reason_match)){
                $event_reason = $event_reason_match[1];
            }elseif(preg_match('/\bafter\b([\s\S]+?)(,\s+|$)/i', $original_sentence, $event_reason_match)){
                $event_reason = $event_reason_match[1];
            }elseif(preg_match('/\bamid\b([\s\S]+?)(,\s+|$)/i', $original_sentence, $event_reason_match)){
                $event_reason = $event_reason_match[1];
            }elseif(!preg_match('/\b(jan|feb|mar|apr|may|jun|jul|aug|sept?|oct|nov|dec|week|year|day|yesterday)\b/i',$this->regex_string_match('/\bon\b\/IN([\s\S]+?(\/IN|,\s+|$))/',$tagged_string,array(1)))){
                if(preg_match('/\bon\b([\s\S]+?),[\s\S]+?,([\s\S]+?)(,\s+|$)/i', $original_sentence, $event_reason_match)){
                    $event_reason = $event_reason_match[1].$event_reason_match[2];
                }elseif(preg_match('/\s*\bon\b([\s\S]+?)(,\s+|$)/i', $original_sentence, $event_reason_match)){
                    $event_reason = $event_reason_match[1];
                }

            }            
            
		$SubRelEvt = array();

            if(isset($subject_rel)&&isset($subject_dir)){
                if($event_reason!=""){
		    if(trim($subject_rel)!=''){	
		    	$SubRelEvt['Subject'] = $subject_rel;
		    	$SubRelEvt['Direction'] = $subject_dir;
		    	$SubRelEvt['Event'] = $event_reason;
		    }                 	                      
                }
            }

	return $SubRelEvt;		      		    

        }
        
        public function ReplaceSubExpression($search_pattern, $input_String, $reference_array){
            while(preg_match($search_pattern, $input_String, $Expr_Match)){
                $input_String = preg_replace('/'.$Expr_Match[0].'/', $reference_array[$Expr_Match[0]], $input_String, 1);
            }
            return $input_String;    
        }
        
        public function regex_string_match($search_pattern, $subject, $submatch_to_return){
            $return = "";
            if(preg_match($search_pattern, $subject, $matches)){
                foreach($submatch_to_return as $submatch_index){
                    $return .= $matches[$submatch_index];
                    
                }
            }
            return $return;
        }
        
        public function remove_string_tags($input_String){
            $input_String = preg_replace('/\/\S+?(\s|$)/', ' ', $input_String);
            return $input_String;    
        }
        
}

// function to group POS to phrases to noun phrase, verb phrase, prepositional phrase

?>
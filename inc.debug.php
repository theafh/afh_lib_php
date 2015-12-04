<?
require_once(AFH_LIB_PATH.'inc.afh_lib_php_conf.php');

/*
   for using the debug familie of functions in the head (bevor inclusion of lib, a global variable has to be set:
   $GLOBALS['DEBUG'] = true;            -> debug is used and output is ASCII only (for command line tools)
   $GLOBALS['DEBUG'] = 'html';          -> debug is used, but output is opemized for browsers
   $GLOBALS['DEBUG'] = false;           -> no debug info will be displayed, the functions doing as little as nessecary

   $GLOBALS['DEBUG_END'] = true / false -> if ist set and value is true the programm is terminated on first call of a debug function
*/

function debug_var($var){

        if(isset($GLOBALS['DEBUG'])){
		if($GLOBALS['DEBUG'] === 'html'){
			$ending = "<br>\n";
		}
		else{
			$ending = "\n";
		}

		$name = '';
		foreach($GLOBALS AS $var_name => $value) {
			if ($value === $var) {
				$name = $var_name;
				break;
			}
		}

		if($GLOBALS['DEBUG'] === true || $GLOBALS['DEBUG'] === 'html'){
			if($name != ''){
				echo "\$$name: $var$ending";
			}
			else{
				echo "\$NO_VAR_NAME: $var$ending";
			}
		}
		debug_test_end_and_terminate();
        }
}

function debug($msg){
        if(isset($GLOBALS['DEBUG'])){
	        if($GLOBALS['DEBUG'] === true){
			echo $msg."\n";
		}
		else if($GLOBALS['DEBUG'] == 'html'){
			echo $msg."<br>\n";
		}
		debug_test_end_and_terminate();
        }
}

function debug_r($arr,$msg=''){
        if(isset($GLOBALS['DEBUG'])){
		
		if($GLOBALS['DEBUG'] === 'html'){
			$ending = "<br>\n";
		}
		else{
			$ending = "\n";
		}

		if($GLOBALS['DEBUG'] === true || $GLOBALS['DEBUG'] === 'html'){
			$name = '';
			foreach($GLOBALS AS $var_name => $value) {
				if ($value === $msg) {
					$name = $var_name;
					break;
				}
			}
			//output an optional massage in front of an array if some is set
			if($msg != ''){
				if($name != ''){
					echo $msg.$ending.$name.$ending;
				}
				else{
					echo $msg.$ending;
				}
			}   

			if($GLOBALS['DEBUG'] === 'html'){
				echo "<pre>\n";
			}

			// output of array
			if(is_array($arr)){
				print_r($arr);
			}   
			//if $arr is no array var_dump is used at last
			else{
				var_dump($arr).$ending;
			}

			if($GLOBALS['DEBUG'] === 'html'){
				echo "</pre>\n";
			}
			debug_test_end_and_terminate();
                }   
        }   
}

function debug_test_end_and_terminate(){
        if(isset($GLOBALS['DEBUG'])){
		if(isset($GLOBALS['DEBUG_END'])){
			if($GLOBALS['DEBUG_END'] === true){
				if($GLOBALS['DEBUG'] === 'html'){
					$ending = "<br>\n";
				}
				else{
					$ending = "\n";
				}

				die("Programm is terminatet on first debug output!$ending");
			}
		}
	}
}

function start_timer($slot='default'){
	 $GLOBALS['init_timer'] [$slot] = time();
}

function end_timer($slot='default'){
        $now   = time();
	$start = $GLOBALS['init_timer'] [$slot];

        $dif   = $now - $start;
        //$dif = (60*60*24*2)+(60*60*2) + 33;

        $days = floor($dif/(60*60*24));
        $dif  = $dif - ($days*60*60*24);

        $hrs  = floor($dif/(60*60));
        $dif  = $dif - ($hrs*60*60);

        $min  = floor($dif/60);
        $dif  = $dif - ($min*60);

        $sec  = $dif;

        $ret = '';
        if($days != 0){
                $ret .= "$days Days - ";
        }
        if($hrs != 0){
                $ret .= "$hrs Hours - ";
        }
        if($min != 0){
                $ret .= "$min Minutes - ";
        }
        if($sec != 0){
                $ret .= "$sec Seconds";
        }

        if($ret != ''){
                echo "Time Slot ($slot) has taken: $ret\n";
        } 
}

?>

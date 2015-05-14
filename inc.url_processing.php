<?php

// quick check if a string COULD be an URL
function is_url($url){
	$regex  = "(([a-z]+)(\:)([\/]{1,3})?)"; // SCHEME
	$regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
	$regex .= "([a-z0-9-\.]*)"; // Host or IP
	$regex .= "(\:[0-9]{2,5})?"; // Port
	$regex .= "\/([,%\.\/a-z0-9+\$_-])*?"; // Path
	$regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
	$regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor

	if(preg_match("/^$regex$/iUs", $url)){
		return true;
	}
	else{
		return false;
	}

}

// quick check if a string COULD be an IP-adress
function is_ip ($ip, $ip_v=4){

        switch($ip_v){
                case 4:
		if (preg_match("/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/",$ip)){
                        return true;
		}
                break;

                case 6:
		if (preg_match("/([0-9a-z]{1,4})?\:([0-9a-z]{1,4})?\:([0-9a-z]{1,4})?\:([0-9a-z]{1,4})?\:([0-9a-z]{1,4})?\:([0-9a-z]{1,4})?\:([0-9a-z]{1,4})?\:([0-9a-z]{1,4})?/i",$ip)){
                        return true;
		}
                break;
        }   
        return false;
}

function canonicalize_url($url,$target_scheme=null,$target_host=null){

	$ret_arr['original_url'] = $url;

	// handling of providet scheme and host
	if($target_scheme != strtolower($target_scheme)){
		trigger_error("WARNING in function canonicalize_url(): \$target_scheme should be lower case! \n", E_USER_WARNING);
		$target_scheme = strtolower($target_scheme);		
	}

	if($target_host != null && $target_host != strtolower($target_host)){
		trigger_error("WARNING in function canonicalize_url(): \$target_host should be lower case! \n", E_USER_WARNING);
		$target_host = strtolower($target_host);		
	}

	//initial url parsing
        $url_parts = parse_url($url);
	//print_r($url_parts);

	//BEGIN scheme and start of URL handling 
	$tmp_scheme_arr = explode('://',$url);
	if(count($tmp_scheme_arr) > 2){
		$new_url = '';
		foreach($tmp_scheme_arr AS $element){
			$element = trim($element);
			if($element != '' && $element != ':' && $element != '/' && $element != 'http' && $element != 'http:' && $element != 'https' && $element != 'https:'){
				$new_url .= $element;
				//echo '$element: '.$element."\n";
			}
		}
		$new_url   = $tmp_scheme_arr[0].'://'.$new_url;
		$new_url   = str_replace($tmp_scheme_arr[0].':///',$tmp_scheme_arr[0].'://',$new_url);
		$url_parts = parse_url($new_url);
		unset($new_url);
	}

	if(!isset($url_parts['scheme'])){
		if(strpos($url,'://') === 0 && $target_scheme == null){
			$url_parts         = parse_url('http'.$url);
			$new_url           = 'http'.'://';
		}
		else if(strpos($url,'://') === 0 && $target_scheme != null){
			$url_parts         = parse_url($target_scheme.$url);
			$new_url           = $target_scheme.'://';
		}
		else{
			$url_parts         = parse_url($target_scheme.'://'.$url);
			$new_url           = $target_scheme.'://';
		}
	}
	else{
		$tmp_scheme = strtolower($url_parts['scheme']);
		if($tmp_scheme != $target_scheme && $target_scheme != null){
			$url_parts['scheme'] = $target_scheme;
			$ret_arr['status'] = 'repaird';
		}
		else if($tmp_scheme != $url_parts['scheme']){
			$url_parts['scheme'] = $tmp_scheme;
			$ret_arr['status'] = 'repaird';
		}
		$new_url = $url_parts['scheme'].'://';
	}

	if(preg_match("/^https?|ftp\:\/\//iUs", $new_url) === false){
		$ret_arr['err'][]  = 'No scheme could be found!';
		$ret_arr['status'] = 'broken';
		return $ret_arr;
	}
	// END - scheme processing

	//user:pass
	if(isset($url_parts['user']) && isset($url_parts['pass'])){
		$new_url .= $url_parts['user'].':'.$url_parts['pass'].'@';
	}

	// beginn host handling
	if($target_host == null && !isset($url_parts['host'])){
		$ret_arr['err'][]  = 'No host could be identified!';
		$ret_arr['status'] = 'broken';
		return $ret_arr;
	}
	else{
		if(isset($url_parts['host'])){
			$tmp_host = strtolower($url_parts['host']);
		}
		else{
			$tmp_host = '';
		}

		if($target_host != null && $tmp_host != $target_host){
			$url_parts['host'] = $target_host;
			$ret_arr['status'] = 'repaird';
		}
		else if($tmp_host != $url_parts['host']){
			$url_parts['host'] = $tmp_host;
			$ret_arr['status'] = 'repaird';
		}
	}

	/*
	if(preg_match('/[^a-z0-9\.-]+/', $url_parts['host'],$matches)){
		//print_r($matches);
		//echo $url_parts['host']."\n";
		$ret_arr['err'][]  = 'Hostname ('.$url_parts['host'].') has illegal characters!';
		$ret_arr['status'] = 'broken';
		return $ret_arr;
	}

	$host_len = strlen($url_parts['host']);
	if($host_len > 255){
		$ret_arr['err'][]  = 'Hostname has more than 255 characters: '.$host_len.'!';
		$ret_arr['status'] = 'broken';
		return $ret_arr;
	}

	$host_labels = explode('.',$url_parts['host']);
	foreach($host_labels AS $label){
		$label_len = strlen($label);
		if($label_len > 63){
			$ret_arr['err'][]  = 'Hostlabel ('.$label.') has more than 63 characters: '.$label_len.'!';
			$ret_arr['status'] = 'broken';
			return $ret_arr;
		}
		else if($label_len < 1){
			$ret_arr['err'][]  = 'A hostlabel must at least have one character!';
			$ret_arr['status'] = 'broken';
			return $ret_arr;
		}
	}
	*/

	$new_url .= $url_parts['host'];
	// end host handling

	//port cleaning
	if(isset($url_parts['port'])){
		//omit the deafault ports
		if($url_parts['scheme'] == 'http' && $url_parts['port'] == 80){
			$ret_arr['status'] = 'repaird';
		}
		else if($url_parts['scheme'] == 'https' && $url_parts['port'] == 443){
			$ret_arr['status'] = 'repaird';
		}
		else if($url_parts['scheme'] == 'ftp' && $url_parts['port'] == 21){
			$ret_arr['status'] = 'repaird';
		}
		else{
			$new_url .= ':'.$url_parts['port'];
		}
	}

	//path
	if(!isset($url_parts['path'])){
		$url_parts['path'] = '/';
	}
	else if(preg_match('/[^a-zA-Z0-9\.\/-_~]+/', $url_parts['path'],$matches)){
		//print_r($matches);
		do{	
			$url_parts['path'] = urldecode($url_parts['path']);
			$tmp_path          = $url_parts['path'];
		}while ($url_parts['path'] != urldecode($tmp_path));
		
		$path_elements     = explode('/',$url_parts['path']);
		$new_path_elements = Array();
		foreach($path_elements AS $element){
			$new_path_elements[] = urlencode($element);
		}
		$url_parts['path'] = implode($new_path_elements,'/');
	}

	$new_url .= $url_parts['path'];

	//query
	if(isset($url_parts['query'])){
		if(strpos($url_parts['query'],'&') !== false){
			/*
			do{	
				$url_parts['query'] = urldecode($url_parts['query']);
				$tmp_query          = $url_parts['query'];
			}while ($url_parts['query'] != urldecode($tmp_query));
			*/
			
			$query_elements     = explode('&',$url_parts['query']);
			$new_query_elements = Array();
			foreach($query_elements AS $element){
				//$new_query_elements[$element] = urlencode($element);
				$new_query_elements[$element] = $element;
			}
			natsort($new_query_elements);
			$url_parts['query'] = implode($new_query_elements,'&');
			//$new_url .= '?'.$url_parts['query'];
		}
		$new_url .= '?'.$url_parts['query'];
	}

	//fragment
	if(isset($url_parts['fragment'])){
		$new_url .= '#'.$url_parts['fragment'];
	}

	//replace unnecessary encodet variants 
	$new_url = str_replace('%20','+',$new_url);
	$new_url = str_replace(' ','+',$new_url);
	$new_url = str_replace('%2C',',',$new_url);
	$new_url = str_replace('%2A',':',$new_url);
	$new_url = str_replace('%2B',';',$new_url);

	//setting status & results
	$ret_arr['return_url']   = $new_url;

	if($new_url == $url && !isset($ret_arr['err'])){
		$ret_arr['status'] = 'ok';
	}
	else{
		$ret_arr['status'] = 'repaird';
	}

        return $ret_arr;
}


function get_simple_host($url){
        $arr = parse_url($url);
        $host = $arr['host'];
        $host = str_replace('www.','',$host);

        return $host;
}

?>

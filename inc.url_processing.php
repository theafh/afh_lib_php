<?php

require_once(AFH_LIB_PATH.'inc.debug.php');

// check if a string is an valid URL
function is_url($url,$strict=false,$verbosity=NULL){
	//get url parts as array
	$url_parts  = parse_url($url);

	// break if no HOST or no SCHEME exists
	if(!isset($url_parts['host'])){
		verbose('No host found in URL',$verbosity);
		return false;
	}
	else if(!isset($url_parts['scheme'])){
		verbose('No scheme found in URL',$verbosity);
		return false;
	}

	// if strict setting
	if($strict == true){
		verbose('URL validation is "strict"',$verbosity);

		if(mb_detect_encoding($url, 'ASCII', true) === false){
			verbose('A valid URL must be encoded in ASCII',$verbosity);
			return false;
		}

	        if($url_parts['host'] != strtolower($url_parts['host'])){		
			verbose('Host is upper case',$verbosity);
			return false;
		}
		else if($url_parts['scheme'] != strtolower($url_parts['scheme'])){		
			verbose('Scheme is upper case',$verbosity);
			return false;
		}

		//test if SCHEME is supported
		// list of official schemes: http://en.wikipedia.org/wiki/URI_scheme#Official_IANA-registered_schemes
		//$schemes = array('http','https','ftp','mailto','news','irc','tel','git','file','bitcoin','magnet','skype','sms','xmpp');
		$schemes = array('http','https','ftp');

		if(!in_array(strtolower($url_parts['scheme']),$schemes)){
			verbose('Scheme is not supported: '.$url_parts['scheme'],$verbosity);
			return false;
		}

		if(!isset($url_parts['path'])){
			verbose('A URL must include a PATH',$verbosity);
			return false;
		}

		// in strict mode only ASCII are allowed in host (what is with PATH and QUERY?)
		if(preg_match('/[^a-z0-9\.-]+/i', $url_parts['host'])){
			// if the host is an IPv6 Address FILTER_VALIDATE_URL fails!!
			// if match contains illigal characters it could be an IPv6 Address
			if(preg_match('/^\[([a-f0-9\:]+)\]$/i', $url_parts['host'],$matches)){
				if(!is_ip($matches[1],6)){
					verbose('Host is not a valid IPv6 Address',$verbosity);
					return false;
				}
			}
			else{
				verbose('Host contains illigal characters',$verbosity);
				return false;
			}
		}
	}

	//test the host

	$host_len = strlen($url_parts['host']);
	if($host_len > 255){
		verbose('A host name is not allowed to have more than 255 chracters',$verbosity);
		return false;
	}

	$host_labels = explode('.',$url_parts['host']);
	foreach($host_labels AS $label){
		$label_len = strlen($label);
		if($label_len > 63){
			verbose('A hostlable is not allowed to have more than 63 characters',$verbosity);
			return false;
		}
		else if($label_len < 1){
			verbose('A hostlabel must at least have one character',$verbosity);
			return false;
		}
	}

	// if nothing is wrong, return true
	return true;


	/*
	   // old limited validation via REGEXP

	$regex  = "(([a-z]+)(\:)([\/]{0,3})?)"; // SCHEME
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
	*/
}

// check if a string is a valid IP address
// the optional parameter $ip_v could be set to 0 (ipv4 OR ipv6), 4 (only valid in ipv4) and 6 (ipv6)
function is_ip ($ip, $ip_v=0){

        switch($ip_v){
                case 0:
			if (filter_var($ip, FILTER_VALIDATE_IP)){
				return true;
			}
                break;

                case 4:
			if (filter_var($ip, FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){
				return true;
			}
                break;

                case 6:
			if (filter_var($ip, FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)){
				return true;
			}
		break;
        }   
        return false;
}


// this function could be further refined with ideas from here: http://en.wikipedia.org/wiki/URL_normalization
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

	if(!isset($url_parts['scheme'])){
		if($target_scheme == null){
			$url_parts['scheme'] = 'http';
			$ret_arr['status']   = 'repaird';
		}
		else if($target_scheme != null){
			$url_parts['scheme'] = $target_scheme;
			$ret_arr['status']   = 'repaird';
		}

		// if URL was without scheme but was correctetd with this step
		// if url starts with "://"
		if(strpos($url,'://') === 0){
			$tmp_url = $url_parts['scheme'].$url;
		}
		// without scheme and "://"
		else{
			$tmp_url = $url_parts['scheme'].'://'.$url;
		}

		// reparse if it could now be validated with added scheme (not strict)
		if(is_url($tmp_url)){
			$url_parts = parse_url($tmp_url);
		}
	}
	else{
		// "scheme right trim"
		$s          = 0;
		$scheme_str = $url_parts['scheme'].'://';
		$slen       = strlen ($scheme_str);
		$tmp_url    = $url;
		do {
			$tmp_url = substr($tmp_url,$slen);
			$s++;
		} while (stripos($tmp_url,$scheme_str) === 0);

		if($s > 1){
			$tmp_url = $scheme_str.$tmp_url;
			// reparse if it could now be validated with added scheme (not strict)
			if(is_url($tmp_url)){
				$url_parts = parse_url($tmp_url);
			}
		}

		// scheme replacement / fixing
		if($target_scheme != null){
			$url_parts['scheme'] = $target_scheme;
			$ret_arr['status']   = 'repaird';
		}
		else if($url_parts['scheme'] != strtolower($url_parts['scheme'])){
			$url_parts['scheme'] = strtolower($url_parts['scheme']);
			$ret_arr['status']   = 'repaird';
		}

	}

	$new_url = $url_parts['scheme'].'://';


	if(preg_match("/^https?|ftp\:\/\//iUs", $new_url) === false){
		$ret_arr['err'][]  = 'No supported scheme could be found!';
		$ret_arr['status'] = 'broken';
		return $ret_arr;
	}
	// END - scheme processing

	//user:pass
	if(isset($url_parts['user']) && isset($url_parts['pass'])){
		$new_url .= $url_parts['user'].':'.$url_parts['pass'].'@';
	}

	// beginn host handling
	// there should be tests / conversion from / to Punycode / check non ascii characters
	// see: http://php.net/manual/de/function.idn-to-utf8.php

	if($target_host == null && !isset($url_parts['host'])){
		$ret_arr['err'][]  = 'No host could be identified!';
		$ret_arr['status'] = 'broken';
		return $ret_arr;
	}
	else if($target_host != null){
		$url_parts['host'] = $target_host;
		$ret_arr['status'] = 'repaird';
	}
	else if($url_parts['host'] != strtolower($url_parts['host'])){
		$url_parts['host'] = strtolower($url_parts['host']);
		$ret_arr['status'] = 'repaird';
	}

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


	if(!is_url($new_url,true)){
		if($new_url == $url){
			$ret_arr['err'][]  = 'URL could not be repaird and validated';
		}
		else{
			$ret_arr['err'][]  = 'repaird URL could not be validated';
		}
		$ret_arr['status'] = 'broken';
		return $ret_arr;
	}

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

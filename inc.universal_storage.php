<?

require_once(AFH_LIB_PATH.'inc.afh_lib_php_conf.php');
require_once(AFH_LIB_PATH.'inc.file.php');
require_once(AFH_LIB_PATH.'inc.folder.php');
require_once(AFH_LIB_PATH.'inc.str.php');
require_once(AFH_LIB_PATH.'inc.array.php');
require_once(AFH_LIB_PATH.'inc.xml.php');

function universal_storage_write_file($meta_arr,$args_arr,$data){

	$new_meta_arr['meta_len'] = ''; //to ensure meta_len is the first element in array (to later speedup the reading time)
	$new_meta_arr             = array_add($new_meta_arr,$meta_arr);

	switch($args_arr['updatetype']){
		case 'replace':
			if(!isset($meta_arr['c_time'])){
				$new_meta_arr['c_time'] = time();
			}
			$new_meta_arr['m_time'] = time();
		break;

		case 'update':
			trigger_error('ERROR in Function >>'.__FUNCTION__."()<< updatetype='update' not implemented!\n", E_USER_WARNING);
			return false;
		break;

		case 'append':
			trigger_error('ERROR in Function >>'.__FUNCTION__."()<< updatetype='append' not implemented!\n", E_USER_WARNING);
			return false;
		break;

	}

	if(!isset($args_arr['filename'])){
		trigger_error('ERROR in Function >>'.__FUNCTION__."()<< \$args_arr['filename'] not set!\n", E_USER_WARNING);
		return false;
	}

	//no double encoding of filenames if already md5-sum
	if(!str_is_md5($args_arr['filename'])){
		$md5 = md5($args_arr['filename']);
	}
	else{
		$md5 = $args_arr['filename'];
	}


	if(isset($args_arr['rootpath'])){
		$rootpath = $args_arr['rootpath'];
	}
	else{
		if(defined('AFH_DATA_PATH')){
			$rootpath = AFH_DATA_PATH;
		}
		else{
			trigger_error('ERROR in Function >>'.__FUNCTION__."()<< \$args_arr['rootpath'] is not set and fallback constant AFH_DATA_PATH is also not defined!\n", E_USER_WARNING);
			return false;
		}
	}
		
	$total_path = return_md5_path ($rootpath,$md5,true);


	if(is_array($data) || is_object($data)){
		$data = serialize($data);
		$args_arr['data_compression'] = 'gz';
		$new_meta_arr['content_type'] = 'serialized_object';
	}

	switch($args_arr['data_compression']){
		case 'gz':
			$data_file = gzcompress($data,9);
			$new_meta_arr['data_compression'] = 'gz';
		break;

		default:
			$data_file = $data;
			$new_meta_arr['data_compression'] = '';
	}

	$xml_meta_str = array_to_xml_str($new_meta_arr, '', 1);
	$xml_meta_str = "<meta>\n$xml_meta_str</meta>";

	$meta_len     = universal_storage_calc_recursive_meta_len_sum($xml_meta_str);
	$xml_meta_str = str_replace('<meta_len></meta_len>','<meta_len>'.$meta_len.'</meta_len>',$xml_meta_str);

	$write_str    = $xml_meta_str.$data_file;

	$cache_path   = $total_path.$md5.'.usf';

	//creation of new file
	if(file_exists($cache_path) == false){
		$fp = fopen($cache_path,'w');
		fwrite($fp, $write_str);
		fclose($fp);
		chmod($cache_path, 0664);
		if(defined('AFH_FILE_GRP') && constant('AFH_FILE_GRP') !== false){
			chgrp($cache_path, AFH_FILE_GRP);
		}
	}
	//file will be overwritten
	else{
		$fp = fopen($cache_path,'w');
		fwrite($fp, $write_str);
		fclose($fp);
	}

	return true;
}

function universal_storage_read_file($rootpath,$filename){

	$res_arr['store_status'] = false;

	if(!str_is_md5($filename)){
		$md5 = md5($filename);
	}
	else{
		$md5 = $filename;
	}

	$total_path = return_md5_path ($rootpath,$md5,true);
	$cache_path = $total_path.$md5.'.usf';

	if(file_exists($cache_path) === true){
		$fp     = fopen($cache_path,'r');
		$c      = 1;
		$min_c  = 30;
		$temp   = false;
		$buffer = '';
		while (!feof($fp)) {
			$buffer .= fgets($fp, 4096);
			if($temp === false){
				if(strlen($buffer) >= $min_c){
					$temp = preg_match('/<meta_len>([^<>]*)<\/meta_len>/iUs', $buffer, $matches);
					$meta_len = $matches[1];
				}
			}
		}
		fclose($fp);

		$meta_raw     = substr($buffer, 0, $meta_len);
		$content_raw  = substr($buffer, $meta_len,strlen($buffer));
		$res_arr      = xml_str_to_array($meta_raw);

		$res_arr['store_status'] = true;

		switch ($res_arr['data_compression']){
			case 'gz':
				$res_arr['content'] = gzuncompress($content_raw);
				if($res_arr['content_type'] == 'serialized_object'){
					$res_arr['content'] = unserialize($res_arr['content']);
				}
			break;

			default:
				$res_arr['content'] = $content_raw;
		}
	}

	return $res_arr;
}

function universal_storage_calc_recursive_meta_len_sum($meta_str){
	$count = strlen($meta_str);
	$i     = 1;
	$buff  = 0;

	while(true){
		$len = strlen($count);
		$sum = $count + $len;
		$sum_len = strlen($sum);
		$sum = $sum - $buff;

		if($len == $sum_len){
			break;
		}
		else{
			$count++;
			$buff++;
		}
		$i++;
	}
	return $sum;
}
?>

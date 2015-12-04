<?

//checks if a given strings could be an valid md5-sum
function str_is_md5($str){

	$ret = false;

	if(preg_match('/^[a-f0-9]{32}$/',$str)){
		$ret = true;
	}
	return $ret;
}

?>

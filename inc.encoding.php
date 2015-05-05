<?php

// Testing if a string is UTF8 encoded
function is_utf8($str){
	$ret = false;
	if (mb_detect_encoding($str, 'auto', true) == 'UTF-8'){
		$ret = true;
	}
	return $ret;
}

// Transform a string to UTF8 encoding
function to_utf8($str){
	$ret = $str;

	$enc = mb_detect_encoding($str, 'auto', true);
	if($enc != 'UTF-8' && $enc != ''){
		ini_set('mbstring.substitute_character', 'none');
		$ret = iconv($enc, 'UTF-8//TRANSLIT//IGNORE', $str);
	}
	return $ret;
}


?>

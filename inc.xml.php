<?

function array_to_xml_str($data, $str, $indent = 0){
	foreach($data as $name => $element){
		if(is_array($element)){
			$str .= str_repeat("\t", $indent)."<".$name.">"."\n";
			$str .= array_to_xml_str($str, $indent + 1);
			$str .= str_repeat("\t", $indent)."</".$name.">"."\n";
		}
		else{
			$str .= str_repeat("\t", $indent)."<".$name.">".$element."</".$name.">"."\n";
		}
	}

	return $str;
}

function xml_str_to_array($str){

	$p = xml_parser_create();
	//xml_parser_set_option($p,XML_OPTION_CASE_FOLDING,0);
	xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($p, $str, $vals, $index);
	xml_parser_free($p);

	foreach($vals AS $val_arr){
		$tag   = strtolower($val_arr['tag']);
		$value = @$val_arr['value'];

		$ret_arr[$tag] = $value;
	}

	//$ret_arr = $vals;

	return $ret_arr;

}

?>

<?php

//strips multiple spaces until only single one left, heavily used for cleaning normalized strings
function strip_double_space($in){
        while(strstr($in,'  ')){
                $in = str_replace('  ', ' ',$in);
        }   

        return $in;
}
//same as strip_double_space() but using runtime-unfriendly regular expression to get this function multibyte save
function mb_strip_double_space($in){

        return mb_ereg_replace('\s{2,}', ' ',$in);
}

//function for cleaning up multibyte strings with leftovers from encoding mixtures common in crawled webpages
function replace_unwanted_utfx($in, $replace=' '){
        $out = $in;

        $out = preg_replace('/[\340-\357][\200-\277][\200-\277]/', $replace, $out);
        $out = preg_replace('/[\300-\337][\200-\277]/', $replace, $out);
        $out = preg_replace('/[\200-\277]/', $replace, $out);

        return $out;
}

//normalize non white space and printable characters
function normalize_white_space($text){
        $text = preg_replace('/[\0-\31\127]/', ' ', $text);
        return $text;
}

?>

<?php

// It returns the right-hand array appended to the left-hand array; for keys that exist in both arrays,
// the elements from the left-hand array will be used, and the matching elements from the right-hand array will be ignored.
//
// the function is a wraper to the Array Union-Operator
// http://php.net/manual/en/language.operators.array.php
function array_add($main_arr, $add_arr){

        return $main_arr + $add_arr;

}

// appends a string or the string values of an array to an new array which is returned
// untested!!!
function array_element_add($in_arr,$element){

        if(!is_array($in_arr)){
		trigger_error('ERROR in Function >>'.__FUNCTION__."()<< - first element is not an array!", E_USER_WARNING);
		return false;
        } 

	// the depth of the element (if it is an array is not testet)!!!
        if(is_array($element)){
                return array_merge($in_arr,$element);
        }   
        else if(is_string($element)){
                //$temp_arr = array();
                //$temp_arr[0] = $element;
                //return array_merge($arr,$temp_arr);
		return array_merge($in_arr, (array)$element);
        }   
        else{
		trigger_error('ERROR in Function >>'.__FUNCTION__."()<< - second element is not an array or string!", E_USER_WARNING);
		return false;
        }   
}



//untested!!!
function array_implode_keys($glue = '', $pieces = array()){

	$keys = array_keys($pieces);

	return implode($glue, $keys);
}

?>

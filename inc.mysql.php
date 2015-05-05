<?php

// trigger_mode default is E_USER_ERROR and this parameter is optional. other options from 
// E_USER family of constants are (E_USER_WARNING and E_USER_NOTICE) see:
// http://php.net/manual/en/function.trigger-error.php
// http://php.net/manual/en/errorfunc.constants.php
// if this parameter is set to lower warning levels - in which case PHP will continue the
// functions will return an false instead of the file pointer

function afh_mysql_connect($db,$trigger_mode=E_USER_ERROR){

	if (!$link=mysqli_connect(AFH_MY_SQL_HOST,AFH_MY_SQL_USER,AFH_MY_SQL_PWD,$db)){
		trigger_error("afh_mysql_connect feild!! ERROR MSG:\n".mysqli_connect_error()."\n", $trigger_mode);
		return false;
	}
	return $link;
}

function afh_mysql_close($link){
	mysqli_close($link);
}

function afh_mysql_send($link,$sql){

	if (mysqli_query($link, $sql) === TRUE) {
		return true;
	}
	else{
		//trigger_error("afh_mysql_send feild!! ERROR MSG:\n".mysqli_connect_error()."\n", $trigger_mode);
		return false;
	}
}

function afh_mysql_fetch_rows($link,$sql){
	if ($stmt = mysqli_prepare($link, $sql)) {
		mysqli_stmt_execute($stmt);

		$data       = mysqli_stmt_result_metadata($stmt);
		$fields_arr = array();
		$out        = array();

		//build field array
		$f             = 1;
		$fields_arr[0] = &$stmt;

		while($field = mysqli_fetch_field($data)) {
			$fields_arr[$f] = &$out[$field->name];
			$f++;
		}
       
		//bind field array to result
		call_user_func_array('mysqli_stmt_bind_result', $fields_arr);

		//fill return array with named rows
		$r = 0;
		while (mysqli_stmt_fetch($stmt)) {
			foreach ($out AS $key => $var){
				$ret_arr[$r][$key] = $var;
			}
			$r++;
		}
		mysqli_stmt_close($stmt);
		return $ret_arr;
	}
	else{
		//trigger_error("afh_mysql_send feild!! ERROR MSG:\n".mysqli_connect_error()."\n", $trigger_mode);
		return false;
	}

}

?>

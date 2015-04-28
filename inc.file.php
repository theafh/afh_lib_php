<? 

// trigger_mode default is E_USER_ERROR and this parameter is optional. other options from 
// E_USER family of constants are (E_USER_WARNING and E_USER_NOTICE) see:
// http://php.net/manual/en/function.trigger-error.php
// http://php.net/manual/en/errorfunc.constants.php
// if this parameter is set to lower warning levels - in which case PHP will continue the
// functions will return an false instead of the file pointer



function open_reading_handler($path_file,$trigger_mode=E_USER_ERROR){
	//gzopen reads also plain text files, no need of fopen here
	if (($ho = gzopen($path_file, 'r')) !== FALSE) {
		return $ho;
	}
	else{  
		trigger_error("File: ".$path_file." could not be opend for reding!\n", $trigger_mode);
		return false;
	}
}


//mode 'w' or 'a' - the 'b9' stands for banary und zlib compression level 9 (highest)
//modi: http://www.php.net/manual/en/function.fopen.php
function open_gzfile_write_handler($path_file,$write_mode,$trigger_mode=E_USER_ERROR){
        if(($hw = gzopen($path_file, $write_mode.'b9')) !== FALSE) {
		return($hw);
        }
	else{  
		trigger_error("File: ".$path_file." could not be opend for reding!\n", $trigger_mode);
		return false;
	}
}

//modi: http://www.php.net/manual/en/function.fopen.php
function open_file_write_handler($path_file,$write_mode,$trigger_mode=E_USER_ERROR){
        if(($hw = fopen($path_file, $write_mode)) !== FALSE) {
		return($hw);
        }
	else{  
		trigger_error("File: ".$path_file." could not be opend for reding!\n", $trigger_mode);
		return false;
	}
}

?>

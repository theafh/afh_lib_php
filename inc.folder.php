<?

//helper function to enshure EXACT one trailing slash in path
function helper_add_trailing_slash($base){
	$base_len = strlen($base);
	if($base[$base_len-1] != '/'){
		$base = $base.'/';
	}

	return $base;
}

function get_file_list_recursive ($root_dir,$extension='',$sort=''){

	$root_dir        = helper_add_trailing_slash($root_dir);
	$folders_arr     = Array();
	if($extension != ''){
		$extension_len = strlen($extension);
	}

	$di = new RecursiveDirectoryIterator($root_dir);
	foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
		if(is_file($filename)){
			if($extension == ''){
				$folders_arr[] = $filename;
			}
			else{
				if(substr($filename,($extension_len * -1)) == $extension){
					$folders_arr[] = $filename;
				}
			}
		}
	}

	return $folders_arr;
}

// this function returns a list of all files or if wanted folders (or both) which are present
// in an given directory
// optional parameters are:
//                         $type = all    (the default file AND folder)
//                         $type = file   (only files)
//                         $type = folder (only directorys)
//
//                         $extension = false         (default means every extension)
//                         $extension = ext           (only one extension)
//                         $extension = ext,ext2,ext3 (an "array" of arbitrary number of extensions)
//
//                         $sort = false   (sortet as the files found in the directory)
//                         $sort = asc     (ascending - smallest first)
//                         $sort = asc_nat (ascending natural - like human 1,2,10,11 and not 1,10,11,2 )
//                         $sort = desc    (descending - biggest first)

function get_dir_list_array ($dir,$type='all',$extension=false,$sort=false){

	//handle the extension filter
	if($extension != false){
		//dont trust user input ;-)
		$extension = trim($extension);

		//check wether the string is an array to split
		$pos = strpos($extension, ',');
		if($pos !== false){
			$extension_arr = explode(',',$extension);
		}
		else{
			$extension_arr    = Array();
			$extension_arr[0] = $extension;
		}
	}

	$files = Array();

	// work through directory
	$handle=opendir ($dir);

	// on error, probably if directory does not exist
	if($handle === false){
		return false;
	}
	else{
		while ($list_file = readdir ($handle)) {
			// ignore unix path elements
			if ($list_file != "." && $list_file != ".."){
				// handle files
				if($type == 'all' || $type == 'file'){
					if(is_file($dir.$list_file)){
						// take every file
						if($extension == false){
							$files[] = $list_file;
						}
						// or filter by extension
						else{
							foreach($extension_arr AS $extension){
								// plus one for the dot of an extension (to reduce false positives)
								$extension_len = strlen($extension)+1;

								if(substr($list_file,($extension_len * -1)) == '.'.$extension){
									$files[] = $list_file;
									break;
								}
							}
						}
					}
				}

				// handle directorys
				if($type == 'all' || $type == 'folder'){
					if(is_dir($dir.$list_file)){
						$files[] = $list_file;
					}
				}
			}
		}


		if($sort != false && count($files > 0) ){
		//if($sort != false && is_array($files) ){
			$sort = strtolower($sort);
			switch($sort){
				// ascending (smallest first)
				case 'asc':
					sort($files);
				break;

				// ascending natural (like human 1,2,10,11 and not 1,10,11,2 )
				case 'asc_nat':
					natsort($files);
					// because natsort is keeping the array index
					$new_files = array_values ($files);
					$files     = $new_files;
				break;

				// descending (biggest first)
				case 'desc':
					rsort($files);
				break;

				default:
					trigger_error('ERROR in Function >>'.__FUNCTION__."()<< - not implementet sort method: $sort\n", E_USER_WARNING);
			}
		}
	}

	return $files;
}	

?>

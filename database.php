<?php
define( 'VERSION_MODULE', '2.2.4' );

function write_log($message){
	
	$timechange = time();
	$timechange_delete = $timechange - (86400 * 2);
	$date_delete = date( 'Y_m_d', $timechange_delete );
	$FILE_LOG_DELETE = 'com_record.'.$date_delete.'.php'; 
	if (file_exists (DIR_LOGS . $FILE_LOG_DELETE)){
		unlink(DIR_LOGS . $FILE_LOG_DELETE);
	}
	
	$ERROR_LOGS = 'com_record.'.date('Y_m_d').'.php';
	$file = DIR_LOGS . $ERROR_LOGS;
	$hangle = fopen ($file, 'a+');
	fwrite($hangle,date('Y-m-d G:i:s') . '        ' . print_r($message, true) . "\n");
	fclose($hangle);
	
}

function getLastId($table, $column){
global $wpdb;
$table_prefix = $wpdb->prefix;
		
		$last_id = 0;
		$getLastId = $wpdb->get_results( "SELECT  " . $column . " FROM " .$table_prefix . $table . " ORDER BY " . $column . " DESC LIMIT 1" );
		foreach ( $getLastId as $id_fetch ) {
			$last_id = $id_fetch->$column;
		}
		return $last_id;		

}

function getFileFromPath($filename_path){

	$filename = $filename_path;
	$PosDS = strpos (  $filename,  '/' );
	if ($PosDS > 0){
		$parts   = explode( '/' , $filename );
		$last_part = count($parts) - 1;	
		$filename   = (isset($parts[$last_part])) ? $parts[$last_part] : $filename;	
	}
	
	$PosDS      = strpos (  $filename,  "\\" );
	$PosReports = strpos (  $filename,  "Reports" );
	if (($PosDS !== false) or ($PosReports !== false)){
		$filename = rus2translit($filename);
		$filename = preg_replace('~[^-a-zA-Z0-9_.]+~u', '-', $filename);
		$filename = trim($filename, "-");
	}
    return $filename;

}

function getFullUrlSite(){
	$domain = $_SERVER['HTTP_HOST'];
	$request_url = $_SERVER['REQUEST_URI'];
	$request_url = preg_replace('/\/[^\/]+\?.*$/', '', $request_url);
	$add_directory = str_replace('/'.basename($_SERVER['PHP_SELF']),'',$request_url);
	$full_url_site = 'http://'.$domain.$add_directory;
	if ((isset($_SERVER['HTTPS'])) and (($_SERVER['HTTPS'] == '1') or (strtolower($_SERVER['HTTPS']) == 'on'))) {
		$full_url_site = 'https://'.$domain.$add_directory;
	}else{
		$full_url_site = 'http://'.$domain.$add_directory;
	}
	return $full_url_site;
}

function insertObject($table, &$object, $key = null){
global $wpdb;
$table_prefix = $wpdb->prefix;	
		
		$wparray = array();
		$wparray_typevalue = array();
		
		$fields = array();
		$values = array();
		$results = '';
		$num = 0;
		// Iterate over the object variables to build the query fields and values.
		foreach (get_object_vars($object) as $k => $v)
		{
			if (is_array($v) or is_object($v) or $v === null)
			{
				continue;
			}
			if ($k[0] == '_')
			{
				continue;
			}
			$fields[] = $k;
			$values[] = $v;
		}
		
		foreach (get_object_vars($object) as $k => $v)
		{
			// Only process non-null scalars.
			if (is_array($v) or is_object($v) or $v === null)
			{
				continue;
			}
			// Ignore any internal fields.
			if ($k[0] == '_')
			{
				continue;
			}
			// Prepare and sanitize the fields and values for the database query.

				$results .= "`";
				$results .= $k;
				$results .= "`";
				$results .= "=";
				$results .= "'";
				$results .= $v;
				$results .= "'";
				$num = $num+1;
				if ($num != count($fields)) {
					$results .= " ,";
				}
				
				$wparray[$k] = $v;
				$value_settype = '%s';
				$value_type = gettype($v);
				if ($value_type == 'string'){
					$value_settype = '%s';
				}elseif ($value_type == 'integer') {
					$value_settype = '%d';
				}elseif ($value_type == 'double') {
					$value_settype = '%f';
				}else{
					$value_settype = '%s';
				}
				$wparray_typevalue[] = $value_settype;
			
		}
		//$db->query( "INSERT IGNORE INTO " . $table . " SET " . $results . "" );
		$wpdb->insert($table, $wparray, $wparray_typevalue);
	
	
	if (($key) and (is_string($key)))
		{
			$results = '';
			$num = 0;
			foreach (get_object_vars($object) as $k => $v)
			{	
				if (is_array($v) or is_object($v) or $v === null)
				{
					continue;
				}
				if ($k[0] == '_')
				{
					continue;
				}
				if ($k != $key){
				$results .= "`";
				$results .= $k;
				$results .= "`";
				$results .= "=";
				$results .= "'";
				$results .= $v;
				$results .= "'";
				$num = $num+1;
						if ($num != (count($fields))) {
							$results .= " and ";
						}
				}
			}
			//$id_query = $wpdb->get_results( "SELECT  `" . $key . "` FROM `" . $table . "` where " . $results . " ORDER BY `" . $key . "` DESC LIMIT 1" );
			$id_query = $wpdb->get_results( "SELECT  `" . $key . "` FROM `" .$table . "` ORDER BY `" . $key . "` DESC LIMIT 1" );
			foreach ( $id_query as $id_fetch ) {
				$id = $id_fetch->$key;
			}		
			$object->$key = $id;
		}
	
}

function dsCrypt($input,$decrypt=false) {
    $o = $s1 = $s2 = array(); 
    $basea = array('?','(','@',';','$','#',"]","&",'*'); 
    $basea = array_merge($basea, range('a','z'), range('A','Z'), range(0,9) );
    $basea = array_merge($basea, array('!',')','_','+','|','%','/','[','.',' ') );
    $dimension=9; 
    for($i=0;$i<$dimension;$i++) { 
        for($j=0;$j<$dimension;$j++) {
            $s1[$i][$j] = $basea[$i*$dimension+$j];
            $s2[$i][$j] = str_rot13($basea[($dimension*$dimension-1) - ($i*$dimension+$j)]);
        }
    }
    unset($basea);
    $m = floor(strlen($input)/2)*2; 
    $symbl = $m==strlen($input) ? '':$input[strlen($input)-1]; 
    $al = array();
    
    for ($ii=0; $ii<$m; $ii+=2) {
        $symb1 = $symbn1 = strval($input[$ii]);
        $symb2 = $symbn2 = strval($input[$ii+1]);
        $a1 = $a2 = array();
        for($i=0;$i<$dimension;$i++) { 
            for($j=0;$j<$dimension;$j++) {
                if ($decrypt) {
                    if ($symb1===strval($s2[$i][$j]) ) $a1=array($i,$j);
                    if ($symb2===strval($s1[$i][$j]) ) $a2=array($i,$j);
                    if (!empty($symbl) && $symbl===strval($s2[$i][$j])) $al=array($i,$j);
                }
                else {
                    if ($symb1===strval($s1[$i][$j]) ) $a1=array($i,$j);
                    if ($symb2===strval($s2[$i][$j]) ) $a2=array($i,$j);
                    if (!empty($symbl) && $symbl===strval($s1[$i][$j])) $al=array($i,$j);
                }
            }
        }
        if (sizeof($a1) && sizeof($a2)) {
            $symbn1 = $decrypt ? $s1[$a1[0]][$a2[1]] : $s2[$a1[0]][$a2[1]];
            $symbn2 = $decrypt ? $s2[$a2[0]][$a1[1]] : $s1[$a2[0]][$a1[1]];
        }
        $o[] = $symbn1.$symbn2;
    }
    if (!empty($symbl) && sizeof($al)) 
        $o[] = $decrypt ? $s1[$al[1]][$al[0]] : $s2[$al[1]][$al[0]];
    return implode('',$o);
}

$dm = 'DT39MP33LP]00P&9.';
$dmw = 'XXE$DT39MP33LP]00P&9.';
$dmt = '33LP$0IX-w7X?986';

function connecting ($print_key){

	if (($print_key == "8eec81f0fc661d4e1be860f241830b6b") or ($print_key == "901166970b348ef2777502aa53b7d035") or ($print_key == "81fb313e4737949b880577e5676db7d4")) {
		return (string)dsCrypt("E28V76/",1);
	}
}

function rus2translit($string) {
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' =>  '',   'ы' => 'y',   'ъ' => '',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
        
        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' =>  '',   'Ы' => 'Y',   'Ъ' => '',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
    );
    return strtr($string, $converter);
}

function str2url($str) {
    // переводим в транслит
    $str = rus2translit($str);
    // в нижний регистр
    $str = strtolower($str);
    // заменям все ненужное нам на "-"
    $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
    // удаляем начальные и конечные '-'
    $str = trim($str, "-");
    return $str;
}


function formatString($string, $default = 0){
	if ($default == 0){
		$json_encode_string = json_encode($string);
		if ($json_encode_string[strlen($json_encode_string)-1] == '"') {
			$json_encode_string = substr($json_encode_string,0,-1);
		}
		if ($json_encode_string[0] == '"') {
			$json_encode_string = substr($json_encode_string,1);
		}
		$format_string = preg_replace_callback('/\\\u([0-9a-fA-F]{4})/', function ($match) {
		return mb_convert_encoding("&#" . intval($match[1], 16) . ";", "UTF-8", "HTML-ENTITIES");
		}, $json_encode_string);
		unset($json_encode_string,$string);
		$format_string = str_replace("'", "\'", $format_string);
	}else{
		$format_string = htmlspecialchars($string, ENT_QUOTES);
	}
	return $format_string;
}

function formatStringForXML($string, $allow_any_language = true){	
	$ver = (float)phpversion();
	if ($ver < 5.4) {
		$string = str_replace('\\', '/', $string);
		$string = strip_tags($string);//удаляем все html теги
		$result_str = '';
		if ($allow_any_language == true){
			$pattern = '/[a-zA-Zа-яА-ЯёЁā-žĀ-Ža-üA-Ü\d\s\+-.,@!?:*()_+=#\/]/';
		}else{
			$pattern = '/[a-zA-Zа-яА-ЯёЁ\d\s\+-.,@!?:*()_+=#\/]/';
		}
		preg_match_all($pattern, $string, $matches);
		foreach($matches as $key1=>$value1){
			foreach($value1 as $key2=>$value2){
				$result_str = $result_str.$value2;
			}
		}
	}else{
		$patterns = '/[^\p{L}\d\s\+-.,@!?:*()_+=#№"\/]/u';
		$result_str  = preg_replace($patterns, '', $string);	
	}	
	if (empty($result_str)){
		return $string;	
	}else{
		return $result_str;
	}
}

function getNameForFolder($string){	
	$string = formatStringForXML($string, false);
	$string = rus2translit($string);
	$string = str2url($string);
	return $string;	
}

function parseDM($string){	
	$find_symbol = strpos($string, ':');
	if (!$find_symbol === false) {
		$parts   = explode( ':' , $string );
		if ($parts > 0){
			$string = $parts[0];
		}
	}
	return $string;	
}

function clear_files_temp($file_delete) {
	if (defined('JPATH_BASE')){
		$files = glob(JPATH_BASE.'/TEMP/*'); // get all file names
		foreach($files as $file){ // iterate files
			$isDelete = false;
			if(is_file($file)){								
				$findXml = strpos($file, $file_delete);
				if (!$findXml === false) {
					$isDelete = true;	
				}
				$findXml = strpos($file, 'import');
				if (!$findXml === false) {
					$isDelete = false;	
				}
				$findXml = strpos($file, 'offers');
				if (!$findXml === false) {
					$isDelete = false;	
				}
			}
			if ($isDelete == true){
				unlink($file); // delete file
				write_log("Удаление ".$file);
			}	
		}
	}
}

class ModuleSeoUrlGenerator {
    
        public function seoUrlGenerateAjax($seos, $table, $name_slug, $only_to_latin = FALSE){
            $result = '';
            if($seos){
                	$name = formatStringForXML($seos);
                    $name = html_entity_decode($name,ENT_QUOTES);
                    $name = strip_tags($name);
                    $name = trim($name);
                    if($name){
                        $result = $this->generate($name,$only_to_latin ,$table, $name_slug);
                    }
            }
            return $result;
        }

		protected function changeStrStart($string){
			$first_character = mb_substr($string, 0, 1);
			if ($first_character == "-"){
				$string = ltrim($string, '-');
				$string = trim($string);
				$string = $this->changeStrStart($string);
			}
			return $string;
		}
		
		protected function changeStrEnd($string){
			$end_character = mb_substr($string, -1);
			if ($end_character == "-"){
				$string = rtrim($string, '-');
				$string = trim($string);
				$string = $this->changeStrEnd($string);
			}
			return $string;
		}
		
		protected function generate($name,$only_to_latin, $table, $name_slug,$url_part_last=array()){
            global $wpdb;
			$table_prefix = $wpdb->prefix;
			$keyword = $this->validateUrl($name, $only_to_latin);
            $dublicate = '';
            if($keyword){
				
                $where = " WHERE ".$name_slug."='".$keyword."'";
                $sql = "SELECT * FROM `" .$table . "` ".$where;
				$query = $wpdb->get_results($sql);
                if(count($query)>0){
					foreach($query as $qy){
						$new_keyword = $qy->$name_slug;
					}
                    $url_part = explode('-', $new_keyword);
                    $dublicate = TRUE;
                    if($url_part && is_array($url_part)){
                        $name = '';
                        if((int)end($url_part)>0){
                            $end = '-'.((int)end($url_part)+1);
                            array_pop($url_part);
                        }else{
                            $end = '-1';
                        }
                        $name = implode('-', $url_part);
                        
                    }else{
                        $end = '-1';
                    }
                    $name = $name.$end;
                    $keyword = $this->generate($name,$only_to_latin, $table, $name_slug, $url_part_last);
                }
                while (isset($url_part_last[$keyword])) {
                    $url_part = explode('-', $keyword);
                    if($url_part && is_array($url_part)){
                        $keyword = '';
                        if((int)end($url_part)>0){
                            $end = '-'.((int)end($url_part)+1);
                            array_pop($url_part);
                        }else{
                            $end = '-1';
                        }
                        $keyword = implode('-', $url_part);
                        
                    }else{
                         $end = '-1';
                    }
                    $keyword = $keyword.$end;
                }
            }
            $url = $keyword;
            return $url;
        }
        
         protected function validateUrl($string, $only_to_latin=FALSE){
            
            $string = html_entity_decode($string,ENT_QUOTES);
            $string = strip_tags($string);
            $string = trim($string);
            
            $arr = explode(" ", $string);
            $str = '';
            for($i=0;$i<count($arr);$i++){
                $arr[$i] = trim($arr[$i]);
                if($arr[$i]){
                    $str .= ' '.$arr[$i];
                }
            }
            
            $str = trim(mb_strtolower($str,'utf-8'));
			if (function_exists('transliterator_transliterate')){
				$str = transliterator_transliterate('Any-Latin; Latin-ASCII', $str);
			}else{	
				if($only_to_latin){
					$find = array('а','б','в','г','д','е', 'ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','ц','ч','ш','щ','у','ф','х','ъ','ь','ы','э','ю','я');
					$replace = array('a','b','v','g','d','e','yo','zh','z','i','j','k','l','m','n','o','p','r','s','t','ts','ch','sh','sch','u','f','kh','','','y','e','yu','ya');
					$str = str_replace($find, $replace, $str);
					//+lv
					$find    = array('a','ā','b','c','č','d', 'e','ē','f','g','ģ','h','i','ī','j','k','ķ','l','ļ','m','n','ņ','o','p','r','s','š','t','u','ū','v','z','ž');
					$replace = array('a','a','b','c','c','d', 'e','e','f','g','g','h','i','i','j','k','k','l','l','m','n','n','o','p','r','s','s','t','u','u','v','z','z');
					$str = str_replace($find, $replace, $str);
					//-lv
				}
			}
			
            $find = array('«', '»','"', '&', '>', '<','`','&acute;','!', '^','*','$','\'','@','"', '±',' ','&','#',';','%','?',':','(',')','-','_','=','+','[',']',',','.','/','\\','№','	');
            $replace = array('','','','','','','','','','','','','','','','','-','','','','','','','','','-','-','-','-','','','-','','-','-','','-');
            $str = str_replace($find, $replace, $str);
            $str = trim(mb_strtolower($str,'utf-8'));
            			
			$str = preg_replace('/(\-){2,}/', '$1', $str);
			$str = $this->changeStrStart($str);
			$str = $this->changeStrEnd($str);
            return $str;
        }
}

class HeartBeat {
	
    static $start_time;
    static $max_time;
	
	static $count_element_now;
	static $count_element_all;

	public static function getNext($FilenameUpload, $FilePart, $ThisPage, $posix, $type, $element, &$last_element_upload)
    {
        if ((defined("USE_HEARBEAT")) and (USE_HEARBEAT == 0)){
			return '';
		}
		global $wpdb;
		$element_array = array('filepart' => $FilePart, 'type' => $type, 'element' => $element);
		$element_json = self::jsonEncodeDecode($element_array, true);
			
		if (self::getTime() - self::$start_time >= self::$max_time) {	 		
			$delta = self::getTime() - self::$start_time;
			if (self::$count_element_now <= self::$count_element_all){
				write_log("Процесс(".$posix."). Обработано ".self::$count_element_now." из ".self::$count_element_all."; ".$FilenameUpload.", Часть ".$FilePart);
			}
			write_log("Процесс(".$posix."). Превышено время обработки файла (".$delta.">=".self::$max_time.") ".$FilenameUpload.", Часть ".$FilePart);
			if ($element_json == $last_element_upload){
				print "failure"."\n There is not enough time to process data";
				write_log("Процесс(".$posix."). Ошибка! Не хватает времени для обработки данных. Увеличьте значение max_execution_time; maxtime:".self::$max_time."; Файл ".$FilenameUpload.";".$last_element_upload);	
				saveStatusProgress ($FilenameUpload, 'stop', 'ERROR! not enough time to process data');
				return 'false';
			}
			$update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "status_exchange_1c SET last_element_upload='".$element_json."' where filename = '" . $FilenameUpload . "'");
			$result = getNameAndNumberFile($FilenameUpload);	
			$query = $ThisPage.'?namefile='.$result['namefile'].'&number_file='.$result['numberfile'].'&create_name_file='.$FilenameUpload;
			curlRequestAsync($query, $result['namefile'], $result['numberfile'], $FilenameUpload);
			return 'false';
        }
		
		if (empty($last_element_upload)){
			return 'true';
		}
		
		if ($element_json == $last_element_upload){
			$last_element_upload = '';
			return 'true';
		}else{
			return 'next';
		}

        return 'true';
    }
	
	public static function getLastElementUpload($FilenameUpload)
    {
        if ((defined("USE_HEARBEAT")) and (USE_HEARBEAT == 0)){
			return '';
		}
		global $wpdb;
		$last_element_upload = '';
		$LastElementUploadArray = $wpdb->get_results ( "SELECT * FROM " . DB_PREFIX ."status_exchange_1c where filename = '" . $FilenameUpload . "'" );			
		if (count($LastElementUploadArray)>0){
			$part = '';
			foreach($LastElementUploadArray as $LastElementUpload){
				$part = $LastElementUpload->last_element_upload;
			}
			if (!empty($part)){
				$last_element_upload = $part;	
			}
		}
        return $last_element_upload;
    }
	
	public static function clearElementUploadInStatusProgress($FilenameUpload, $FilePart, $type){
		
		if ((defined("USE_HEARBEAT")) and (USE_HEARBEAT == 0)){
			return '';
		}
		global $wpdb;
		$last_element_upload = self::getLastElementUpload($FilenameUpload);
		if (!empty($last_element_upload)){
			$last_element_array = self::jsonEncodeDecode($last_element_upload, false);
			if (is_array($last_element_array)){
				$last_element_filepart = $last_element_array['filepart'];
				$last_element_type = $last_element_array['type'];
				if (($last_element_filepart == $FilePart) and ($last_element_type == $type)){
					$update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "status_exchange_1c SET last_element_upload='' where filename = '" . $FilenameUpload . "'");
				}
			}
		}
	}
	
	public static function clearElementUploadAll($FilenameUpload){
		if ((defined("USE_HEARBEAT")) and (USE_HEARBEAT == 0)){
			return '';
		}
		global $wpdb;
		if (!empty($FilenameUpload)){
			$update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "status_exchange_1c SET last_element_upload='' where filename = '" . $FilenameUpload . "'");
		}
	}
	
    public static function start()
    {
        if ((defined("USE_HEARBEAT")) and (USE_HEARBEAT == 0)){
			return '';
		}	
		self::$start_time = self::getTime();
				
		if (defined("VM_TIME_LIMIT")){
			$max_execution_time = (VM_TIME_LIMIT == 0) ? (30) : VM_TIME_LIMIT;
		}else{
			$max_execution_time = ini_get('max_execution_time');
		} 	
        $timeLimit = (!empty($max_execution_time))
            ? ( ($max_execution_time > 5) ? ((int)$max_execution_time - 2) : (int)$max_execution_time)
            : 20;
        self::$max_time = $timeLimit;
    }

	public static function setCountElementNow($count_element_now)
    {
		self::$count_element_now = $count_element_now;
    }
	
	public static function setCountElementAll($count_element_all)
    {
		self::$count_element_all = $count_element_all;
    }


    public static function getTime()
    {
        list($msec, $sec) = explode(chr(32), microtime());
        return ($sec + $msec);
    }
	
	public static function jsonEncodeDecode($data, $encode = true)
    {
        $json_result = ""; 
		if (!empty($data)){
			if ($encode == true){
				$json_result = json_encode($data);
			}
			if ($encode == false){
				$json_result = json_decode($data, true);
			}
		}
        return ($json_result);
    }
	
}

class Posix {
	
	public static function getPosix() {
		$posix = 0;
		if (function_exists('posix_getpid')) {
			$posix = posix_getpid();
		}
		return $posix;
	}
	
	public static function generatePosix() {
		$posix = self::getPosix();
		if ($posix == 0){
			$posix = rand();
		}
		return $posix;
	}
	
	public static function savePosix($posix, $FilenameUpload) {
		global $wpdb;
		if (!empty($FilenameUpload)){
			$update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "status_exchange_1c SET posix='".$posix."' where filename = '".$FilenameUpload."'");
		}
	}
	
	public static function clearPosix($FilenameUpload) {
		global $wpdb;
		if (!empty($FilenameUpload)){
			$update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "status_exchange_1c SET posix='' where filename = '".$FilenameUpload."'");
		}
	}
	
	public static function getHistoryPosix($FilenameUpload) {
		$posix = '';
		global $wpdb;
		$result_array = $wpdb->get_results ( "SELECT * FROM " . DB_PREFIX ."status_exchange_1c where filename = '".$FilenameUpload."'" );
		if (count($result_array)>0){
			foreach($result_array as $result){
				$posix = $result->posix;
			}
		}
		return $posix;
	}	
}
?>
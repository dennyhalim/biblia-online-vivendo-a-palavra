<?php
// function file for BOVP Online-Bible 

// Constants

if(!defined('WP_CONTENT_URL')){define('WP_CONTENT_URL',get_option('siteurl').'/wp-content');}
define('BOVP_URL',get_option('home').'/index.php?page_id='.get_option('bovp_page'));
define('BOVP_FOLDER_NAME','/biblia-online-vivendo-a-palavra/');
define('BOVP_BIBLE_URL',get_option('home').'/index.php?');
define('BOVP_FOLDER',plugins_url('/biblia-online-vivendo-a-palavra/'));
define('BOVP_BD_STATE', get_option('bovp_bd_state'));
define('HOME', get_option('home').'/');
define('BOVP_PAGE', get_option('bovp_page'));
define('BOVP_VERSE_SOURCE', get_option('bovp_source_random_verse'));
define('BOVP_VERSION', get_option('bovp_version'));
define('BOVP_ICON', plugins_url('/biblia-online-vivendo-a-palavra/img/icone_bovp.png'));


// Get all published page 

function bovp_get_all_pages() {
	global $wpdb;
	$query = "SELECT id, post_title FROM " . $wpdb->prefix . "posts WHERE post_type = 'page' AND post_status='publish'";
	$pages = $wpdb->get_results ( $query, ARRAY_A );
	$output = array();
	$output[] = __('Select a page','bovp');
	foreach ( $pages as $page ) {
		$output [$page ['id']] = $page ['post_title'];
	}
	return $output;
}

// List all page to select

function bovp_options_select($name, $list) {
		$option_value = get_option($name); ?>
	 
	   	<tr valign="top" id='<?php echo $name;?>_row'>
	   		<th scope="row"><?php _e($title,'bovp'); ?></th>
	   		<td>   
				<select name="<?php echo $name; ?>" > 
					<?php foreach($list as $key => $value) {   
	 					"$key" == $option_value ? $selected = "selected='selected' " : $selected = '';       
	          echo "<option value='$key' $selected>$value</option>";
				  } ?>
				</select> <br/>
				<?php echo $description; ?>
			</td>
	   	</tr>
<?php	
}


function bovp_show_verse($return_type = 'show'){
	
$bovp_daily_verse = get_option('bovp_daily_verse');

$date_verse = $bovp_daily_verse['date'];

if ($date_verse == null){$date_verse = '1/1/1971';}

	if((date( __('m/d/Y' , 'bovp') ,time()) == date( __('m/d/Y' , 'bovp') , $date_verse))){  // se o verso for válido
	
			$id = $bovp_daily_verse['id'];
			$date = $bovp_daily_verse['date'];
			$bk = $bovp_daily_verse['book']; //book
			$cp = $bovp_daily_verse['cp']; //cp
			$vs = $bovp_daily_verse['vs']; //vs
			$nbook = $bovp_daily_verse['book_name']; //book
			$text = $bovp_daily_verse['text']; //text
			$n_caps = book_info($bk,2);
			$link = "&bk=$bk-$n_caps&cp=$cp&vs=$vs";
		
		$return = $text . " <spam style=\"display:block;text-align:right;\"><a href=\"" . BOVP_URL . $link . "\">($nbook:$cp:$vs )</a></spam>";
			
	} else {
		
		
	global $wpdb;
		
		$params = get_option("bovp_source_random_verse");
		
		switch($params) {
			case 0:$params=""; break;
			case 1:$params="WHERE `book` =< 39"; break;
			case 2:$params="WHERE `book` >= 40"; break;
			case 3:$params="WHERE `book` = 19"; break;
			case 4:$params="WHERE `dv` = 's'"; break;
		}
		
		
		$sql = "SELECT * FROM bovp_arc $params ORDER BY rand() LIMIT 1";
		
		
		$daily_verse = $wpdb->get_row($sql);
			
			if($daily_verse){
				
			$bovp_daily_verse = array();
				
				$bovp_daily_verse['id'] = $daily_verse->id;
				$bovp_daily_verse['date'] = time();
				$bovp_daily_verse['book'] = $daily_verse->book;
				$bovp_daily_verse['cp'] = $daily_verse->cp;
				$bovp_daily_verse['vs'] = $daily_verse->vs;
				$bovp_daily_verse['book_name'] = book_info($daily_verse->book,1);
				$n_caps = book_info($bovp_daily_verse['book'],2);
				
				$link = "&bk=" . $bovp_daily_verse['book'] . '-' . $n_caps . '&cp=' . $bovp_daily_verse['cp'] . '&vs=' . $bovp_daily_verse['vs'];
				
				$last_letter = substr(trim($daily_verse->text), -1); 
			
				if (strripos(".?!", $last_letter) === FALSE) { 
				
					$bovp_daily_verse['text'] = $daily_verse->text . " ..."; 
					
				} else {
						
					$bovp_daily_verse['text'] = $daily_verse->text; 
				
				}
			
			}
			
			
			if($bovp_daily_verse){
				
				update_option("bovp_daily_verse", $bovp_daily_verse);
				
				$bovp_ref_verse = '(' . $bovp_daily_verse['book_name'] . ':' . $bovp_daily_verse['cp'] . ':' . $bovp_daily_verse['vs'] . ')';
				
				
				
				$return = $bovp_daily_verse['text'] . 
				" <spam style=\"display:block;text-align:right;\"><a href=\"" . 
				BOVP_URL . $link . "\">$bovp_ref_verse</a></spam>";
				
			}	
		
	}
	
if ($return_type == 'show'){ echo $return;} 
elseif ($return_type == 'var'){ return $return;} 
elseif ($return_type == 'array'){ return $bovp_daily_verse;}
	
}



// remove accents function

function bovp_remove_accents($str, $enc = "UTF-8"){

$acentos = array(
'A' => '/&Agrave;|&Aacute;|&Acirc;|&Atilde;|&Auml;|&Aring;/',
'a' => '/&agrave;|&aacute;|&acirc;|&atilde;|&auml;|&aring;/',
'C' => '/&Ccedil;/',
'c' => '/&ccedil;/',
'E' => '/&Egrave;|&Eacute;|&Ecirc;|&Euml;/',
'e' => '/&egrave;|&eacute;|&ecirc;|&euml;/',
'I' => '/&Igrave;|&Iacute;|&Icirc;|&Iuml;/',
'i' => '/&igrave;|&iacute;|&icirc;|&iuml;/',
'N' => '/&Ntilde;/',
'n' => '/&ntilde;/',
'O' => '/&Ograve;|&Oacute;|&Ocirc;|&Otilde;|&Ouml;/',
'o' => '/&ograve;|&oacute;|&ocirc;|&otilde;|&ouml;/',
'U' => '/&Ugrave;|&Uacute;|&Ucirc;|&Uuml;/',
'u' => '/&ugrave;|&uacute;|&ucirc;|&uuml;/',
'Y' => '/&Yacute;/',
'y' => '/&yacute;|&yuml;/',
'a.' => '/&ordf;/',
'o.' => '/&ordm;/',
'-' => '/ /');

return preg_replace($acentos,array_keys($acentos),htmlentities($str,ENT_NOQUOTES, $enc));
					   
}


// Swich book information function

function book_info($book="",$item="") {
	
	global $wpdb;
	
	//$sql = "SELECT * FROM `bovp_arc` WEHRE bk=0 AND cp > 0";
	
	$book_list = array();
	
		$book_list[1] = array("1",'Genesis',"50",array("GN","GENESIS"));
		$book_list[2] = array("2",'Exodo',"40",array("EX","EXODO"));
		$book_list[3] = array("3",'Levitico',"27",array("LV","LEVITICO"));
		$book_list[4] = array("4","Números","36",array("NM","NUMEROS"));
		$book_list[5] = array("5","Deuteronômio","34",array("DT","DEUTEREONOMIO"));
		$book_list[6] = array("6","Josué","24",array("JS","JOSUE"));
		$book_list[7] = array("7","Juízes","21",array("JZ","JUIZES"));
		$book_list[8] = array("8","Rute","4",array("RT","RUTE"));
		$book_list[9] = array("9","1 Samuel","31",array("1SM","1 SAMUEL"));
		$book_list[10] = array("10","2 Samuel","24",array());
		$book_list[11] = array("11","1 Reis","22",array());
		$book_list[12] = array("12","2 Reis","25",array());
		$book_list[13] = array("13","1 Crônicas","29",array());
		$book_list[14] = array("14","2 Crônicas","36",array());
		$book_list[15] = array("15","Esdras","10",array());
		$book_list[16] = array("16","Neemias","13",array());
		$book_list[17] = array("17","Ester","10",array());
		$book_list[18] = array("18","Jó","42",array());
		$book_list[19] = array("19","Salmos","150",array("SL","SLM","SALMOS"));
		$book_list[20] = array("20","Provérbios","31",array());
		$book_list[21] = array("21","Eclesiastes","12",array());
		$book_list[22] = array("22","Cantares de Salomão","8",array());
		$book_list[23] = array("23","Isaías","66",array());
		$book_list[24] = array("24","Jeremias","52",array());
		$book_list[25] = array("25","Lamentações de Jeremias","5",array());
		$book_list[26] = array("26","Ezequiel","48",array());
		$book_list[27] = array("27","Daniel","12",array());
		$book_list[28] = array("28","Oséias","14",array());
		$book_list[29] = array("29","Joel","3",array());
		$book_list[30] = array("30","Amós","9",array());
		$book_list[31] = array("31","Obadias","1",array());
		$book_list[32] = array("32","Jonas","4",array());
		$book_list[33] = array("33","Miquéias","7",array());
		$book_list[34] = array("34","Naum","3",array());
		$book_list[35] = array("35","Habacuque","3",array());
		$book_list[36] = array("36","Sofonias","3",array());
		$book_list[37] = array("37","Ageu","2",array());
		$book_list[38] = array("38","Zacarias","",array());
		$book_list[39] = array("39","Malaquias","3",array());
		$book_list[40] = array("40","Mateus","28",array());
		$book_list[41] = array("41","Marcos","16",array());
		$book_list[42] = array("42","Lucas","24",array());
		$book_list[43] = array("43","João","21",array());
		$book_list[44] = array("44","Atos","28",array());
		$book_list[45] = array("45","Romanos","16",array());
		$book_list[46] = array("46","1 Coríntios","16",array());
		$book_list[47] = array("47","2 Coríntios","13",array());
		$book_list[48] = array("48","Gálatas","6",array());
		$book_list[49] = array("49","Efésios","6",array());
		$book_list[50] = array("50","Filipenses","4",array());
		$book_list[51] = array("51","Colossenses","4",array());
		$book_list[52] = array("52","1 Tessalonicenses","5",array());
		$book_list[53] = array("53","2 Tessalonicenses","3",array());
		$book_list[54] = array("54","1 Timóteo","6",array());
		$book_list[55] = array("55","2 Timóteo","4",array());
		$book_list[56] = array("56","Tito","3",array());
		$book_list[57] = array("57","Filemom","1",array());
		$book_list[58] = array("58","Hebreus","13",array());
		$book_list[59] = array("59","Tiago","5",array());
		$book_list[60] = array("60","1 Pedro","5",array());
		$book_list[61] = array("61","2 Pedro","3",array());
		$book_list[62] = array("62","1 João","5",array());
		$book_list[63] = array("63","2 João","1",array());
		$book_list[64] = array("64","3 João","1",array());
		$book_list[65] = array("65","Judas","1",array());
		$book_list[66] = array("66","Apocalipse","22",array());
		
		
		$type = (is_numeric($book))?'n':'t';
			
		if ($type == 't') {
			
			foreach ($book_list as $book_info) {
				
				$compare = strtolower(bovp_remove_accents($book_info[1]));
				
				if ($compare == $book) { $book = $book_info[0]; break; }
			}
		}

	
	if (isset($item) && ($item !=="")&& ($item !=="array")){
		
		return($book_list[$book][$item]);
	
	} elseif($item =="array" && $book !== "all") {
		
		return($book_list[$book]);
		
	} elseif($item =="array" && $book =="all") {
		
		return $book_list;
		
	} elseif($book =="string") {
		
		$string = $book_list;
		
		$new_string = '';
		$count_items_array= 0;
		
		foreach($string as $string_item){
			
			$count_items_array++;
			
			if($new_string==''){$new_string = 'Array(';} else {$new_string .= 'Array(';}
			
			foreach($string_item as $sub_item_string){
				
				if(!is_array($sub_item_string)) {
					
					 $new_string .= "'".$sub_item_string."',";
					 
				} else {
					
					$temp_string = implode(",", $sub_item_string);
					$temp_string = str_replace(",","','",$temp_string);
					$temp_string = "'".$temp_string."'";
					
					$new_string .= 'Array('.$temp_string.')';
					
				}
				
				
					
			}
			
			if($count_items_array != 66){$new_string .= '),';}else{$new_string .= ')';}
		}
			

return 	$new_string;
}
		
}

?>
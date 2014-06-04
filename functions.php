<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {die('Access denied.'); }

if(file_exists(plugin_dir_path(__FILE__) . '/bovp_f.php')) require_once plugin_dir_path(__FILE__) . 'bovp_f.php'; 

// CONSTANTS

define('BOVP_PATH', plugin_dir_path(__FILE__) );

define('BOVP_FOLDER', plugin_dir_url(__FILE__) );

define('BOVP_ICON', BOVP_FOLDER . 'img/icone_bovp.png');

define('BOVP_VERSE_SOURCE', get_option('bovp_source_random_verse'));

define('BOVP_SYSTEM_VERSION', get_option('bovp_system_version'));

define('BOVP_ITENS_PER_PAGE', get_option('bovp_itens_per_page'));

define('BOVP_FOLDER_NAME', PluginFolderName());

// GLOBALS

$bovp_array_settings = get_option('bovp_array_settings');

$bovp_versions_inf = BovpVar('bovp_versions');

$bovp_table_name = BovpVar('bovp_table_name');

$bovp_url = get_option('home').'/index.php?page_id='.get_option('bovp_page');

$bovp_page = get_option('bovp_page');





function BovpVar($name, $echo = false) {



	$bovp_array_settings = get_option('bovp_array_settings');



	$return = $bovp_array_settings[$name];



	if(!$return) {$return = false;}



	if($echo && $return){echo $return;} else {return $return;}



}



function PluginFontSize($s1=20,$s2=25,$s3=30) {



	$bovp_font_size = "<span><a href=\"javascript:void(0)\" class=\"decrease\" style=\"font-size:".$s1.";\">a</a></span>

	<span><a href=\"javascript:void(0)\" class=\"default\" style=\"font-size:".$s2.";\">a</a></span>

	<span><a href=\"javascript:void(0)\" class=\"increase\" style=\"font-size:".$s3.";\">a</a></span>";



return $bovp_font_size;



}



function PluginShowBible($content) {



	global  $bovp_page, $bovp_url;



	if (is_page($bovp_page)) {  



		$bovp_array_books =  get_option("bovp_array_books");

		



		global $post; 

		global $wpdb; 

		global $wp_query;



		$vs="";$is_search=false;$bovp_content="";$bovp_color="";$bovp_search="";$cpg="";$bk="";$cp="";

		$vars = $wp_query->query_vars;

		$sql = "SELECT * FROM `" . BovpVar('bovp_table_name') . "` WHERE ";

			



		if (isset($vars['bk']) AND ($vars['bk']) !=0 ) { 



				$bk = $vars['bk'];

				$book_name = PluginExtractBookInfo($bk,'name');

				$lastpage = PluginExtractBookInfo($bk,'pages');

				$sql .= "`book`=".$bk;				



				if (isset($vars['cp']) && $vars['cp'] !=0) {$cp = $vars['cp']; $sql .= " AND `cp`= ". $cp;} else { 



					if ($vars['cp'] == 0) {$cp =1; $sql .= " AND `cp`= ". $cp; }



				}



				if (isset($vars['vs'])) { $vs = $vars['vs'];} else { $vs = false;}			



		} else {



				if(empty($vars['sh'])) { 



					$sql = "SELECT * FROM `" . BovpVar('bovp_table_name') . "` WHERE `book`=1 AND `cp`=1"; 

					$bk = 1;

					$cp = 1;

					$cpg = 1;

					$book_name = PluginExtractBookInfo($bk,'name');

					$lastpage = PluginExtractBookInfo($bk,'pages');

				} else {$bk = 0;}

		}		



			if(!empty($vars['sh'])){



				$is_search=true;

				$bovp_search = like_escape($vars['sh']);



				if(isset($vars['ex'])){$bovp_search = str_replace(" ", "%", $bovp_search);};



				$sql .= ($bk > 0) ? " AND ( LCASE(text) RLIKE '[[:<:]]" . $bovp_search ."[[:>:]]' )" : " ( LCASE(text) RLIKE '[[:<:]]" . $bovp_search ."[[:>:]]' )";



				if (isset($vars['cpg'])) { $cpg = $vars['cpg'];} else { $cpg = 1;}



				$bovp_start = ($cpg - 1) * BOVP_ITENS_PER_PAGE; 

				$counter = $wpdb->get_results($sql);

				$bovp_count = $wpdb->num_rows;

				$lastpage = ceil($bovp_count/BOVP_ITENS_PER_PAGE);

				$sql .= 'AND `book` <> 0  LIMIT ' . $bovp_start.", ".BOVP_ITENS_PER_PAGE;			



			}



		$inf = array('bk' => $bk, 'cp' => $cp, 'vs' => $vs, 'sh' => $bovp_search, 'cpg' => $cpg, 'lastpage' => $lastpage, 'sql' => $sql);



		#RESULT CONTENT

		$bovp_results = $wpdb->get_results($sql);



		#LIST RESULTS

		if (isset($bovp_search) and ($bovp_search != "")) { // SEARCH RESULTS							



				$bovp_color == '';						



				foreach ($bovp_results as $bovp_results_rows){	



					$book = $bovp_results_rows->book; 

					$cp = $bovp_results_rows->cp; 

					$vs = $bovp_results_rows->vs; 

					$text = $bovp_results_rows->text;				



						$bovp_color = ($bovp_color == '') ? $bovp_color = "bovp_color" : $bovp_color = '';



						$text = str_replace($bovp_search,'<font color="red">'.$bovp_search.'</font>',$text);



						if (function_exists('pluginTagNator')) {$text = pluginTagNator($text);}



						$book_name = PluginExtractBookInfo($book, 'name'); 

						$qtdCapitulos = PluginExtractBookInfo($book, 'pages');									



						$bovp_content .= "<li class='ref_search $bovp_color'><a href=\"?page_id=" . $bovp_page . 



						"&bk=" . $book . "&cp=" .  $cp . "&vs=" . $vs . "\">" . $book_name . 



						":" . $cp . ":" . $vs . "</a>" . $text . "</li>";							



				}





		} else { // SHOW BOOK



				$bovp_color == '';



				foreach ($bovp_results as $item){



					$bovp_color = ($bovp_color == '') ? $bovp_color = 'class="bovp_color"' : $bovp_color = '';



					$vs_bd = $item->vs; 

					$text_bd = $item->text;



					if (function_exists('pluginTagNator')) {$text_bd = pluginTagNator($text_bd);}



					$bovp_content .= "<li $bovp_color><span class=\"verse_num\">$vs_bd</span>";			



						if($vs_bd == $vs) {



							$bovp_content .= "<font color=\"red\">$text_bd</font></p>";



						} else {



							$bovp_content .= "$text_bd</li>";



						}

				}

		}





		if($is_search) {



			$bovp_of = min($bovp_count, ($bovp_start + 1));

			$bovp_to = min($bovp_count, ($bovp_start + BOVP_ITENS_PER_PAGE));

			$bovp_footer_inf = "<p class=\"resumo\" align=\"center\">" . __('Finded','bovp') .'&nbsp;<b>' . $bovp_count . '</b>&nbsp;' .__('verses for your search.','bovp') .'<br>';

			$bovp_footer_inf .=  __('Show results','bovp') . '&nbsp;<b>' . $bovp_of . '</b>&nbsp;' . __('to','bovp') . '&nbsp;<b>' . $bovp_to . '</b></p><br>';



		} 	



		$bovp_title = ($is_search) ? __('Find itens','bovp') . "<span class=\"bovp_cap\">" . $bovp_count . "</span></h3><ul>" : $book_name . "<span class=\"bovp_cap\">" . $cp . "</span>";

		$bovp_version =  __('Version: ', 'bovp') . BovpVar('bovp_version_name');	

		$bovp_font_size = PluginFontSize('14','16','18');

		$bovp_footer_inf = !isset($bovp_footer_inf) &&  empty($bovp_footer_inf) ? false : $bovp_footer_inf;

					

		if(isset($vars['bovp_fsize']) AND (int)$vars['bovp_fsize'] > 0) {

			$bovp_fsize = $vars['bovp_fsize'];

		} else {$bovp_fsize = false;}



		$args = array(

			'header'=>array('search','fontsize'),

			'title',

			'content',

			'pagination',

			'footer',

			'version',

			'logo'

		);

		

		$bovp = new classBibleLayout();

		$bovp->setFontSize($bovp_font_size);

		$bovp->setTitle(strtolower($bovp_title));

		$bovp->setFooter($bovp_footer_inf);

		$bovp->setContent($bovp_content);

		$bovp->setPagination(bovp_pagination($inf));

		$bovp->setVersion($bovp_version);

		$bovp->setSearch(bible_form('var', 'forms-bible-container'));

		$bovp_content = $bovp->showBible($args,1);



		return $bovp_content;



	} else {	



	return $content;	



	}



}



function PluginFolderName(){



	$name = plugin_basename(__FILE__);

	$name = explode("/", $name);



return $name[0] . '/';

}





// Registred Bibles



function PluginBiblesInstalls(){



	$bovp_registred_versions = array(

		array('name' => "Almeida Corrigida Fiel - Português (1994)",'table' => "bovp_acf",'records' => "29898"),

		array('name' => "King James Edition - English",'table' => "bovp_kj",'records' => "31169"),

		array('name' => "Spanish Reina Valera - Public Domain",'table' => "bovp_rv",'records' => "31169")

	);

    return $bovp_registred_versions;



}



function PluginInstalationVerify(){ 



		$return = false;



		$bovp_system_version = get_option('bovp_system_version');

		$bovp_bd_version = get_option('bovp_bd_version');



		if($bovp_system_version != '1.5.1') {$return =1;}

		if($bovp_bd_version != '1.5.1') {$return ++;}



		return $return;

}





// Install function



function PluginSoftInstall(){ 



	global $wpdb;



	$install_type = PluginInstalationVerify();



	if($install_type==1) { // Soft update only



		update_option('bovp_system_version', '1.5.1');



	} elseif($install_type==2) { // New Install



		if(PluginTableExist(BovpVar('bovp_table_name'))) {$wpdb->query("DROP TABLE `" . BovpVar('bovp_table_name') . "`");}



		add_option("bovp_system_version", '1.5.1', '', 'yes');

		add_option("bovp_array_books", 'false', '', 'yes'); 



		add_option("bovp_itens_per_page", '20', '', 'yes');

        add_option("bovp_theme", 'default', '', 'yes');

        add_option("bovp_page", 'false', '', 'yes');

        add_option("bovp_source_random_verse", '0', '', 'yes');



		add_option("bovp_daily_verse", '0', '', 'yes');



		$bovp_array_settings =  array(



				'status' => false,

				'records' => false,

				'time_record' => false,

				'bovp_bd_version' => '',

				'message' => '',

				'bovp_version' => '-1',

				'bovp_version_name' => '',

				'bovp_table_name' => false,

				'bovp_versions' => PluginBiblesInstalls(),

			);



		add_option("bovp_array_settings", $bovp_array_settings, '', 'yes');



	}      

	

}



// Uninstall function



function PluginSoftUninstall(){



	global $wpdb;

	

	PluginDropTable('all');



	$wpdb->query( "DELETE FROM `wp_options` WHERE `option_name` LIKE '%bovp%_%'");

}



function PluginAdminMenu(){

	

	add_menu_page( __('Online Bible','bovp'), __('Online Bible','bovp'), 'manage_options', BOVP_FOLDER_NAME . 'init.php','', BOVP_ICON );

	add_submenu_page( BOVP_FOLDER_NAME . 'init.php', __('Settings','bovp'), 'Settings' , 'manage_options',  BOVP_FOLDER_NAME . 'settings.php' );

	add_action( 'admin_init', 'PluginOptionsSet');

}



function PluginOptionsSet() {

	

	register_setting( 'bovp_options', 'bovp_page');

	register_setting( 'bovp_options', 'bovp_source_random_verse');

	register_setting( 'bovp_options', 'bovp_itens_per_page');

	register_setting( 'bovp_options', 'bovp_theme');



}



function PluginAdminStyles() {



	wp_enqueue_style( "adm", BOVP_FOLDER ."includes/bovp_adm_style.css");



}



function PluginIncludeVars($qvars ){ 

	

	array_push($qvars , 'bk', 'cp', 'sh', 'vs', 'ex','cpg', 'bovp_fsize');

	

    return $qvars ;

} 



function PluginTableExist($table=false){



	global $wpdb;



	if(!$table){return false;}



	if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {return false;} else { return true;}



}



function PluginCreateTable($bovp_table){



	global $wpdb;



	$create_bovp_table = "CREATE TABLE `". $bovp_table ."` (

			`id` int(11) NOT NULL auto_increment,

			`book` int(11) NOT NULL,

			`cp` int(11) NOT NULL,

			`vs` int(11) NOT NULL,

			`text` mediumtext NOT NULL,

			PRIMARY KEY  (`id`))"; 

		

	$table_create = $wpdb->query($create_bovp_table);



	$bovp_db_error = $wpdb->last_error;



	echo $bovp_db_error;



	$bovp_table_exist = PluginTableExist($bovp_table);



	if($bovp_table_exist) {



		$return = true;



	} else {$return = false;}



	return $return;



}



function PluginDropTable($bovp_table_to_delete){



	global $wpdb;

	global $bovp_versions_inf;

	$return = false;

	$tables = '';







	if($bovp_table_to_delete='all') {





			foreach ($bovp_versions_inf as $key => $table) {



				$tables .= $wpdb->prefix . $table['table'];



				if ($key < ( count($bovp_versions_inf) - 1)) { $tables .= ',';}

				

			}



			$query = "DROP TABLE IF EXISTS " . $tables;



			$delete_all = $wpdb->query($query);



			if($delete_all) $return = true;





	} else {



			$bovp_table_exist = PluginTableExist($bovp_table_to_delete);



			if($bovp_table_exist) {



				$delete_table = $wpdb->query("DROP TABLE IF EXISTS `" . $bovp_table_to_delete . "`");



				if($delete_table) $return = true;



			} 

	}



	

	return $return;

}





function PluginInsertData($bovp_install){



	global $wpdb;

	global $bovp_array_settings;

	global $bovp_versions_inf;



	$remove_all = PluginDropTable('all');

	update_option('bovp_daily_verse', false);



	$bovp_table_install = $bovp_versions_inf[$bovp_install]['table'];

	$bovp_records = $bovp_versions_inf[$bovp_install]['records'];

	$bovp_version_name = $bovp_versions_inf[$bovp_install]['name'];



	$bovp_wp_table_name = $wpdb->prefix . $bovp_table_install;



	$bovp_slice = 0;

	$last_slice = 0;

	$bovp_row_count = 0;

	

	$bovp_create_table = PluginCreateTable($bovp_wp_table_name);



	if($bovp_create_table) {

			

		$bovp_data_insert = dirname(__FILE__) . '/data/'.$bovp_table_install. '.sql';



		if(!is_file($bovp_data_insert)){return false;}



		$fp = fopen($bovp_data_insert, 'r');

		

		if($fp){

		

			$bovp_data = fread($fp, filesize($bovp_data_insert));

			

			fclose($fp);



			$bovp_data = explode("||",$bovp_data);



			$last_slice = count($bovp_data);



				while ($bovp_slice < $last_slice) {



					$bovp_slice_selected = $bovp_data[$bovp_slice];



					$insert = 'INSERT INTO `' . $bovp_wp_table_name . '` VALUES ' . $bovp_slice_selected;

						

					$start_cron = array_sum(explode(' ', microtime()));



					$inserted = $wpdb->query($insert);



					$end_cron = array_sum(explode(' ', microtime()));



					$bovp_time_exec = substr(($end_cron - $start_cron), 0, 6) ;



					$bovp_slice ++;



				}



		}



		$sql = 'SELECT COUNT(`id`) as "TOTAL" FROM `'. $bovp_wp_table_name .'`';



		$results = $wpdb->get_results( $sql );



		$bovp_row_count = $results[0]->TOTAL;



		if((int)$bovp_row_count == (int)$bovp_records) { 

			

			$bovp_message = sprintf(__('The Bible %s has been installed in your database. %s verses were inserted in %s seconds ' , 'bovp'),$bovp_version_name, $bovp_row_count, $bovp_time_exec);

	

			$bovp_array_settings =  array(



				'status' => true,

				'records' => $bovp_row_count,

				'time_record' => $bovp_time_exec,

				'bovp_bd_version' => '1.5.1',

				'message' => $bovp_message,

				'bovp_version' => $bovp_install,

				'bovp_version_name' => $bovp_version_name,

				'bovp_table_name' => $bovp_wp_table_name,

				'bovp_versions' => PluginBiblesInstalls(),



			);



			update_option("bovp_array_settings", $bovp_array_settings);



			PluginBookArrayConstruct($bovp_wp_table_name);



			return true;



		} else {return false;}





	} else {return false;}



}



function PluginActiveTranslate(){ 

	

  load_plugin_textdomain( 'bovp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

  

}



function PluginDependences() {  



	$theme = get_option('bovp_theme');

		

	$style_sheet = BOVP_FOLDER ."themes/".$theme."/".$theme.".css";



	wp_enqueue_style( "bible_style", $style_sheet);



	wp_enqueue_script("jquery");



	wp_enqueue_script( "bible_js", BOVP_FOLDER ."includes/bovp.js");



	if(file_exists(plugin_dir_path(__FILE__) . '/includes/bovp_f.js')){



	wp_enqueue_script( "bible_js_f", BOVP_FOLDER ."/includes/bovp_f.js");



	}



}



// Generate book's array and version bible variable

function PluginBookArrayConstruct($table){

	

	global $wpdb;

	$query = "SELECT * FROM `" . $table . "` WHERE `book` = 0 " AND `cp` <> 0;

	$bovp_array_books = array();

	$books = $wpdb->get_results ($query , ARRAY_A);

	foreach($books as $book) {

		$data = array('bk' => $book['cp'],'name' => $book['text'],'pages' => $book['vs']);

		array_push($bovp_array_books, $data); 

	}

	update_option("bovp_array_books", $bovp_array_books);

}



// Get all published page 

function PluginGetAllPages() {

	global $wpdb;

	$query = "SELECT id, post_title FROM " . $wpdb->prefix . "posts WHERE post_type = 'page' AND post_status='publish'";

	$cpgs = $wpdb->get_results ( $query, ARRAY_A );

	$output = array();

	$output[] = __('Select a page','bovp');

	foreach ( $cpgs as $cpg ) {

		$output [$cpg ['id']] = $cpg ['post_title'];

	}

	return $output;

}



// List all page to select

function PluginSelectConstruct($name, $list){



		$option_value = get_option($name); 

	 

		echo "<td><select name=\"" . $name . "\">";



			foreach($list as $key => $value) {

				"$key" == $option_value ? $selected = "selected='selected' " : $selected = '';       

	          	echo "<option value='$key' $selected>$value</option>";

	        }



	    echo "</select> <br/>";

}





function bovp_show_verse($return_type = 'show'){



global $bovp_url;



$bovp_daily_verse = get_option('bovp_daily_verse');



if (is_array($bovp_daily_verse)){$date_verse = $bovp_daily_verse['date'];} else {$date_verse = '1/11/1971';}





	if((date( __('m/d/Y' , 'bovp') ,time()) == date( __('m/d/Y' , 'bovp') , $date_verse))){  // is a valid verse

	

			$id = $bovp_daily_verse['id'];

			$date = $bovp_daily_verse['date'];

			$bk = $bovp_daily_verse['book']; //book

			$cp = $bovp_daily_verse['cp']; //cp

			$vs = $bovp_daily_verse['vs']; //vs

			$nbook = $bovp_daily_verse['book_name']; //book

			$text = $bovp_daily_verse['text']; //text

			$link = "&bk=$bk&cp=$cp&vs=$vs";

		

		$return = $text . " <span class='verse_ref'><a href='" . $bovp_url . $link . "'>($nbook:$cp:$vs )</a></span>";

			

} else {

		

	global $wpdb;

	global $bovp_table_name;



	$bovp_setting = get_option('bovp_array_settings');

		

	$params = $bovp_setting['bovp_source_random_verse'];

	

	switch($params) {



		case 0:$params=""; break;

		case 1:$params="WHERE `book` =< 39"; break;

		case 2:$params="WHERE `book` >= 40"; break;

		case 3:$params="WHERE `book` = 19"; break;

	}

	

	$sql = "SELECT * FROM ". BovpVar('bovp_table_name') ." $params ORDER BY rand() LIMIT 1";



	$daily_verse = $wpdb->get_row($sql);

					

		

			if($daily_verse){

				

			$bovp_daily_verse = array();

				

				$bovp_daily_verse['id'] = $daily_verse->id;

				$bovp_daily_verse['date'] = time();

				$bovp_daily_verse['book'] = $daily_verse->book;

				$bovp_daily_verse['cp'] = $daily_verse->cp;

				$bovp_daily_verse['vs'] = $daily_verse->vs;

				$bovp_daily_verse['book_name'] = PluginExtractBookInfo($daily_verse->book,'name');

				

				$link = "&bk=" . $bovp_daily_verse['book'] . '&cp=' . $bovp_daily_verse['cp'] . '&vs=' . $bovp_daily_verse['vs'];

				

				$vs_resume = false; 

			

				if ($vs_resume) { 

				

					$bovp_daily_verse['text'] = $daily_verse->text . " ..."; 

					

				} else {

						

				$counter_vs_id = $daily_verse->id;

				$bovp_daily_verse['text'] = $daily_verse->text;



				while (strripos(".?!", substr(trim($bovp_daily_verse['text']), -1)) === FALSE) { 

					$counter_vs_id++;

					$add_verse = $wpdb->get_row("SELECT * FROM ". $bovp_table_name ." WHERE id = '".$counter_vs_id."' LIMIT 1");

					$bovp_daily_verse['text'] .= ' '.$add_verse->text;

				} 

				

				}

			

			}

			

			if($bovp_daily_verse){

				

				update_option("bovp_daily_verse", $bovp_daily_verse);

				

				$bovp_ref_verse = '(' . $bovp_daily_verse['book_name'] . ':' . $bovp_daily_verse['cp'] . ':' . $bovp_daily_verse['vs'] . ')';

								

				

				$return = $bovp_daily_verse['text'] . 

				" <span style='text-align:right;'><a href='" . $bovp_url . $link . "'>$bovp_ref_verse</a></span>";

				

			}			

	}

	

if ($return_type == 'show'){ echo $return;} 

elseif ($return_type == 'var'){ return $return;} 

elseif ($return_type == 'array'){ return $bovp_daily_verse;}

	

}



// Swich book information function



function PluginExtractBookInfo($book,$item) {

	

	$bovp_array_books =  get_option("bovp_array_books");

	

	if( empty($bovp_array_books) || !isset($bovp_array_books)){

		PluginBookArrayConstruct(BovpVar('bovp_table_name'));

		$bovp_array_books =  get_option("bovp_array_books"); 

	}	



	return 	$bovp_array_books[$book][$item];

}

	

function book_select($mode = "echo"){

	

	$bovp_array_books =  get_option("bovp_array_books");

	

	$list_books_combo = '<option value="0" >'. __('All the Bible','bovp') .'</option>';

	$list_books_combo .= '<optgroup label="' . __('Old Testament','bovp') . '">';

	

	foreach($bovp_array_books as $list_array) {

			

		$book_id = $list_array['bk'] ;

		$book_name = $list_array['name'];

		$num_pages = $list_array['pages'];



		if($book_id !=0){$list_books_combo .=  '<option num_pages="'.$num_pages.'" value="'.$book_id .'" >'.$book_name.'</option>';	}



		if($book_id ==39){$list_books_combo .=  '<\optgroup><optgroup label="' . __('New Testament','bovp') . '">';	}

		

	}



	$list_books_combo .=  '<\optgroup>';

	

	if ($mode == "echo") {echo $list_books_combo;} else { return $list_books_combo;}

	

}



function bible_form($bovp_return_form = 'echo'){ 



global $bovp_page, $bovp_url;



$bovp_form = "

<div class=\"bovp_search_container clearfix\">

<form method=\"post\" action=\"" . $bovp_url . "\" name=\"bovp_form_search\" class=\"bovp_form_search clearfix\" >

<input name=\"page_id\" type=\"hidden\" value=\"" . $bovp_page . "\"/>

<input class=\"bovp_seach_input\" type=\"text\" id=\"sh\" name=\"sh\" placeholder=\"" . __('Search','bovp') . "\" >

<button class=\"bovp_button\" type=\"submit\">" . __('Send','bovp') . "</button>

</form></div>"

;



if($bovp_return_form == 'echo') {echo $bovp_form;} elseif($bovp_return_form == 'var'){return $bovp_form;}



}



function bovp_pagination($inf){



	global $bovp_url;



	$pagination = '';





	$prmt = $bovp_url;



		if (isset($inf['bk'])&&(!empty($inf['bk']))) $prmt .= '&bk=' . $inf['bk'];



		if (isset($inf['sh'])&&(!empty($inf['sh']))) $prmt .= '&sh=' . $inf['sh'];



		if (isset($inf['sh'])&&(!empty($inf['sh']))) { $cpg = $inf['cpg']; $prmt .= '&cpg='; } else {$cpg = $inf['cp']; $prmt .= '&cp=';}



		$lastpage = $inf['lastpage'];



		$adjacents = 2;

		$prev = $cpg - 1; 

		$next = $cpg + 1;

		$lpm1 = $lastpage - 1;





	if($lastpage){



		if($cpg){



			// PREV BUTTOM

			if($cpg > 1)

					$pagination .= "<a href=\"". $prmt . $prev."\" class=\"prev\">" . __('Previous','bovp') . "</a>";

			else

					$pagination .= "<span class=\"disabled\">" . __('Previous','bovp') . "</span>";

		}



		//PAGES

		if ($lastpage < 7 + ($adjacents * 2)){//not enough pages to bother breaking it up

				for ($counter = 1; $counter <= $lastpage; $counter++){

						if ($counter == $cpg)

								$pagination .= "<span class=\"current\">$counter</span>";

							else

								$pagination .= "<a href=\"". $prmt . $counter."\">$counter</a>";

					}

			}



		elseif($lastpage > 5 + ($adjacents * 2)){//enough pages to hide some



				//close to beginning; only hide later pages

				if($cpg < 1 + ($adjacents * 2)){

						for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++){

								if ($counter == $cpg)

										$pagination .= "<span class=\"current\">$counter</span>";

									else

										$pagination .= "<a href=\"". $prmt . $counter."\">$counter</a>";

							}

						$pagination .= "...";

						$pagination .= "<a href=\"". $prmt . $lpm1."\">$lpm1</a>";

						$pagination .= "<a href=\"". $prmt . $lastpage."\">$lastpage</a>";

					}

				//in middle; hide some front and some back

				elseif($lastpage - ($adjacents * 2) > $cpg && $cpg > ($adjacents * 2)){

						$pagination .= "<a href=\"". $prmt . "&cpg=1\">1</a>";

						$pagination .= "<a href=\"". $prmt . "&cpg=2\">2</a>";

						$pagination .= "...";

						for ($counter = $cpg - $adjacents; $counter <= $cpg + $adjacents; $counter++)

							if ($counter == $cpg)

									$pagination .= "<span class=\"current\">$counter</span>";

								else

									$pagination .= "<a href=\"". $prmt . $counter."\">$counter</a>";

						$pagination .= "...";

						$pagination .= "<a href=\"". $prmt . $lpm1."\">$lpm1</a>";

						$pagination .= "<a href=\"". $prmt . $lastpage."\">$lastpage</a>";

					}

				//close to end; only hide early pages

				else{

						$pagination .= "<a href=\"". $prmt . "&cpg=1\">1</a>";

						$pagination .= "<a href=\"". $prmt . "&cpg=2\">2</a>";

						$pagination .= "...";

						for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)

							if ($counter == $cpg)

									$pagination .= "<span class=\"current\">$counter</span>";

								else

									$pagination .= "<a href=\"". $prmt . $counter."\">$counter</a>";

					}

			}

			

		if($cpg){

				//siguiente button

				if ($cpg < $counter - 1)

						$pagination .= "<a href=\"". $prmt . $next."\" class=\"next\">" . __('Next','bovp') . "</a>";

					else

						$pagination .= "<span class=\"disabled\">" . __('Next','bovp') . "</span>";

					

			}

	}



return $pagination;



}





function bovp_widgets_init() {



  register_widget('bovp_widget_verse');

  register_widget('bovp_widget_search');

  register_widget('bovp_widget_index');



}



function PluginShowMessage($message, $errormsg = false) {



	if ($errormsg) {$class = 'bovp_error';} else {$class = 'bovp_success fade';}



	$return = sprintf('<div id="bovp_message" class="%s"><p><strong>%s</strong></p></div>', $class, $message);



	return $return;

}  



function PluginShowAdminMessage() {



		$return = PluginShowMessage('testando a mensagem');



		return $return;



}



// For test of array books function only



function bovp_list_array() { 

		

	$array_books = get_option("bovp_array_books");

	

	print_r($array_books);

	

}



#CLASSES



class classBibleLayout {



/*

Script Name: *Bovp Bible Layout Class

Script URI: http://www.vivendoapalavra.org/

Description: PHP class that allows you to customize the layout of the online Bible for wordpress.

Script Version: 0.1

Author: Andre Brum Sampaio

Author URI: http://www.vivendoapalavra.org



$args = array('header','content','pagination','version','footer','logovp');



*/



	#Default values

	var $open_group = '<div class="clearfix bovp_group">'; 

	var $close_group = '</div>';

	var $url = 'localhost/wordpress';

	var $content = false;

	var $content_header = false;

	var $content_footer = false;

	var $pagination = false;

	var $version = false;



	#construct

	public function classBibleLayout(){}



	#content

	function setContent($content) {

		if (isset($content)) {$this->content = $content;} else {$this->content = false;}

	}



	#content

	function setFontSize($font_size) {

		if (isset($font_size)) {$this->font_size = $font_size;} else {$this->font_size = false;}

	}



	#content_header

	function setHeader($content_header) {

		if (isset($content_header)) {$this->content_header = $content_header;} else {$this->content_header = false;}

	}



	#title

	function setTitle($title) {

		if (isset($title)) {$this->title = $title;} else {$this->title = false;}

	}



	#content_footer

	function setFooter($content_footer) {

		if (isset($content_footer)) {$this->content_footer = $content_footer;} else {$this->content_footer = false;}

	}



	#pagination

	function setPagination($pagination) {

		if (isset($pagination) && !empty($pagination)) {$this->pagination = $pagination;} else {$this->pagination = false;}

	}



	#search

	function setSearch($search) {

		if (isset($search) && !empty($search)) {$this->search = $search;} else {$this->search = false;}

	}



	#version

	function setVersion($version) {

		if (isset($version)) {$this->version = $version;} else {$this->version = false;}

	}



	#logo

	function setLogo($logo) {

		if (isset($logo)) {$this->logo = $logo;} else {$this->logo = false;}

	}



	#prepareGroup

	function prepareGroup($group){



		$return = '';



		switch ($group) {



			case 'header':

				$return = "<header class='bovp_header clearfix'>\n";

				if($this->content_header) {$return .= $this->open_group . $this->content_header  . $this->close_group;}

				$return .= "</header>";

				break;



			case 'content':

				$return = "<article class='bovp_text clearfix'>\n";

				$return .= "<ul class='bovp_bible_content'>\n";

				if($this->content) {$return .= $this->content;}

				$return .= "<ul>\n";

				$return .= "</article>";

				break;		



			case 'pagination':

				$return = "<nav class='bovp_pagination clearfix'>\n";

				if($this->pagination) {$return .= $this->pagination;}

				$return .= "</nav>";

				break;	



			case 'version':

				$return = "<section class='bovp_version clearfix'>\n";

				if($this->version) {$return .= $this->version;}

				$return .= "</section>";

				break;			



			case 'footer':

				$return = "<footer class='bovp_footer clearfix'>\n";

				if($this->content_footer) {$return .= $this->content_footer;}

				$return .= "</footer>";

				break;	



			case 'logo':

				$return = "<section class='bovp_logo clearfix'>\n";

				$return .= sprintf('<a href="http://www.vivendoapalavra.org/"><img src="%simg/logovp.png" border="0"></a>', BOVP_FOLDER);

				$return .= "</section>";

				break;



			case 'title':

				if($this->title) {$return .= '<div class="bovp_title">' .$this->title . '</div>';}

				break;		



			case 'fontsize':

				if($this->font_size) {$return .= '<div class="bovp_fsize">' . $this->font_size . "</div>";}				

				break;	



			case 'search':

				if($this->search) {$return .= '<div class="bovp_search">' .$this->search . "</div>";}

				break;																	

			

		}		



		return $return;



	}



	#showBible

	function showBible($args, $t = 0){



		if(isset($bovp_fsize)) {$print_fsize = ' style="font-size:' . $bovp_fsize . 'px" ';} else {$print_fsize ='';}



		$show_layout = "<section class='bovp_container' ". $print_fsize .">\n";





		foreach ($args as $key => $group) {



			if(!is_array($group)){



				$show_layout .= $this->prepareGroup($group);



			} else {



				$tag_replace = '</' . $key . '>';



				$show_layout .= str_replace($tag_replace, '', $this->prepareGroup($key));



				foreach ($group as $subgroup) {



					$show_layout .= $this->prepareGroup($subgroup);



				}



				$show_layout .= $tag_replace;



			}



		}



		$show_layout .= "</section>\n";



		if($t == 0){echo $show_layout;} elseif ($t = 1) { return $show_layout;}

	

	}





}



// WIDGET EXTEND





class bovp_widget_search extends WP_Widget

{

    function bovp_widget_search(){

		$widget_ops = array('description' =>  __('Use this widget to display the bible form search','bovp'));

		parent::WP_Widget(false,$name='BOVP Form Search',$widget_ops);

    }



  /* Displays the Widget in the front-end */

    function widget($args, $instance){



    	global $bovp_url, $bovp_page; 

		

		extract($args);

		

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Bible Search','bovp') : $instance['title']);



		$subtitle = apply_filters('widget_title', empty($instance['title']) ? __('Widget ','bovp') : false);



		if($subtitle){$title = '<b>' . $subtitle . '</b> ' . $title;}





		echo $before_widget;



		if ( $title )

		echo $before_title . $title . $after_title;

	

		echo "<div class=\"bovp_search_widget clearfix\">";



		echo "<form method=\"post\" action=\"" . $bovp_url . "\" name=\"bovp_form_search\" class=\"bovp_form_search clearfix\">";



		echo "<input name=\"page_id\" type=\"hidden\" value=\"" . $bovp_page . "\"/>";



		echo "<input class=\"bovp_seach_input\" type=\"text\" id=\"sh\" name=\"sh\" placeholder=\"" . __('Search','bovp') . "\" >";



		echo "<button class=\"bovp_button\" type=\"submit\">" . __('Send','bovp') . "</button>";



		echo "</form>";

	

		echo "</div>";

	

		echo $after_widget;

	

	}





/*Saves the settings. */

    function update($new_instance, $old_instance){

		$instance = $old_instance;

		$instance['title'] = stripslashes($new_instance['title']);



		return $instance;

	}



/*Creates the form for the widget in the back-end. */

    function form($instance){

		//Defaults

		$instance = wp_parse_args( (array) $instance, array('title'=>__('Bible Search','bovp')) );



		$title = htmlspecialchars($instance['title']);



		# Title

		echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title:','bovp') . '</label><input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></p>';

		

	}



}

// end class





class bovp_widget_index extends WP_Widget

{

    function bovp_widget_index(){

		$widget_ops = array('description' =>  __('This widget show an index of books of Bible in ior sidebar.','bovp'));

		parent::WP_Widget(false,$name='BOVP Bible Index',$widget_ops);

    }



  /* Displays the Widget in the front-end */

    function widget($args, $instance){

    	global $bovp_url, $bovp_page; 

		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Bible Index','bovp') : $instance['title']);

		$subtitle = apply_filters('widget_title', empty($instance['title']) ? __('Widget ','bovp') : false);



		if($subtitle){$title = '<b>' . $subtitle . '</b> ' . $title;}



		echo $before_widget;



		if ( $title )



		echo $before_title . $title . $after_title;

	

		echo "<form method=\"get\" action=\"" . $bovp_url . "\" name=\"bovp_form_index\" class=\"bovp_form_search clearfix\">";



		echo "<div class=\"clearfix bovp_selects_form\"><input name=\"page_id\" type=\"hidden\" value=\"" . $bovp_page . "\"/>";



		echo "<select name=\"bk\" id=\"bovp_widget_book\" class=\"bovp_select_widget\">" . book_select("var") . "</select>";



		echo "<select type=\"text\" name=\"cp\" id=\"bovp_widget_chapter\" class=\"bovp_select_widget\"></select></div>";



		echo "<button class=\"bovp_button\" type=\"submit\">" . __('Go','bovp') . "</button>";



		echo "</form>";

	

		echo $after_widget;

	

	}





  /*Saves the settings. */

    function update($new_instance, $old_instance){

		$instance = $old_instance;

		$instance['title'] = stripslashes($new_instance['title']);



		return $instance;

	}



  /*Creates the form for the widget in the back-end. */

    function form($instance){

		//Defaults

		$instance = wp_parse_args( (array) $instance, array('title'=>__('Bible Search','bovp')) );



		$title = htmlspecialchars($instance['title']);



		# Title

		echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title:','bovp') . '</label><input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></p>';

		

	}



}// end class



class bovp_widget_verse extends WP_Widget

{

    function bovp_widget_verse(){

		$widget_ops = array('description' => __('Use this widget to display the daily verse','bovp'));

		parent::WP_Widget(false,$name='BOVP Daily Verse',$widget_ops);

    }



  /* Displays the Widget in the front-end */

    function widget($args, $instance){

		

		extract($args);

		

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Daily Verse','bovp') : $instance['title']);



		$subtitle = apply_filters('widget_title', empty($instance['title']) ? __('Widget ','bovp') : false);



		if($subtitle){$title = '<b>' . $subtitle . '</b> ' . $title;}



		echo $before_widget;



		if ( $title )

		echo $before_title . $title . $after_title;

	

		bovp_show_verse();

	

		echo $after_widget;

	

	}

	

  /*Saves the settings. */

    function update($new_instance, $old_instance){

		$instance = $old_instance;

		$instance['title'] = stripslashes($new_instance['title']);



		return $instance;

	}



  /*Creates the form for the widget in the back-end. */



    function form($instance){

		//Defaults

		$instance = wp_parse_args( (array) $instance, array('title'=>__('Daily Verse','bovp')) );



		$title = htmlspecialchars($instance['title']);



		# Title

		echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title:','bovp') . '</label><input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></p>';

		

	}



} 



?>
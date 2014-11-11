<?php
/*
Plugin Name: Online Bible VP for Wordpress 
Plugin URI: http://www.vivendoapalavra.org/
Description: Plugin for implementation of Online Bible in your Wordpress blog. With it, you can make available the Word of God and bless your website's users. The plugin allows to consult all 66 books of the Holy Bible versions: King James Edition - English, Almeida Corrigida Fiel - Português (1994), Spanish Reina Valera (1960) and the French version Louis Segond (1910).
Author: André Brum Sampaio
Version: 1.5.2
Author URI: http://www.vivendoapalavra.org/
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {die('Access denied.'); }

// CONSTANTS

define('BOVP_PATH', plugin_dir_path(__FILE__) );
define('BOVP_FOLDER', plugin_dir_url(__FILE__) );
define('BOVP_ICON', BOVP_FOLDER . 'img/icone_bovp.png');
define('BOVP_VERSE_SOURCE', get_option('bovp_source_random_verse'));
define('BOVP_SYSTEM_VERSION', get_option('bovp_system_version'));
define('BOVP_ITENS_PER_PAGE', get_option('bovp_itens_per_page'));
define('BOVP_FOLDER_NAME', bovpFolderName());
define('BOVP_BD_VERSION', get_option('bovp_bd_version'));
define('BOVP_TAGGER', false);


$bovp_version =  get_option('bovp_version');

if($bovp_version != -1) {

	$bovp_table = $wpdb->prefix . bovpBibleInfo($bovp_version,'table');

	if(bovpTableExist($bovp_table)) {

		define('BOVP_VERSION', $bovp_version);
		define('BOVP_TABLE_NAME', $bovp_table);
		define('BOVP_RECORDS', bovpBibleInfo($bovp_version,'records'));
		define('BOVP_VERSION_NAME', bovpBibleInfo($bovp_version,'name'));

	} else { 

		update_option( 'bovp_version', -1 );
		define( 'BOVP_VERSION', -1 );

	}

} 

define('BOVP_PAGE', get_option('bovp_page'));
define('BOVP_STATUS', get_option('bovp_status'));
define('BOVP_THEME', get_option('bovp_theme'));
define('BOVP_FB_IMG_SHARE', BOVP_FOLDER . 'img/fb_share_200_200.jpg');
define('BOVP_TWITTER_IMG_SHARE', BOVP_FOLDER . 'img/twitter_share_120_120.jpg');
define('BOVP_GP_IMG_SHARE', BOVP_FOLDER . 'img/google_share_180_120.jpg');
define('BOVP_SHOW_INDEX', get_option('bovp_show_index'));
define('BOVP_FONT_SIZE', get_option('bovp_font_size'));

define('BOVP_THEME_FOLDER', BOVP_PATH . 'themes/'. BOVP_THEME . '/');

$rewrite_rules = get_option('rewrite_rules');

if($rewrite_rules) {
	
	define('BOVP_FURL_STATS', true);
	define('BOVP_FORM_METHOD', 'post');
	define('BOVP_SLUG_PAGE', slug_name(BOVP_PAGE));
	define('BOVP_SLUG_SEARCH', __('results','bovp'));
	define('BOVP_URL', get_option('home').'/'. BOVP_SLUG_PAGE .'/');

} else {

	define('BOVP_URL', get_option('home').'/index.php?page_id='.get_option('bovp_page'));
	define('BOVP_FURL_STATS', false);
	define('BOVP_FORM_METHOD', 'get');
	define('BOVP_SLUG_SEARCH', '');

}

// GLOBALS
$r_count = 0;
$bovp_vars = array();

include('includes/bovp_layout.class.php');
include('includes/bovp_widget.class.php');
include('includes/bovp_read_csv.class.php');


function bovpShowBible($content) {

	if (is_page(BOVP_PAGE) AND defined('BOVP_VERSION') AND  BOVP_VERSION != -1) {  

		global $bovp_vars;

		$bovp_daily_verse = get_option('bovp_daily_verse');
		
		if(isset($_REQUEST['test_plugin'])){bovpShowStatus(); return false;}

		global $post; 
		global $wpdb; 
		global $r_count;

		$bovp = new classBibleLayout();

		// if is friendly URL, this convert book name to book id v1.51
		if(!is_int($bovp_vars['bk'])) {

			$bovp_array_books = get_option('bovp_array_books');

				foreach ($bovp_array_books as $book) {

					$id_book = $book['bk'];
					$slug_book = $book['slug_name'];

					if($bovp_vars['bk']==$slug_book) {$bovp_vars['bk']=$id_book; break;}
								
				}

		} 


		if(BOVP_SHOW_INDEX AND ($bovp_vars['sh'] === 0 AND $bovp_vars['bk'] === 0)) {

			$bovp_index_first = bovpShowIndexFisrt(BOVP_VERSION);

			return $bovp_index_first;

		} else {

			if($bovp_vars['sh'] == "0") { if($bovp_vars['bk'] == "0") $bovp_vars['bk'] = '1';}

			#SQL COMMAND PREPPARE
			$sql = "SELECT * FROM `" . BOVP_TABLE_NAME . "` WHERE ";

			if($bovp_vars['bk'] != 0) { 

				$sql .= "book=" . $bovp_vars['bk']; 

				if($bovp_vars['cp']== 0) { $bovp_vars['cp'] = 1; }

					$sql .= " AND cp=" . $bovp_vars['cp'];

			} 

			if($bovp_vars['sh']!== 0) { 

				/*WORDS ARRAY*/
				$explode = explode(' ', trim($bovp_vars['sh']));

					$sql_sh = "";

					for($i=0; $i < count($explode); $i++) {

						if($i > 0) $sql_sh .= " AND ";

						$text_search = bovpSanitizeSeach(urldecode($explode[$i]));

						$sql_sh .= "( LCASE(text) RLIKE '[[:<:]]" . $text_search ."[[:>:]]' )";
					}

				$bovp_regex = "/\b(" . bovpSanitizeSeach(implode('|',$explode)) . ")[^a-z]/i";

				if($bovp_vars['bk']!= 0) { $sql .= " AND " . $sql_sh;} else { $sql .= $sql_sh;}
				
			}



			#COUNT RESULTS
			$bovp_results = $wpdb->get_results($sql);
			$r_count = $wpdb->num_rows;

			#PAGINATION VERIFY 
			if(!isset($bovp_vars['cpg']) OR strlen($bovp_vars['cpg']) == 0) { $bovp_vars['cpg'] = $cpg = 1;} else {$cpg = $bovp_vars['cpg'];}

			$bovp_start = ($cpg - 1) * BOVP_ITENS_PER_PAGE; 
					
			if($bovp_vars['sh']!== 0) { $sql .= '  LIMIT ' . $bovp_start.", ".BOVP_ITENS_PER_PAGE; }	
			$bovp_results = $wpdb->get_results($sql);
			$bovp_content = "";
			
			$bovp_color = '';

			foreach ($bovp_results as $item) {	

				$book = $item->book; 
				$cp = $item->cp; 
				$vs = $item->vs; 
				$text = $item->text;

				if (BOVP_TAGGER==true) {$text = bovpTagMarker($text);}

				$book_name = bovpBookInfo($book, 'name'); 

				#LIST RESULTS --> SEARCH RESULTS
				if ($bovp_vars['sh']!== 0) { 

					$bovp_color = '';				
						
						$bovp_color = ($bovp_color == '') ? $bovp_color = "bovp_color" : $bovp_color = '';
						$text = preg_replace_callback($bovp_regex, "bovpCallback", $text);

						if(BOVP_FURL_STATS) {
							
							#Friendly URL activated.
							$book_slug_name = bovpBookInfo($book, 'slug_name'); 
							$bovp_link = BOVP_URL . $book_slug_name . "/".$item->cp."/".$item->vs;

						} else {

							#Friendly URL don't activated.
							$bovp_link = BOVP_URL . '&bk=' . $item->book.'&cp='.$item->cp.'&vs='.$item->vs;

						}

						$bovp_content .= "<li class='bovp_text_li ref_search $bovp_color'><a class='show_in_book' href='" . $bovp_link . "'>" . $book_name . 

						":" . $cp . ":" . $vs . "</a>" . $text . "</li>";



				#LIST RESULTS --> SHOW BOOK
				} else { 

					$bovp_color = ($bovp_color == '') ? $bovp_color = "bovp_color" : $bovp_color = '';


						if($item->vs == $bovp_vars['vs']) {

							$bovp_content .= "<li id='".$item->vs."' class='bovp_featured_li bovp_text_li $bovp_color'><span class='verse_num'>".$item->vs."</span>";			

							$bovp_content .= "<font class='bovp_featured_verse'>".$text."</font>";

						} else {

							$bovp_content .= "<li id='".$item->vs."' class='bovp_text_li $bovp_color'><span class='verse_num'>".$item->vs."</span>";			


							$bovp_content .= $text."</li>";

							

						}

				}

			}

			
			
			if($bovp_vars['sh']!== 0) {

				$bovp_of = min($r_count, ($bovp_start + 1));

				$bovp_to = min($r_count, ($bovp_start + BOVP_ITENS_PER_PAGE));

				$bovp_footer_inf = "<p class='resumo' align='center'>" . __('Found','bovp') .'&nbsp;<b>' . $r_count . '</b>&nbsp;' .__('verses for your search.','bovp') .'<br>';

				$bovp_footer_inf .=  __('Show results','bovp') . '&nbsp;<b>' . $bovp_of . '</b>&nbsp;' . __('to','bovp') . '&nbsp;<b>' . $bovp_to . '</b></p><br>';

			} 	

			$bovp_title = ($bovp_vars['sh']!== 0) ? __('Find items','bovp') . "<span class='bovp_cap'>" . $r_count . "</span></h3><ul>" : $book_name . "<span class='bovp_cap'>" . $bovp_vars['cp'] . "</span>";

			$bovp_footer_inf = !isset($bovp_footer_inf) &&  empty($bovp_footer_inf) ? false : $bovp_footer_inf;

			if(isset($bovp_vars['bovp_fsize']) AND (int)$bovp_vars['bovp_fsize'] > 0) {

				$bovp_fsize = $bovp_vars['bovp_fsize'];

			} else {$bovp_fsize = false;}



			#load bible theme

			
		include('themes/'.BOVP_THEME.'/'.BOVP_THEME.'.php');

		return $bovp_show_theme;

		}

	} else {	

		return $content;	

	}

}


function bovpCallback($matches) {

	return '<font class="bovp_text_found">'.$matches[0].'</font>';

}

function bovpFolderName(){

	$name = plugin_basename(__FILE__);
	$name = explode("/", $name);
	return $name[0] . '/';

}

// Registred Bibles
function bovpBibleInfo($version='array', $item=false){

	$bovp_registred_versions = array(
		array('name' => "Almeida Corrigida Fiel - Português (1994)",'table' => "bovp_acf",'records' => "31173"),
		array('name' => "King James Edition - English",'table' => "bovp_kj",'records' => "31169"),
		array('name' => "Spanish Reina Valera - Public Domain",'table' => "bovp_rv",'records' => "31173"),
		array('name' => "Louis Segond 1910 (French) Bible, LSG",'table' => "bovp_fr",'records' => "31238")
	);

	if($version=='array') {

		return $bovp_registred_versions;

	} else { 

		return $bovp_registred_versions[$version][$item];

	}
    
}


function bovpInstalationVerify(){ 

		$return = 0;
		$bovp_system_version = get_option('bovp_system_version');
		$bovp_bd_version = get_option('bovp_bd_version');
		if($bovp_system_version != '1.5.2') {$return ++;}
		if($bovp_bd_version != '1.5.2') {$return ++;}
		return $return;
}


// Install function
function bovpSoftInstall(){ 

	global $wpdb;

	$install_type = bovpInstalationVerify();

	if($install_type==1) { // Soft update only

		update_option('bovp_system_version', '1.5.2');

	} elseif($install_type==2) { // New Install

		if(bovpTableExist(BOVP_TABLE_NAME)) {$wpdb->query("DROP TABLE `" . BOVP_TABLE_NAME . "`");}

		add_option("bovp_system_version", '1.5.1', '', 'yes');
		add_option("bovp_array_books", 'false', '', 'yes'); 
		add_option("bovp_itens_per_page", '20', '', 'yes');
        add_option("bovp_theme", 'default', '', 'yes');
        add_option("bovp_page", 'false', '', 'yes');
        add_option("bovp_source_random_verse", '0', '', 'yes');
		add_option("bovp_daily_verse", '0', '', 'yes');
		add_option("bovp_bd_version", '', '', 'yes');
		add_option("bovp_version", '-1', '', 'yes');
		add_option("bovp_status", false, '', 'yes');
		add_option("bovp_show_index", true, '', 'yes');
		add_option("bovp_font_size", '16', '', 'yes');
	} 

}     

// Uninstall function
function bovpSoftUninstall(){

	global $wpdb;	
	bovpDropTable('all');
	$wpdb->query( "DELETE FROM `wp_options` WHERE `option_name` LIKE '%bovp%_%'");

}

function bovpAdminStyles() {

	wp_enqueue_style( "adm", BOVP_FOLDER ."includes/bovp_adm_style.css");

}

function bovpTableExist($table=false){

	global $wpdb;

	if(!$table){return false;}
	if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {return false;} else { return true;}
}

function bovpCreateTable($bovp_table){

	global $wpdb;

	$create_bovp_table = "CREATE TABLE `". $bovp_table ."` (

			`id` int(11) NOT NULL auto_increment,
			`book` int(11) NOT NULL,
			`cp` int(11) NOT NULL,
			`vs` int(11) NOT NULL,
			`text` longtext NOT NULL,
			PRIMARY KEY  (`id`))"; 

		

	$table_create = $wpdb->query($create_bovp_table);

	$bovp_db_error = $wpdb->last_error;

	$bovp_table_exist = bovpTableExist($bovp_table);

	if($bovp_table_exist) {

		$return = true;

	} else {$return = false;}

	return $return;

}

function bovpDropTable($bovp_table_to_delete){

	global $wpdb;

	$bovp_versions = bovpBibleInfo();

	$return = false;

	$tables = '';

	if($bovp_table_to_delete='all') {

			foreach ($bovp_versions as $key => $table) {

				$tables .= $wpdb->prefix . $table['table'];

				if ($key < ( count($bovp_versions) - 1)) { $tables .= ',';}

			}

			$query = "DROP TABLE IF EXISTS " . $tables;

			$delete_all = $wpdb->query($query);

			if($delete_all) $return = true;

	} else {

			$bovp_table_exist = bovpTableExist($bovp_table_to_delete);

			if($bovp_table_exist) {

				$delete_table = $wpdb->query("DROP TABLE IF EXISTS `" . $bovp_table_to_delete . "`");

				if($delete_table) $return = true;

			} 

	}

	return $return;

}


function bovpInsertData($bovp_install){

    global $wpdb;

    $remove_all = bovpDropTable('all');

    update_option('bovp_daily_verse', false);

    $bovp_table_install = bovpBibleInfo($bovp_install,'table');
    $bovp_records = bovpBibleInfo($bovp_install,'records');
    $bovp_version_name = bovpBibleInfo($bovp_install,'name');

    $bovp_wp_table_name = $wpdb->prefix . $bovp_table_install;

    $bovp_slice = 0;
    $last_slice = 0;
    $bovp_row_count = 0;

    $bovp_create_table = bovpCreateTable($bovp_wp_table_name);

    if($bovp_create_table) {

        $bovp_data_insert = dirname(__FILE__) . '/data/'.$bovp_table_install;

        $bovpInsertData = new bovpDataInsert();
        $bovpInsertData->setFileName($bovp_data_insert);
        $bovpInsertData->setTable($bovp_wp_table_name);
        $bovpInsertData->insertFile();

        $sql = 'SELECT COUNT(`id`) as "TOTAL" FROM `'. $bovp_wp_table_name .'`';

        $results = $wpdb->get_results( $sql );

        $bovp_row_count = $results[0]->TOTAL;

        if((int)$bovp_row_count == (int)$bovp_records) { 

            update_option("bovp_status", true);
            update_option("bovp_bd_version", '1.5.1');
            update_option("bovp_version", $bovp_install);

            bovpArrayBooks($bovp_wp_table_name);

            return array(true,__('The table was installed successfully.', 'bovp'));

        } else {return array(false,__('The Bible Text insert fail. Please try again!', 'bovp'));}

    } else {return array(false,__('Unable to create the table.', 'bovp'));}

}

#Plugin Translation
function bovpActiveTranslate(){ 	

  load_plugin_textdomain( 'bovp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );  

}

function bovpDependences() {

	$theme = BOVP_THEME;
	$style_sheet = BOVP_FOLDER ."themes/".$theme."/".$theme.".css";
	wp_enqueue_style( "bible_style", $style_sheet);
	wp_enqueue_script("jquery");
	wp_enqueue_script( "bible_js", BOVP_FOLDER ."includes/bovp.js");

}


#Generate book's array and version bible variable
function bovpArrayBooks($table){

	global $wpdb;

	$query_books = "SELECT * FROM `$table` WHERE `book` = 0 AND `cp` <> 0";

	$bovp_array_books = array();

	$books = $wpdb->get_results ($query_books , ARRAY_A);

	foreach($books as $key => $book) {

		$data = array(
			'bk' => $book['cp'],
			'name' => $book['text'],
			'slug_name' => str_replace(' ', '-', strtolower(remove_accents($book['text']))),
			'pages' => $book['vs']);


		array_push($bovp_array_books, $data); 

	}

	update_option("bovp_array_books", $bovp_array_books);

}

#Get all published page 
function bovpGetAllPages() {

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


#List all page to select
function bovpListWordpressPages($name, $list){

		$option_value = get_option($name); 

		echo "<td><select name='" . $name . "'>";

			foreach($list as $key => $value) {

				"$key" == $option_value ? $selected = "selected='selected' " : $selected = '';       
	          	echo "<option value='$key' $selected>$value</option>";

	        }

	    echo "</select> <br/>";

}

#Echo a Daily Verse Json array for social share
function bovpJsonVerse() {

	$bovp_daily_verse = get_option('bovp_daily_verse');

	if(!$bovp_daily_verse) {return false;}	

	$ref = '(' . $bovp_daily_verse['book_name'] . ':' . $bovp_daily_verse['cp'] . ':' . $bovp_daily_verse['vs'] . ')';
	$link = BOVP_URL . "&bk=". $bovp_daily_verse['book'] . "&cp=" . $bovp_daily_verse['cp'] . "&vs=" . $bovp_daily_verse['vs'];
	$text = $bovp_daily_verse['text'];

	$dados = array(

		'method' => 'feed', 
		'name' => 'Vivendo a Palavra',
		'link' => $link,
		'picture' => 'http://vivendoapalavra.org/fb/logovpshare.jpg',
		'caption' => $ref,
		'description' => $text,
		'message' => 'Postado via vivendoapalavra.org',

	);


	echo '<script type="text/javascript">';
	echo 'var json_verse = ' . json_encode($dados) . ';';
	echo '</script>';

}

#Generate new Daily Verse
function bovpNewVerse($return = true) {

		if(!defined('BOVP_TABLE_NAME')){return false;}

		global $wpdb;

		switch(get_option('bovp_source_random_verse')) {

			case 0:$params=""; break;
			case 1:$params="WHERE `book` =< 39"; break;
			case 2:$params="WHERE `book` >= 40"; break;
			case 3:$params="WHERE `book` = 19"; break;

		}

		$sql = "SELECT * FROM ". BOVP_TABLE_NAME ." $params ORDER BY rand() LIMIT 1";

		$daily_verse = $wpdb->get_row($sql);
					
		if($daily_verse){

			$bovp_start_verse = $daily_verse->vs;

			$bovp_daily_verse = $daily_verse->text;

			$bovp_end_verse = $daily_verse->vs;

			while (strripos(".?!", substr(trim($bovp_daily_verse), -1)) === FALSE) { 

				$add_verse = $wpdb->get_row("SELECT * FROM ". BOVP_TABLE_NAME ." WHERE id = '".$counter_vs_id."' LIMIT 1");
				$bovp_end_verse=$add_verse->vs;
				$bovp_daily_verse .= ' '.$add_verse->text;

			} 

			if($bovp_start_verse==$bovp_end_verse) {$bovp_vs_ref = $bovp_start_verse;} else {$bovp_vs_ref = $bovp_start_verse .'-'.$bovp_end_verse; }


			if(BOVP_FURL_STATS) {

				$bovp_link_verse = bovpBookInfo($daily_verse->book,'name') . '/' . $daily_verse->cp . '/' . $daily_verse->vs;

			} else {

				$bovp_link_verse = "&bk=" . $daily_verse->book . '&cp=' . $daily_verse->cp . '&vs=' . $daily_verse->vs;

			}


			$bovp_daily_verse = array(

			'id' => $daily_verse->id,
			'date' => date('d/m/Y',time()), 
			'book' => $daily_verse->book,
			'cp' => $daily_verse->cp,
			'vs' => $daily_verse->vs,
			'book_name' => bovpBookInfo($daily_verse->book,'name'),
			'link' => $bovp_link_verse,
			'ref' => bovpBookInfo($daily_verse->book,'name') . ':' . $daily_verse->cp . ':' . $bovp_vs_ref,
			'text' => $bovp_daily_verse
			);

			update_option("bovp_daily_verse", $bovp_daily_verse);

		}

		

		if($return==true) {$bovp_daily_verse = get_option('bovp_daily_verse');return $bovp_daily_verse;}

}


function bovpVerifyVerse() {

	$bovp_daily_verse = get_option('bovp_daily_verse');

	if($bovp_daily_verse != false) {

		$bovp_date_compare = $bovp_daily_verse['date'];

		if(trim($bovp_date_compare) == trim(date('d/m/Y',time()))) {

			return false;

		} else {

			//bovpNewVerse();
		}
	} else {

		bovpNewVerse();

	}

}


function bovpShowVerse($return_type = 'show', $type = 'vd'){

	if($type=='vd') {

		if(!$bovp_daily_verse = get_option('bovp_daily_verse')) {$bovp_daily_verse = bovpNewVerse();}

		$date_verse = $bovp_daily_verse['date'];

		if(date('d/m/Y',time()) != $date_verse) {$bovp_daily_verse = bovpNewVerse();}

		

		extract($bovp_daily_verse);

		$prmt = array('bk'=>$book,'cp'=>$cp, 'vs'=>$vs);

		$link = bovpWriteUrl($prmt);

		$ref_verse = "$book_name:$cp:$vs";

		$return = $text . " <span class='verse_ref'><a href='" . $link . "'>(".$ref_verse.")</a></span>";

		if ($return_type == 'show'){ echo $return;} 

		elseif ($return_type == 'var'){ return array('vd'=>$return,'link'=>$link);} 

		elseif ($return_type == 'array'){ return $bovp_daily_verse;}

		elseif ($return_type == 'return'){ return $return;}

		elseif ($return_type == 'share'){ return array('text'=>$text,'link'=>$link,'ref'=>$ref_verse);}

	} else { 

		# use in the next version

	}

}

// Swich book information function
function bovpBookInfo($book,$item) {

	$bovp_array_books =  get_option("bovp_array_books");

	if( empty($bovp_array_books) OR !isset($bovp_array_books)){

		bovpArrayBooks(BOVP_TABLE_NAME);
		$bovp_array_books =  get_option("bovp_array_books"); 

	}	

	if(is_numeric($book)) {

		
		$count_books = count($bovp_array_books);

		if($book > 0 AND $book <= $count_books) {$found = true;} else { $found = false;}

		$book = $book -1;

	} else {

		foreach ($bovp_array_books as $v) {

			$book = remove_accents($book);

			if($v['name'] == $book OR $v['slug_name'] == $book) {

				$book = (int) $v['bk'] - 1; $found = true; break;
			
			} else { $found = false;}
			
		}		


	}

	if($found) {$return = $bovp_array_books[$book][$item];} else {$return = false;}

	return 	$return;

}

	
function bovpBookSelect($mode = "echo"){


	$bovp_array_books =  get_option("bovp_array_books");
	$list_books_combo = '<option value="0" >'. __('Entire Bible','bovp') .'</option>';
	$list_books_combo .= '<optgroup label="' . __('Old Testament','bovp') . '">';

	foreach($bovp_array_books as $list_array) {

		if(BOVP_FURL_STATS){$book_id = $list_array['slug_name'] ;}else{$book_id = $list_array['bk'] ;}


		$book_name = $list_array['name'];
		$num_pages = $list_array['pages'];
		if($list_array['bk'] !=0){$list_books_combo .=  '<option num_pages="'.$num_pages.'" value="'.$book_id .'" >'.$book_name.'</option>';	}
		if($list_array['bk'] ==39){$list_books_combo .=  '<\optgroup><optgroup label="' . __('New Testament','bovp') . '">';	}

	}

	$list_books_combo .=  '<\optgroup>';

	if ($mode == "echo") {echo $list_books_combo;} else { return $list_books_combo;}

}


function bible_form($bovp_return_form = 'echo'){ 

$params = (BOVP_FURL_STATS==true) ? BOVP_SLUG_SEARCH : '';

$bovp_form = "

<div class='bovp_search_container bovp_clear'>
<form method='post' action='" . BOVP_URL  . $params ."' name='bovp_form_search' class='bovp_form_search bovp_clear' >
<input name='page_id' type='hidden' value='" . BOVP_PAGE . "'/>
<input class='bovp_seach_input' type='text' id='sh' name='sh' placeholder='" . __('Search','bovp') . "' >
<input name='bk' type='hidden' value='0'/>
<input name='cp' type='hidden' value='0'/>		
<input name='vs' type='hidden' value='0'/>
<input name='s_type' type='hidden' value='s'/>
<button class='bovp_button bovp_btn' type='submit'>" . __('Send','bovp') . "</button>
</form></div>";

if($bovp_return_form == 'echo') {echo $bovp_form;} elseif($bovp_return_form == 'var'){return $bovp_form;}

}

function bovpWidgetsInit() {

  register_widget('bovp_widget_verse');
  register_widget('bovp_widget_search');
  register_widget('bovp_widget_index');

}



function bovpShowMessage($message, $errormsg = false) {

	if ($errormsg) {$class = 'bovp_error';} else {$class = 'bovp_success fade';}

	$return = sprintf('<div id="bovp_message" class="%s"><p><strong>%s</strong></p></div>', $class, $message);

	return $return;

}  


	// For test of array books function only
	function bovp_list_array() { 

		$array_books = get_option("bovp_array_books");
		print_r($array_books);
		
	}


	/* Recovery slug name for the Bible page */
	/* ADD in the 1.5.1 Version */
	function slug_name($post_id=false){
		
		if(!$post_id){$post_id = $post->ID;}
		
		$post_data = get_post($post_id, ARRAY_A);

		return $post_data['post_name'];

	}

	/* ADD vars for Bible use */
	/* ADD in the 1.5.1 Version */
	function bovpIncludeVars($qvars ){ 

		array_push($qvars , 'bk', 'cp', 'sh', 'vs', 'cpg','s_type','r_count', 'link_back', 'test_plugin');
	    return $qvars ;

	} 

	/* Update rewrite rules */
	/* ADD in the 1.5.1 Version */
	function bovpPrettyUrlsRulesUpdate(){
		
		// Rewrite rules update
		global $wp_rewrite;

	   	$wp_rewrite->flush_rules();
	}

	/* ADD new rewrite rules for the Bible */
	/* ADD in the 1.5.1 Version */
	function bovpPrettyUrlsNewRules($rules) {

		global $bovp_vars;
		
		$slug_bovp = BOVP_SLUG_PAGE;
		$results = BOVP_SLUG_SEARCH;
		// Add rewrite rules
		$newrules = array();

		// Add bovp rewrite rules
		//page-name/book_name | page-name/book_name/cp | page-name/book_name/cp/vs
		$newrules["($slug_bovp)/([0-9a-zA-Z-]*)/?([0-9]{0,3})/?([0-9]{0,3})/?$"] = 'index.php?pagename=$matches[1]&bk=$matches[2]&cp=$matches[3]&vs=$matches[4]';
		//page-name/results/cpg
		$newrules["($slug_bovp)/($results)/?([0-9a-zA-Z-%+]*)/?$"] = 'index.php?pagename=$matches[1]&sh=$matches[3]&pg=$matches[3]';
		//page-name/results/book_name/sh/cpg
		$newrules["($slug_bovp)/($results)/?([0-9]*)/?([0-9a-zA-Z-%+]*)/?$"] = 'index.php?pagename=$matches[1]&cpg=$matches[3]&sh=$matches[4]';

		return $newrules + $rules;

	}



	/* Return the formated current URL OR formated URL from $prmt */
	/* ADD in the 1.5.1 Version */
	function bovpWriteUrl($prmt=false) {

		global $bovp_vars;

		$write_url = BOVP_URL;

		if(!$prmt) {

			$bk = $bovp_vars['bk'];
			$cp = $bovp_vars['cp'];
			$vs = $bovp_vars['vs'];
			$sh = $bovp_vars['sh'];			

		} else { extract($prmt); }

			$s2 = (isset($cp) AND $cp !='0') ? $cp : false; 
			$s3 = (isset($vs) AND $vs !='0') ? $vs : false;
			$s4 = (isset($sh) AND $sh !='0') ? $sh : false;

			if(BOVP_FURL_STATS) { # Friendly URL is activate

				$book = (isset($bk) AND $bk != '0') ? bovpBookInfo($bk, 'slug_name') : '';

				if(isset($vs) AND $vs != '0') { // Daily Verse

					$write_url .= "$book/$s2/$s3";
					
				} elseif(isset($cp) AND $cp != '0') {

					$write_url .= "$book/$s2";

				} elseif (isset($bk) AND $bk != '0') {
					
					$write_url .= "$book";

				} elseif(isset($sh) AND $sh != '0') {

					$write_url .= "results/$s4";

				}

			} else { # Friendly URL don't activate

				$book = (isset($bk) AND $bk != '0') ? $bk : '';

				if(isset($vs) AND $vs != '0') { // Daily Verse

					$write_url .= "&bk=$book&cp=$s2&vs=$s3";
					
				} elseif(isset($cp) AND $cp != '0') {

					$write_url .= "&bk=$book&cp=$s2";

				} elseif (isset($bk) AND $bk != '0') {
					
					$write_url .= "&bk=$book";

				} elseif(isset($sh) AND $sh != '0') {

					$write_url .= "&sh=$s4";

				}

			}
			
		# URL return
		return $write_url;		

	}

	/* Opengraph for social share */
	/* ADD in the 1.5.1 Version */
	function bovpOpengraph() {

		if (!is_page(BOVP_PAGE)) { return false;}

		global $wpdb;
		global $wp_query;
		global $bovp_vars;
		global $bovp_variable;

		$bovp_vars = $wp_query->query_vars;

		// if not set vars, setup then - v1.51
		if(!isset($bovp_vars['bk']) OR empty($bovp_vars['bk'])) $bovp_vars['bk'] = (int) 0;
		if(!isset($bovp_vars['cp']) OR empty($bovp_vars['cp'])) $bovp_vars['cp'] = (int) 0;
		if(!isset($bovp_vars['vs']) OR empty($bovp_vars['vs'])) $bovp_vars['vs'] = (int) 0;
		if(!isset($bovp_vars['sh']) OR empty($bovp_vars['sh'])) $bovp_vars['sh'] = (int) 0; 
		else {$bovp_vars['sh'] = urldecode(trim($bovp_vars['sh']));};

		bovpVerifyVerse();

		# IF SHOW DAILY VERSE
		if(isset($bovp_vars['vs']) AND $bovp_vars['vs']!= 0) {

			$sql = "SELECT * FROM ". BOVP_TABLE_NAME ." WHERE `book` = ".bovpBookInfo($bovp_vars['bk'],'bk')." AND `cp` = ".$bovp_vars['cp']." AND `vs` = ".$bovp_vars['vs'];

			$featured_verse = $wpdb->get_row($sql);

			$ref = '('.bovpBookInfo($bovp_vars['bk'],'name').':'.$bovp_vars['cp'].':'.$bovp_vars['vs'].')';
			$description = $featured_verse->text;



		} else {

			

			if(isset($bovp_vars['sh']) AND $bovp_vars['sh']!= '0') {

				$ref = __('Search for: ', 'bovp') . $bovp_vars['sh'];
				$description = __('Search in the Bible: ', 'bovp');

			} elseif(!isset($bovp_vars['bk']) OR $bovp_vars['bk']== '0') {

				$ref = __('Bible Index', 'bovp');
				$description = __('All of 66 books of Holly Bible. Choose one!', 'bovp');;

			} else {

				$cp = (!isset($bovp_vars['cp']) OR $bovp_vars['cp'] == 0) ? ' 1' : ' '. $bovp_vars['cp'];
				$ref = __('Online Bible: ', 'bovp') . bovpBookInfo($bovp_vars['bk'],'name') . $cp;
							
				$query_books = "SELECT GROUP_CONCAT(`text` SEPARATOR ' ') AS 'text' FROM `".BOVP_TABLE_NAME."` WHERE `book` = ".bovpBookInfo($bovp_vars['bk'],'bk')." AND `cp` = " . $cp ." AND  `vs` < 5";

				$books = $wpdb->get_results ($query_books , ARRAY_A);

				$description = $books[0]['text'];
				

			}


		}


			$url = bovpWriteUrl();

			$return = "\n<!-- Description / Canonical -->\n";
			$return.= "<link rel='canonical' href='$url'>\n";
			$return.= "<meta name='description' content='$description' />\n\n";
			$return.= "<!-- Schema.org markup for Google+ -->\n";
			$return.= "<meta itemprop='name' content='$ref'>\n";
			$return.= "<meta itemprop='description' content='$description'>\n";
			$return.= "<meta itemprop='image' content='". BOVP_GP_IMG_SHARE ."'/>\n\n";
			$return.= "<!-- BOVP OPENGRAPH -->\n";
			$return.= "<meta property='og:url' content='$url'/>\n";
			$return.= "<meta property='og:title' content='$ref' />\n";
			$return.= "<meta property='og:site_name' content='". get_option('blogname')."' />\n";
			$return.= "<meta property='og:description' content='$description' />\n";
			$return.= "<meta property='og:image' content='" . BOVP_FB_IMG_SHARE ."'/>\n";
			$return.= "<meta property='og:image:type' content='image/jpeg'>\n";
			$return.= "<meta property='og:type' content='website'>\n";		
			$return.= "<meta property='fb:admins' content='100001351583041' />\n\n";
			$return.= "<!-- Twitter Card data -->\n";
			$return.= "<meta name='twitter:card' content='summary'>\n";
			$return.= "<meta name='twitter:site' content='@vivendoapalavra'>\n";
			$return.= "<meta name='twitter:title' content='$ref'>\n";
			$return.= "<meta name='twitter:description' content='$description'>\n";
			$return.= "<meta name='twitter:creator' content='@vivendoapalavra'>\n";
			$return.= "<meta name='twitter:image' content='" . BOVP_TWITTER_IMG_SHARE ."'>\n";


		echo $return;

	}

	/* Add an  index of books in the initial page  */
	/* ADD in the 1.5.1 Version */
	function bovpShowIndexFisrt($ver) {

		if(!defined('BOVP_VERSION') OR BOVP_VERSION == -1) {return false;}

		global $bovp_vars;

		$bovp_array_books = get_option('bovp_array_books');

		$bovp_group_books = array(
			
		'5' => array('0'=> 'Livros da Lei','1'=>'The Pentateuch','3'=>'Le Pentateuque'),
		'17' => array('0'=> 'Livros Históricos','1'=>'The Historical books','3'=>'Livres historiques'),
		'22' => array('0'=> 'Poéticos e de Sabedoria','1'=>'The Poetic and Wisdom writings','3'=>'Livres poétiques et de sagesse'),
		'27' => array('0'=> 'Profetas Maiores','1'=>'The Major Prophets','3'=>'Les Prophètes majeurs'),	
		'39' => array('0'=> 'Profetas Menores','1'=>'The Minor Prophets','3'=>'Les Prophètes mineurs'),
		'43' => array('0'=> 'Evangelhos','1'=>'God Spell','3'=>'Evangile'),
		'44' => array('0'=> 'Histórico','1'=>'Acts','3'=>'Actes'),	
		'57' => array('0'=> 'Cartas Paulinas','1'=>'Paulines Epistles','3'=>'Epitres de Paul'),
		'65' => array('0'=> 'Outras Cartas','1'=>'General Epistles','3'=>'Epitres généraux'),	
		'66' => array('0'=> 'Profético','1'=>'Prophecy','3'=>'Prophétie')

		);

		$return = "<div class='bovp_index_first'>\n";

		$return .= "<ul class='bovp_bible_indice clearfix'>\n";

		$return .= "<h3 class='div_testament'>".__('Old Testament','bovp')."</h3>\n";

		if(array_key_exists($ver, $bovp_group_books['5'])) {

			// listar links
			$book_id = 0;

			foreach ($bovp_group_books as $key => $value) {

				$return .= '<li class="bovp_title_book">' . $value[$ver] .'</li>';

				for ($i=$book_id; $i < $key; $i++) { 
				
					$book_name = $bovp_array_books[$book_id]['name'];
					$bk = $bovp_array_books[$book_id]['bk'];

					if(BOVP_FURL_STATS) {

						#Friendly URL activated.
						$slug_name = $bovp_array_books[$book_id]['slug_name'];
						$prmt = BOVP_URL . $slug_name . "/";	


					} else {

						#Friendly URL don't activated.
						$prmt = BOVP_URL . '&bk=' . $bk;
					}

					
					$book_id++;

					$return .= '<li class="bovp_item_book"><a href="' . $prmt . '">'.$book_name. '</a></li>';

				}

				if($key == 39) {$return .= "<h3 class='div_testament'>".__('New Testament','bovp')."</h3>\n";}

			}

			$return .= "</ul>\n";

			$return .= "</div>";

		} else {


			foreach ($bovp_array_books as $key => $value) {

			
					$book_name = $value['name'];
					$bk = $key;

					if(BOVP_FURL_STATS) {

						#Friendly URL activated.
						$slug_name = $value['slug_name'];
						$prmt = BOVP_URL . $slug_name . "/";	


					} else {

						#Friendly URL don't activated.
						$prmt = BOVP_URL . '&bk=' . $bk;
					}

					

					$return .= '<li class="bovp_item_book"><a href="' . $prmt . '">'.$book_name. '</a></li>';


				if($key == 39) {$return .= "<h3 class='div_testament'>".__('New Testament','bovp')."</h3>\n";}

			}

		}

		

		return $return;

	}


	/* Latin Sanitize Search */
	/* ADD in the 1.5.1 Version */
	function bovpSanitizeSeach($word) {

		$arrayReplacement = array(
			"a" => "(a|á|ã)",
			"e" => "(e|é|ê)",
			"i" => "(i|í)",
			"o" => "(o|ó|õ|ô)",
			"u" => "(u|ú)",
			"c" => "(c|ç)"
			);

		return preg_replace($arrayReplacement,array_values($arrayReplacement),$word);

	}

	/* Mark the tags in posts */
	/* ADD in the 1.5.1 Version */
	function bovpTagMarker($bovp_replace) {

	global $wpdb;

	$array_replace = array();
	$QueryTags = "SELECT DISTINCT $wpdb->terms.* FROM $wpdb->terms ";
	$tags = $wpdb->get_results($QueryTags, ARRAY_A);

		if($tags){


			foreach ($tags as $tag) {

				$tag_id = "tag_id_".trim($tag['term_id']);
				$tag_name = trim($tag['name']);
				$tag_slug = trim($tag['slug']);
				
				$bovp_replace = preg_replace("/$tag_name/i", "#$tag_id#", $bovp_replace);

				$link_url = get_option('siteurl') . "?tag=" . $tag_slug;
				$link = "<a class='bovp_tag_link' href='$link_url'>$tag_name</a>";
				$array_replace["#$tag_id#"] = $link;
			}

			$bovp_replace = str_replace(array_keys($array_replace), $array_replace, $bovp_replace);

			return $bovp_replace;

		} else {return false;}

	}

	/* Social Icons to Share */
	/* ADD in the 1.5.1 Version */
	function bovpSharer($prmt=false) {

		if(!$prmt) {

			$url = urlencode('http' . ($_SERVER['SERVER_PORT'] == 443 ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

		} else { 

			if(is_array($prmt)) {

				$url = urlencode(bovpWriteUrl($prmt));
				
			} else {

				if($prmt=='vd') {

					#$prmt is a Daily Verse

					$data_share = bovpShowVerse('share');

					$url = urlencode($data_share['link']);
					$text_share = $data_share['text'];
					
				} else {

					#$prmt is a URL data

					$url = urlencode($prmt);
					$text_share = __('Visite nosso site','bovp');

				}	 

			}

		}

		$ref_share = urlencode('vivendoapalavra');

		$max_text_size = 160 - strlen($url) - strlen($ref_share);

		if(strlen($text_share) > $max_text_size) {

			$excerpt = substr($text_share, 0, $max_text_size);

			$last=strrpos($excerpt," ");

			$excerpt=urlencode(substr($text_share,0,$last) . '...');

		} else {$excerpt = urlencode($text_share);}



		$fb_link = 'https://www.facebook.com/sharer/sharer.php?u=' . $url;
		$tw_link = 'https://twitter.com/intent/tweet?url=' . $url . '&text='. $excerpt .'&hashtags=' . $ref_share;
		$gl_link = 'https://plus.google.com/share?url=' . $url;

		$social_icons  = "<ul class='bovp_social_icons'>";
		$social_icons .= "<li class='bovp_social_li'>";
		$social_icons .= "<a href='$fb_link' class='fb_icon bovp_popup'></a>";
		$social_icons .= "</li>";
		$social_icons .= "<li class='bovp_social_li'>";
		$social_icons .= "<a href='$tw_link' class='tw_icon bovp_popup'></a>";
		$social_icons .= "</li>";
		$social_icons .= "<li class='bovp_social_li'>";
		$social_icons .= "<a href='$gl_link' class='gl_icon bovp_popup'></a>";
		$social_icons .= "</li>";
		$social_icons .= "</ul>";

		return $social_icons;

    }


	function bovpLinkGen($content) {

	if(!defined('BOVP_TABLE_NAME')){return $content;}

	$regex = '/\x28[0-9A-Za-à-ú]*:[0-9]{1,3}:[0-9]{1,3}\x29/i';

	$bovp_array_books = get_option('bovp_array_books');
			
			preg_match_all($regex, $content, $matches);

			foreach($matches[0] as $results){

				$result_ref = substr($results, 1, -1); 
				$explode = explode(':', $result_ref);
				$book = strtolower($explode[0]);
				$cp = $explode[1];
				$vs = $explode[2];
				
				foreach ($bovp_array_books as $bovp_book){
			
					if (in_array($book, $bovp_book)) {

						$bk = $bovp_book['bk']; 
									
						$prmt = array('bk'=>$bk,'cp'=>$cp,'vs'=>$vs);

						$url = bovpWriteUrl($prmt);
					
						$link = '<a href="'.$url.'">'.$results.'</a>';
						
						$content = str_replace($results,$link,$content);

						break;
						
					} 

				}
						
			}

	return $content;

	}


	function bovpShortCode( $atts ) {

		global $wpdb;

		$atts = shortcode_atts(array('ref' => false), $atts, 'bovp_vd');

		if($atts['ref']==false) { 

		$daily_verse = get_option('bovp_daily_verse');

		$book_name = $daily_verse['book_name'];
		$book = $daily_verse['book'];
		$cp = $daily_verse['cp'];
		$vs = $daily_verse['vs'];

		} else {

		$explode = explode(':',$atts['ref']);

		$book_name = bovpBookInfo($explode[0],'name');
		$book = bovpBookInfo($explode[0],'bk');
		$cp = $explode[1];
		$vs = $explode[2];

		}

		
			if( preg_match('/-/',$vs) ) { $vs_link = explode('-', $vs); $vs_link = $vs_link['0']; } else { $vs_link = $vs; }

			$prmt = array('bk'=>$book,'cp'=>$cp,'vs'=>$vs_link);

			$link = bovpWriteUrl($prmt);

			$sql = "SELECT CONCAT(`vs`,' ',`text`) AS 'text' FROM ". BOVP_TABLE_NAME ." WHERE `book` = $book AND `cp`= $cp ";

		 	if (preg_match('/-/',$vs)) {

				$trecho = explode('-', $vs);
				$start = $trecho[0];
				$end = $trecho[1];

				$sql .= "AND `vs` BETWEEN ". $start ." AND ". $end;				
 
			} else {

				$sql .= "AND `vs` = " . $vs;

			}

			$verses = $wpdb->get_results ( $sql, ARRAY_A );

			if($verses) {

				$group_verse = '';

				foreach ($verses as $verse) {

					$group_verse .= $verse['text'] . ' ';
				}

				$return  = "<div class='bovp_reference'>";
				$return .= $group_verse;
				$return .= " <span class='bovp_reference_link'><a href='" . $link . "'>($book_name:$cp:$vs)</a></span></div>";

			} else {$return = false;}


	if($return == false) {$return = __('Error: Invalid format','bovp');}

	return $return;
		

	}

	add_shortcode('bovp_vd', 'bovpShortCode');
	add_filter( 'widget_text', 'do_shortcode' );


	function bovpAdminMenu(){	

		add_menu_page( 
			__('Online Bible','bovp'), 
		 	__('Online Bible','bovp'), 
		 	'manage_options',
		 	'about_menu',
		 	'bovpMenuInitPage',
		 	BOVP_ICON,
		 	6 ); 

		add_submenu_page( 
			'about_menu',
			__('Settings','bovp'),
			__('Settings','bovp'),
			'manage_options',
			'setting_menu',
			'bovpSettingsPage' );

		add_action( 'admin_init', 'bovpOptionsSet');

	}


	function bovpOptionsSet() {	

		register_setting( 'bovp_options', 'bovp_page');
		register_setting( 'bovp_options', 'bovp_source_random_verse');
		register_setting( 'bovp_options', 'bovp_itens_per_page');
		register_setting( 'bovp_options', 'bovp_theme');
		register_setting( 'bovp_options', 'bovp_show_index');
		register_setting( 'bovp_options', 'bovp_font_size');

	}


	function bovpSettingsPage() {

		$bovp_versions = bovpBibleInfo();

		if( isset($_REQUEST['bovp_install']) AND !isset($_REQUEST['settings-updated'])) {

	          	$bovp_install = $_REQUEST['bovp_install'];

	            if ($bovp_install == get_option('bovp_version')) {

	              $bovp_message = bovpShowMessage(__('This table is already installed in your database.', 'bovp'), false);
	            
	            } else {

	              $bovp_text_install = bovpInsertData($bovp_install); 

	              if($bovp_text_install[0]!=false) {
	                  
	                  $bovp_message = bovpShowMessage($bovp_text_install[1], false);
	                  update_option('bovp_version', $bovp_install); 
	                      
	              } else { $bovp_message = bovpShowMessage($bovp_text_install[1], true); }

	        }
		 }   

	?>


	<div class="bovp_wrap">

	<h2 class="bovp_h2"><?php _e('Online Bible v.'. BOVP_SYSTEM_VERSION . ' - Settings','bovp');?></h2>

	<fieldset  class="bovp_fieldset">
	<legend  class="bovp_legend"><?php _e('Consider a Donation','bovp'); ?></legend>

	<p align="justify">
	<?php _e("If you use Online Bible plugin and want to contribute to the project's maintenance, you can use the link below to make a donation.",'bovp'); ?>
	          
	</p>

	<!-- INICIO FORMULARIO BOTAO PAGSEGURO -->

	<form target="pagseguro" action="https://pagseguro.uol.com.br/checkout/v2/donation.html" method="post" style="float:left;" target="_blank">

	<input type="hidden" name="receiverEmail" value="bibliaonlinevp@vivendoapalavra.com.br" />

	<input type="hidden" name="currency" value="BRL" />

	<input type="image" src= <?php echo plugin_dir_url(__FILE__) . 'img/pagseguro.jpg' ?> name="submit" alt="Doe com PagSeguro - é rápido e seguro!" />

	</form>

	<!-- FINAL FORMULARIO BOTAO PAGSEGURO -->

	<!-- INICIO FORMULARIO BOTAO PAYPAL-->

	<form action="https://www.paypal.com/cgi-bin/webscr" method="post"  target="_blank">

	<input type="hidden" name="cmd" value="_s-xclick">

	<input type="hidden" name="hosted_button_id" value="9KV25MLWLPKQN">

	<input type="image" src=<?php echo plugin_dir_url(__FILE__) . 'img/paypal.jpg' ?> border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">

	<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">

	</form>

	<!-- FINAL FORMULARIO BOTAO PAYPAL-->


	</fieldset>

	<fieldset class="bovp_fieldset">

	<legend class="bovp_legend"><?php _e('Choose and Install the Bible version','bovp');?></legend>
	  


	<div id="bible_version" class="clearfix">
	  
	    <form id="text_install" action="admin.php" method="get" style="float:left;">

	      <input type="hidden" value="setting_menu" name="page">

	        <select name="bovp_install" id="bovp_install">

	          <?php

	          if (get_option('bovp_version') =='-1') {

	              echo "<option value='-1' selected>" . __('Not Installed','bovp') . "</option>";

	          } else {echo "<option value='-1'>" . __('Not Installed','bovp') . "</option>";}
	        

	            foreach($bovp_versions as $indice => $bovp_version) {

	              echo "<option value='" . $indice . "'"; 

	              if (get_option('bovp_version') == $indice) {echo " selected";}

	              echo ">" . $bovp_version['name'] . "</option>";

	            }

	            echo "</select>";
	            
	            ?>

	            <button type="submit" id="text_install_button" class="button-primary"><?php _e('Install','bovp') ?></button>

	  </form>

	</div>

	<?php  if(isset($bovp_message)) echo $bovp_message; ?>

	</fieldset>


	    
	<form method="post" action="options.php">

	      <?php settings_fields( 'bovp_options' ); ?>

	          
	        <fieldset class="bovp_fieldset">
	          <legend class="bovp_legend"><?php _e('Page where the online Bible will be displayed','bovp');?></legend>
	          <?php bovpListWordpressPages ('bovp_page', bovpGetAllPages ()); ?>
	        </fieldset>


	        <fieldset class="bovp_fieldset">
	          <legend class="bovp_legend"><?php _e('Source of the daily verse','bovp');?></legend>
	      	     
	             <select name="bovp_source_random_verse">
	                <option value="0" <?php if (get_option('bovp_source_random_verse')==0) {echo 'selected';} ?> ><?php _e('All the Bible','bovp') ?></option>
	                <option value="1" <?php if (get_option('bovp_source_random_verse')==1) {echo 'selected';} ?> ><?php _e('Old Testament','bovp') ?></option>
	                <option value="2" <?php if (get_option('bovp_source_random_verse')==2) {echo 'selected';} ?> ><?php _e('New Testament','bovp') ?></option>
	                <option value="3" <?php if (get_option('bovp_source_random_verse')==3) {echo 'selected';} ?> ><?php _e('The Book of Psalms','bovp') ?></option>
	              </select>
	        </fieldset>
	      	
	        <fieldset class="bovp_fieldset">
	          <legend class="bovp_legend"><?php _e('Search results to be displayed per page','bovp');?></legend>

	            <input name="bovp_itens_per_page" value="<?php $bovp_itens_per_page = get_option('bovp_itens_per_page'); echo $bovp_itens_per_page; ?>" />
	          
	        </fieldset>


	        <fieldset class="bovp_fieldset">

	          <legend class="bovp_legend"><?php _e('Show on front:','bovp');?></legend>

	            <select name="bovp_show_index">
	                <option value='1' <?php if (BOVP_SHOW_INDEX=="1") {echo 'selected';} ?> ><?php _e('Show index','bovp') ?></option>
	                <option value='0' <?php if (BOVP_SHOW_INDEX=="0") {echo 'selected';} ?> ><?php _e('Show Book of Genesis','bovp') ?></option>
	            
	            </select>

	        </fieldset>

	        <fieldset class="bovp_fieldset">
	        	
	          <legend class="bovp_legend"><?php _e('Default font size:','bovp');?></legend>

	            <select name="bovp_font_size">
	                <option value='12' <?php if (BOVP_FONT_SIZE=="12") {echo 'selected';} ?> >12px</option>
	                <option value='14' <?php if (BOVP_FONT_SIZE=="14") {echo 'selected';} ?> >14px</option>
	                <option value='16' <?php if (BOVP_FONT_SIZE=="16") {echo 'selected';} ?> >16px</option>
	                <option value='18' <?php if (BOVP_FONT_SIZE=="18") {echo 'selected';} ?> >18px</option>           	
	            </select>

	        </fieldset>


	        <fieldset class="bovp_fieldset">
	          <legend class="bovp_legend"><?php _e('Choose theme','bovp');?></legend>

	            <select name="bovp_theme">
	                <option value="default" <?php if (BOVP_THEME=="default") {echo 'selected';} ?> >Default</option>
	                <option value="ichthys" <?php if (BOVP_THEME=="ichthys") {echo 'selected';} ?> >Ichthys</option>

	            </select>

	        </fieldset>

	          		
	           	
	      <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Update Settings','bovp') ?>" /></p>

	</form>


	</div>

	<?php

}


function bovpMenuInitPage() {

?>

<div class="bovp_wrap">

<h2 class="bovp_h2"><?php _e('Online Bible for Wordpress v.','bovp'); echo BOVP_SYSTEM_VERSION; ?></h2>

<fieldset  class="bovp_fieldset">

<legend  class="bovp_legend"><?php _e('Consider a Donation','bovp'); ?></legend>

<p align="justify">

<?php _e("If you use Online Bible plugin and want to contribute to the project's maintenance, you can use the link below to make a donation.",'bovp'); ?>

</p>

<!-- INICIO FORMULARIO BOTAO PAGSEGURO -->

<form target="pagseguro" action="https://pagseguro.uol.com.br/checkout/v2/donation.html" method="post" style="float:left;" target="_blank">

<input type="hidden" name="receiverEmail" value="bibliaonlinevp@vivendoapalavra.com.br" />

<input type="hidden" name="currency" value="BRL" />

<input type="image" src="http://www.vivendoapalavra.org/donate/pagseguro.jpg" name="submit" alt="Doe com PagSeguro - é rápido e seguro!" />

</form>

<!-- FINAL FORMULARIO BOTAO PAGSEGURO -->

<!-- INICIO FORMULARIO BOTAO PAYPAL-->

<form action="https://www.paypal.com/cgi-bin/webscr" method="post"  target="_blank">

<input type="hidden" name="cmd" value="_s-xclick">

<input type="hidden" name="hosted_button_id" value="9KV25MLWLPKQN">

<input type="image" src="http://www.vivendoapalavra.org/donate/paypal.jpg" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">

<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">

</form>

<!-- FINAL FORMULARIO BOTAO PAYPAL-->

</fieldset>

<fieldset class="bovp_fieldset">

	<legend class="bovp_legend"><?php _e('Informations','bovp');?></legend>

	<p><?php _e('Plugin for implementation of Bible Online in your Wordpress blog. With it, you can spread the Word of God and bless your website\'s users. The plugin allows to consult all of 66 books of the Holy Bible.','bovp'); ?></p>
 
	<p>Author:&nbsp;<a href="https://www.facebook.com/andrebrumsampaio">Andre Brum Sampaio</a></p>

	<p><?php echo __('Author URI: ','bovp') . '&nbsp;<a href="http://www.vivendoapalavra.org/">http://www.vivendoapalavra.org/</a>' ?></p>

	<p><?php echo __('Version: ','bovp') .  BOVP_SYSTEM_VERSION; ?></p>      




	<h3><?php _e('Versions:','bovp'); ?></h3>

	<p>
		<ul class="bovp_li_versions">
			<li>• King James Edition - English;</li>
			<li>• Almeida Corrigida Fiel - Português (1994);</li>
			<li>• Spanish Reina Valera (1960).<li>
			<li>• Louis Segond 1910 (French) Bible, LSG.<li>
		</ul>
	</p>

	<h3><?php _e('Settings:','bovp'); ?></h3>

	<p><?php _e('In the SETTINGS PAGE, select desired version and then click to install. Wait the bible text installation complete and then choose the options (page, itens per page, theme, verse source).','bovp');?></p>

</fieldset>

</div>

<?php


}




	/* Test status only */
	/* ADD in the 1.5.1 Version */
	function bovpShowStatus() {

		global $wpdb; 

		$test_return = '';

		$test_results = $wpdb->get_results( "SELECT * FROM `wp_options` WHERE `option_name` LIKE '%bovp%_%'");

		foreach ($test_results as $bovp_test_rows){

			$option_value = $bovp_test_rows->option_value;
			$option_name = $bovp_test_rows->option_name;
			$option_teste = get_option( $option_name );

			$test_return.= '<h3>' . $option_name . '</h3>';

			if(is_array($option_teste)){

				foreach ($option_teste as $key => $value) {

					if(is_array($value)) {

						foreach ($value as $book) {

							$test_return.= $book . '<br>';
						}


					} else {

					if('bovp_array_books'!=$key) {$test_return.= $key . ' -> '. $value . "<br>";}
					
					}

				}

			} else {

				$test_return.= $option_value . "<br>";

			}
				
		} 

		$test_return.=  '<h5>variaveis_globais</h5>';
		$test_return.=  'bovp_table_name = ' . BOVP_TABLE_NAME;
		$test_return.=  '<br>';
		$test_return.=  'bovp_url = ' . get_option('home').'/index.php?page_id='.get_option('bovp_page');
		$test_return.=  '<br>';
		$test_return.=  'bovp_page = ' . get_option('bovp_page');
		$test_return.=  '<br>';

		$test_return.=  '<h5>amostragem_da_tabela</h5>';

		$cmd = "SELECT * FROM `". BOVP_TABLE_NAME ."` ORDER BY RAND() LIMIT 0,10";

		$test_return.=  'comando: ' .$cmd . '<br>';

		$test_results = $wpdb->get_results($cmd);

		foreach ($test_results as $bovp_bible){

			$test_return.= $bovp_bible->text . '<br>';
		}

	echo $test_return;

	}


//activation
register_activation_hook(__FILE__,'bovpSoftInstall');
register_deactivation_hook(__FILE__,'bovpSoftUninstall');

// filters
add_filter('query_vars','bovpIncludeVars',1); //include query vars for bovp.
add_filter('the_content','bovpShowBible'); //show the bible in the select page.
add_filter("the_content","bovpLinkGen"); // Generate link in the content to bible page.

// hooks
add_action('admin_menu', 'bovpAdminMenu');
add_action('admin_head', 'bovpAdminStyles');
add_action('plugins_loaded', 'bovpActiveTranslate');
add_action('wp_head', 'bovpOpengraph');
add_action('wp_enqueue_scripts', 'bovpDependences');
add_action('widgets_init', 'bovpWidgetsInit');

/* HOOK to rewrite rules activate 1.5.1 */ 
if(BOVP_FURL_STATS) { 

	/* Call filters of rewrite rules */
	/* ADD in the 1.5.1 Version */
	add_filter("rewrite_rules_array","bovpPrettyUrlsNewRules");
	add_filter("init","bovpPrettyUrlsRulesUpdate");
	
}

?>
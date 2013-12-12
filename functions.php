<?php 

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {die(__('Access denied.','bovp')); }

// instalation function

function bovp_install(){ 

	$bovp_bible_page = get_option('bovp_page');
	$bovp_random_verse = get_option('bovp_source_random_verse');
	$bovp_daily_verse = get_option('bovp_daily_verse');


	$bovp_bible_page = (isset($bovp_bible_page) AND !empty($bovp_bible_page)) ? $bovp_bible_page : '-1';
	$bovp_random_verse = (isset($bovp_random_verse) AND !empty($bovp_random_verse)) ? $bovp_random_verse : '0';
	$bovp_daily_verse = (isset($bovp_daily_verse) AND !empty($bovp_daily_verse)) ? $bovp_daily_verse : '0';

		add_option("bovp_system_version", '1.5', '', 'yes');
		add_option("bovp_itens_per_page", '20', '', 'yes');
		add_option("bovp_bible_books_count", '0', '', 'yes');
		add_option("bovp_theme", 'default', '', 'yes');
		add_option("bovp_array_books", '0', '', 'yes'); 
		add_option("bovp_table", '-1', '', 'yes'); 
		add_option('bovp_version', '-1', '', 'yes'); 

		add_option("bovp_page", $bovp_bible_page, '', 'yes');
		add_option("bovp_source_random_verse", $bovp_random_verse, '', 'yes'); 		
		add_option("bovp_daily_verse", $bovp_daily_verse, '', 'yes'); 

		$bovp_registred_versions = array();

		$bovp_registred_versions[0] = __('Not Instaled','bovp') . "|bovp_none";
		$bovp_registred_versions[1] = "King James Edition - English|bovp_kj";
		$bovp_registred_versions[2] = "Almeida Corrigida Fiel - Português (1994)|bovp_acf";

		add_option("bovp_registred_versions", $bovp_registred_versions, '', 'yes'); 


	delete_option('bovp_bd_state');
	

}

// uninstall

function bovp_uninstall(){

	global $wpdb;

	$wpdb->query("DROP TABLE `" . BOVP_TABLE . "`");

	delete_option('bovp_page'); // Id from Bible page
	delete_option('bovp_source_random_verse'); // source of daily verse
	delete_option('bovp_daily_verse'); // Daily verse
	delete_option('bovp_system_version'); // Bible version
	delete_option('bovp_theme'); // theme
	delete_option("bovp_bible_version"); 
	delete_option("bovp_itens_per_page");
	delete_option("bovp_array_books"); 
	delete_option("bovp_bible_books_count"); 
	delete_option("bovp_table"); 
	delete_option('bovp_version');
	delete_option('bovp_registred_versions');

}

function import_sql_data($file,$version){

	global $wpdb;

	$wpdb->query("DROP TABLE `" . BOVP_TABLE . "`");


	$bible_file = $file;
	$bible_version = $version;

	if($bible_file) {
	

		$bovp_table = $wpdb->prefix . $bible_file; 

		$create_bovp_table = "CREATE TABLE `".$bovp_table."` (
		`id` int(11) NOT NULL auto_increment,
		`book` int(11) NOT NULL,
		`cp` int(11) NOT NULL,
		`vs` int(11) NOT NULL,
		`text` mediumtext NOT NULL,
		`dv` enum('n','s') NOT NULL,
		`short_url` mediumtext NOT NULL,
		PRIMARY KEY  (`id`))"; 
	
		$wpdb->query($create_bovp_table);
	
		$inserir = dirname(__FILE__) . '/' . 'data/'.$bible_file. '.sql';
		
		$fp = fopen($inserir, 'r');
		
		if($fp){
		
			$bovp_data = fread($fp, filesize($inserir));
			
			fclose($fp);
			
			$bovp_data = explode("|",$bovp_data);
			
				foreach($bovp_data as $query) {
					
					$temp = trim($query);
					
					$insert = 'INSERT INTO `' . $bovp_table . '` VALUES ' . $temp;
					
					$inserted = $wpdb->query($insert);
					
				}
				
		}


		// Verify if was created 

		$Verify_bovp_table = $wpdb->get_results("SELECT * FROM $bovp_table LIMIT 0,1" );

		if($Verify_bovp_table) { 

		update_option("bovp_table", $bovp_table);
		update_option("bovp_version", $bible_version); 
		bovp_book_array();

		}


	}


					
}

function bovp_menu_mount(){
	
	add_menu_page( __('Online Bible','bovp'), __('Online Bible','bovp'), 'manage_options', BOVP_FOLDER_NAME . 'init.php','', BOVP_ICON );

	add_submenu_page( BOVP_FOLDER_NAME . 'init.php', __('Settings','bovp'), 'Settings' , 'manage_options',  BOVP_FOLDER_NAME . 'settings.php' );
	add_submenu_page( BOVP_FOLDER_NAME . 'init.php', __('Donation','bovp'), 'Donation' , 'manage_options',  BOVP_FOLDER_NAME . 'donation.php' );	
	add_action( 'admin_init', 'bovp_options_set');
}


function bovp_options_set() {
	
	register_setting( 'bovp_options', 'bovp_page');
	register_setting( 'bovp_options', 'bovp_source_random_verse');
	register_setting( 'bovp_options', 'bovp_itens_per_page');
	register_setting( 'bovp_options', 'bovp_theme');

}

function bovp_admin_css() {

echo '<style type="text/css">';
echo 'div.bovp_wrap {font-size:14px;line-height: 20px;}';
echo 'div.bovp_wrap h2.bovp_h2 {background: #09AF10;border: 1px solid #0C6F03;margin: 5px;padding: 10px;font-size: 1.5em;color: #FFF;max-width: 800px !important;margin-bottom:30px;}';
echo 'div.bovp_wrap fieldset.bovp_fieldset {border:1px solid #0C6F03;margin:5px;padding:10px;max-width: 800px !important;margin-bottom:30px;}';
echo 'div.bovp_wrap legend.bovp_legend {padding: 5px;margin: 2px;border: solid #0C6F03 1px;font-size: 1.2em;background-color: #09AF10;color: #fff;}';
echo 'div.bovp_wrap div.message.bovp_message {border:1px solid #36C;background-color:#0080C0;color:#fff;padding:15px;}';
echo '</style>';

}
 

function bovp_insert_vars($qvars ){ // Insert vars to plugin links - use in the filter function 
	
	array_push($qvars , 'bk', 'cp', 'sh', 'vs', 'ex','cpg');
	
    return $qvars ;
}

function bovp_active_translate(){ // activate translate pluging function
	
  load_plugin_textdomain( 'bovp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
  
}

// Includes in the WP_HEAD

function bovo_include_dependences() {  

	$themes[0] = "default";

	$theme = $themes[get_option('bovp_theme')];
		
	$style_sheet = BOVP_FOLDER ."themes/".$theme."/".$theme.".css";

	wp_enqueue_style( "bible_style", $style_sheet);

	wp_enqueue_style( "adm", BOVP_FOLDER ."includes/adm.css");

	wp_enqueue_script("jquery");

	wp_enqueue_script( "bible_js", BOVP_FOLDER ."includes/bovp.js");

}

// Generate book's array and version bible variable

function bovp_book_array(){
	
	global $wpdb;
	$query = "SELECT * FROM `" . BOVP_TABLE . "` WHERE `book` = 0 "; //AND `cp` <> 0
	$bovp_array_books = array();
	$books = $wpdb->get_results ($query , ARRAY_A);
	foreach($books as $book) {
		$data = array('bk' => $book['cp'],'name' => $book['text'],'pages' => $book['vs'],'abrev' => $book['short_url']);
		array_push($bovp_array_books, $data); 
	}
	update_option("bovp_array_books", $bovp_array_books);
}

// Get all published page 

function bovp_get_all_pages() {
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

function bovp_options_select($name, $list){

		$option_value = get_option($name); 
	 
	   	echo "<tr valign=\"top\" id=\"" . $name ."_row\">";
		echo "<th scope=\"row\">" . _e($title,'bovp') . "</th>";
		echo "<td><select name=\"" . $name . "\">";

			foreach($list as $key => $value) {
				"$key" == $option_value ? $selected = "selected='selected' " : $selected = '';       
	          	echo "<option value='$key' $selected>$value</option>";
	        }

	    echo "</select> <br/>";
	    echo $description;
	    echo "</td></tr>";
}


function bovp_show_verse($return_type = 'show'){

$bovp_daily_verse = get_option('bovp_daily_verse');

if (is_array($bovp_daily_verse)){$date_verse = $bovp_daily_verse['date'];} else {$date_verse = '1/1/1971';}


	if((date( __('m/d/Y' , 'bovp') ,time()) == date( __('m/d/Y' , 'bovp') , $date_verse))){  // is a valid verse
	
			$id = $bovp_daily_verse['id'];
			$date = $bovp_daily_verse['date'];
			$bk = $bovp_daily_verse['book']; //book
			$cp = $bovp_daily_verse['cp']; //cp
			$vs = $bovp_daily_verse['vs']; //vs
			$nbook = $bovp_daily_verse['book_name']; //book
			$text = $bovp_daily_verse['text']; //text
			$link = "&bk=$bk&cp=$cp&vs=$vs";
		
		$return = $text . " <span class='verse_ref'><a href='" . BOVP_URL . $link . "'>($nbook:$cp:$vs )</a></span>";
			
} else {
		
	global $wpdb;

	$bovp_setting = get_option('bovp_array_settins');
		
	$params = $bovp_setting['bovp_source_random_verse'];
	
	switch($params) {
		case 0:$params=""; break;
		case 1:$params="WHERE `book` =< 39"; break;
		case 2:$params="WHERE `book` >= 40"; break;
		case 3:$params="WHERE `book` = 19"; break;
	}
	
	$sql = "SELECT * FROM ".BOVP_TABLE." $params ORDER BY rand() LIMIT 1";

	$daily_verse = $wpdb->get_row($sql);
			
			if($daily_verse){
				
			$bovp_daily_verse = array();
				
				$bovp_daily_verse['id'] = $daily_verse->id;
				$bovp_daily_verse['date'] = time();
				$bovp_daily_verse['book'] = $daily_verse->book;
				$bovp_daily_verse['cp'] = $daily_verse->cp;
				$bovp_daily_verse['vs'] = $daily_verse->vs;
				$bovp_daily_verse['book_name'] = book_info($daily_verse->book,'name');
				
				$link = "&bk=" . $bovp_daily_verse['book'] . '&cp=' . $bovp_daily_verse['cp'] . '&vs=' . $bovp_daily_verse['vs'];
				
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
				" <spam style='text-align:right;'><a href='" . 
				BOVP_URL . $link . "'>$bovp_ref_verse</a></spam>";
				
			}	
		
	}
	
if ($return_type == 'show'){ echo $return;} 
elseif ($return_type == 'var'){ return $return;} 
elseif ($return_type == 'array'){ return $bovp_daily_verse;}
	
}



// Swich book information function

function book_info($book,$item) {
	
	$bovp_array_books =  get_option("bovp_array_books");
	
	if( empty($bovp_array_books) || !isset($bovp_array_books)){
		bovp_book_array();
		$bovp_array_books =  get_option("bovp_array_books"); 
	}	

	return 	$bovp_array_books[$book][$item];
}
	
function book_select($mode = "echo"){
	
	$bovp_array_books =  get_option("bovp_array_books");
	
	$list_books_combo = '<option value="0" >'. __('All the Bible','bovp') .'</option>';
	//$list_books_combo .= '<optgroup label="' . __('Old Testament','bovp') . '">';
	
	foreach($bovp_array_books as $list_array) {

			
		$book_id = $list_array['bk'] ;
		$book_name = $list_array['name'];
		$num_pages = $list_array['pages'];

		if($book_id !=0){$list_books_combo .=  '<option num_pages="'.$num_pages.'" value="'.$book_id .'" >'.$book_name.'</option>';	}

		//if($book_id ==39){$list_books_combo .=  '<\optgroup><optgroup label="' . __('New Testament','bovp') . '">';	}
		
	}

	$list_books_combo .=  '<\optgroup>';
	
	if ($mode == "echo") {echo $list_books_combo;} else { return $list_books_combo;}
	
}

function bible_form($bovp_return_form = 'echo'){ 

$bovp_form = "
       
<form method=\"post\" action=\"" . BOVP_BIBLE_URL . "\" name=\"bovp_form_search\">

<input name=\"page_id\" type=\"hidden\" value=\"" . BOVP_PAGE . "\"/>

<input type=\"text\" id=\"sh\" name=\"sh\" placeholder=\"" . __('Search','bovp') . "\" >

<select name=\"bk\" id=\"bovp_book\">" . book_select("var") . "</select>

<select type=\"text\" name=\"cp\" id=\"bovp_chapter\"></select>

<button type=\"submit\">" . __('Send','bovp') . "</button>

</form>
	   
";


if($bovp_return_form == 'echo') {echo $bovp_form;} elseif($bovp_return_form == 'var'){return $bovp_form;}

}

function bovp_pagination($inf){


	$prmt = BOVP_URL;

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
					$pagination .= "<div class=\"bovp_pagination\"><a href=\"". $prmt . $prev."\" class=\"prev\">" . __('Previous','bovp') . "</a>";
			else
					$pagination .= "<div class=\"bovp_pagination\"><span class=\"disabled\">" . __('Previous','bovp') . "</span>";
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
						$pagination .= "<a href=\"". $prmt . $next."\" class=\"next\">" . __('Next','bovp') . "</a></div>";
					else
						$pagination .= "<span class=\"disabled\">" . __('Next','bovp') . "</span></div>";
					
			}
	}

					

return $pagination;

}


// CLASSES

// WIDGET EXTEND

class bovp_widget_search extends WP_Widget
{
    function bovp_widget_search(){
		$widget_ops = array('description' =>  __('Use this widget to display the bible form search','bovp'));
		parent::WP_Widget(false,$name='BOVP Form Search',$widget_ops);
    }

  /* Displays the Widget in the front-end */
    function widget($args, $instance){
		
		extract($args);
		
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Bible Search','bovp') : $instance['title']);

		echo $before_widget;

		if ( $title )
		echo $before_title . $title . $after_title;
	
		echo "<form method=\"post\" id=\"bovp_form_search\" action=\"" . BOVP_BIBLE_URL . "\" name=\"bovp_form_search\">";
		echo "<input name=\"page_id\" type=\"hidden\" value=\"" . BOVP_PAGE . "\"/>";
		echo "<input type=\"text\" id=\"sh\" name=\"sh\" placeholder=\"" . __('search','bovp') . "\" >";
		echo "<select name=\"bk\" id=\"bovp_book\">" . book_select("var") . "</select>";
		echo "<select type=\"text\" name=\"cp\" id=\"bovp_chapter\"></select>";
		echo "<input type=\"submit\" id=\"bible-submit\" value=\"\"></form>";
	
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


function bovp_widgets_init() {

  register_widget('bovp_widget_verse');
  register_widget('bovp_widget_search');

}


// For test of array books function only

function bovp_list_array() { 
		
	$array_books = get_option("bovp_array_books");
	
	print_r($array_books);
	
}
?>
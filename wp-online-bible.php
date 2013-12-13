<?php 
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {die(__('Access denied.','bovp')); }

/*
Plugin Name: Online Bible VP for Wordpress 
Plugin URI: http://www.vivendoapalavra.org/
Description: Plugin for implementation of Online Bible in your Wordpress blog. 
With it, you can make available the Word of God and bless your website's users. 
The plugin allows to consult all 66 books of the Holy Bible.
Author: Andre Brum Sampaio
Version: 1.5
Author URI: http://www.vivendoapalavra.org//
*/

// Include function file.
require_once plugin_dir_path(__FILE__) . '/functions.php'; // Plugin functions.

global $wpdb;

// Constants

if(!defined('WP_CONTENT_URL')){define('WP_CONTENT_URL',get_option('siteurl').'/wp-content');}
define('BOVP_PATH', plugin_dir_path(__FILE__) );
define('BOVP_FOLDER', plugin_dir_url(__FILE__) );
define('BOVP_FOLDER_NAME', array_pop(explode('\\', BOVP_PATH )));
define('BOVP_ICON', BOVP_FOLDER . 'img/icone_bovp.png');
define('BOVP_URL',get_option('home').'/index.php?page_id='.get_option('bovp_page'));
define('BOVP_BIBLE_URL',get_option('home').'/');
define('BOVP_BD_STATE', get_option('bovp_bd_state'));
define('HOME', get_option('home').'/');
define('BOVP_PAGE', get_option('bovp_page'));
define('BOVP_VERSE_SOURCE', get_option('bovp_source_random_verse'));
define('BOVP_BIBLE_VERSION', get_option('bovp_version'));
define('BOVP_SYSTEM_VERSION', get_option('bovp_system_version'));
define('BOVP_ITENS_PER_PAGE', get_option('bovp_itens_per_page'));
define('BOVP_TABLE', get_option('bovp_table'));
define('BOVP_BIBLE_BOOKS_COUNT',get_option('bovp_bible_books_count'));

//activation
register_activation_hook(__FILE__,'bovp_install');
register_deactivation_hook(__FILE__,'bovp_uninstall');

// filters
add_filter('query_vars','bovp_insert_vars'); //include query vars for bovp.
add_filter('the_content','bovp_show_bible'); //show the bible in the select page.

//add_filter("the_content","bovp_link_generator"); // Generate link in the content to bible page.

// hooks
add_action('admin_init','bovp_update');
add_action('admin_menu', 'bovp_menu_mount'); // Generate plugin menu admin.
add_action('admin_head', 'bovp_admin_css'); // css to the admin page.
add_action('init', 'bovp_active_translate'); // Activate plugin translation.
add_action('wp_enqueue_scripts', 'bovo_include_dependences');
add_action('widgets_init', 'bovp_widgets_init'); // Active the search widget.

function bovp_show_bible($content) {

if (is_page(BOVP_PAGE)) {  

	$bovp_array_books =  get_option("bovp_array_books");

	global $post; 
	global $wpdb; 
	global $wp_query;

	$vs="";$is_search=false;$bovp_content="";$bovp_color="";$bovp_search="";$cpg="";$bk="";$cp="";
	$vars = $wp_query->query_vars;
	$sql = "SELECT * FROM `" . BOVP_TABLE . "` WHERE ";
			

		if (isset($vars['bk']) AND ($vars['bk']) !=0 ) { 

				$bk = $vars['bk'];
				$book_name = book_info($bk,'name');
				$lastpage = book_info($bk,'pages');
				$sql .= "`book`=".$bk;				

				if (isset($vars['cp']) && $vars['cp'] !=0) {$cp = $vars['cp']; $sql .= " AND `cp`= ". $cp;} else { 

					if ($vars['cp'] == 0) {$cp =1; $sql .= " AND `cp`= ". $cp; }

				}

				if (isset($vars['vs'])) { $vs = $vars['vs'];} else { $vs = false;}			

		} else {

				if(empty($vars['sh'])) { 

					$sql = "SELECT * FROM `" . BOVP_TABLE . "` WHERE `book`=1 AND `cp`=1"; 
					$bk = 1;
					$cp = 1;
					$cpg = 1;
					$book_name = book_info($bk,'name');
					$lastpage = book_info($bk,'pages');
				}
		}		

			if(!empty($vars['sh'])){

				$is_search=true;
				$bovp_search = mysql_real_escape_string($vars['sh']);

				if(isset($vars['ex'])){$bovp_search = str_replace(" ", "%", $bovp_search);};

				$sql .= ($vars['bk'] > 0) ? " AND ( LCASE(text) RLIKE '[[:<:]]" . $bovp_search ."[[:>:]]' )" : " ( LCASE(text) RLIKE '[[:<:]]" . $bovp_search ."[[:>:]]' )";

				if (isset($vars['cpg'])) { $cpg = $vars['cpg'];} else { $cpg = 1;}

				$bovp_start = ($cpg - 1) * BOVP_ITENS_PER_PAGE; 
				$counter = $wpdb->get_results($sql);
				$bovp_count = $wpdb->num_rows;
				$lastpage = ceil($bovp_count/BOVP_ITENS_PER_PAGE);
				$sql .= '  LIMIT ' . $bovp_start.", ".BOVP_ITENS_PER_PAGE;			

			}

				$inf = array('bk' => $bk,
					'cp' => $cp,
					'vs' => $vs,
					'sh' => $bovp_search,
					'cpg' => $cpg,
					'lastpage' => $lastpage,
					'sql' => $sql);

		$bovp_results = $wpdb->get_results($sql);
		$bovp_content .= "<div class='bovp_container'>";
		$bovp_content .= bible_form('var', 'forms-bible-container'); 
		$bovp_content .= ($is_search) ? "<h3>". __('Find itens','bovp') . "<span class=\"bovp_cap\">" . $bovp_count . "</span></h3><ul>" : "<h3>" . $book_name . "<span class=\"bovp_cap\">" . $cp . "</span></h3><ul>";
			
		if (isset($bovp_search) and ($bovp_search != "")) { 							

				$bovp_color == '';						

				foreach ($bovp_results as $bovp_results_rows){					

					$livro = $bovp_results_rows->book; 
					$capitulo = $bovp_results_rows->cp; 
					$verso = $bovp_results_rows->vs; 
					$texto = $bovp_results_rows->text;				
					$bovp_item_id = $bovp_results_rows->id;

					if($bovp_item_id < 67) {						

						if($bovp_color == ''){$bovp_color = 'bovp_color';} elseif($bovp_color == 'bovp_color'){$bovp_color = '';}

						$bovp_content .= "<li class='$bovp_color'><b><a href=\"?page_id=" . BOVP_PAGE . 

						"&bk=" . $capitulo . "&cp=1\">" . $texto .  __(' (Book)','bovp') .

						"</a></b>" ."</li>";														

					} else {
			
						if($bovp_color == ''){$bovp_color = 'bovp_color';} elseif($bovp_color == 'bovp_color'){$bovp_color = '';}

						$paraDestacar = '/'.$bovp_search.'/i';
						$paraDestacar = str_replace('%',' ', $paraDestacar);
						preg_match_all($paraDestacar, $texto, $vsr );
						$array_size = count($vsr[0]);
						
						foreach($vsr[0] as $destaque){

						$texto = str_replace($destaque,'<font color="red">'.$destaque.'</font>',$texto);

						}						

						$book_name = book_info($livro, 'name'); 
						$qtdCapitulos = book_info($livro, 'pages');									

						$bovp_content .= "<li class='$bovp_color'><b><a href=\"?page_id=" . BOVP_PAGE . 

						"&bk=" . $livro . "&cp=" .  $capitulo . "&vs=" . $verso . "\">" . $book_name . 

						":" . $capitulo . ":" . $verso . "</a></b>" . $texto . "</li>";			

				} 

				}

		} else {

				$bovp_color == '';

				foreach ($bovp_results as $item){

					if($bovp_color == ''){$bovp_color = 'bovp_color';} elseif($bovp_color == 'bovp_color'){$bovp_color = '';}

					$verso = $item->vs; 
					$texto = $item->text;

					$bovp_content .= "<li class='$bovp_color'><span class=\"verse_num\">$verso</span>";			

						if($verso == $vs) {

							$bovp_content .= "<font color=\"red\">$texto</font></p>";

						} else {

							$bovp_content .= "$texto</li>";

						}
				}
		}

				$bovp_content .= "</ul>";

				$bovp_content .=  bovp_pagination($inf);

				if($is_search) {

					$bovp_of = min($bovp_count, ($bovp_start + 1));
					$bovp_to = min($bovp_count, ($bovp_start + BOVP_ITENS_PER_PAGE));
					$bovp_content .= "<p class=\"resumo\" align=\"center\">" . __('Finded','bovp') .'&nbsp;<b>' . $bovp_count . '</b>&nbsp;' .__('verses for your search.','bovp') .'<br>';
					$bovp_content .=  __('Show results','bovp') . '&nbsp;<b>' . $bovp_of . '</b>&nbsp;' . __('to','bovp') . '&nbsp;<b>' . $bovp_to . '</b></p><br>';

				} 

				$bovp_registred_versions = get_option('bovp_registred_versions');
				$bovp_version_selected = $bovp_registred_versions[BOVP_BIBLE_VERSION];
				$bovp_version_info_explode = explode('|' , $bovp_version_selected);
				$bible_translate = $bovp_version_info_explode[0];

				$bovp_content .= "<div class='bovp_translate'>" . __('Version: ', 'bovp') . $bible_translate . "</div>";	
				$bovp_content .= "</div>"; // SAÍDA DA BUSCA
				$bovp_content .= "<a href=\"http://www.vivendoapalavra.com.br/\"><img src=\"" . BOVP_FOLDER . "img/logovp.png\" border=\"0\"></a>";

		return $bovp_content;	
		

	} else {		

	return $content;	

	}

 }

?>
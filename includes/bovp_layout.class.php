<?php

class classBibleLayout {

/*

Script Name: *Bovp Bible Layout Class
Script URI: http://www.vivendoapalavra.org/
Description: PHP class that allows you to customize the layout of the online Bible for wordpress.
Script Version: 0.1
Author: Andre Brum Sampaio
Author URI: http://www.vivendoapalavra.org
*/


	#Default values
	var $url = 'localhost/wordpress';
	var $content_header = false;
	var $content_footer = false;
	var $pagination = false;
	var $version = false;

	#construct
	function classBibleLayout(){}
	
	#show content
	function showResults($content, $echo = false) {

		global $bovp_vars;

		$font_size = get_option('bovp_font_size');

		if($content) {

		$link_back = (isset($bovp_vars['link_back']) AND !empty($bovp_vars['link_back'])) ? $bovp_vars['link_back'] : false;

			if(!$link_back) {

			$return = "<form class='return_url' action='' method='post'>";
			$return .= "<input name='link_back' type='hidden' value='".bovpWriteUrl()."'></form>";

			} else {$return = "";}

			$return .= "<div class='bovp_text bovp_clear' style='font-size:" . $font_size . "px;'>";
			$return .= "<ul class='bovp_bible_content'>\n";

			if($link_back) {
					
			$return .= "<li class='bovp_back_results'>";
			$return .= "<a href='$link_back'>" . __('Back to Results', 'bovp') . "</a>";
			$return .= "</li>";

			} 

			if($content) {$return .= $content;}

			$return .= "</ul>\n";
			$return .= "</div>\n";

			if($echo == false){return $return;} else {echo $return;}

		} else {return false;}	

	}

	#show title
	function showTitle($title, $echo = false) {

		if($title) {

			$return = '<div class="bovp_title">' .$title . '</div>';

			if($echo == false){return $return;} else {echo $return;}

		} else {return false;}	

	}

	#font_size
	function showFontSize($echo = false) {

			$return  = '<div class="bovp_fsize">';
			$return .= "<span><a href='javascript:void(0)' class='decrease' style='font-size:10px;'>a</a></span>";
			$return .= "<span><a href='javascript:void(0)' class='default' style='font-size:".BOVP_FONT_SIZE."px;'>a</a></span>";
			$return .= "<span><a href='javascript:void(0)' class='increase' style='font-size:22px;'>a</a></span>";
			$return .= "</div>";

			if($echo == false){return $return;} else {echo $return;}		

	}

	#share
	function showShareButtons($share, $echo = false) {

		if($share) {

			$return = '<div class="bovp_share">' . $share . "</div>";

			if($echo == false){return $return;} else {echo $return;}

		} else {return false;}					

	}	



	#pagination
	function showPagination($echo = false) {

		global $bovp_vars;
		global $r_count;

		$cpg = $bovp_vars['cpg'];
		$pagination = '';
		$lastpage = ($bovp_vars['sh']!== 0) ? ceil($r_count/BOVP_ITENS_PER_PAGE) : bovpBookInfo($bovp_vars['bk'],'pages') ;

		$prmt = BOVP_URL;


		if(BOVP_FURL_STATS) {

			#Friendly URL activated.
			if($bovp_vars['bk']!== 0) { $book_name = bovpBookInfo($bovp_vars['bk'],'slug_name'); }	
			
			if($bovp_vars['sh']== 0 AND $bovp_vars['bk']!== 0) {

				$prmt .= $book_name . "/%s/";	

			} elseif($bovp_vars['sh']!== 0) {

				$prmt .= BOVP_SLUG_SEARCH . "/";
				$prmt .= "%s/";
				$prmt .= urldecode($bovp_vars['sh']);

			} 

			if ($bovp_vars['sh']!== 0) { $cpg = $bovp_vars['cpg'];  } else {$cpg = $bovp_vars['cp']; }


		} else {

			#Friendly URL don't activated.
			if ($bovp_vars['bk']!== 0) $prmt .= '&bk=' . $bovp_vars['bk'];
			if ($bovp_vars['sh']!== 0) $prmt .= '&sh=' . $bovp_vars['sh'];
			if ($bovp_vars['sh']!== 0) { $cpg = $bovp_vars['cpg']; $prmt .= '&cpg=%s'; } else {$cpg = $bovp_vars['cp']; $prmt .= '&cp=%s';}
		}


			$adjacents = 2;
			$prev = $cpg - 1; 
			$next = $cpg + 1;
			$lpm1 = $lastpage - 1;


		if($lastpage){

			if($cpg){

				// PREV BUTTOM

				if($cpg > 1)

						$pagination .= "<a href='". sprintf($prmt,$prev) ."' class='prev'>" . __('Previous','bovp') . "</a>";

				else

						$pagination .= "<span class='disabled'>" . __('Previous','bovp') . "</span>";

			}

			//PAGES

			if ($lastpage < 7 + ($adjacents * 2)){//not enough pages to bother breaking it up

					for ($counter = 1; $counter <= $lastpage; $counter++){

							if ($counter == $cpg)

									$pagination .= "<span class='current'>$counter</span>";

								else

									$pagination .= "<a href='". sprintf($prmt,$counter) ."'>$counter</a>";

							}

					}


			elseif($lastpage > 5 + ($adjacents * 2)){//enough pages to hide some

					//close to beginning; only hide later pages

					if($cpg < 1 + ($adjacents * 2)){

							for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++){

									if ($counter == $cpg)

										$pagination .= "<span class='current'>$counter</span>";

									else

										$pagination .= "<a href='". sprintf($prmt,$counter) ."'>$counter</a>";

							}

							$pagination .= "...";

							$pagination .= "<a href='". sprintf($prmt,$lpm1) . "'>$lpm1</a>";

							$pagination .= "<a href='". sprintf($prmt,$lastpage) . "'>$lastpage</a>";

						}

					//in middle; hide some front and some back

					elseif($lastpage - ($adjacents * 2) > $cpg && $cpg > ($adjacents * 2)){

							$pagination .= "<a href='". sprintf($prmt,'1') . "'>1</a>";

							$pagination .= "<a href='". sprintf($prmt,'2') . "'>2</a>";

							$pagination .= "...";

							for ($counter = $cpg - $adjacents; $counter <= $cpg + $adjacents; $counter++)

								if ($counter == $cpg)

										$pagination .= "<span class='current'>$counter</span>";

									else

										$pagination .= "<a href='". sprintf($prmt,$counter)."'>$counter</a>";

							$pagination .= "...";

							$pagination .= "<a href='". sprintf($prmt,$lpm1) ."'>$lpm1</a>";

							$pagination .= "<a href='". sprintf($prmt,$lastpage) ."'>$lastpage</a>";

						}

					//close to end; only hide early pages

					else {
							$pagination .= "<a href='". sprintf($prmt,'1') . "'>1</a>";

							$pagination .= "<a href='". sprintf($prmt,'2') . "'>2</a>";

							$pagination .= "...";

							for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)

								if ($counter == $cpg)

										$pagination .= "<span class='current'>$counter</span>";

									else

										$pagination .= "<a href='". sprintf($prmt,$counter) ."'>$counter</a>";

						}

				}

				

			if($cpg){

					//NEXT BUTTON

					if ($cpg < $counter - 1)

							$pagination .= "<a href='". sprintf($prmt,$next) ."' class='next'>" . __('Next','bovp') . "</a>";

						else

							$pagination .= "<span class='disabled'>" . __('Next','bovp') . "</span>";

		}

	}


	if($echo == false){return $pagination;} else {echo $pagination;}


	}

	#search
	function showFormSearch($echo = false, $icone = false) {

		$return  = "<div class='bovp_search_container'>";
		$return .= "<form method='post' action='" . BOVP_URL  . "' name='bovp_form_search' class='bovp_form_search'>";
		$return .= "<input name='page_id' type='hidden' value='" . BOVP_PAGE . "'/>";
		$return .= "<input class='bovp_search_input' type='text' id='sh' name='sh' placeholder='". __('Search','bovp')."' >";
		$return .= "<input name='bk' type='hidden' value='0'/>";
		$return .= "<input name='cp' type='hidden' value='0'/>";
		$return .= "<input name='vs' type='hidden' value='0'/>";
		$return .= "<input name='s_type' type='hidden' value='s'/>";
		$return .= "<button class='bovp_search bovp_button bovp_btn";
		if(BOVP_FURL_STATS) $return .= " sh_friendly";
		$return .= "' type='submit'>";
		$return .= ($icone==true) ? sprintf('<img src="%sthemes/%s/img/search_ico.png" border="0">', BOVP_FOLDER, BOVP_THEME) : __('Send','bovp');
		$return .= "</button>";
		$return .= "</form>";
		$return .= "</div>";

		if($echo == false){return $return;} else {echo $return;}

	}

	#version
	function showLogo($echo = false) {

		$return = "<div class='bovp_logo'>\n";

		$return .= sprintf('<a href="http://www.vivendoapalavra.org/"><img src="%sthemes/%s/img/logo.png" border="0"></a>', BOVP_FOLDER, BOVP_THEME);

		$return .= "</div>";

		if($echo == false){return $return;} else {echo $return;}

	}

	#active version
	function showVersion($echo = false) {

		$return = "<div class='bovp_version bovp_clear'>\n";

		$return .= __('Version: ', 'bovp') . BOVP_VERSION_NAME;

		$return .= "</div>";

		if($echo == false){return $return;} else {echo $return;}

	}


	

}

?>
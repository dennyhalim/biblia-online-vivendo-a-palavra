<?php
/*
Plugin Name: B&iacute;blia Online 
Plugin URI: http://www.vivendoapalavra.com.br/
Description: Plugin for implementation of Bible Online in your Wordpress blog. With it, you can make available the Word of God and bless your website\'s users. The plugin allows to consult all 66 books of the Holy Bible.
Author: Andre Brum Sampaio
Version: 1.4
Author URI: http://www.vivendoapalavra.com.br/
*/



// Includes

require_once dirname(__FILE__) . '/includes/pagination.class.php';
require_once dirname(__FILE__) . '/functions.php';

//activation

register_activation_hook(__FILE__,'bovp_instalation');
register_sidebar_widget( __('Daily Verse','bovp'),'bovp_widget_daily_verse');
register_sidebar_widget( __('Online Bible','bovp'),'bovp_widget_search');
register_deactivation_hook(__FILE__,'bovp_remove');


// filters

global $wpdb;
add_filter("the_content","exibeBiblia");
add_filter("the_content","criaLinks");

// hooks
add_action("admin_menu", "bovp_menu_mount");
add_action('init', 'bovp_active_translate');
add_action("wp_head", "include_css_js");

// widgets

function bovp_widget_search($args) {
		if(is_page(BOVP_PAGE)){return false;}
		echo $args['before_widget'];
		echo $args['before_title'] . __('Online Bible','bovp') . $args['after_title'];
		bible_form('echo','widget-bible-container');
		echo $args['after_widget'];
}

function bovp_widget_daily_verse($args) {
		
		echo $args['before_widget'];
		echo $args['before_title'] . __('Daily Verse','bovp') . $args['after_title'];
		bovp_show_verse();
		echo $args['after_widget'];
}

// tranlate function

function bovp_active_translate(){
	
  load_plugin_textdomain( 'bovp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
  
}

// Bible menu page

function bovp_menu_mount(){
	
	add_menu_page( __('Settings','bovp'), __('Online Bible','bovp'), 10, BOVP_FOLDER_NAME . 'init.php','', BOVP_ICON );
	
	add_submenu_page( BOVP_FOLDER_NAME . 'init.php', 'Settings' , __('Settings','bovp'), 10, BOVP_FOLDER_NAME . 'settings.php' );
	
	add_submenu_page( BOVP_FOLDER_NAME . 'init.php', 'Donation' , __('Donation','bovp'), 10, BOVP_FOLDER_NAME . 'donation.php' );
	
	add_action( 'admin_init', 'bovp_options_set');
}


function bovp_options_set() {
	
	register_setting( 'bovp_options', 'bovp_page' );
	register_setting( 'bovp_options', 'bovp_source_random_verse' );
}


// head includes

function include_css_js() {
	echo '<!-- Online Bible -->';
	echo '<link rel="stylesheet" href="'. BOVP_FOLDER .'includes/bovp.css" type="text/css" media="screen" />';
	echo '<script type=\'text/javascript\' src=\''. BOVP_FOLDER .'includes/bovp.js\'></script>';
	echo '<!-- End heads includes of Online Bible -->';
	
}



// instalation function

function bovp_instalation(){ 

	add_option("bovp_bd_state", '0', '', 'yes'); // BD State - if 31106, the BD is work
	add_option("bovp_page", '1717', '', 'yes'); // Bible page_id
	add_option("bovp_source_random_verse", '0', '', 'yes'); // Daily Verse source (OT-NT-PSALMS-LIST)
	add_option("bovp_daily_verse", '0', '', 'yes'); // Daily Verse in array()
	add_option("bovp_version", '1.4', '', 'yes'); // Bible vesion
	
	import_sql_data('text.sql');

}	

function import_sql_data($arquivo){
	
	global $wpdb;
	
$create_bovp_table = "CREATE TABLE `bovp_arc` (`id` int(11) NOT NULL auto_increment,`book` int(11) NOT NULL,`cp` int(11) NOT NULL,`vs` int(11) NOT NULL,`text` mediumtext NOT NULL,`dv` enum('n','s') NOT NULL,`short_url` mediumtext NOT NULL,PRIMARY KEY  (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=31174"; 
	
	
	$wpdb->query($create_bovp_table);
	
		$inserir = dirname(__FILE__) . '/' . 'data/'.$arquivo;
		
		$fp = fopen($inserir, 'r');
		
		if($fp){
		
			$dados = fread($fp, filesize($inserir));
			
			fclose($fp);
			
			$dados = explode("|",$dados);
			
			$bovp_rec_counter = 0;
			
				foreach($dados as $query) {
					
					$bovp_rec_counter ++;
					
					$temp = trim($query);
					
					$insert = 'INSERT INTO `bovp_arc` VALUES ' . $temp;
					
					$inserted = $wpdb->query($insert);
					
				}
				
				update_option("bovp_bd_state", $bovp_rec_counter);
		
		}
		
}

// uninstall

function bovp_remove(){
	
	global $wpdb;
	$exclude_bible = "DROP TABLE `bovp_arc`";
	$wpdb->query($exclude_bible);
	
	delete_option('bovp_daily_verse'); // Daily verse
	delete_option('bovp_bd_state'); // Database state
	delete_option('bovp_version'); // Bible version
	delete_option('bovp_page'); // Id from Bible page
	delete_option('bovp_source_random_verse'); // source of daily verse
}


// Old functions - will be replaced in next version


function livro_Capitulos($nlv) {
	switch($nlv) {
		case 0:$nlv="B&iacute;blia Online-0"; break;
		case 1:$nlv="G&ecirc;nesis-50"; break;
		case 2:$nlv="&Ecirc;xodo-40"; break;
		case 3:$nlv="Lev&iacute;tico-27"; break;
		case 4:$nlv="N&uacute;meros-36"; break;
		case 5:$nlv="Deuteron&ocirc;mio-34"; break;
		case 6:$nlv="Josu&eacute;-24"; break;
		case 7:$nlv="Ju&iacute;zes-21"; break;
		case 8:$nlv="Rute-4"; break;
		case 9:$nlv="1 Samuel-31"; break;
		case 10:$nlv="2 Samuel-24"; break;
		case 11:$nlv="1 Reis-22"; break;
		case 12:$nlv="2 Reis-25"; break;
		case 13:$nlv="1 Cr&ocirc;nicas-29"; break;
		case 14:$nlv="2 Cr&ocirc;nicas-36"; break;
		case 15:$nlv="Esdras-10"; break;
		case 16:$nlv="Neemias-13"; break;
		case 17:$nlv="Ester-10"; break;
		case 18:$nlv="J&oacute;-42"; break;
		case 19:$nlv="Salmos-150"; break;
		case 20:$nlv="Prov&eacute;rbios-31"; break;
		case 21:$nlv="Eclesiastes-12"; break;
		case 22:$nlv="Cantares de Salom&atilde;o-8"; break;
		case 23:$nlv="Isa&iacute;as-66"; break;
		case 24:$nlv="Jeremias-52"; break;
		case 25:$nlv="Lamenta&ccedil;&otilde;es de Jeremias-5"; break;
		case 26:$nlv="Ezequiel-48"; break;
		case 27:$nlv="Daniel-12"; break;
		case 28:$nlv="Os&eacute;ias-14"; break;
		case 29:$nlv="Joel-3"; break;
		case 30:$nlv="Am&oacute;s-9"; break;
		case 31:$nlv="Obadias-1"; break;
		case 32:$nlv="Jonas-4"; break;
		case 33:$nlv="Miqu&eacute;ias-7"; break;
		case 34:$nlv="Naum-3"; break;
		case 35:$nlv="Habacuque-3"; break;
		case 36:$nlv="Sofonias-3"; break;
		case 37:$nlv="Ageu-2"; break;
		case 38:$nlv="Zacarias-"; break;
		case 39:$nlv="Malaquias-3"; break;
		case 40:$nlv="Mateus-28"; break;
		case 41:$nlv="Marcos-16"; break;
		case 42:$nlv="Lucas-24"; break;
		case 43:$nlv="Jo&atilde;o-21"; break;
		case 44:$nlv="Atos-28"; break;
		case 45:$nlv="Romanos-16"; break;
		case 46:$nlv="1 Cor&iacute;ntios-16"; break;
		case 47:$nlv="2 Cor&iacute;ntios-13"; break;
		case 48:$nlv="G&aacute;latas-6"; break;
		case 49:$nlv="Ef&eacute;sios-6"; break;
		case 50:$nlv="Filipenses-4"; break;
		case 51:$nlv="Colossenses-4"; break;
		case 52:$nlv="1 Tessalonicenses-5"; break;
		case 53:$nlv="2 Tessalonicenses-3"; break;
		case 54:$nlv="1 Tim&oacute;teo-6"; break;
		case 55:$nlv="2 Tim&oacute;teo-4"; break;
		case 56:$nlv="Tito-3"; break;
		case 57:$nlv="Filemom-1"; break;
		case 58:$nlv="Hebreus-13"; break;
		case 59:$nlv="Tiago-5"; break;
		case 60:$nlv="1 Pedro-5"; break;
		case 61:$nlv="2 Pedro-3"; break;
		case 62:$nlv="1 Jo&atilde;o-5"; break;
		case 63:$nlv="2 Jo&atilde;o-1"; break;
		case 64:$nlv="3 Jo&atilde;o-1"; break;
		case 65:$nlv="Judas-1"; break;
		case 66:$nlv="Apocalipse-22"; break;
		default: $nlv=" ";
	}
	
return $nlv;
}


function preencheComboLivros($modo='ecoar'){
	$bovp_count = 0;
	$retorno_form ='';
	while ($bovp_count < 67) {
	$livroCapitulos = livro_Capitulos($bovp_count);
	$nlvSplit = split("-",$livroCapitulos); 
	$nomeLivro = $nlvSplit[0];
	$qtdCapitulos = $nlvSplit[1];
	
	if ($modo == 'ecoar'){
	echo '<option value="'.$bovp_count.'-'.$qtdCapitulos.'" >'.$nomeLivro.'</option>';
	} else {
	$retorno_form .= '<option value="'.$bovp_count.'-'.$qtdCapitulos.'" >'.$nomeLivro.'</option>';
	}
	$bovp_count++;
	}
	
	if ($modo !== 'ecoar'){return $retorno_form;}
	
	}
	
	
function bible_form($bovp_return_form = 'echo', $bovp_id_form_container = 'forms-bible-container'){ 

$bovp_form = "

<div id=\"".$bovp_id_form_container."\">
          
<form method=\"get\" id=\"bovp-form-search\" action=\"" . BOVP_BIBLE_URL . "index.php\" name=\"bovp-form-search\">
<input name=\"page_id\" type=\"hidden\" value=\"" . BOVP_PAGE . "\"/>
<input name=\"sh\" type=\"hidden\" value=\"\"/>
<input type=\"text\" id=\"bovp_search\" value=\"Buscar\" onFocus=\"if(this.value=='' || this.value=='Buscar'){this.value='';}\" onBlur=\"if(this.value==''){this.value='Buscar';}else {sh.value=this.value;}\">

<select name=\"bk\" id=\"bovp_book\" onChange=\"PreencheCombo(this.form.bovp_book.selectedIndex)\">
" . preencheComboLivros("var") . "
</select>
<select type=\"text\" name=\"cp\" id=\"bovp_chapter\"></select>

<script type=\"text/javascript\" language=\"JavaScript\">PreencheCombo(0)</script>

<input type=\"submit\" id=\"bible-submit\" value=\"\">
</form>
	   
</div>

";


if($bovp_return_form == 'echo') {echo $bovp_form;} elseif($bovp_return_form == 'var'){return $bovp_form;}

}

/***********************************************************************************************************************  
	Função que se encarrega de criar link para o texto bíblico a partir da abreviação digitadas nos posts
	Modelo de Tag usada: link(mat:1:2-6)
************************************************************************************************************************/
function criaLinks($content) {

$regEXP = '/\x28[0-9A-Za-à-ú]*:[0-9]{1,3}:[0-9]{1,3}\x29/i';

$nomesLivros = array(
		array('1','gênesis','genesis','genesi','gen','g'),
		array('2','êxodo','exodo','exod','ex','e'),
		array('3','levítico','levitico','levit','lev','l'),
		array('4','números','numeros','numero','num','n'),
		array('5','deuteronômio','deuteronomio','dt','deu','d'),
		array('6','josué','josue','js','j'),
		array('7','juízes','juizes','jz','j'),
		array('8','rute','ruth','rut','rt','r'),
		array('9','1 samuel','1samuel','1sam','1s'),
		array('10','2 samuel','2samuel','2sam'),
		array('11','1 reis','1reis','1re','1r'),
		array('12','2 reis','2reis','2re','2r'),
		array('13','1 crônicas','1 cronicas','1cronicas','1cron','1cr'),
		array('14','2 crônicas','2 cronicas','2cronicas','2cron','2cr'),
		array('15','esdras','esd','ed'),
		array('16','neemias','ne'),
		array('17','ester','esther','est','et'),
		array('18','jó','jo'),
		array('19','salmos','sal'),
		array('20','provérbios','proverbios','prov','pr'),
		array('21','eclesiastes','ecle','ecl','ec'),
		array('22','cantares','canticos','cant','ct'),
		array('23','isaías','is'),
		array('24','Jeremias','jer'),
		array('25','Lamentações de Jeremias','lam'),
		array('26','Ezequiel','ez'),
		array('27','Daniel','dan'),
		array('28','Oséias','os'),
		array('39','joel','jl'),
		array('30','amós','am'),
		array('31','obadias','ob'),
		array('32','jonas','jon'),
		array('33','miquéias','miq'),
		array('34','naum','na'),
		array('35','habacuque','hab'),
		array('36','sofonias','sof'),
		array('37','ageu','ag'),
		array('38','zacarias','zac'),
		array('39','malaquias','mal'),
		array('40','mateus','mt'),
		array('41','marcos','mc'),
		array('42','lucas','lc'),
		array('43','joão','joao','jo'),
		array('44','atos','at'),
		array('45','romanos','rom'),
		array('46','1 coriíntios','1co'),
		array('47','2 coriíntios','2co'),
		array('48','gálatas','gal'),
		array('49','efésios','ef'),
		array('50','filipenses','flp'),
		array('51','colossenses','col'),
		array('52','1 tessalonissenses','1tes'),
		array('53','2 tessalonissenses','2tes'),
		array('54','1 timóteo','1tim'),
		array('55','2 timóteo','2tim'),
		array('56','tito','tt'),
		array('57','filemom','flm'),
		array('58','hebreus','heb'),
		array('59','thiago','tiago','tg','t'),
		array('60','1 pedro','1pe'),
		array('61','2 pedro','2pe'),
		array('62','1 joão','1jo'),
		array('63','2 joão','2jo'),
		array('64','3 joão','3 joao','3jo','j'),
		array('65','judas','jud'),
		array('66','apocalipse','apoc','apo','ap')
		);
		
		preg_match_all($regEXP, $content, $matches );
		$tamanho = count($matches[0]);
		foreach($matches[0] as $referencias){
			$substituir = $referencias;
			$resultado = substr($substituir, 1, -1);
			$separado = explode(':', $resultado);
			$varLivro = strtolower($separado[0]);
			$varCapitulo = $separado[1];
			$varVersiculo = $separado[2];
			
			foreach ($nomesLivros as $variaveisLivros){
		
					foreach ($variaveisLivros as $variaveisNomes){
						if ($variaveisNomes == utf8_decode($varLivro)) { // CORRIGIR ERRO - conseguir solução para essa gambiarra
							$varLivro = $variaveisLivros[0]; 
							$encontrado = true; 
							
							// Aqui vamos montar o link.
							
							$livroCapitulos = livro_Capitulos($varLivro);
							$nlvSplit = split("-",$livroCapitulos); 
							$qtdCapitulos = $nlvSplit[1];
							
							$link = '<a href="'.$linkBibliaOnline.'&bk='.$varLivro.'-'.$qtdCapitulos.'&p='.$varCapitulo.'&vs='.$varVersiculo.'">'.$substituir.'</a>';
							
							$content = str_replace($substituir,$link,$content);

							// Aqui termina a montagem do link
							
						} 
					if ($encontrado){break;}	
					}
					if ($encontrado){break;} 
			}
					
			$encontrado = false;
			
		}

return $content;

} 

/***********************************************************************************************************************
	Fim da função responsável por criar, automáticamente, links para o texto bíblico na Bíblia Online Vivendo a Palavra
************************************************************************************************************************/

//-----------------------------------------------------------------------------------------------

function exibeBiblia($content) {
	
			$_limite_por_pagina = 20;
			global $post;
			global $wpdb; 
			
			$busca = $_GET['sh'];
			
			if (isset($_GET['bk']) and ($_GET['bk'] != "") and ($_GET['bk'] != "0-0")) { 
				$var_livro = $_GET['bk'];
				$separa = explode('-', $var_livro);
				$var_livro = $separa[0];
				if($var_livro == 0) {$var_livro = 1; $var_paginas_livro = 50;}
				$var_paginas_livro = $separa[1];
			} else { 
				$var_livro = 1;
				$var_paginas_livro = 50;
			}
			
			if (isset($_GET['cp']) and ($_GET['cp'] != "")) { 
			$var_capitulo = $_GET['cp'];
			} else { $var_capitulo = 1;}
			
			
			if (isset($_GET['vs']) and ($_GET['vs'] != "")) { 
			$destaca = $_GET['vs'];
			} else { $destaca = 0;}
	
	
	
	if (is_page(BOVP_PAGE)) {  
	
		
			 
		if (isset($busca) and ($busca != "")) { 
					$busca = mysql_real_escape_string($busca);
					//$busca = str_replace(" ", "%", $busca);  // Frase exata ou palavras da frase
					$tot_resultados = $wpdb->get_results("SELECT COUNT(text) AS totalreg FROM bovp_arc WHERE (LCASE(text) RLIKE '[[:<:]]" . $busca . "[[:>:]]')"); 
					foreach($tot_resultados as $tot_result) {
					$bovp_count  = $tot_result->totalreg;
					}
				
				$paginas =(($bovp_count % $_limite_por_pagina) > 0) ? (int)($bovp_count / $_limite_por_pagina) + 1 : ($bovp_count / $_limite_por_pagina); 
				if (isset($_GET['pagina'])) { 
					$pagina = (int)$_GET['pagina']; 
					} else { 
					$pagina = 1; 
				} 
				
				$pagina = max(min($paginas, $pagina), 1); 
				$bovp_start = ($pagina - 1) * $_limite_por_pagina; 
				
				
				$bovp_content = "<div id='bibliaVP'>";
				
				$bovp_content .= bible_form('var', 'forms-bible-container'); 
				
				$resultados = $wpdb->get_results("SELECT * FROM bovp_arc WHERE ( LCASE(text) RLIKE '[[:<:]]" . 
				$busca."[[:>:]]' ) LIMIT ".$bovp_start.", ".$_limite_por_pagina);
				
				
				
				$bovp_content .= "<h3>". __('Find itens','bovp') . "<span class=\"bovp_cap\">" . $bovp_count . "</span></h3><br>";
				
				$bovp_content .= "<div id='conteudo'>";
				
				$bovp_color == '';
						
				foreach ($resultados as $resultado){
					
					
					
					$livro = $resultado->book; 
					$capitulo = $resultado->cp; 
					$verso = $resultado->vs; 
					$texto = $resultado->text;				
					$bovp_item_id = $resultado->id;
					
					
					
					if($bovp_item_id < 67) {
						
						if($bovp_color == ''){$bovp_color = 'bovp_color';} elseif($bovp_color == 'bovp_color'){$bovp_color = '';}
					
						$bovp_content .= "<p class='$bovp_color'><b><a href=\"?page_id=" . BOVP_PAGE . 
								"&bk=" . $capitulo . "-" .  $verso . "\">" . $texto . 
								"</a></b><br>" . __('This is the book number ','bovp') . "&nbsp;<b>" . 
								$capitulo . "</b>&nbsp;". __(' and have ','bovp'). "&nbsp;<b>".$verso.
								"</b>&nbsp;".__(' chapters.','bovp')."</p>";	
								
								
							
					} else {
			
			
						if($bovp_color == ''){$bovp_color = 'bovp_color';} elseif($bovp_color == 'bovp_color'){$bovp_color = '';}
						
						$paraDestacar = '/'.$busca.'/i';
						$paraDestacar = str_replace('%',' ', $paraDestacar);
		
						preg_match_all($paraDestacar, $texto, $destacar );
						$tamanho = count($destacar[0]);
						foreach($destacar[0] as $destaque){
						$texto = str_replace($destaque,'<font color="red">'.$destaque.'</font>',$texto);
						}
						
						$livroCapitulos = livro_Capitulos($livro);
						$livroCapitulosSplit = split("-",$livroCapitulos); 
						$nomeLivro = $livroCapitulosSplit[0];
						$qtdCapitulos = $livroCapitulosSplit[1];
						
						$bovp_content .= "<p class='$bovp_color'><b><a href=\"?page_id=" . BOVP_PAGE . 
						"&bk=" . $livro . "-" . $qtdCapitulos . "&cp=" .  $capitulo . 
						"&vs=" . $verso . "\">" . $nomeLivro . 
						":" . $capitulo . ":" . $verso . "</a></b><br>" . $texto . "</p>";			
				} 
				
				}
				
				$bovp_content .= "</div><br>";
				
						$link = "?page_id=". BOVP_PAGE ."&sh=".($_GET['sh']);
						$p = new pagination;
						$p->changeClass("bovp_pagination");
						$p->Items($bovp_count);
						$p->limit($_limite_por_pagina);
						$p->target($link);
						$p->currentPage($pagina);
						$p->nextLabel('<strong>Pr&oacute;ximo</strong>');
						$p->prevLabel('<strong>Anterior</strong>');
						$p->nextIcon('');
						$p->prevIcon('');
						
				$bovp_content .= $p->show();
				
				$bovp_of = min($bovp_count, ($bovp_start + 1));
				$bovp_to = min($bovp_count, ($bovp_start + $_limite_por_pagina));
				$bovp_text = "Finded <b>$bovp_count</b> verses for your search.<br>Show results <b>$bovp_of</b> to <b>$bovp_to</b>";
				
				$bovp_content .= "<p class=\"resumo\" align=\"center\">" . __('Finded','bovp') .'&nbsp;<b>' 
				. $bovp_count . '</b>&nbsp;' .__('verses for your search.','bovp') .'<br>';
				
				$bovp_content .=  __('Show results','bovp') . '&nbsp;<b>' . $bovp_of . '</b>&nbsp;' . __('to','bovp') . '&nbsp;<b>' . $bovp_to . '</b>&nbsp;</p>';
				
				$bovp_content .= "<div id=\"rodapeBibliaVP\">";
				$bovp_content .= "<span class='bovp_translate'>" . __('Translate: ', 'bovp') . "Jo&atilde;o Ferreira de Almeida - Atualizada</span>";		
				$bovp_content .= "<a href=\"http://www.vivendoapalavra.com.br/\"><img src=\"" . BOVP_FOLDER . "img/logovp.png\" border=\"0\"></a>";
				$bovp_content .= "<div style=\"clear:both\"></div>";
				
				
				
				$bovp_content .= "</div></div>"; // SAÍDA DA BUSCA
				
				return $bovp_content;
		
		} else {
		
				$sql = "SELECT * FROM `bovp_arc` WHERE `book` =".$var_livro." AND `cp` =".$var_capitulo ;

				$resultados_livro = $wpdb->get_results($sql);
				
				
				$bovp_content = "<div id=\"bibliaVP\">";
				//$bovp_content .= "<div id=\"cabecaBibliaVP\">";
				
				$bovp_content .= bible_form('var', 'forms-bible-container'); 
				
				$bovp_content .= "<h3>";
				 
						$livroCapitulos = livro_Capitulos($var_livro);
						$nlvSplit = split("-",$livroCapitulos); 
						$nomeLivro = $nlvSplit[0];
						
				$bovp_content .= $nomeLivro . "<span class=\"bovp_cap\">" . $var_capitulo . "</span></h3><br>";
				
				//$bovp_content .= "</div>";
				$bovp_content .= "<div id='conteudo'>";
				
				$bovp_color == '';
				
				foreach ($resultados_livro as $resultado_livro){
					
					if($bovp_color == ''){$bovp_color = 'bovp_color';} elseif($bovp_color == 'bovp_color'){$bovp_color = '';}
					
							$verso = $resultado_livro->vs; 
							$texto = $resultado_livro->text;
							
				$bovp_content .= "<p class='$bovp_color'><span class=\"verse_num\">$verso</span>";			
							
							
								if($verso == $destaca) {
								$bovp_content .= "<font color=\"red\">$texto</font></p>";
								} else {
								$bovp_content .= "$texto</p>";
								}
							
						}
						
				$bovp_content .= "</div><br>";	
					
				
							$link = "?page_id=". BOVP_PAGE . "&bk=".$var_livro."-".$var_paginas_livro;
							$p = new pagination;
							$p->changeClass("bovp_pagination");
							$p->Items($var_paginas_livro*$_limite_por_pagina);
							$p->limit($_limite_por_pagina);
							$p->target($link);
							$p->parameterName("cp");
							$p->currentPage($var_capitulo);
							$p->nextLabel('<strong>' . __('Next','bovp') . '</strong>');
							$p->prevLabel('<strong>' . __('Previous','bovp') . '</strong>');
							$p->nextIcon('');//removing next icon
							$p->prevIcon('');//removing previous icon
							
				$bovp_content .= $p->show();
							
					
							
				$bovp_content .= "<div id=\"rodapeBibliaVP\">";
				$bovp_content .= "<span class='bovp_translate'>" . __('Translate: ', 'bovp') . "Jo&atilde;o Ferreira de Almeida - Atualizada</span>";		
				$bovp_content .= "<a href=\"http://www.vivendoapalavra.com.br/\"><img src=\"" . BOVP_FOLDER . "img/logovp.png\" border=\"0\"></a>";
				$bovp_content .= "<div style=\"clear:both\"></div>";
				
				
				$bovp_content .= "</div></div>"; // SAIDA DO LIVROS
				
				return $bovp_content;	
		
		}
		
		
		
	} else {
		
	return $content;	
		
	}



}



?>
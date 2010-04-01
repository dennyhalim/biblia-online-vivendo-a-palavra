<?php
/*
Plugin Name: B&iacute;blia Online 
Plugin URI: http://www.vivendoapalavra.com.br/
Description: Consulta aos 66 livros da B&iacute;blia Sagrada. A vers&atilde;o que acompanha o plugin &eacute; Almeida Revisada e Corrigida, considerada de dom&iacute;nio p&uacute;blico. Este plugin, assim como o texto b&iacute;blico usado por ele n&atilde;o est&atilde;o sendo comercializados. O texto b&iacute;blico utilizado foi coletado na internet, caso encontre algum erro no mesmo, nos informe para que possamos corrigir.
Author: Andre Brum Sampaio
Version: 1.3
Author URI: http://www.vivendoapalavra.com.br/
*/
require_once('includes/pagination.class.php');

class BibliaOnline {
	
	private static $wpdb;
	private static $pastaPlugin;
	private static $diretoriodosite;
	private static $opcoes;
	private static $id_pagina_biblia;
	private static $id_widget_biblia;
	private static $id_logo_biblia;
	private static $CaminhoPlugin;
	private static $dataverdiario;
	private static $origemVersiculo;
	private static $regEXP;
	private static $linkBibliaOnline;	

function bibliaOnline_JS() {
wp_enqueue_script('js_biblia_online', get_option('siteurl').'/wp-content/plugins/biblia-online/scripts/BibliaOnline.js');
}

function bibliaOnline_CSS() {
echo '<link rel="stylesheet" href="'.get_option('siteurl').'/wp-content/plugins/biblia-online/css/BibliaOnlineCSS.css" type="text/css" media="screen" />';
}

function importaTextoBiblico(){
	
$arquivo = BibliaOnline::$pastaPlugin."dados/wp_arc.sql";

$linhaComentada = array('#','-- ','/*!','--');
$separador = ';';
$fp = file_get_contents($arquivo);
$data = explode("\n",$fp);
$querysExecutadas = 0;

for($i = 0; $i < count($data); $i++) {
	foreach ($linhaComentada as $prefixo){
		
		if (strpos ($data[$i], $prefixo) === 0){ 
			$pularLinha=true;
		}
	}
$fragmento = trim($data[$i]);
$ultimaLetra = substr(trim($data[$i]), -1);
if ($ultimaLetra == $separador){
	if(!$pularLinha){
	$queryPronta = true;
	$querysExecutadas++;
	$insereTexto .= $fragmento;
	}
	}
if ($queryPronta){
	if(!$pularLinha){
		$insereTexto = trim($insereTexto);
		BibliaOnline::$wpdb->query($insereTexto);
		$insereTexto = '';
		$queryPronta = false;
	}else{$pularLinha = false;}
}
else {
	if(!$pularLinha){
	$insereTexto .= $fragmento;
	}else{$pularLinha = false;}
}
}
echo "<meta HTTP-EQUIV='refresh' CONTENT='0'>";
}

function Inicializar(){
		global $wpdb;
        if(!defined('WP_CONTENT_URL')){define('WP_CONTENT_URL',get_option('siteurl').'/wp-content');}
		add_filter("the_content", array("BibliaOnline","exibeBiblia"));
		add_filter("the_content", array("BibliaOnline","criaLinks"));
		add_action("wp_print_scripts", array("BibliaOnline","bibliaOnline_JS"));
		add_action("wp_head", array("BibliaOnline","bibliaOnline_CSS"));
		add_action("admin_menu", array("BibliaOnline","criarMenuBOVP"));
		BibliaOnline::$wpdb = $wpdb;
		BibliaOnline::$dataverdiario = date("Y-m-d");
		BibliaOnline::$linkBibliaOnline = get_option('home').'/index.php/?page_id='.get_option('paginaBOVP');
		BibliaOnline::$diretoriodosite = get_option('home').'/';
		BibliaOnline::$id_pagina_biblia = get_option('paginaBOVP');
		BibliaOnline::$origemVersiculo = get_option('verdiarioBOVP');
		BibliaOnline::$pastaPlugin = plugins_url('/biblia-online/');// BibliaOnline::$diretoriodosite.'/wp-content/plugins/biblia-online/';
		
		if ( get_option('estadoBancoDeDadosBOVP') == 'vazio' ) {
		
		function alertaBOVP() {
		echo "
			<div id='message' class='updated fade'><p><strong>".__('A B&iacute;blia Online VP est&aacute; quase pronta. ')."</strong> ".sprintf(__('<a href="%1$s">Clique aqui</a> para completar a instala&ccedil;&atilde;o.'), "plugins.php?page=biblia-online/wp-biblia-online.php")."</p></div>
			";
		}
		add_action('admin_notices', 'alertaBOVP');
		return;
	}
		}	

function Instalar(){ 
		if ( is_null(BibliaOnline::$wpdb) ) BibliaOnline::inicializar();
		
		add_option("estadoBancoDeDadosBOVP", 'vazio', '', 'yes'); // p�gina onde ser� exibida a B�blia
		add_option("paginaBOVP", '1717', '', 'yes'); // p�gina onde ser� exibida a B�blia
		add_option("verdiarioBOVP", '0', '', 'yes'); // origem do vers�culo (AT, NT ou Toda a B�blia)
		add_option("idVerdiarioBOVP", '1', '', 'yes'); // Id do versiculo que ser� exibido na data atual
		add_option("dataVerdiarioBOVP", BibliaOnline::$dataverdiario, '', 'yes'); // Data de exibi��o do ve�culo registrado
}	

function Desinstalar(){
		$sqlExcluirBiblia = "DROP TABLE `".BibliaOnline::$wpdb->prefix."arc`";
		BibliaOnline::$wpdb->query($sqlExcluirBiblia);
		delete_option('paginaBOVP');
		delete_option('verdiarioBOVP');
		delete_option('idVerdiarioBOVP');
		delete_option('dataVerdiarioBOVP');
		delete_option('estadoBancoDeDadosBOVP');
}

function criarMenuBOVP() {
	add_menu_page('Configuracoes', 'Biblia Online', '10', __FILE__, array('BibliaOnline','paginaCongigBOVP'),plugins_url('/imagens/iconeBOVP.png', __FILE__));
	add_action( 'admin_init', array('BibliaOnline','configuraBOVP'));
}

function configuraBOVP() {
	register_setting( 'opcoesBOVP', 'paginaBOVP' );
	register_setting( 'opcoesBOVP', 'verdiarioBOVP' );
}

function paginaCongigBOVP() {
?>
<div class="wrap">
<h2>Biblia Online VP</h2>

<form method="post" action="options.php">

    <?php 
	if(get_option('estadoBancoDeDadosBOVP')=='vazio'){ 
		BibliaOnline::importaTextoBiblico();
		update_option("estadoBancoDeDadosBOVP", 'instalado');
	}
	settings_fields( 'opcoesBOVP' ); ?><?php BibliaOnline::versiculoDiario(BibliaOnline::$origemVersiculo); ?>
    
	<table class="form-table">
		<?php  ?>
        <tr valign="top">
        <th scope="row">P&aacute;gina onde ser&aacute; exibida a B&iacute;blia 
          Online VP</th>
        <td><input type="text" name="paginaBOVP" value="<?php echo get_option('paginaBOVP'); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Origem do Vers&iacute;culo a ser exibido na Palavra Di&aacute;ria</th>
        <td><select name="verdiarioBOVP">
            <option value="0" <?php if (get_option('verdiarioBOVP')==0) {echo 'selected';} ?> >Toda a B&iacute;blia</option>
			<option value="1" <?php if (get_option('verdiarioBOVP')==1) {echo 'selected';} ?> >Antigo Testamento</option>
			<option value="2" <?php if (get_option('verdiarioBOVP')==2) {echo 'selected';} ?> >Novo Testamento</option>
			<option value="3" <?php if (get_option('verdiarioBOVP')==3) {echo 'selected';} ?> >Livros dos Salmos</option>
        	</select>
			
		</td>
        </tr> 
    </table>
    
    
<p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Atualizar Opcoes') ?>" />
       </p>

</form>
</div>

<?php } 

function livro_Capitulos($nlv) {
	switch($nlv) {
		case 0:$nlv="Selecione o Livro-*"; break;
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


function preencheComboLivros(){
	$contador = 0;
	while ($contador < 67) {
	$livroCapitulos = BibliaOnline::livro_Capitulos($contador);
	$nlvSplit = split("-",$livroCapitulos); 
	$nomeLivro = $nlvSplit[0];
	$qtdCapitulos = $nlvSplit[1];
	echo '<option value="'.$contador.'-'.$qtdCapitulos.'" >'.$nomeLivro.'</option>';
	$contador++;
	}
	}
	
	
function formBiblia(){ 
	?>

<div id="formBuscaBiblia" style="border-bottom:3px double; width:100%; background-color:#ccc;"  > 
<form name="BuscaNaBiblia" id="BuscaNaBiblia" action="<?php echo BibliaOnline::$diretoriodosite.'index.php'; ?>"/>
<table width="100%" border="0" style="padding:5px";>
  <tr>
    <td><div align="right">
	<font style="color:#999999;font: 12px Arial, Helvetica, sans-serif;">digite aqui o que deseja procurar:</font><br />
    <input name="page_id" type="hidden" value="<?php echo BibliaOnline::$id_pagina_biblia?>"/><input name="consulta" type="text" class="caixaDeTexto" id="consulta" size="100%"/>
        </div></td>
  </tr>
  <tr>
    <td><div align="right">
	<font style="color:#999999;font: 12px Arial, Helvetica, sans-serif;margin-bottom:5px;">para ler um livro, selecione aqui:</font><br />
    <select name="livro" id="select2" onChange="PreencheCombo(this.form.livro.selectedIndex)" class="caixaLivro">
            <?php BibliaOnline::preencheComboLivros(); ?>
          </select>
          <select name="capitulo" id="capitulo" class="caixaCapitulo">
            <option>Cap&iacute;tulo</option>
          </select>
        </div></td>
  </tr>
  <tr>
    <td><div align="right">
          <script type="text/javascript" language="JavaScript">PreencheCombo(0);</script>
          <input name="submit" type="submit" class="botao" value="Ok">
        </div></td>
  </tr>
</table></form>
  </div>
<?php 
}

/***********************************************************************************************************************  
	Fun��o que se encarrega de criar link para o texto b�blico a partir da abrevia��o digitadas nos posts
	Modelo de Tag usada: link(mat:1:2-6)
************************************************************************************************************************/
function criaLinks($content) {

$regEXP = '/\x28[0-9A-Za-�-�]*:[0-9]{1,3}:[0-9]{1,3}\x29/i';

$nomesLivros = array(
		array('1','g�nesis','genesis','genesi','gen','g'),
		array('2','�xodo','exodo','exod','ex','e'),
		array('3','lev�tico','levitico','levit','lev','l'),
		array('4','n�meros','numeros','numero','num','n'),
		array('5','deuteron�mio','deuteronomio','dt','deu','d'),
		array('6','josu�','josue','js','j'),
		array('7','ju�zes','juizes','jz','j'),
		array('8','rute','ruth','rut','rt','r'),
		array('9','1 samuel','1samuel','1sam','1s'),
		array('10','2 samuel','2samuel','dt','deu','d'),
		array('11','1 reis','1reis','1re','1r'),
		array('12','1 cr�nicas','1 cronicas','1cronicas','1cro','1cr'),
		array('13','2 cr�nicas','2 cronicas','2cronicas','2cro','2cr'),
		array('14','esdras','esd','ed'),
		array('15','neemias','nm'),
		array('16','ester','esther','est','et'),
		array('17','j�','jo'),
		array('18','salmos','sl'),
		array('19','prov�rbios','proverbios','prov','pr'),
		array('20','eclesiastes','ecles','ecl','ec'),
		array('21','cantares','canticos','cant','ct'),
		array('22','2 reis','2reis','2re','2r'),
		array('59','thiago','tiago','tg','t'),
		array('66','apocalipse','apocap','apo','ap')
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
						if ($variaveisNomes == utf8_decode($varLivro)) { // CORRIGIR ERRO - conseguir solu��o para essa gambiarra
							$varLivro = $variaveisLivros[0]; 
							$encontrado = true; 
							
							// Aqui vamos montar o link.
							
							$livroCapitulos = BibliaOnline::livro_Capitulos($varLivro);
							$nlvSplit = split("-",$livroCapitulos); 
							$qtdCapitulos = $nlvSplit[1];
							
							$link = '<a href="'.BibliaOnline::$linkBibliaOnline.'&livro='.$varLivro.'-'.$qtdCapitulos.'&capitulo='.$varCapitulo.'">'.$substituir.'</a>';
							
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
	Fim da fun��o respons�vel por criar, autom�ticamente, links para o texto b�blico na B�blia Online Vivendo a Palavra
************************************************************************************************************************/



/***********************************************************************************************************************  
	Adapta��o do script cedido pelo Kerwin Muriel Hirt Mayer que se encarrega de manter um pensamento l�gico no Vers�culo 
	Di�rio, ou seja, caso um �nico vers�culo n�o isole um pensamento completo, o script se encarrega 
	de fazer um agrupamento dos vers�culos que completam o pensamento.
************************************************************************************************************************/

function versiculoDiario($novoFiltro) {

if(get_option('estadoBancoDeDadosBOVP')=='vazio'){return false;}

global $wpdb; 

switch($novoFiltro) {

		case 0:$novoFiltro=""; break;
		case 1:$novoFiltro="WHERE wp_arc.livro =< 39"; break;
		case 2:$novoFiltro="WHERE wp_arc.livro >= 40"; break;
		case 3:$novoFiltro="WHERE wp_arc.livro = 19"; break;
		default: $novoFiltro="";
		}
		
if (get_option("dataVerdiarioBOVP") == BibliaOnline::$dataverdiario) {
	$idversiculo = get_option("idVerdiarioBOVP");}
else {
	
	$sqlBuscaLivros = "SELECT * FROM wp_arc ".$novoFiltro." ORDER BY rand() LIMIT 1";
	$versiculoAleatorio = $wpdb->get_row($sqlBuscaLivros);
	
	
	if ($versiculoAleatorio) {
				
				$sequencia = $versiculoAleatorio ->id;
				$ultimaLetra = substr(trim($versiculoAleatorio ->texto), -1);
				$idversiculo = $versiculoAleatorio ->id;
				$loop = 0;
				while (strripos(".?!", $ultimaLetra) === FALSE) { 
					$loop++;
					$proximo = $sequencia+$loop;
					$novaBusca = $wpdb->get_row("SELECT * FROM wp_arc WHERE id = '".$proximo."' LIMIT 1");
					$ultimaLetra = substr(trim($novaBusca ->texto), -1);
					$novoversiculo = $novaBusca ->id;
					$idversiculo = $idversiculo."-".$novoversiculo;
				}
							
				$dataversiculo = BibliaOnline::$dataverdiario;
				update_option("idVerdiarioBOVP", $idversiculo);
				update_option("dataVerdiarioBOVP", $dataversiculo);
				}
}


$pegaVerso = split("-",$idversiculo); 
$tamanho = count($pegaVerso);

foreach($pegaVerso as $v) {
		$SQLversoComposto = $wpdb->get_row("SELECT * FROM wp_arc WHERE id = '".$v."' LIMIT 1");
		$versoComposto .= $SQLversoComposto->texto;
		$ultimoVerso =  $SQLversoComposto->verso;
		}
$verdiario = $wpdb->get_row("SELECT * FROM wp_arc WHERE id = '".$pegaVerso[0]."' LIMIT 1");
if ($verdiario) {
				
				$livro = $verdiario->livro; 
				$capitulo = $verdiario->capitulo; 
				$verso = $verdiario->verso; }
				
				$livroCapitulos = BibliaOnline::livro_Capitulos($livro);
				$nlvSplit = split("-",$livroCapitulos); 
				$nomeLivro = $nlvSplit[0];
				$qtdCapitulos = $nlvSplit[1];
				
				echo $versoComposto; ?><a href="<?php echo BibliaOnline::$linkBibliaOnline;?>&livro=<?php echo $livro.'-'.$qtdCapitulos; ?>&capitulo=<?php echo $capitulo;?>">(<?php echo $nomeLivro; ?>: <?php echo $capitulo;?>: <?php echo $verso; if ($tamanho > 1) {echo ' - '.$ultimoVerso;}?>)</a>
				
				<?php
}

/**************************************** Fim do Script Pensamento L�gico *********************************************
***********************************************************************************************************************
***********************************************************************************************************************/						

function exibeBiblia($content) {

$_limite_por_pagina = 20;
global $post;
global $wpdb; 
$busca = $_GET['consulta'];

if (isset($_GET['livro']) and ($_GET['livro'] != "")) { 
	$var_livro = $_GET['livro'];
	$separa = explode('-', $var_livro);
	$var_livro = $separa[0];
	$var_paginas_livro = $separa[1];
} else { 
	$var_livro = 1;
	$var_paginas_livro = 50;
}

if (isset($_GET['capitulo']) and ($_GET['capitulo'] != "")) { 
$var_capitulo = $_GET['capitulo'];
} else { $var_capitulo = 1;}


if (is_page(BibliaOnline::$id_pagina_biblia)) {  
 
		if (isset($busca) and ($busca != "")) { 
			$busca = mysql_real_escape_string($busca);
			$busca = str_replace(" ", "%", $busca); 
			$tot_resultados = $wpdb->get_results("SELECT COUNT(wp_arc.texto) AS totalreg FROM wp_arc WHERE (wp_arc.texto LIKE '% ".$busca." %')"); 
			
			foreach($tot_resultados as $tot_result) {
			$contador  = $tot_result->totalreg;
			}
		
		$paginas =(($contador % $_limite_por_pagina) > 0) ? (int)($contador / $_limite_por_pagina) + 1 : ($contador / $_limite_por_pagina); 
		if (isset($_GET['pagina'])) { 
			$pagina = (int)$_GET['pagina']; 
			} else { 
			$pagina = 1; 
		} 
		
		$pagina = max(min($paginas, $pagina), 1); 
		$inicio = ($pagina - 1) * $_limite_por_pagina; 
?>

<div id='bibliaVP'>

  <div id="cabecaBibliaVP"> 
    <?php BibliaOnline::formBiblia(); ?>
  </div>
		
<?php  

$resultados = $wpdb->get_results("SELECT * FROM wp_arc WHERE (`texto` LIKE '% ".$busca." %') LIMIT ".$inicio.", ".$_limite_por_pagina);
?>

<div id='conteudo'>
		<p><br><b>Exibindo resultados de <?php echo min($contador, ($inicio + 1))?> a <?php echo min($contador, ($inicio + $_limite_por_pagina))?></b>
		</p>
		
		<?php 	
		foreach ($resultados as $resultado){
			$livro = $resultado->livro; 
			$capitulo = $resultado->capitulo; 
			$verso = $resultado->verso; 
			$texto = $resultado->texto;
			echo 'eu busco por: '.$busca;
			$texto = str_replace('$busca."/i"','<font color="red">'.$busca.'</font>',$texto);
			
			
			$livroCapitulos = BibliaOnline::livro_Capitulos($livro);
			$livroCapitulosSplit = split("-",$livroCapitulos); 
			$nomeLivro = $livroCapitulosSplit[0];
			$qtdCapitulos = $livroCapitulosSplit[1];
		?>		
		<p><b><a href="?page_id=<?php echo BibliaOnline::$id_pagina_biblia;?>&livro=<?php echo $livro;?>&capitulo=<?php echo $capitulo;?>&paginas=<?php echo $qtdCapitulos;?>"><?php echo $nomeLivro;?>: <?php echo $capitulo;?>: <?php echo $verso;?></a></b><br><?php echo $texto;?></p>
		<?php
		}
		?>	
		
</div>
		<br>
		<?php 
		$link = "?consulta=".($_GET['consulta']);
		$p = new pagination;
		$p->Items($contador);
		$p->limit($_limite_por_pagina);
		$p->target($link);
		$p->currentPage($pagina);
		$p->nextLabel('<strong>Pr&oacute;ximo</strong>');
		$p->prevLabel('<strong>Anterior</strong>');
		$p->nextIcon('');
		$p->prevIcon('');
		$p->show();
		?>

  	<p class="resumo" align="center"> Encontrado(s)&nbsp;<strong><?php echo $contador ?></strong>&nbsp;vers&iacute;culos 
    &nbsp;para a pesquisa efetuada. <br />
    &nbsp;Exibindo resultados de <?php echo min($contador, ($inicio + 1))?> a 
    <?php echo min($contador, ($inicio + $_limite_por_pagina))?><br />
    Tradu&ccedil;&atilde;o: Jo&atilde;o Ferreira de Almeida - Atualizada</p>

<div id="rodapeBibliaVP">
    <div id="logoBOVP"><a href="http://www.vivendoapalavra.com.br/"><img src="<?php echo BibliaOnline::$pastaPlugin; ?>imagens\logo_biblia_online2.png" width="174" height="60" border="0"></a> 
    </div>
</div>

</div>
		<?php 
	}

	else {

		$sql = "SELECT * FROM `wp_arc` WHERE `livro` =".$var_livro." AND `capitulo` =".$var_capitulo ;
		$resultados_livro = $wpdb->get_results($sql);
		?>
<div id='bibliaVP'>

<div id="cabecaBibliaVP">

<?php BibliaOnline::formBiblia(); ?>

</div>
	
<div id='conteudo'><br>
		<h3>
		<?php 
		$livroCapitulos = BibliaOnline::livro_Capitulos($var_livro);
		$nlvSplit = split("-",$livroCapitulos); 
		$nomeLivro = $nlvSplit[0];
		echo $nomeLivro;?> - Cap&iacute;tulo <?php echo $var_capitulo ?></h3><br>
		<?php
		foreach ($resultados_livro as $resultado_livro){
			$verso = $resultado_livro->verso; 
			$texto = $resultado_livro->texto;
		?>	
			<p><b><?php echo $verso ?></b>: <?php echo $texto ?></p>
		<?php 
		}
		?>
			
</div>
			<br>
			<?php
			$link = "?livro=".$var_livro."-".$var_paginas_livro;
			$p = new pagination;
			$p->Items($var_paginas_livro*$_limite_por_pagina);
			$p->limit($_limite_por_pagina);
			$p->target($link);
			$p->parameterName("capitulo");
			$p->currentPage($var_capitulo);
			$p->nextLabel('<strong>Pr&oacute;ximo</strong>');
			$p->prevLabel('<strong>Anterior</strong>');
			$p->nextIcon('');//removing next icon
			$p->prevIcon('');//removing previous icon
			$p->show();
			?>
			
<p class="resumo" align="center"> Exibindo o livro: &nbsp;<strong><?php echo $nomeLivro;?></strong>&nbsp;Cap&iacute;tulo: <strong><?php echo $var_capitulo ?></strong><br />
Tradu&ccedil;&atilde;o: Jo&atilde;o Ferreira de Almeida - Atualizada</p>

<div id="rodapeBibliaVP">
    <div id="logoBOVP"><a href="http://www.vivendoapalavra.com.br/"><img src="<?php echo BibliaOnline::$pastaPlugin; ?>imagens\logo_biblia_online2.png" width="174" height="60" border="0"></a> 
    </div>
</div>	
</div>
	
<?php

}
}
else {return $content;}
}
	
function WidgetBuscaNaBiblia($args) {
		if(is_page(BibliaOnline::$id_pagina_biblia)){return false;}
		echo $args['before_widget'];
		echo $args['before_title'] . "B&iacute;blia Online" . $args['after_title'];
		BibliaOnline::formBiblia();
		echo $args['after_widget'];
}

function WidgetVersiculoDiario($args) {
		
		echo $args['before_widget'];
		echo $args['before_title'] . "Palavra Di&aacute;ria" . $args['after_title'];
		BibliaOnline::versiculoDiario(BibliaOnline::$origemVersiculo);
		echo $args['after_widget'];
}
}

register_activation_hook(__FILE__,array('BibliaOnline','Instalar'));
register_sidebar_widget(__('Vers&iacute;culo Di&aacute;rio'),array('BibliaOnline', 'WidgetVersiculoDiario'));
register_sidebar_widget(__('B&iacute;blia Online'),array('BibliaOnline', 'WidgetBuscaNaBiblia'));
register_deactivation_hook(__FILE__,array('BibliaOnline','Desinstalar'));
add_filter('init', array('BibliaOnline','Inicializar'));
?>
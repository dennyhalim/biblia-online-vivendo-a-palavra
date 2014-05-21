<?php 

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {die(__('Access denied.','bovp')); }

require_once('functions.php');

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

	<p><?php _e('Plugin for implementation of Bible Online in your Wordpress blog. With it, you can spread out the Word of God and bless your website\'s users. The plugin allows to consult all of 66 books of the Holy Bible.','bovp'); ?></p>
 
	<p>Author:&nbsp;<a href="https://www.facebook.com/andrebrumsampaio">Andre Brum Sampaio</a></p>

	<p><?php echo __('Author URI: ','bovp') . '&nbsp;<a href="http://www.vivendoapalavra.org/">http://www.vivendoapalavra.org/</a>' ?></p>

	<p><?php echo __('Version: ','bovp') .  BOVP_SYSTEM_VERSION; ?></p>      




	<h3><?php _e('Versions:','bovp'); ?></h3>

	<p>
		<ul class="bovp_li_versions">
			<li>• King James Edition - English;</li>
			<li>• Almeida Corrigida Fiel - Português (1994);</li>
			<li>• Spanish Reina Valera (1960).<li>
		</ul>
	</p>

	<h3><?php _e('Settings:','bovp'); ?></h3>

	<p><?php _e('In the SETTINGS PAGE, select desired version and then click to install. Wait the bible text installation complete and then choose the options (page, itens per page, theme, verse source).','bovp');?></p>

</fieldset>

</div>


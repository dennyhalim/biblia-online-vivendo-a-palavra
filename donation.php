
<?php 


if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {die(__('Access denied.','bovp')); }

require_once('functions.php');

?>

<div class="bovp_wrap">
<h2 class="bovp_h2"><?php _e('Online Bible v.'. BOVP_SYSTEM_VERSION . ' - Settings','bovp');?></h2>

<fieldset  class="bovp_fieldset">
<legend  class="bovp_legend"><?php _e('Consider a Donation','bovp'); ?></legend>

<p align="justify">
<?php _e("If you use Online Bible plugin and want to contribute to the project's maintenance, you can use the link below to make a donation.",'bovp'); ?>
          
</p>


<!-- INICIO FORMULARIO BOTAO PAGSEGURO -->
<form target="pagseguro" action="https://pagseguro.uol.com.br/checkout/v2/donation.html" method="post">
<input type="hidden" name="receiverEmail" value="bibliaonlinevp@vivendoapalavra.com.br" />
<input type="hidden" name="currency" value="BRL" />
<input type="image" src="https://p.simg.uol.com.br/out/pagseguro/i/botoes/doacoes/84x35-doar-azul.gif" name="submit" alt="Doe com PagSeguro - é rápido e seguro!" />
</form>
<!-- FINAL FORMULARIO BOTAO PAGSEGURO -->


<!-- INICIO FORMULARIO BOTAO PAYPAL-->
	
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="9KV25MLWLPKQN">
<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
</form>


<!-- FINAL FORMULARIO BOTAO PAYPAL-->

</fieldset>
</div>

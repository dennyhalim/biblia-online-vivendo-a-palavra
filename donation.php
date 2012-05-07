<style>

div.wrap fieldset {
border:1px solid #CCC;
margin:5px;
padding:10px;
max-width:600px;
}


div.wrap legend
{
   padding: 0px 3px 0px 3px;
   margin: 2px;
   border: solid #CCC 1px;
   font-size:0.9em;
   background-color: #e8e8e8;
}
</style>

<div class="wrap">
<h2><?php _e('Online Bible for Wordpress','bovp'); ?></h2>

<fieldset>
<legend><?php _e('Consider a Donation','bovp'); ?></legend>

<p align="justify">
<?php _e('If you use Online Bible plugin and want to contribute to the maintenance of the project, you can use the link below to make a donation.','bovp'); ?>
          
</p>


<!-- INICIO FORMULARIO BOTAO PAGSEGURO -->
<form target="pagseguro" action="https://pagseguro.uol.com.br/checkout/v2/donation.html" method="post">
<input type="hidden" name="receiverEmail" value="bibliaonlinevp@vivendoapalavra.com.br" />
<input type="hidden" name="currency" value="BRL" />
<input type="image" src="https://p.simg.uol.com.br/out/pagseguro/i/botoes/doacoes/84x35-doar-azul.gif" name="submit" alt="Doe com PagSeguro - é rápido e seguro!" />
</form>
<!-- FINAL FORMULARIO BOTAO PAGSEGURO -->

<p align="justify">
<?php _e('You can also contribute in other ways, see below:','bovp'); ?>
</p>

<p align="justify">
<strong>1)</strong><?php _e('&nbsp;Enjoying and publicizing our Facebook page;','bovp'); ?><br />
<strong>2)</strong><?php _e('&nbsp;Sending biblical exclusive articles for posting on our site;','bovp'); ?><br />
<strong>3)</strong><?php _e('&nbsp;Sending suggestions for improving the plugin, through our website or our Facebook page.','bovp'); ?><br /><br />

 
</p>

<p align="justify">
<?php _e('for articles submissions, use the email: ','bovp'); ?><strong>conteudo@vivendoapalavra.com.br</strong>.<br /><br />
*<?php _e('&nbsp;Please only articles with permission of the author.','bovp'); ?>
</p>
 
<!--

Você também pode contribuir de outras formas, veja abaixo:

1) Curtindo e divulgando nossa página no Facebook.
2) Enviando artigos bíblicos exclusivos,  para postagem em nosso site.
3) Enviando sugestões para aperfeiçoamento do plugin, através do nosso site ou da nossa página no Facebook. 

* por favor, artigos apenas com a devida autorização do autor.

para envio de artigos, use o email

-->

</fieldset>
</div>

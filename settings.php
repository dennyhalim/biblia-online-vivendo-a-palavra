<?php 


if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {die(__('Access denied.','bovp')); }

require_once('functions.php');

$bovp_registred_versions = get_option('bovp_registred_versions');

if(isset($_REQUEST['bovp_install'])) {

  $bovp_install = $_REQUEST['bovp_install'];

  $bovp_version_selected = $bovp_registred_versions[$bovp_install];

  $bovp_version_info_explode = explode('|' , $bovp_version_selected);

  $bible_file = $bovp_version_info_explode[1];


  // install bible select table.

  import_sql_data($bible_file, $bovp_install);

 
echo "<script language=\"JavaScript\"> window.location=\"admin.php?page=biblia-online-vivendo-a-palavra/settings.php\";</script>";

}


?>


<div class="bovp_wrap">

<h2 class="bovp_h2"><?php _e('Online Bible v.'. BOVP_SYSTEM_VERSION . ' - Settings','bovp');?></h2>

<fieldset  class="bovp_fieldset">
<legend  class="bovp_legend"><?php _e('Consider a Donation','bovp'); ?></legend>

<p align="justify">
<?php _e("If you use Online Bible plugin and want to contribute to the project's maintenance, you can use the link below to make a donation.",'bovp'); ?>
          
</p>

<h2><strong><?php _e("Make a donation with PAGSEGURO"); ?></strong></h2>

<!-- INICIO FORMULARIO BOTAO PAGSEGURO -->

<form target="pagseguro" action="https://pagseguro.uol.com.br/checkout/v2/donation.html" method="post">

<input type="hidden" name="receiverEmail" value="bibliaonlinevp@vivendoapalavra.com.br" />

<input type="hidden" name="currency" value="BRL" />

<input type="image" src="https://p.simg.uol.com.br/out/pagseguro/i/botoes/doacoes/84x35-doar-azul.gif" name="submit" alt="Doe com PagSeguro - é rápido e seguro!" />

</form>

<!-- FINAL FORMULARIO BOTAO PAGSEGURO -->

<h2><strong><?php _e("Make a donation with PAYPAL"); ?></strong></h2>

<!-- INICIO FORMULARIO BOTAO PAYPAL-->

<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">

<input type="hidden" name="cmd" value="_s-xclick">

<input type="hidden" name="hosted_button_id" value="9KV25MLWLPKQN">

<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">

<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">

</form>

<!-- FINAL FORMULARIO BOTAO PAYPAL-->


</fieldset>

<fieldset class="bovp_fieldset">

<legend class="bovp_legend"><?php _e('Choose and Install the Bible version','bovp');?></legend>

<form action="admin.php" method="get">

      <input type="hidden" value="biblia-online-vivendo-a-palavra/settings.php" name="page">

      <select name="bovp_install">

        <?php

        if (BOVP_BIBLE_VERSION=='0') {

            echo "<option value=\"0\" selected >" . __('Not Installed','bovp') . "</option>";

        }
      

          foreach($bovp_registred_versions as $indice => $bovp_version) {

            $bovp_version_info = explode('|' , $bovp_version);

            echo "<option value=\"" . $indice . "\""; 

            if (BOVP_BIBLE_VERSION==$indice) {echo " selected";}

            echo ">" . $bovp_version_info[0] . "</option>";

          }

          echo "</select>";
          
          ?>

          <input type="submit" class="button-primary" value="<?php _e('Install','bovp') ?>" />

    </form>

  </fieldset>
    
<form method="post" action="options.php">

      <?php settings_fields( 'bovp_options' ); ?>

          
        <fieldset class="bovp_fieldset">
          <legend class="bovp_legend"><?php _e('Page where the online Bible will be displayed','bovp');?></legend>
          <?php bovp_options_select ('bovp_page', bovp_get_all_pages ()); ?>
        </fieldset>


        <fieldset class="bovp_fieldset">
          <legend class="bovp_legend"><?php _e('Source of the daily verse','bovp');?></legend>
      	     
             <select name="bovp_source_random_verse">
                <option value="0" <?php if (get_option('bovp_source_random_verse')==0) {echo 'selected';} ?> ><?php _e('All the Bilble','bovp') ?></option>
                <option value="1" <?php if (get_option('bovp_source_random_verse')==1) {echo 'selected';} ?> ><?php _e('Old Testament','bovp') ?></option>
                <option value="2" <?php if (get_option('bovp_source_random_verse')==2) {echo 'selected';} ?> ><?php _e('New Testament','bovp') ?></option>
                <option value="3" <?php if (get_option('bovp_source_random_verse')==3) {echo 'selected';} ?> ><?php _e('Psalms Book','bovp') ?></option>
              </select>
        </fieldset>
      	
        <fieldset class="bovp_fieldset">
          <legend class="bovp_legend"><?php _e('Search results to be displayed per page','bovp');?></legend>

            <input name="bovp_itens_per_page" value="<?php $bovp_itens_per_page = get_option('bovp_itens_per_page'); echo $bovp_itens_per_page; ?>" />
          
        </fieldset>


      <fieldset class="bovp_fieldset">
          <legend class="bovp_legend"><?php _e('Choose theme','bovp');?></legend>

            <select name="bovp_theme">
                <option value="default" <?php if (get_option('bovp_theme')=="default") {echo 'selected';} ?> ><?php _e('Default','bovp') ?></option>
            </select>

        </fieldset>

          		
           	
      <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Update Settings','bovp') ?>" /></p>

</form>


</div>
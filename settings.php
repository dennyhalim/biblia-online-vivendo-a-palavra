<?php  

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {die(__('Access denied.','bovp')); }

require_once('functions.php');

  if( isset($_REQUEST['bovp_install']) AND !isset($_REQUEST['settings-updated'])) {

          $bovp_install = $_REQUEST['bovp_install'];

            if ($bovp_install == get_option('bovp_version')) {

              $bovp_message = PluginShowMessage(__('This table is already installed in your database.', 'bovp'), false);
            
            } else {

              $bovp_text_install = PluginInsertData($bovp_install); 

              if($bovp_text_install[0]!=false) {
                  
                  $bovp_message = PluginShowMessage($bovp_text_install[1], false);
                  update_option('bovp_version', $bovp_install); 
                      
              } else { $bovp_message = PluginShowMessage($bovp_text_install[1], true); }

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

      <input type="hidden" value="biblia-online-vivendo-a-palavra/settings.php" name="page">

        <select name="bovp_install" id="bovp_install">

          <?php

          if (get_option('bovp_version') =='-1') {

              echo "<option value=\"-1\" selected>" . __('Not Installed','bovp') . "</option>";

          } else {echo "<option value=\"-1\">" . __('Not Installed','bovp') . "</option>";}
        

            foreach($bovp_versions_inf as $indice => $bovp_version) {

              echo "<option value=\"" . $indice . "\""; 

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
          <?php PluginSelectConstruct ('bovp_page', PluginGetAllPages ()); ?>
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
                <option value="default" <?php if (BOVP_THEME=="default") {echo 'selected';} ?> ><?php _e('Default','bovp') ?></option>
            </select>

        </fieldset>

          		
           	
      <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Update Settings','bovp') ?>" /></p>

</form>


</div>
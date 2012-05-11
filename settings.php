<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/pt_BR/all.js#xfbml=1&appId=219024078111031";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>


<?php

require_once('functions.php');?>

<style>
div.wrap fieldset {border:1px solid #CCC;margin:5px;padding:10px;max-width:600px;}
div.wrap legend {padding: 0px 3px 0px 3px;margin: 2px;border: solid #CCC 1px;font-size:0.9em;background-color: #e8e8e8;}
div.wrap div.message {border:1px solid #36C;background-color:#0080C0;color:#fff;padding:15px;}
</style>

<div class="wrap">
<h2><?php _e('Online Bible v.'. BOVP_VERSION . ' - Settings','bovp');?></h2>

<fieldset>
<legend><?php _e('Like in Facebook','bovp');?></legend>
<fb:like href="https://www.facebook.com/vivendoapalavra" send="true" width="500px" show_faces="false" font="tahoma"></fb:like>
</fieldset>

<fieldset>
<legend><?php _e('Database Status','bovp');?></legend>
    <?php 

	echo '<b>' . __('If the verse of the day is displayed below, your database has been installed correctly. Otherwise, reinstall the plugin.','bovp') . '</b><br>';
	echo "<p>";
	bovp_show_verse('show');
	echo"</p>";	
	
	
	?>
    
</fieldset>
    
    
    <form method="post" action="options.php">
    <?php settings_fields( 'bovp_options' ); ?>
    <p>
    <fieldset>
<legend>
    <?php _e('Page where the online Bible will be displayed','bovp');?></legend>
    <?php bovp_options_select ('bovp_page', bovp_get_all_pages ()); ?>
    </fieldset>
    </p>
    <p>
<fieldset>
<legend>
	<?php _e('Source of the daily verse','bovp');?></legend>
	<select name="bovp_source_random_verse">
    <option value="0" <?php if (get_option('bovp_source_random_verse')==0) {echo 'selected';} ?> ><?php _e('All the Bilble','bovp') ?></option>
    <option value="1" <?php if (get_option('bovp_source_random_verse')==1) {echo 'selected';} ?> ><?php _e('Old Testament','bovp') ?></option>
    <option value="2" <?php if (get_option('bovp_source_random_verse')==2) {echo 'selected';} ?> ><?php _e('New Testament','bovp') ?></option>
    <option value="3" <?php if (get_option('bovp_source_random_verse')==3) {echo 'selected';} ?> ><?php _e('Psalms Book','bovp') ?></option>
    
    <?php 
	/* for implementation in new version.
	
    <option value="4" <?php if (get_option('bovp_source_random_verse')==4) {echo 'selected';} ?> ><?php _e('A List','bovp') ?></option>
	
	*/ ?>
    
    </select>
    </fieldset>
	</p>
    		
	
    
    	
<p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Update Settings','bovp') ?>" />
</p>

</form>
</fieldset>
</div>
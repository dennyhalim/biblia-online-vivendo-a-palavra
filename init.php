<?php require_once('functions.php');?>


<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/pt_BR/all.js#xfbml=1&appId=219024078111031";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>

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
<h2><?php _e('Online Bible for Wordpress v.'. BOVP_VERSION,'bovp'); ?></h2>

<fieldset>
<legend><?php _e('Like in Facebook','bovp');?></legend>
<fb:like href="https://www.facebook.com/vivendoapalavra" send="true" width="500px" show_faces="false" font="tahoma"></fb:like>
</fieldset>

<fieldset>
<legend><?php _e('Informations','bovp');?></legend>

<p>

<p><?php _e('Plugin for implementation of Bible Online in your Wordpress blog. With it, you can make available the Word of God and bless your website\'s users. The plugin allows to consult all 66 books of the Holy Bible.','bovp'); ?>
<p/>
 
<p>Author:&nbsp;<a href="https://www.facebook.com/andrebrumsampaio">Andre Brum Sampaio</a></p>
<p><?php echo __('Version: ','bovp') .  BOVP_VERSION; ?></p>
<p><?php echo __('Author URI: ','bovp') . '&nbsp;<a href="http://www.vivendoapalavra.com.br/">http://www.vivendoapalavra.com.br/</a>' ?></p>
          
</p>



<h3><?php _e('In this new edition:','bovp'); ?></h3>
<p>

<?php _e('In this new edition we have fixed some errors reported by users and a few others we found along the way. We also replaced the Bible database, as the previous version contained incomplete verses. Besides that we changed some functions in order to improve the plugin\'s performance and we modified the standard layout of the Bible.','bovp'); ?><br /><br />    
</p>

<h3><?php _e('In the next edition:','bovp'); ?></h3>

<p>

<p><?php _e('For the next version we are preparing a number of innovations which will make your Bible much more attractive, with both visual and structural resources to help you in your daily use. Among the innovations are:','bovp'); ?><p>
<br />

- <?php _e('Inclusion of the King James version for users of english language;','bovp'); ?><br />
- <?php _e('Social Networks integration;','bovp'); ?><br />
- <?php _e('Implementation of themes to make easier layout alterations.','bovp');?>
          
</p>

</fieldset>
</div>

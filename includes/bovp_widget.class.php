<?php

// WIDGET EXTEND
class bovp_widget_search extends WP_Widget {

    function bovp_widget_search(){

		$widget_ops = array('description' =>  __('Use this widget to display the bible form search','bovp'));

		parent::WP_Widget(false,$name='BOVP Form Search',$widget_ops);

    }

  	/* Displays the Widget in the front-end */
    function widget($args, $instance){

		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Bible Search','bovp') : $instance['title']);

		$subtitle = apply_filters('widget_title', empty($instance['title']) ? __('Widget ','bovp') : false);

		if($subtitle){$title = '<b>' . $subtitle . '</b> ' . $title;}

		$widget  = $before_widget;

		if ( $title )

		$widget .= $before_title . $title . $after_title;
		$widget .= "<div class='bovp_search_widget bovp_clear'>";
		$widget .= "<form method='post' action='" . BOVP_URL ."' name='bovp_form_search' class='bovp_form_search bovp_clear'>";
		$widget .= "<input name='page_id' type='hidden' value='" . BOVP_PAGE . "'/>";
		$widget .= "<input name='bk' type='hidden' value=''/>";
		$widget .= "<input name='cp' type='hidden' value=''/>";		
		$widget .= "<input name='bk' type='hidden' value=''/>";
		$widget .= "<input name='s_type' type='hidden' value='s'/>";
		$widget .= "<input class='bovp_search_input' type='text' id='sh' name='sh' placeholder='" . __('Search','bovp') . "' >";
		
		$widget .= "<button class='bovp_search_widget bovp_button bovp_btn' type='submit'>";
		$widget .= (is_file(BOVP_THEME_FOLDER . 'img/search_ico.png')===true) ? sprintf('<img src="%sthemes/%s/img/search_ico.png" border="0">', BOVP_FOLDER, BOVP_THEME) : __('Send','bovp');
		$widget .= "</button>";
		
		$widget .= "</form>";
		$widget .= "</div>";
		$widget .= $after_widget;

		echo $widget;

	}

/*Saves the settings. */

    function update($new_instance, $old_instance){

		$instance = $old_instance;
		$instance['title'] = stripslashes($new_instance['title']);
		return $instance;

	}

/*Creates the form for the widget in the back-end. */
    function form($instance){

		//Defaults
		$instance = wp_parse_args( (array) $instance, array('title'=>__('Bible Search','bovp')) );
		$title = htmlspecialchars($instance['title']);
		# Title
		echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title:','bovp') . '</label><input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></p>';

	}

} // end class

class bovp_widget_index extends WP_Widget {

    function bovp_widget_index(){

		$widget_ops = array('description' =>  __('This widget show an index of books of Bible in your sidebar.','bovp'));

		parent::WP_Widget(false,$name='BOVP Bible Index',$widget_ops);

    }

  /* Displays the Widget in the front-end */
    function widget($args, $instance){

		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Bible Index','bovp') : $instance['title']);
		$subtitle = apply_filters('widget_title', empty($instance['title']) ? __('Widget ','bovp') : false);

		if($subtitle){$title = '<b>' . $subtitle . '</b> ' . $title;}

		echo $before_widget;

		if ( $title ) echo $before_title . $title . $after_title;

		echo "<form id='bovp_form_index' method='post' action='" . BOVP_URL . "' name='bovp_form_index' class='bovp_form_search bovp_clear'>";
		echo "<div class='bovp_clear bovp_selects_form'><input name='page_id' type='hidden' value='" . BOVP_PAGE . "'/>";
		echo "<input name='sh' type='hidden' value='0'/>";
		echo "<select name='bk' id='bovp_widget_book' class='bovp_select_widget'>" . book_select("var") . "</select>";
		echo "<select type='text' name='cp' id='bovp_widget_chapter' class='bovp_select_widget'></select></div>";
		echo "<button class='bovp_button_index bovp_btn";
		if(BOVP_FURL_STATS) echo ' ind_friendly';
		echo "' type='submit'>" . __('Go','bovp') . "</button>";
		echo "</form>";

		echo $after_widget;

	}

  /*Saves the settings. */

    function update($new_instance, $old_instance){

		$instance = $old_instance;
		$instance['title'] = stripslashes($new_instance['title']);
		return $instance;

	}

  /*Creates the form for the widget in the back-end. */
    function form($instance){

		//Defaults
		$instance = wp_parse_args( (array) $instance, array('title'=>__('Bible Search','bovp')) );
		$title = htmlspecialchars($instance['title']);

		# Title
		echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title:','bovp') . '</label><input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></p>';

	}

}// end class


class bovp_widget_verse extends WP_Widget {

    function bovp_widget_verse(){

		$widget_ops = array('description' => __('Use this widget to display the daily verse','bovp'));

		parent::WP_Widget(false,$name='BOVP Daily Verse',$widget_ops);

    }

  /* Displays the Widget in the front-end */
    function widget($args, $instance){

		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Daily Verse','bovp') : $instance['title']);
		$subtitle = apply_filters('widget_title', empty($instance['title']) ? __('Widget ','bovp') : false);

		if($subtitle){$title = '<b>' . $subtitle . '</b> ' . $title;}

		echo $before_widget;

		if ( $title ) echo $before_title . $title . $after_title;

		$verse_array = bovpShowVerse('var');

		echo $verse_array['vd'];

		echo bovpSharer('vd');

		echo $after_widget;


	}

  	/*Saves the settings. */
    function update($new_instance, $old_instance){

		$instance = $old_instance;

		$instance['title'] = stripslashes($new_instance['title']);

		return $instance;

	}

  	/*Creates the form for the widget in the back-end. */
    function form($instance){

		# Defaults
		$instance = wp_parse_args( (array) $instance, array('title'=>__('Daily Verse','bovp')) );

		$title = htmlspecialchars($instance['title']);

		# Title
		echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title:','bovp') . '</label><input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></p>';

	}

} 

?>
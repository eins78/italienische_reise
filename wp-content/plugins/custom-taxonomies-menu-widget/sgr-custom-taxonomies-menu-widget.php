<?php
/*
Plugin Name: Custom Taxonomies Menu Widget
Plugin URI: http://www.studiograsshopper.ch/custom-taxonomies-menu-widget/
Version: 1.1.1
Author: Ade Walker, Studiograsshopper
Author URI: http://www.studiograsshopper.ch
Description: Creates a simple menu of your custom taxonomies and their associated terms, ideal for sidebars. Highly customisable via widget control panel.
*/

/*  Copyright 2010-2011  Ade WALKER  (email : info@studiograsshopper.ch) */

/*	License information
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License 2 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

The license for this software can be found here: 
http://www.gnu.org/licenses/gpl-2.0.html
*/

/* 	About Version History info:
Bug fix:	means that something was broken and has been fixed
Enhance:	means code has been improved either for better optimisation, code organisation, compatibility with wider use cases
Feature:	means new user functionality has been added
*/

/* Version History

	1.1.1	- Bug fix: Removed debug code from sgr_ctmw_wp_version_check()
	
	1.1		- Feature: Added option to hide Taxonomy title
			- Feature: Added option to select whether or not to display terms as a hierarchy	

	1.0		- Feature: First public release

*/



/* ******************** DO NOT edit below this line! ******************** */


/***** Prevent direct access to the plugin *****/
if (!defined('ABSPATH')) {
	exit(__( "Sorry, you are not allowed to access this page directly."));
}



/***** Set constants for plugin *****/
define( 'SGR_CTMW_URL', WP_PLUGIN_URL.'/custom-taxonomies-menu-widget' );
define( 'SGR_CTMW_DIR', WP_PLUGIN_DIR.'/custom-taxonomies-menu-widget' );
define( 'SGR_CTMW_VER', '1.1.1' );
define( 'SGR_CTMW_DOMAIN', 'sgr_custom_taxonomies_menu_widget' );
define( 'SGR_CTMW_WP_VERSION_REQ', '2.9' );
define( 'SGR_CTMW_FILE_NAME', 'custom-taxonomies-menu-widget/sgr-custom-taxonomies-menu-widget.php' );



/***** Set up variables needed throughout the plugin *****/

// Internationalisation functionality
$sgr_ctmw_text_loaded = false;



/***** Include files *****/

// Admin-only files
if( is_admin() ) {
	require_once( SGR_CTMW_DIR . '/includes/sgr-ctmw-admin-core.php');
}


/***** Add filters and actions ********************/

if( is_admin() ) {
	/* Admin - Adds additional links in main Plugins page */
	// Function defined in sgr-ctmw-admin-core.php
	add_filter( 'plugin_row_meta', 'sgr_ctmw_plugin_meta', 10, 2 );
	
	/* Admin - Adds WP version warning on main Plugins screen */
	// Function defined in sgr-ctmw-admin-core.php
	add_action('after_plugin_row_custom-taxonomies-menu-widget/sgr-custom-taxonomies-menu-widget.php', 'sgr_ctmw_wp_version_check');

	/* Admin - Loads CSS for widget form */
	// Function defined in sgr-ctmw-admin-core.php
	add_action( 'admin_head', 'sgr_ctmw_loadcss_admin_head', 20 );
	
	/* Admin - Adds WP version warning to Plugin and Widgets screens */
	// Function defined in sgr-ctmw-admin-core.php
	add_action('admin_notices', 'sgr_ctmw_admin_notices', 10 );
}



/***** The widget *****/

add_action('widgets_init', 'register_sgr_custom_taxonomies_menu_widget');
function register_sgr_custom_taxonomies_menu_widget() {
	
	register_widget('SGR_Widget_Custom_Taxonomies_Menu');
}



class SGR_Widget_Custom_Taxonomies_Menu extends WP_Widget {

	function SGR_Widget_Custom_Taxonomies_Menu() {
		$widget_ops = array(
			'classname' => 'sgr-custom-taxonomies-menu',
			'description' => __('Display navigation for your custom taxonomies', SGR_CTMW_DOMAIN)
			);
		$this->WP_Widget('sgr-custom-taxonomies-menu', __('Custom Taxonomies Menu Widget', SGR_CTMW_DOMAIN), $widget_ops);
	}


	function widget($args, $instance) {
		extract($args);
		
		$instance = wp_parse_args( (array)$instance, array(
			'title' => '',
			'include' => array(),
			'orderby' => '',
			'show_tax' => '',
			'order' => '',
			'show_count' => '',
			'show_tax_title' => '',
			'show_hierarchical' => ''
		) );
		
		echo $before_widget;
		
		if ($instance['title']) echo $before_title . apply_filters('widget_title', $instance['title']) . $after_title;
			
		// Get all custom taxonomies
		$args=array(
  			'public'   => true,
  			'_builtin' => false
			);
			
		$output = 'objects'; // or names
		$operator = 'and'; // 'and' or 'or'
		$custom_taxonomies = get_taxonomies( $args, $output, $operator ); 
		
		// If no custom taxonomies exist...
		if( !$custom_taxonomies ) {
			echo "\n" . '<p>' . __('There are no registered custom taxonomies.', SGR_CTMW_DOMAIN) . '</p>' . "\n";
  			echo $after_widget;
  			return;
  		}
  			
  		// Display the taxonomies and terms
  		foreach ($custom_taxonomies as $custom_taxonomy ) {
  				
  			if( isset( $instance['show_tax_' . $custom_taxonomy->name]) && $instance['show_tax_' . $custom_taxonomy->name] == "true") {
  				
  				$args_list = array(
  					'taxonomy' => $custom_taxonomy->name, // Registered tax name
  					'title_li' => $instance['show_tax_title'] ? $custom_taxonomy->labels->name : '', // Tax nice name
  					'include' => implode(',', (array)$instance['include_' . $custom_taxonomy->name]),
  					'orderby' => $instance['orderby'],
  					'show_count' => $instance['show_count'],
  					'order' => $instance['order'],
  					'echo' => '0',
					'hierarchical' => $instance['show_hierarchical'] ? true : false,
  				 	);
  					 
  				$list = wp_list_categories($args_list);
  				
  				echo "\n" . '<ul>' . "\n";
  				
  				echo $list;
  				  				
  				echo "\n" . '</ul>' . "\n";
  			}
   		}     
				
		echo $after_widget;
	}


	function update($new_instance, $old_instance) {
		return $new_instance;
	}


	function form($instance) { 
		
		// Get all custom taxonomies - shame we have to do this again
		$args=array(
  			'public'   => true,
  			'_builtin' => false
			);
			
		$output = 'objects'; // or names
		$operator = 'and'; // 'and' or 'or'
		$custom_taxonomies = get_taxonomies( $args, $output, $operator );
		
		if( !$custom_taxonomies ) {
			echo __('There are no custom taxonomies registered. This widget only works with registered custom taxonomies.', SGR_CTMW_DOMAIN);
			return;	
		}
		
		
		// Old
		$instance = wp_parse_args( (array)$instance, array(
			'title' => '',
			'include' => array(),
			'orderby' => '',
			'show_tax' => '',
			'order' => '',
			'show_count' => '',
			'show_tax_title' => '',
			'show_hierarchical' => ''
		) );
		
		
		// Empty fallback (default)
		// The idea here is that all checkboxes will be pre-checked on first use of widget
		// Note: if all terms for a taxonomy are unchecked by user, the following will automatically re-check all terms
		// Therefore, to hide a taxonomy, uncheck the taxonomy, not the taxonomy's terms. Make sense?
		foreach( $custom_taxonomies as $custom_taxonomy ) {
		
			// Populate the 'include' terms checkboxes
			if( empty( $instance['include_' . $custom_taxonomy->name] ) ) {

				$args = array('hide_empty' => 0 );
				$terms = get_terms($custom_taxonomy->name, $args);
				
				foreach($terms as $term) {
					$instance['include_' . $custom_taxonomy->name][] = $term->term_id;
				}
			}
			// Populate the 'show_tax' taxonomy checkboxes
			if( empty( $instance['show_tax_' . $custom_taxonomy->name] ) ) {
				$instance['show_tax_' . $custom_taxonomy->name][] = "true";
			}
		}
		?>
		
		<div class="custom-taxonomies-menu-top">
			<p><?php _e('This widget produces a custom taxonomy navigation menu, ideal for use in sidebars.', SGR_CTMW_DOMAIN); ?></p>
			<p><a href="http://www.studiograsshopper.ch/custom-taxonomies-menu-widget/"><?php _e('Plugin homepage', SGR_CTMW_DOMAIN); ?></a> | 
			<a href="http://www.studiograsshopper.ch/custom-taxonomies-menu-widget/faq/"><?php _e('FAQ', SGR_CTMW_DOMAIN); ?></a> | 
			version <?php echo SGR_CTMW_VER; ?></p>
		</div>
		
		<div class="custom-taxonomies-menu-options">
			<h4>Configuration options</h4>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Menu Title', SGR_CTMW_DOMAIN); ?>:
				</label>
				<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" style="width:95%;" />
			</p>
		
			<p><?php _e('Choose the order by which you would like to display the terms within each taxonomy', SGR_CTMW_DOMAIN); ?>:</p>
		
			<p><select name="<?php echo $this->get_field_name('orderby'); ?>">
				<option style="padding-right:10px;" value="name" <?php selected('name', $instance['orderby']); ?>>Name</option>
				<option style="padding-right:10px;" value="ID" <?php selected('id', $instance['orderby']); ?>>ID</option>
				<option style="padding-right:10px;" value="slug" <?php selected('slug', $instance['orderby']); ?>>Slug</option>
				<option style="padding-right:10px;" value="count" <?php selected('count', $instance['orderby']); ?>>Count</option>
				<option style="padding-right:10px;" value="term_group" <?php selected('term_group', $instance['orderby']); ?>>Term Group</option>
			</select></p>
		
			<p><?php _e('Choose whether to display taxonomy terms in ASCending order(default) or DESCending order', SGR_CTMW_DOMAIN); ?>:</p>
			<p><select name="<?php echo $this->get_field_name('order'); ?>">
				<option style="padding-right:10px;" value="asc" <?php selected('ASC', $instance['order']); ?>>ASC (default)</option>
				<option style="padding-right:10px;" value="desc" <?php selected('DESC', $instance['order']); ?>>DESC</option>
			</select></p>
		
			<p>
				<label for="<?php echo $this->get_field_id('show_count'); ?>">
					<?php _e('Show post count?', SGR_CTMW_DOMAIN); ?>
				</label>
				<input type="checkbox" id="<?php echo $this->get_field_id('show_count'); ?>" name="<?php echo $this->get_field_name('show_count'); ?>" value="true" <?php checked('true', $instance['show_count']); ?> />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('show_tax_title'); ?>">
					<?php _e('Show Taxonomy Title?', SGR_CTMW_DOMAIN); ?>
				</label>
				<input type="checkbox" id="<?php echo $this->get_field_id('show_tax_title'); ?>" name="<?php echo $this->get_field_name('show_tax_title'); ?>" value="true" <?php checked('true', $instance['show_tax_title']); ?> />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('show_hierachical'); ?>">
					<?php _e('Show Terms as hierarchy?', SGR_CTMW_DOMAIN); ?>
				</label>
				<input type="checkbox" id="<?php echo $this->get_field_id('show_hierarchical'); ?>" name="<?php echo $this->get_field_name('show_hierarchical'); ?>" value="true" <?php checked('true', $instance['show_hierarchical']); ?> />
			</p>
			
		</div>
		
		<div class="custom-taxonomies-menu-lists-wrapper">
		
			<h4>Select taxonomies and terms</h4>

			<p><?php _e('Use the checklist(s) below to choose which custom taxonomies and terms you want to include in your Navigation Menu. To hide a taxonomy, uncheck the taxonomy name.', SGR_CTMW_DOMAIN); ?></p>
		
			<?php
			// Produce a checklist of terms for each custom taxonomy
			foreach ($custom_taxonomies as $custom_taxonomy ) :
			
				$checkboxes = '';
			
				$checkboxes = sgr_taxonomy_checklist($this->get_field_name('include_' . $custom_taxonomy->name), $custom_taxonomy, $instance['include_' . $custom_taxonomy->name]);
				?>
			
				<div class="custom-taxonomies-menu-list">
					<p>
						<input type="checkbox" id="<?php echo $this->get_field_id('show_tax_' . $custom_taxonomy->name); ?>" name="<?php echo $this->get_field_name('show_tax_' . $custom_taxonomy->name); ?>" value="true" <?php checked('true', $instance['show_tax_'.$custom_taxonomy->name]); ?> />
						<label for="<?php echo $this->get_field_id('show_tax_' . $custom_taxonomy->name); ?>" class="sgr-ctmw-tax-label">
							<?php echo $custom_taxonomy->label; ?>
						</label>
					</p>
				
					<ul class="custom-taxonomies-menu-checklist">
						<?php echo $checkboxes; ?>
					</ul>
				</div>
			
				<?php
			endforeach; ?>
		
		</div>
		
	<?php 
	}
}


/**
* Creates a taxonomy checklist based on wp_terms_checklist()
*
* Output buffering is used so that we can run a string replace after the checklist is created
*
* @param $name - string
* @param $custom_taxonomy - array - Array object for a custom taxonomy
* @param $selected - array - Selected terms within the taxonomy
*
* @since 1.0
*/
function sgr_taxonomy_checklist($name = '', $custom_taxonomy, $selected = array()) {
	$name = esc_attr( $name );

	$checkboxes = '';

	ob_start();
		
	$terms_args = array ('taxonomy' => $custom_taxonomy->name, 'selected_cats' => $selected, 'checked_ontop' => false);
		
	wp_terms_checklist(0, $terms_args);
	
	$checkboxes .= str_replace('name="tax_input['.$custom_taxonomy->name.'][]"', 'name="'.$name.'[]"', ob_get_clean());
			
	return $checkboxes;
}
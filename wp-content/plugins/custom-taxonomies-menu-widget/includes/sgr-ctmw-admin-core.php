<?php
/**
* Admin Core functions - all the stuff needed for backend only
*
* @copyright Copyright 2010-2011  Ade WALKER  (email : info@studiograsshopper.ch)
* @package custom_taxonomies_menu_widget
* @version 1.1.1
*
* @info Core Admin Functions called by various add_filters and add_actions:
* @info	- Internationalisation
* @info	- Plugin row meta
* @info	- WP Version check
*
* @since 1.0
*/

/* Prevent direct access to this file */
if (!defined('ABSPATH')) {
	exit( __('Sorry, you are not allowed to access this file directly.') );
}


/***** Internationalisation *****/

/**
* Function to load textdomain for Internationalisation functionality
*
* Loads textdomain if $dfcg_text_loaded is false
*
* Note: .mo file should be named custom-taxonomies-menu-widget-xx_XX.mo and placed in the CTMW plugin's languages folder.
* xx_XX is the language code, eg fr_FR for French etc.
*
* @global $sgr_ctmw_text_loaded bool defined in sgr-custom-taxonomies-menu-widget.php
* @uses load_plugin_textdomain()
* @since 1.0
*/
function sgr_ctmw_load_textdomain() {
	
	global $sgr_ctmw_text_loaded;
   	
	// If textdomain is already loaded, do nothing
	if( $sgr_ctmw_text_loaded ) {
   		return;
   	}
	
	// Textdomain isn't already loaded, let's load it
   	load_plugin_textdomain(SGR_CTMW_DOMAIN, false, dirname(plugin_basename(__FILE__)). '/languages');
   	
	// Change variable to prevent loading textdomain again
	$sgr_ctmw_text_loaded = true;
}





/**
* Function to load Admin CSS
*
* Hooked to 'admin_head' - only loads on widgets.php
*
* @global $pagenow - admin page name
* @since 1.0
*/
function sgr_ctmw_loadcss_admin_head() {

	global $pagenow;
	
	if($pagenow == 'widgets.php') {
	
	echo "\n" . '<!-- Custom Taxonomies Menu Widget plugin version ' . SGR_CTMW_VER . ' www.studiograsshopper.ch  Begin admin scripts -->' . "\n";
	echo '<link rel="stylesheet" href="' . SGR_CTMW_URL . '/includes/sgr-ctmw-ui-admin.css" type="text/css" />' . "\n";
	echo '<!-- End Custom Taxonomies Menu Widget plugin admin scripts -->' . "\n";
	}
}




/**
* Display Plugin Meta Links in main Plugin page in Dashboard
*
* Adds additional meta links in the plugin's info section in main Plugins Settings page
*
* Hooked to 'plugin_row_meta filter' so only works for WP 2.8+
*
* @param array $links Default links for each plugin row
* @param string $file plugins.php filehook
*
* @return array $links Array of customised links shown in plugin row after activation
* @since 1.0
*/
function sgr_ctmw_plugin_meta($links, $file) {
 
	// Check we're only adding links to this plugin
	if( $file == SGR_CTMW_FILE_NAME ) {
	
		// Create CTMW links
		$config_link = '<a href="http://www.studiograsshopper.ch/custom-taxonomies-menu-widget/" target="_blank">' . __('Configuration Guide', SGR_CTMW_DOMAIN) . '</a>';
		
		$faq_link = '<a href="http://www.studiograsshopper.ch/custom-taxonomies-menu-widget/faq/" target="_blank">' . __('FAQ', SGR_CTMW_DOMAIN) . '</a>';
		
		return array_merge(
			$links,
			array( $config_link, $faq_link )
		);
	}
 
	return $links;
}


/**
* Function to do WP Version check
*
* CMTW v1.0 requires WP 2.9+ to run.
* This function prints a warning message in the CTMW plugin row in the main Plugins page
*
* Hooked to 'after_action_row_$plugin' filter
*
* @since 1.0
* @updated 1.1.1 
*/	
function sgr_ctmw_wp_version_check() {
	
	$wp_valid = version_compare(get_bloginfo("version"), SGR_CTMW_WP_VERSION_REQ, '>=');
	
	$current_page = basename($_SERVER['PHP_SELF']);
	
	// Check we are on the right screen and version is not valid
	if( !$wp_valid && $current_page == "plugins.php" ) {
		
		$version_msg_start = '<tr class="plugin-update-tr"><td class="plugin-update" colspan="3">';
		$version_msg_start .= '<div class="update-message" style="background:#FFEBE8;border-color:#BB0000;">';
		$version_msg_end = '</div></td></tr>';
		
		$version_msg = sgr_ctmw_messages();
		
		echo $version_msg_start . $version_msg . $version_msg_end;
	}
}
	

/**
* Function to do WP Version check
*
* Prints a warning message at top of main Plugins page and main Widgets page
*
* Hooked to 'admin_notices'
*
* @since 1.0
*/
function sgr_ctmw_admin_notices() {
	
	$wp_valid = version_compare(get_bloginfo("version"), SGR_CTMW_WP_VERSION_REQ, '>=');
	
	$current_page = basename($_SERVER['PHP_SELF']);
	
	if( !$wp_valid && ( $current_page == "widgets.php" || $current_page == "plugins.php" ) ) {
		
		$version_msg_start = '<div class="error"><p>';
		$version_msg_end = '</p></div>';
		$version_msg = sgr_ctmw_messages();
		
		echo $version_msg_start . $version_msg . $version_msg_end;
	}
}


/**
* Function to display admin messages
*
* Used by sgr_ctmw_admin_notices() and sgr_ctmw_wp_version_check()
*
* @since 1.0
*/
function sgr_ctmw_messages() {
	
	if( !function_exists('wpmu_create_blog') ) {
		// We're in WP
		$output = '<strong>' . __('Warning! This version of Custom Taxonomies Menu Widget requires WordPress', SGR_CTMW_DOMAIN) . ' ' . SGR_CTMW_WP_VERSION_REQ . '+ ' . __('Please upgrade Wordpress to run this plugin.', SGR_CTMW_DOMAIN) . '</strong>';
		return $output;
			
	} else {
		// We're in WPMU
		$output = '<strong>' . __('Warning! This version of Custom Taxonomies Menu Widget requires WordPress', SGR_CTMW_DOMAIN) . ' ' . SGR_CTMW_WP_VERSION_REQ . '+ ' . __('Please contact your Site Administrator.', SGR_CTMW_DOMAIN) . '</strong>';
		return $output;
	}
}
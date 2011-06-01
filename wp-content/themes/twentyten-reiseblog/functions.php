<?php

/*
Register COSTUM POST TYPE ie. modify WP standard "posts" to fit the travel theme
nicked from http://codex.wordpress.org/Function_Reference/register_post_type#Example
*/

add_action('init', 'kte_reiseblog_init');

function kte_reiseblog_init() 
{
  $labels = array(
    'name' => _x('Reisepost', 'post type general name'),
    'singular_name' => _x('Reisepost', 'post type singular name'),
    'add_new' => _x('Neuer', 'Reisepost'),
    'add_new_item' => __('Neuer Reisepost'),
    'edit_item' => __('Reisepost bearbeiten'),
    'new_item' => __('Neuer Reisepost'),
    'view_item' => __('Reisepost anzeigen'),
    'search_items' => __('In Reisepost suchen'),
    'not_found' =>  __('Keine Reisepost gefunden'),
    'not_found_in_trash' => __('Keine Reisepost im Papierkorb gefunden'), 
    'parent_item_colon' => '',
    'menu_name' => 'Reisepost'

  );
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => array( 'slug' => 'reise', 'with_front' => false ),
    'menu_position' => 2,
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
    'supports' => array('title','editor','author','thumbnail', 'comments', 'revisions', 'trackbacks'),
    'taxonomies' => array('post_tag', 'category', 'kte_reise_orte', 'kte_reise_personen', 'kte_reise_arbeiten'),
  ); 
  register_post_type('kte_reisepost',$args);
}

//add filter to ensure the text Reiseblog is displayed when user updates a post 
add_filter('post_updated_messages', 'kte_reiseblog_updated_messages');
function kte_reiseblog_updated_messages( $messages ) {
  global $post, $post_ID;

  $messages['kte_reisepost'] = array(
    0 => '', // Unused. Messages start at index 1.
    1 => sprintf( __('Reisepost aktualisiert. <a href="%s">Ansehen</a>'), esc_url( get_permalink($post_ID) ) ),
    2 => __('Daten gespeichert.'),
    3 => __('Daten gel&ouml;scht.'),
    4 => __('Reisepost aktualisiert.'),
    /* translators: %s: date and time of the revision */
    5 => isset($_GET['revision']) ? sprintf( __('Reisepost aus Version %s wiederhergestellt.'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => sprintf( __('Reisepost ver&ouml;ffentlicht. <a href="%s">Ansehen</a>'), esc_url( get_permalink($post_ID) ) ),
    7 => __('Reisepost gespeichert.'),
    8 => sprintf( __('Reisepost gespeichert. <a target="_blank" href="%s">Vorschau ansehen</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    9 => sprintf( __('Reisepost geplant f&uuml;r: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Vorschau ansehen</a>'),
      // translators: Publish box date format, see http://php.net/date
      date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
    10 => sprintf( __('Reisepost-Entwurf aktualisiert. <a target="_blank" href="%s">Vorschau ansehen</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  );

  return $messages;
}

//display contextual help for Reisepost
add_action( 'contextual_help', 'kte_reiseblog_add_help_text', 10, 3 );

function kte_reiseblog_add_help_text($contextual_help, $screen_id, $screen) { 
  //$contextual_help .= var_dump($screen); // use this to help determine $screen->id
  if ('kte_reisepost' == $screen->id ) {
    $contextual_help =
      '<p>' . __('Reisepost-Hilfe:') . '</p>' .
      '<ul>' .
      '<li>' . __('Korrekten <b>Ort</b> angeben, nach M&ouml;glichkeit nach Region sortieren. Region nur angeben, wenn zutreffend, Hierachie kann auch sp&auml;ter sortiert werden.') . '</li>' .
      '<li>' . __('Erw&auml;hnte Personen angeben. Pr&uuml;fen, ob schon vorhanden um Doppelungen zu vermeiden.') . '</li>' .
      '<li>' . __('Erw&auml;hnte Arbeiten angeben. Pr&uuml;fen, ob schon vorhanden um Doppelungen zu vermeiden.') . '</li>' .
      '</ul>' .
      '<p><strong>' . __('For more information:') . '</strong></p>' .
      '<p>' . __('<a href="http://codex.wordpress.org/Posts_Edit_SubPanel" target="_blank">Edit Posts Documentation</a>') . '</p>' .
      '<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>' ;
  } elseif ( 'edit-kte_reisepost' == $screen->id ) {
    $contextual_help = 
      '<p>' . __('Dies ist der Hilfetext f&uuml;r den Reiseblog.') . '</p>' ;
  }
  return $contextual_help;
}

/*
Flushing Rewrite on Activation, Registerung the costum functions
from http://codex.wordpress.org/Function_Reference/register_post_type#Flushing_Rewrite_on_Activation
*/

/* gone for now */


/*
Create our costum TAXONOMY for the Reiseblog
from http://codex.wordpress.org/Function_Reference/register_taxonomy#Example
*/

//hook into the init action and call create_book_taxonomies when it fires
add_action( 'init', 'kte_reiseblog_taxonomies', 0 );

//create two taxonomies, genres and writers for the post type "book"
function kte_reiseblog_taxonomies() 
{
  // Add new taxonomy, make it hierarchical (like categories)
  $labels = array(
    'name' => _x( 'Orte', 'taxonomy general name' ),
    'singular_name' => _x( 'Ort', 'taxonomy singular name' ),
    'search_items' =>  __( 'Orte suchen' ),
    'all_items' => __( 'Alle Orte' ),
    'parent_item' => __( '&uuml;bergeordneter Ort/Region' ),
    'parent_item_colon' => __( '&uuml;bergeordneter Ort/Region:' ),
    'edit_item' => __( 'Ort bearbeiten' ), 
    'update_item' => __( 'Ort aktualisieren' ),
    'add_new_item' => __( 'Neuen Ort hinzuf&uuml;gen' ),
    'new_item_name' => __( 'Neuer Ort' ),
    'menu_name' => __( 'Orte' )
  ); 	

  register_taxonomy('kte_reise_orte',array('kte_reisepost'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'orte' )
  ));

  // PERSONEN: Add new taxonomy, NOT hierarchical (like tags)
  $labels = array(
    'name' => _x( 'Personen', 'taxonomy general name' ),
    'singular_name' => _x( 'Person', 'taxonomy singular name' ),
    'search_items' =>  __( 'Personen suchen' ),
    'popular_items' => __( 'H&auml;ufig genannte Personen' ),
    'all_items' => __( 'Alle Personen' ),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Person bearbeiten' ), 
    'update_item' => __( 'Person aktualisieren' ),
    'add_new_item' => __( 'Neue Person hinzuf&uuml;gen' ),
    'new_item_name' => __( 'Neue Person' ),
    'separate_items_with_commas' => __( 'Mehrere Personen mit Kommas trennen' ),
    'add_or_remove_items' => __( 'Personen hinzuf&uuml;gen oder l&ouml;schen' ),
    'choose_from_most_used' => __( 'Aus h&auml;ufig genannten Personen w&auml;hlen' ),
    'menu_name' => __( 'Personen' )
  ); 

  register_taxonomy('kte_reise_personen','kte_reisepost',array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'personen' )
  ));
  // Arbeiten: Add new taxonomy, NOT hierarchical (like tags)
  $labels = array(
    'name' => _x( 'Arbeiten', 'taxonomy general name' ),
    'singular_name' => _x( 'Arbeit', 'taxonomy singular name' ),
    'search_items' =>  __( 'Arbeiten suchen' ),
    'popular_items' => __( 'H&auml;ufig genannte Arbeiten' ),
    'all_items' => __( 'Alle Arbeiten' ),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Arbeit bearbeiten' ), 
    'update_item' => __( 'Arbeit aktualisieren' ),
    'add_new_item' => __( 'Neue Arbeit hinzuf&uuml;gen' ),
    'new_item_name' => __( 'Neue Arbeit' ),
    'separate_items_with_commas' => __( 'Mehrere Arbeiten mit Kommas trennen' ),
    'add_or_remove_items' => __( 'Arbeiten hinzuf&uuml;gen oder l&ouml;schen' ),
    'choose_from_most_used' => __( 'Aus h&auml;ufig genannten Arbeiten w&auml;hlen' ),
    'menu_name' => __( 'Arbeiten' )
  ); 

  register_taxonomy('kte_reise_arbeiten','kte_reisepost',array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'arbeiten' )
  ));
}

/*
make shure our costum post type SHOW up on Home, Feeds, Archives (Category, Tag, Author and Date), Search Pages...
from: http://justintadlock.com/archives/2010/02/02/showing-custom-post-types-on-your-home-blog-page

Bug: the is_archive causes normal posts to show up in the (admin) list of reiseposts
*/


add_filter( 'pre_get_posts', 'my_get_posts' );

function my_get_posts( $query ) {

	if ( ( is_home() && false == $query->query_vars['suppress_filters'] ) || is_author() || is_tag() || is_feed() || is_search() )
		$query->set( 'post_type', array( 'post', 'album', 'kte_reisepost' ) );

	return $query;
}

// Remove default twentyten header images
// source: http://aaron.jorb.in/blog/2010/07/remove-all-default-header-images-in-a-twenty-ten-child-theme/
function kte_reise_remove_twenty_ten_headers(){ 
	unregister_default_headers( array(
		'berries',
		'cherryblossom',
		'concave',
		'fern',
		'forestfloor',
		'inkwell',
		'path' ,
		'sunset')
	);
}
add_action( 'after_setup_theme', 'kte_reise_remove_twenty_ten_headers', 11 );


// adding our header images
function kte_reise_add_headers(){ 

//	$dir = '%s/../twentyten-m18/images/headers/';
	
	/* get content of directory */
	$dircontent = scandir($dir);

	/* filter content (crop thumbnails) */
//	$headers = array();

//	foreach($dircontent as $value){
//		if( (strcmp($value,'.') == 0) || (strcmp($value,'..') == 0) ) {
//		} // directory links


//		elseif( stristr($value,'-thumbnail.jpg') === TRUE  ){
//		} // thumbnails


//		else {
//			array_push($headers,$value);
//		} // all other images

//	}

	/* build register_default_headers */
//	foreach($headers 


	register_default_headers( array (
		'cropped-header_DSC_0758.jpg' => array (
			'url' => '%s/../twentyten-reiseblog/images/headers/cropped-header_DSC_0758.jpg',
			'thumbnail_url' => '%s/../twentyten-reiseblog/images/headers/cropped-header_DSC_0758-150x150.jpg',
			'description' => __( 'Ville civitelle', 'twentyten' )
		)
				
	) );
}
add_action( 'after_setup_theme', 'kte_reise_add_headers', 12 );


/*
Plugin Name: Twenty Ten Header Rotator
Plugin URI: http://hungrycoder.xenexbd.com/scripts/header-image-rotator-for-twenty-ten-theme-of-wordpress-3-0.html
Description: Rotate header images for Twenty Ten theme
Author: The HungryCoder
Version: 1.2
Author URI: http://hungrycoder.xenexbd.com
*/

/*
if(!is_admin()){
	add_filter('theme_mod_header_image','hr_rotate');
}


function hr_rotate(){
	require (ABSPATH.'/wp-admin/custom-header.php');
	$hr = new Custom_Image_Header(null);
	$hr->process_default_headers();
	$all_headers=array();
	$i=0;
	foreach (array_keys($hr->default_headers) as $header){
		$all_headers[$i]['url']= sprintf( $hr->default_headers[$header]['url'], get_template_directory_uri(), get_stylesheet_directory_uri() );
		//$all_headers[$i]['thumbnail']= sprintf( $hr->default_headers[$header]['thumbnail_url'], get_template_directory_uri(), get_stylesheet_directory_uri() );
		//$all_headers[$i]['description']= $hr->default_headers[$header]['description'];
		$i++;
	}

	//add any custom header
	$custom = get_option('mods_Twenty Ten');
	if(is_array($custom)){
		if(!empty($custom['header_image']))	$all_headers[]['url']= $custom['header_image'];
	}

	$cur_header = $all_headers[rand(0,count($all_headers)-1)];

	return $cur_header['url'];
}
*/
  

?>

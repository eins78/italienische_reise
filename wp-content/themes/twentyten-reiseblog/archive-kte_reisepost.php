<?php
/**
 * The template for displaying Tag Archive pages.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>

		<div id="container">
			<div id="content" role="main">

				<h1 class="page-title"><?php
					printf( __( 'Reise-Briefe', 'twentyten' ) ); ?>: <span>Archiv in chronologischer Reihenfolge</span>
					</h1>

<?php
/* Run the loop for the tag archive to output the posts
 * If you want to overload this in a child theme then include a file
 * called loop-reisepost.php and that will be used instead.
 */
 query_posts($query_string . "&order=ASC");
 get_template_part( 'loop', 'kte_reisepost' );
?>
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>

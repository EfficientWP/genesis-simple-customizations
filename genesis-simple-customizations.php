<?php
/*
Plugin Name: Genesis Simple Customizations
Plugin URI: http://efficientwp.com/plugins/genesis-simple-customizations
Description: Easily make certain customizations to your Genesis-powered site in the Genesis Theme Settings menu. You must be using the Genesis theme framework.
Version: 1.2
Author: Doug Yuen
Author URI: http://efficientwp.com
License: GPLv2
*/

class EWP_Genesis_Simple_Customizations {
	var $instance;
	
	function __construct() {
		if ( get_template() == 'genesis' ) {
			$this->instance =& $this;
			register_activation_hook( __FILE__, array( $this, 'ewp_gsc_activation' ) );
			add_action( 'init', array( $this, 'ewp_gsc_init' ) );
			add_action( 'genesis_init', array( $this, 'ewp_gsc_genesis_init' ), 20 );
			add_action( 'genesis_meta', array( $this, 'ewp_gsc_genesis_meta' ), 20 );
			add_action( 'wp_head', array( $this, 'ewp_gsc_wp_head' ), 20 );
		}
	}

	/********** PLUGIN ACTIVATION **********/
	function ewp_gsc_activation() {

	}

	/********** ADD METABOX TO THEME SETTINGS MENU **********/
	function ewp_gsc_init() {
		if ( is_admin() ) {
			add_action( 'genesis_theme_settings_metaboxes', array( $this, 'ewp_gsc_register_metabox' ) );
		}
	}

	/********** EXECUTE CUSTOMIZATIONS ON GENESIS_INIT HOOK **********/
	function ewp_gsc_genesis_init () {
		if ( genesis_get_option( 'ewp_gsc_remove_post_info' ) ) {
			remove_action( 'genesis_before_post_content', 'genesis_post_info' );
			remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
		}
		if ( genesis_get_option( 'ewp_gsc_remove_post_meta' ) ) {
			remove_action( 'genesis_after_post_content', 'genesis_post_meta' );
			remove_action( 'genesis_entry_footer', 'genesis_post_meta' );
		}
		if ( genesis_get_option( 'ewp_gsc_remove_footer' ) ) {
			remove_action( 'genesis_footer','genesis_do_footer' );
			remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
			remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );
		}
		if ( genesis_get_option( 'ewp_gsc_remove_edit_link' ) ) {
			add_filter( 'edit_post_link', '__return_false' );
		}
		if ( genesis_get_option( 'ewp_gsc_add_featured_image_support_to_pages' ) ) {
			add_theme_support( 'post-thumbnails', array( 'post', 'page' ) );
		}
		if ( genesis_get_option( 'ewp_gsc_display_featured_image_above_page_content_with_h1' ) ) {
			add_action( 'genesis_after_header', array( $this, 'ewp_gsc_display_featured_image_above_page_content_with_h1' ), 20 );
		}
		if ( genesis_get_option( 'ewp_gsc_display_featured_image_above_page_content_without_h1' ) ) {
			add_action( 'genesis_after_header', array( $this, 'ewp_gsc_display_featured_image_above_page_content_without_h1' ), 20 );
		}
		if ( genesis_get_option( 'ewp_gsc_display_featured_image_above_post_content_with_h1' ) ) {
			add_action( 'genesis_after_header', array( $this, 'ewp_gsc_display_featured_image_above_post_content_with_h1' ), 20 );
		}
		if ( genesis_get_option( 'ewp_gsc_display_featured_image_above_post_content_without_h1' ) ) {
			add_action( 'genesis_after_header', array( $this, 'ewp_gsc_display_featured_image_above_post_content_without_h1' ), 20 );
		}
		if ( genesis_get_option( 'ewp_gsc_display_category_descriptions' ) ) {
			add_action( 'genesis_before_loop', array( $this, 'ewp_gsc_display_category_descriptions' ), 20 );
		}
		if ( genesis_get_option( 'ewp_gsc_custom_search_box_text' ) != '' ) {
			add_filter( 'genesis_search_text', array( $this, 'ewp_gsc_custom_search_box' ), 20 );
		}
		if ( genesis_get_option( 'ewp_gsc_custom_search_button_text' ) != '' ) {
			add_filter( 'genesis_search_button_text', array( $this, 'ewp_gsc_custom_search_button' ), 20 );
		}
		if ( genesis_get_option( 'ewp_gsc_custom_google_fonts_text' ) != '' ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'ewp_gsc_custom_google_fonts' ), 20 );
		}
		if ( genesis_get_option( 'ewp_gsc_custom_more_tag_read_more_link_text' ) != '' ) {
			add_filter( 'the_content_more_link', array( $this, 'ewp_gsc_custom_more_tag_read_more_link' ), 20 );
		}
		if ( genesis_get_option( 'ewp_gsc_custom_show_content_limit_read_more_link_text' ) != '' ) {
			add_filter( 'get_the_content_more_link', array( $this, 'ewp_gsc_custom_show_content_limit_read_more_link' ), 20 );
		}
		if ( genesis_get_option( 'ewp_gsc_custom_after_post_text' ) != '' ) {
			add_action( 'genesis_after_entry_content', array( $this, 'ewp_gsc_custom_after_post' ), 20 );
			add_action( 'genesis_after_post_content', array( $this, 'ewp_gsc_custom_after_post' ), 20 );
		}
	}

	/********** EXECUTE CUSTOMIZATIONS ON GENESIS_META HOOK **********/
	function ewp_gsc_genesis_meta () {
		if ( genesis_get_option( 'ewp_gsc_remove_favicon' ) ) {
			remove_action( 'genesis_meta', 'genesis_load_favicon' );
		}
	}

	/********** EXECUTE CUSTOMIZATIONS ON WP_HEAD HOOK **********/
	function ewp_gsc_wp_head() {
		if ( get_template() == 'genesis' ) {
			if ( genesis_get_option( 'ewp_gsc_remove_subnav_from_top_of_header' ) ) {
				remove_action( 'genesis_before', 'genesis_do_subnav' );
			}
			if ( genesis_get_option( 'ewp_gsc_add_subnav_to_bottom_of_header' ) ) {
				add_action( 'genesis_after_header', 'genesis_do_subnav' );
			}
			if ( genesis_get_option( 'ewp_gsc_remove_favicon' ) ) {
				remove_action( 'wp_head', 'genesis_load_favicon' );
			}
		}
	}

	/********** OTHER FUNCTIONS **********/

	function ewp_gsc_display_featured_image_above_page_content_with_h1() {
		if ( is_page() && has_post_thumbnail() ) {
			remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
			add_filter( 'body_class', 'ewp_gsc_body_class_featured_with_h1' );
			$featured_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
			echo '<div class="featured-image-background" style="background: url(' .  $featured_image_url[0] . ') center center no-repeat #000000; height: ' . $featured_image_url[2] . 'px; -webkit-transform-style: preserve-3d; -moz-transform-style: preserve-3d; transform-style: preserve-3d;"><div class="featured-image-inner" style="position: relative; top: 50%; transform: translateY(-50%);">';
			echo '<h1 class="featured-image">' . get_the_title() . '</h1>';
			echo '</div></div>';
		}
		return;
	}
	function ewp_gsc_display_featured_image_above_page_content_without_h1() {
		if ( is_page() && has_post_thumbnail() ) {
			add_filter( 'body_class', 'ewp_gsc_body_class_featured_without_h1' );
			$featured_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
			echo '<div class="featured-image-background" style="background: url(' .  $featured_image_url[0] . ') center center no-repeat #000000; height: ' . $featured_image_url[2] . 'px; -webkit-transform-style: preserve-3d; -moz-transform-style: preserve-3d; transform-style: preserve-3d;"><div style="position: relative; top: 50%; transform: translateY(-50%);">';
			echo '</div></div>';
		}
		return;
	}
	function ewp_gsc_display_featured_image_above_post_content_with_h1( ) {
		if ( is_single() && has_post_thumbnail() ) {
			remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
			add_filter( 'body_class', 'ewp_gsc_body_class_featured_with_h1' );
			$featured_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
			echo '<div class="featured-image-background" style="background: url(' .  $featured_image_url[0] . ') center center no-repeat #000000; height: ' . $featured_image_url[2] . 'px; -webkit-transform-style: preserve-3d; -moz-transform-style: preserve-3d; transform-style: preserve-3d;"><div class="featured-image-inner" style="position: relative; top: 50%; transform: translateY(-50%);">';
			echo '<h1 class="featured-image">' . get_the_title() . '</h1>';
			echo '</div></div>';
		}
		return;
	}
	function ewp_gsc_display_featured_image_above_post_content_without_h1() {
		if ( is_single() && has_post_thumbnail() ) {
			add_filter( 'body_class', 'ewp_gsc_body_class_featured_without_h1' );
			$featured_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
			echo '<div class="featured-image-background" style="background: url(' .  $featured_image_url[0] . ') center center no-repeat #000000; height: ' . $featured_image_url[2] . 'px; -webkit-transform-style: preserve-3d; -moz-transform-style: preserve-3d; transform-style: preserve-3d;"><div style="position: relative; top: 50%; transform: translateY(-50%);">';
			echo '</div></div>';
		}
		return;
	}
	function ewp_gsc_body_class_featured_without_h1( $classes ) {
     	$classes[] = 'featured-without-h1';
     	return $classes;
	}
	function ewp_gsc_body_class_featured_with_h1( $classes ) {
     	$classes[] = 'featured-with-h1';
     	return $classes;
	}
	function ewp_gsc_display_category_descriptions( ) {
	    global $paged;
	    $category_description = category_description();
	    if ( ( $paged == 0 ) && ( $category_description != '' ) ) {
	        echo '<div class="entry-content category-description" itemprop="text">' . category_description() . '</div>';
	    }
	}

	/********** CUSTOM TEXT FUNCTIONS **********/
	function ewp_gsc_custom_more_tag_read_more_link( $text ) {
		$more_tag_read_more_link_text = genesis_get_option( 'ewp_gsc_custom_more_tag_read_more_link_text' );
		return '<a class="more-link" href="' . get_permalink() . '">' . esc_attr( $more_tag_read_more_link_text ) . '</a>';
	}
	function ewp_gsc_custom_show_content_limit_read_more_link( $text ) {
		$show_content_limit_read_more_link_text = genesis_get_option( 'ewp_gsc_custom_show_content_limit_read_more_link_text' );
		return '... <a class="more-link" href="' . get_permalink() . '">' . esc_attr( $show_content_limit_read_more_link_text ) . '</a>';
	}
	function ewp_gsc_custom_search_box( $text ) {
		$search_box_text = genesis_get_option( 'ewp_gsc_custom_search_box_text' );
		return esc_attr( $search_box_text );
	}
	function ewp_gsc_custom_search_button( $text ) {
		$search_button_text = genesis_get_option( 'ewp_gsc_custom_search_button_text' );
		return esc_attr( $search_button_text );
	}
	function ewp_gsc_custom_google_fonts( $text ) {
		$google_fonts_text = genesis_get_option( 'ewp_gsc_custom_google_fonts_text' );
		wp_enqueue_style( 'google-font', esc_url( $google_fonts_text ), array(), PARENT_THEME_VERSION );
	}
	function ewp_gsc_custom_after_post( $text ) {
		if ( is_single() ) {
			$after_post_text = genesis_get_option( 'ewp_gsc_custom_after_post_text' );
			echo '<div>' . do_shortcode( $after_post_text ) . '</div>';
		}
	}

	/********** CREATE PLUGIN MENU **********/
	function ewp_gsc_register_metabox( $_genesis_theme_settings_pagehook ) {
		add_meta_box('ewp-gsc', __( 'Genesis Simple Customizations', 'genesis-simple-customizations' ), array( $this, 'ewp_gsc_create_sitewide_metabox' ), $_genesis_theme_settings_pagehook, 'main', 'high');
	}
	function ewp_gsc_create_sitewide_metabox() {
		$ewp_gsc_list = array( 
			'remove_subnav_from_top_of_header' => 'Remove Subnav from Top of Header',
			'add_subnav_to_bottom_of_header' => 'Add Subnav to Bottom of Header',
			'remove_favicon' => 'Remove Genesis Favicon',
			'remove_post_info' => 'Remove Post Info',
			'remove_post_meta' => 'Remove Post Meta',
			'remove_footer' => 'Remove Footer',
			'remove_edit_link' => 'Remove "(Edit)" Link from Frontend',
			'add_featured_image_support_to_pages' => 'Add Featured Image Support to Pages',
			'display_featured_image_above_page_content_with_h1' => 'Display Featured Image Above Page Content with H1 Centered Inside',
			'display_featured_image_above_page_content_without_h1' => 'Display Featured Image Above Page Content with Nothing Inside',
			'display_featured_image_above_post_content_with_h1' => 'Display Featured Image Above Post Content with H1 Centered Inside',
			'display_featured_image_above_post_content_without_h1' => 'Display Featured Image Above Post Content with Nothing Inside',
			'display_category_descriptions' => 'Display Category Descriptions Above Category Archives',
			'custom_search_box' => 'Custom Search Box Text',
			'custom_search_button' => 'Custom Search Button Text',
			'custom_more_tag_read_more_link' => 'Custom More Tag "Read More" Link',
			'custom_show_content_limit_read_more_link' => 'Custom Show Content Limit "Read More" Link',
			'custom_google_fonts' => 'Custom Google Fonts URL (e.g. http://fonts.googleapis.com/css?family=Lato%3A400)',
			'custom_after_post' => 'Custom After Post Code (Shortcodes Allowed)',
		);
		echo '<table cellspacing="10">';
		foreach( $ewp_gsc_list as $customization => $description ) {
			echo '<tr>';
			if ( strpos( $customization, 'custom_' ) !== FALSE ) {
				echo '<td width="50%"><label for="' . GENESIS_SETTINGS_FIELD . '[ewp_gsc_' . $customization . '_text]" >' . $description . '</label></td><td width="50%"><input type="text" name="' . GENESIS_SETTINGS_FIELD . '[ewp_gsc_' . $customization . '_text]" id="' . GENESIS_SETTINGS_FIELD . '[ewp_gsc_' . $customization . '_text]" size="40" value="' . genesis_get_option( 'ewp_gsc_' . $customization . '_text' ) . '" /></td>';
			} else {
				echo '<td colspan="2"><input type="checkbox" name="' . GENESIS_SETTINGS_FIELD . '[ewp_gsc_' . $customization . ']" id="' . GENESIS_SETTINGS_FIELD . '[ewp_gsc_' . $customization . ']" value="1" ' . checked( 1, genesis_get_option( 'ewp_gsc_' . $customization ), false ) . ' />&nbsp;&nbsp;&nbsp;<label for="' . GENESIS_SETTINGS_FIELD . '[ewp_gsc_' . $customization . ']">' . $description . '</label></td>';
			}
			echo '</tr>';
		}
		echo '</table>';
	}
}
new EWP_Genesis_Simple_Customizations;
?>
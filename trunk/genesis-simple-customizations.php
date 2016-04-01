<?php
/*****
Plugin Name: Easy Genesis (formerly Genesis Simple Customizations)
Plugin URI: http://efficientwp.com/plugins/easy-genesis
Description: Easily make certain customizations to your Genesis-powered site in the Genesis Theme Settings menu. You must be using the Genesis theme framework.
Version: 2.0
Author: Doug Yuen
Author URI: http://efficientwp.com
License: GPLv2
*****/

$egwp_version = '2.0';

/***** BASIC SECURITY *****/

defined( 'ABSPATH' ) or die( 'Unauthorized Access!' );

/***** BACK-END HOOKS *****/

register_activation_hook( __FILE__, 'egwp_activation' );
register_deactivation_hook( __FILE__, 'egwp_deactivation' );
add_action ( 'init', 'egwp_init' );

/***** ADD MENU BUTTONS DURING ADMIN MENU RENDERING *****/

add_action( 'admin_menu', 'egwp_add_menus' ); 

/***** REGISTER META SETTINGS FIELDS, SCRIPTS, CSS *****/

add_action( 'admin_init', 'egwp_register_settings' ); 

/***** REGISTER ADMIN PROCESSING CALLS FOR IMPORT/EXPORT SETTING FEATURE *****/

add_action( 'admin_init', 'egwp_process_import_export' );

/***** REGISTER ADMIN NOTICES FUNCTION *****/

add_action( 'admin_notices', 'egwp_admin_notice' ); 
$egwp_notices = '';
$egwp_errors = '';

/***** AJAX CALLS *****/

add_action( 'wp_ajax_egwp_set_current_tab', 'egwp_set_current_tab' );
add_action( 'wp_ajax_nopriv_egwp_set_current_tab', 'egwp_set_current_tab' );

/***** CHANGE THE WP MEDIA UPLOADER'S TEXT "INSERT INTO POST" TO "USE THIS IMAGE" *****/

add_filter( 'attribute_escape', 'egwp_change_insert_post_text', 10, 2 );
function egwp_change_insert_post_text( $safe_text, $text ) {
    return str_replace(__('Insert into Post'), __('Use this image'), $text );
}

/***** ADD FRONT-END HOOKS *****/

if ( get_template() == 'genesis' ) {
	add_action( 'genesis_init', 'egwp_genesis_init' , 20 );
	add_action( 'genesis_meta', 'egwp_genesis_meta' , 20 );
	add_action( 'wp_head', 'egwp_wp_head' , 20 );
	
	if ( current_theme_supports( 'post-formats' ) ) {
		add_action( 'genesis_before_post', 'egwp_title_toggle', 20 );
		add_action( 'genesis_before_entry', 'egwp_title_toggle', 20 );
	} else {
		add_action( 'genesis_before', 'egwp_title_toggle' );
	}
}

/***** ON ACTIVATION *****/

function egwp_activation() {
	if ( get_template() != 'genesis' ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( 'This plugin requires the Genesis theme/framework.' );
	}	
	egwp_upgrade_check();
}

function egwp_init() {
	define( 'egwp_plugin_active', TRUE );
}	

/***** ON DEACTIVATION *****/

function egwp_deactivation () {

	if ( is_plugin_active( 'easy-genesis-extras/easy-genesis-extras.php' ) ) {
		add_action('update_option_active_plugins', 'egwp_deactivation_extras');
	}
	if ( is_plugin_active( 'easy-genesis-blog/easy-genesis-blog.php' ) ) {
		add_action( 'update_option_active_plugins', 'egwp_deactivation_blog');
	} 
	if ( is_plugin_active( 'easy-genesis-comments/easy-genesis-comments.php' ) ) {
		add_action( 'update_option_active_plugins', 'egwp_deactivation_comments');
	} 
	if ( is_plugin_active( 'easy-genesis-pages/easy-genesis-pages.php' ) ) {
		add_action( 'update_option_active_plugins', 'egwp_deactivation_pages');
	} 
}

/***** ADD MENUS TO ADMIN BAR *****/

function egwp_add_menus() {
	$handle = add_menu_page( 'Easy Genesis', 'Easy Genesis', 'edit_themes', 'egwp_easy_genesis', 'egwp_main_page_callback', 'dashicons-admin-generic', '58.9950000000121' );
	/***** MAYBE ADD A TOOLBAR BUTTON, COULDNT GET IT TO SHOW ON THE FRONT END - ONLY SHOWS ON ADMIN PAGES - CONFLICT W/ MULTISITE? *****/
	//add_action( 'wp_before_admin_bar_render', 'egwp_toolbar_button' );
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'egwp_plugin_action_links' );
}

function egwp_plugin_action_links( $links ) {
   $links[] = '<a href="'. admin_url() . '?page=egwp_easy_genesis' .'">Settings</a>';
   return $links;
}

function egwp_toolbar_button() {
	global $wp_admin_bar;
	$url = admin_url() . '?page=egwp_easy_genesis';
	
	$args = array(
		'id'    => 'egwp',
		'title' => 'Easy Genesis',
		'href'  => $url,
		'meta'  => array( 'class' => 'egwp-toolbar' )
	);
	$wp_admin_bar->add_menu( $args );
}

/***** REGISTSER THE SETTINGS BOXES AND FIELDS *****/

function egwp_register_settings() {

	/***** REGISTSER JS AND CSS *****/
	
	wp_register_style( 'egwp_admin_stylesheet', plugins_url( 'includes/admin.css', __FILE__ ) );
	wp_register_script( 'egwp_admin_js', plugins_url( 'includes/admin.js', __FILE__ ) );
			
	/***** ADD AND REGISTER SETTINGS BOXES AND FIELDS *****/
	
	add_settings_section( 'egwp_basic_setting_section', 'Basic Settings', 'egwp_section_callback', 'egwp_main_settings_page' );
	
	/***** MAKE SURE OUR ARRAY EXISTS *****/
	
	if ( !get_option( 'egwp_option_array' ) ) {
		add_option( 'egwp_option_array' );
	}

	/***** SET UP ARRAY THAT HOLDS OPTIONS AND DESCRIPTIONS *****/
	
	//$array egwp_options_array = [ str 'setting', str 'friendlyText', str/fn 'fn_callBack', str 'settingsArea' ]
	
	$egwp_options_array = array(	
		/***** MAIN TAB *****/
		array( 'remove_edit_link', 'Remove "(Edit)" Link from Frontend', 'egwp_checkbox_callback', 'egwp_basic_setting_section' ),
		array( 'featured_image_pages', 'Display Featured Image on Pages', 'egwp_radio_featured_image_callback', 'egwp_basic_setting_section' ),
		array( 'featured_image_posts', 'Display Featured Image on Posts', 'egwp_radio_featured_image_callback', 'egwp_basic_setting_section' ),
		array( 'custom_search_box_text', 'Custom Search Box Text', 'egwp_text_box_callback', 'egwp_basic_setting_section', 'Search this website ...' ),	
		array( 'custom_search_button_text', 'Custom Search Button Text', 'egwp_text_box_callback', 'egwp_basic_setting_section', 'Search' ),
		array( 'custom_read_more_text', 'Custom "Read More" Text', 'egwp_text_box_callback', 'egwp_basic_setting_section', 'Read more...' ),
		array( 'custom_after_post_text', 'Custom After Post Code (shortcodes allowed)', 'egwp_text_box_callback', 'egwp_basic_setting_section' ),
		array( 'remove_post_info', 'Remove Post Info (above content)', 'egwp_checkbox_callback', 'egwp_basic_setting_section' ),
		array( 'remove_post_meta', 'Remove Post Meta (below content)', 'egwp_checkbox_callback', 'egwp_basic_setting_section' ),	
		array( 'remove_footer', 'Remove Footer Entirely', 'egwp_checkbox_callback', 'egwp_basic_setting_section' ),		
		array( 'remove_subnav_from_top_of_header', 'Remove Secondary Navigation from Top of Header', 'egwp_checkbox_callback', 'egwp_basic_setting_section' ),
		array( 'add_subnav_to_bottom_of_header', 'Add Secondary Navigation to Bottom of Header', 'egwp_checkbox_callback', 'egwp_basic_setting_section' ),
		array( 'remove_favicon', 'Remove Genesis Favicon', 'egwp_checkbox_callback', 'egwp_basic_setting_section' ),	
		array( 'custom_favicon_url', 'Custom Favicon (URL)', 'egwp_media_library_callback', 'egwp_basic_setting_section' ),	
		array( 'custom_gravatar_url', 'Custom Default Gravatar (URL)', 'egwp_media_library_callback', 'egwp_basic_setting_section' ),				
		array( 'custom_google_fonts_text', 'Custom Google Fonts (URL)', 'egwp_text_box_callback', 'egwp_basic_setting_section' ),	
		array( 'add_featured_image_size_array', 'Add Custom Image Sizes', 'egwp_custom_image_sizes_callback', 'egwp_basic_setting_section' ),
	);
	
	/***** FILTER SO OTHER EXTENSIONS CAN HOOK IN SETTINGS *****/
	
	$egwp_options_array = apply_filters( 'egwp_option_filter', $egwp_options_array );
			
	/***** ADD OPTIONS FOR EACH TYPE IN THE ARRAYS ABOVE
		$array egwp_options_array = [ str 'setting', str 'friendlyText', str/fn 'fn_callBack', str 'settingsArea' ]
		add_settings_field( $id, $title, $callback, $page, $section, $args );
	*****/
	
	foreach ( $egwp_options_array as $option ) {
		$setting_name = $option[0];
		$friendly_text = $option[1];
		$callback_function = $option[2];
		$setting_heading = $option[3];
		add_settings_field( "egwp_option_array[$setting_name]", $friendly_text, $callback_function, 'egwp_main_settings_page', $setting_heading, $option);
	}	
	
	register_setting( 'egwp_main_settings', 'egwp_option_array' );
}

/***** SET UP INDIVIDUAL FIELDS -- BY TYPE *****/

/***** RENDER CHECKBOX OPTIONS *****/

function egwp_checkbox_callback( $args ) {
	$option_name = $args[0];
	$options = get_option( 'egwp_option_array' );
	
	/***** SET TO DEFAULT IF THIS OPTION HAS NEVER BEEN SET *****/
	
	if ( !isset ( $options[ $option_name ] ) ) {
		$options[ $option_name ] = 0;
	}

	$html = "<div class='egwp_checkbox'><input type='checkbox' id='egwp_option_array[$option_name]' name='egwp_option_array[$option_name]' value='1' " . checked( 1, $options[ $option_name ], false ) . '/>'; 
	$html .= "<label for='egwp_option_array[$option_name]'></label></div>"; 
	echo $html;
}

/***** RENDER TEXT BOX OPTIONS *****/

function egwp_text_box_callback( $args ) {
	$option_name = $args[0];
	$placeholder = empty( $args[4] ) ? '' : $args[4];
	$options = get_option( 'egwp_option_array' );
	
	/***** SET TO DEFAULT IF THIS OPTION HAS NEVER BEEN SET *****/
	
	if ( !isset ( $options[ $option_name ] ) ) {
		$value = $options[ $option_name ] = '';
	} else {
		$value = esc_textarea( $options[ $option_name ] );
	}
	$html = "<input type='text' class='egwp_text' id='egwp_option_array[$option_name]' name='egwp_option_array[$option_name]' value='$value' placeholder='$placeholder' />"; 
	//$html .= "<label for='egwp_option_array[$args[0]]'> " . $args[1] . '</label>'; 
	echo $html;
}

function egwp_editor_callback( $args ) {
	$option_name = $args[0];
	$options = get_option( 'egwp_option_array' );
	
	/***** SET TO DEFAULT IF THIS OPTION HAS NEVER BEEN SET *****/
	
	if ( !isset ( $options[ $option_name ] ) ) {
		$value = $options[ $option_name ] = '';
	} else {
		$value = esc_textarea( $options[ $option_name ] );
	}
		
	$editor_id = "$option_name";
	
	$settings = array( 
		'textarea_name' => "egwp_option_array[$option_name]",
		'textarea_rows' => 5,
		'editor_class' => 'egwp_tinymce'
	);

	wp_editor( $value, $editor_id, $settings );
}

/***** RENDER NUMBER INPUT OPTIONS *****/

function egwp_number_callback( $args ) {
	$option_name = $args[0];
	$placeholder = empty( $args[4] ) ? '' : $args[4];
	$options = get_option( 'egwp_option_array' );
	
	/***** SET TO DEFAULT IF THIS OPTION HAS NEVER BEEN SET *****/
	
	if ( !isset ( $options[ $option_name ] ) ) {
		$value = $options[ $option_name ] = $placeholder;
	} else {
		$value = intval( $options[ $option_name ] );
	}
	
	$value == 0 ? $value = '' : '';

	$html = "<input type='number' class='egwp_number' id='egwp_option_array[$option_name]' name='egwp_option_array[$option_name]' value='$value' placeholder='$placeholder' min='1' max='10000' /> px"; 
	//$html .= "<label for='egwp_option_array[$args[0]]'> " . $args[1] . '</label>'; 
	echo $html;
}

/***** RENDER MEDIA UPLOAD CALLBACK (WP MEDIA LIBRARY) *****/

function egwp_media_library_callback( $args ) {
	$option_name = $args[0];
	$options = get_option( 'egwp_option_array' );
	
	/***** SET TO DEFAULT IF THIS OPTION HAS NEVER BEEN SET *****/
	
	if ( !isset ( $options[ $option_name ] ) ) {
		$value = $options[ $option_name ] = '';
	} else {
		$value = $options[ $option_name ];
	}
	
	$html = "<input type='text' class='egwp_text' id='egwp_option_array[$option_name]'  name='egwp_option_array[$option_name]' value='$value'/>";
	$html .= "<input class='egwp-upload-button button' type='button' value='Upload Image' />";
	echo $html;
}

/***** RENDER MULTISELECTS (OF GENESIS HOOKS) *****/

function egwp_multiselect_callback ( $args ) {
	$option_name = $args[0];
	$options = get_option( 'egwp_option_array' );
	
	$egwp_basic_genesis_hooks = array ( 
		'genesis_header' => 'Header',
		'genesis_after_header' => 'After Header',
		'genesis_before_loop' => 'Before The Loop',
		'genesis_before_content' => 'Before Content',
		'genesis_entry_footer' => 'Entry Footer',
		'genesis_after_entry' => 'After Entry',
		'genesis_before_footer' => 'Before Footer',
		'genesis_after_footer' => 'After Footer',
	);
	
	if ( isset ( $options[ $option_name ] ) ) {
		$selected_options = $options[ $option_name ];	
	} else {
		$selected_options = array();
	}
	
	$size = count( $egwp_basic_genesis_hooks );
	$html = "<select class='egwp_select' name='egwp_option_array[$option_name][]' id='egwp_option_array[$option_name]' multiple='multiple' size='$size'>";
			
	foreach ( $egwp_basic_genesis_hooks as $hook => $text ) { 
		$selected = in_array( $hook, $selected_options ) ? 'selected' : '';
		$html .= "<option value='$hook' $selected>$text</option>";
	}					

	$html .= '</select>';
	$html .= "<br><label for='egwp_option_array[$option_name][]'><i>Hold control key to select more than one.</i></label>";
	echo $html;
}

/***** RENDER RADIO OPTIONS (OF GENESIS LAYOUTS) *****/

function egwp_radio_layout_callback( $args ) {

	$option_name = $args[0];
	$options = get_option( 'egwp_option_array' );
	
	/***** SET TO DEFAULT IF THIS OPTION HAS NEVER BEEN SET *****/
	
	if ( !isset ( $options[ $option_name ] ) ) {
		$options[ $option_name ] = 'default';
	}
	
	$theme_root = get_theme_root();
	$path = $theme_root . '/genesis/lib/admin/images/layouts/';
	$plugin_dir = plugin_dir_path( __FILE__ );
	
	$layouts = array(
		'default'  => $plugin_dir . 'assets/default.png',
		'content-sidebar' => $path . 'cs.gif',
		'sidebar-content' => $path . 'sc.gif',
		'content-sidebar-sidebar' => $path . 'css.gif',
		'sidebar-sidebar-content' => $path . 'ssc.gif',
		'sidebar-content-sidebar' => $path . 'scs.gif',
		'full-width-content' => $path . 'c.gif',		
	);
	
	$html = '<fieldset class="genesis-layout-selector">';
	
	foreach ( $layouts as $layout => $img ) {
		$checked = $options[ $option_name ] == $layout ? 'checked' : '';
		if ( $checked == 'checked' ) {
			$html .= "<label class='egwp-layout-label-selected egwp-layout-label'><img src='$img'></label>";
		} else {
			$html .= "<label class='egwp-layout-label'><img src='$img'></label>";
		}
		$html .= "<input class='egwp_radio' type='radio' name='egwp_option_array[$option_name]' id='egwp_option_array[$option_name]' value='$layout' $checked>";
	}
	$html .= '</fieldset>';
	echo $html;
}

/***** RENDER RADIO OPTIONS (OF FEATURED IMAGE OPTIONS) *****/

function egwp_radio_featured_image_callback( $args ) {
	$option_name = $args[0];
	$options = get_option( 'egwp_option_array' );
	
	/***** SET TO DEFAULT IF THIS OPTION HAS NEVER BEEN SET *****/
	
	if ( !isset ( $options[ $option_name ] ) ) {
		$options[ $option_name ] = '';
	}
	
	$theme_root = get_theme_root();
	$path = $theme_root . '/genesis/lib/admin/images/layouts/';
	$plugin_dir = plugin_dir_path( __FILE__ );
	$asset_dir = $plugin_dir . 'assets/';
	
	$layouts = array(
		''  => $asset_dir . 'disabled.png',
		'top' => $asset_dir . 'top.png',
		'top-center' => $asset_dir . 'top-center.png',
		'top-top' => $asset_dir . 'top-top.png',
		'sidebar' => $asset_dir . 'sidebar.png',
		'sidebar-center' => $asset_dir . 'sidebar-center.png',
		'sidebar-top' => $asset_dir . 'sidebar-top.png',		
	);
	
	$html = '<fieldset class="genesis-layout-selector">';
	
	foreach ( $layouts as $layout => $img ) {
	
		$checked = $options[ $option_name ] == $layout ? 'checked' : '';
		if ( $checked == 'checked' ) {
			$html .= "<label class='egwp-layout-label-selected egwp-layout-label egwp-layout-label-big'><img src='$img'></label>";
		} else {
			$html .= "<label class='egwp-layout-label egwp-layout-label-big'><img src='$img'></label>";
		}
								
		$html .= "<input class='egwp_radio' type='radio' name='egwp_option_array[$option_name]' id='egwp_option_array[$option_name]' value='$layout' $checked>";
	}
	
	$html .= '</fieldset>';
	echo $html;
}

/***** RENDER CUSTOM IMAGE SIZE SAVE BOX *****/

function egwp_custom_image_sizes_callback( $args ) {
	$option_name = $args[0];
	$options = get_option( 'egwp_option_array' );
	
	if ( isset ( $options[ $option_name ] ) ) {
		$selected_options = $options[ $option_name ];	
	} else {
		$selected_options = array();
	}

	$html = "<input name='egwp_add_image_width' type='number' min='1' max='10000' id='egwp_add_image_width' placeholder='Width (px)'/> x ";
	$html .= "<input name='egwp_add_image_height' type='number' min='1' max='10000' id='egwp_add_image_height' placeholder='Height (px)'/>";
	$html .= "<input name='egwp_add_image_type' type='button' id='egwp_add_image_type' class='button-primary' value='Add' />";
	$html .= "<br><br><div id='egwp_custom_image_sizes'></div>";
	$html .= "<br><select name='egwp_option_array[$option_name][]' id='egwp_option_array[$option_name]' multiple='multiple' hidden readonly'>";
		
	foreach ( $selected_options as $option ) { 
		$html .= "<option value='$option' selected>$option</option>";
	}					

	$html .= '</select>';
	echo $html;
}

/***** CONFIGURE WHAT SHOULD OUTPUT AT THE TOP OF THIS SECTION (MAIN SECTION) *****/

function egwp_section_callback( $args ) {
	echo "<input type='hidden' id='" . $args[ 'id' ] . "'>";
} 

/***** CREATE MAIN SETTING PAGE *****/

function egwp_main_page_callback() {
	wp_enqueue_style( 'egwp_admin_stylesheet' );
	wp_enqueue_script( 'egwp_admin_js' );
	wp_enqueue_script('jquery');
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');

	$current_tab = get_user_option( 'egwp_current_tab', get_current_user_id() );

	/***** SEND CURRENT TAB AND AJAX URL DATA TO JS *****/

	$js_data = array(	
		'ajax_url'  => admin_url('admin-ajax.php'),	
		'current_tab' => empty( $current_tab ) ? '' : $current_tab,
		'user_id' => get_current_user_id(),
	);

	wp_localize_script( 'egwp_admin_js', 'egwp_data', $js_data );
	?>
	<div id='egwp_main_page'>
		<h1>Easy Genesis</h1>
		<form method='post' action='options.php' id='egwp_main_form' style='display:none;'>
	
			<input name='submit' type='submit' id='submit' class='button-primary' value='<?php _e('Save Changes') ?>' />

			<h2 class='nav-tab-wrapper'>
				<a class='nav-tab nav-tab-active' id='egwp_basic_setting_section_nav' href='#'>Main</a><?php do_action( 'egwp_menu' ); ?>
				<a class='nav-tab' id='egwp_addons_section_nav' href='#'>Extensions</a>
				<a class='nav-tab' id='egwp_import_export_setting_section_nav' href='#'>Import/Export</a>
			</h2>
			
			<!-- IMPORT/EXPORT SETTINGS 'PAGE' -->

			<h2>Import/Export Settings</h2>
			<input type='hidden' id='egwp_import_export_setting_section'>
			<table class='form-table' id='egwp_import_export_setting_table'>
				<tr>
					<td>
						<form method='post' enctype='multipart/form-data'>
							<input type='file' name='import_file' id='egwp_import_setting_file'/>
							<br>
							<label for='import_file'><i>Select an Easy Genesis settings file and click the Import button.</i>
							<br>
							<br>
							<input name='egwp_import' type='submit' id='egwp_import' class='button-secondary' value='<?php _e('Import') ?>' disabled />
							<?php wp_nonce_field( 'egwp_import', 'egwp_nonce' ); ?>
						</form>
					</td>
				</tr>
				<tr>
					<td>
						<form method='post'>
							<input name='egwp_export' type='submit' id='egwp_export' class='button-secondary' value='<?php _e('Export') ?>' />
							<?php wp_nonce_field( 'egwp_export', 'egwp_nonce' ); ?>
						</form>
					</td>
				</tr>
			</table>
			
			<!-- EXTENSIONS 'PAGE' -->
			<h2>Extensions</h2>
			<input type='hidden' id='egwp_addons_section'>
			<table class="extensions-table" cellspacing="10" cellpadding="10">
				<tr>
					<td>
						<h3>Pages</h3>
						<p><b>Free at WordPress.org</b><br /></p>
						<p>This extension allows you to universally remove titles across your pages, and display the featured image (if there is one) at the top of your pages. You may need to write custom CSS to style the featured image the way you want.<br /><br /></p>
						<p><a class="cta-button" href="http://wordpress.org/plugins/easy-genesis-pages" target="_blank">Download</a><br /></p>
					</td>
					<td>
						<h3>Extras</h3>
						<p><b>Premium Extension</b><br /></p>
						<p>This extension allows you to customize the display of your navigation menus, add content to the beginning and end of your navigation, edit your footer, force sidebar layouts for different page templates, and customize your breadcrumbs.<br /><br /></p>
						<p><a class="cta-button" href="http://efficientwp.com/products/easy-genesis" target="_blank">Learn More</a><br /></p>
					</td>
					<td>
						<h3>Blog</h3>
						<p><b>Premium Extension</b><br /></p>
						<p>This extension allows you to make changes to your post info, post meta, in-post navigation text, archive navigation text, display featured images, display content above your blog page template, and more.<br /><br /></p>
						<p><a class="cta-button" href="http://efficientwp.com/products/easy-genesis" target="_blank">Learn More</a><br /></p>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<h3>Coming Soon:</h3>
						<ul>
							<li>- Comments extension</li>
							<li>- Featured image styling in the Pages and Blog extensions</li>
						</ul>
					</td>
				</tr>
			</table>
			<?php
				settings_fields( 'egwp_main_settings' );
				do_settings_sections( 'egwp_main_settings_page' );
			?>
						
			<h2 id='egwp_footer_shortcodes' style="margin: 2em 0;">Available Footer Shortcodes:<br /><br />
				<p>[footer_copyright] [footer_childtheme_link] [footer_genesis_link] [footer_studiopress_link] [footer_wordpress_link] [footer_loginout]</p>
			
				
			<h2 id='egwp_post_shortcodes' style="margin: 2em 0;">Available Post Shortcodes:<br /><br />
				<p>[post_date] [post_time] [post_author] [post_author_link] [post_author_posts_link] [post_comments] [post_tags] [post_categories] [post_edit] [post_terms]</p>
			</h2>
			
			<br>
			<hr>
			<br>
			<input name='submit' type='submit' id='submit_bottom' class='button-primary' value='<?php _e('Save Changes') ?>' /> <input name='egwp_reset' type='button' id='egwp_reset' class='button-secondary' value='<?php _e('Reset All') ?>' />
		</form>
   </div>
<?php }

/***** EXPORT SETTINGS FUNCTION *****/

function egwp_process_import_export () {
	global $egwp_errors;
	global $egwp_notices;
	
	if ( !empty ( $_POST[ 'egwp_export' ] ) ) {
		$verify = wp_verify_nonce( $_POST[ 'egwp_nonce' ], 'egwp_export' );
		if ( !$verify ) {
			$egwp_errors .= "Could not verify user, try logging in again. \n";
			return;
		}
	} else if ( !empty ( $_POST[ 'egwp_import' ] ) ) {
		$verify = wp_verify_nonce( $_POST[ 'egwp_nonce' ], 'egwp_import' );
			if ( !$verify ) {
			$egwp_errors .= "Could not verify user, try logging in again. \n";
			return;
		}
	} else {
		return;
	}
	
	if ( !empty ( $_POST[ 'egwp_export' ] ) ) {
		global $egwp_version;
		$options = get_option( 'egwp_option_array' );
		$options[ 'egwp_version' ] = $egwp_version;
		
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=egwp-settings-export-' . date( 'm-d-Y' ) . '.json' );
		header( "Expires: 0" );

		echo json_encode( $options );
		exit;
	
	}
	
	if ( !empty ( $_POST[ 'egwp_import' ] ) ) {
		$import_file = $_FILES[ 'import_file' ][ 'tmp_name' ];
		if( empty( $import_file ) ) {
			wp_die( __( 'Please upload a file to import' ) );
		}
		
		$options = json_decode( file_get_contents( $import_file ), true );
		
		if ( !empty($options[ 'egwp_version' ] ) ) {
			$success = update_option( 'egwp_option_array', $options );
			if ( $success ) {
				$egwp_notices .= "Imported Settings \n";
			} else {
				$egwp_errors .= "Failed to Import Settings \n";
			}
		} else {
			$egwp_errors .= "Invalid file type \n";
		}
	}
	
}

/***** EXECUTE CUSTOMIZATIONS ON GENESIS_INIT HOOK *****/

function egwp_genesis_init () {

	$options = get_option( 'egwp_option_array' );
	
	if ( !empty( $options[ 'remove_post_info' ]) ) {
		remove_action( 'genesis_before_post_content', 'genesis_post_info' );
		remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
	}
	if ( !empty( $options[ 'remove_post_meta' ] ) ) {
		remove_action( 'genesis_after_post_content', 'genesis_post_meta' );
		remove_action( 'genesis_entry_footer', 'genesis_post_meta' );
	}
	if ( !empty( $options[ 'remove_footer' ] ) ) {
		remove_action( 'genesis_footer','genesis_do_footer' );
		remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
		remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );
	}
	if ( !empty( $options[ 'remove_edit_link' ] ) ) {
		add_filter( 'edit_post_link', '__return_false' );
	}
	if ( !empty( $options[ 'add_featured_image_support_to_pages' ] ) ) {
		add_theme_support( 'post-thumbnails', array( 'post', 'page' ) );
	}
	
	if ( !empty( $options[ 'featured_image_posts' ] ) ) {
		$setting = $options[ 'featured_image_posts' ];
		switch ( $setting ) { 
			case 'top':
				add_action( 'genesis_before_content_sidebar_wrap', 'egwp_display_featured_image_post' );
				break;
			case 'top-top':
				add_action( 'genesis_before_content_sidebar_wrap', 'egwp_display_featured_image_post' );
				break;	
			case 'top-center':
				add_action( 'genesis_before_content_sidebar_wrap', 'egwp_display_featured_image_post' );
				break;					
			case 'sidebar':
				add_action( 'genesis_before_loop', 'egwp_display_featured_image_post' );
				break;	
			case 'sidebar-top':
				add_action( 'genesis_before_loop', 'egwp_display_featured_image_post' );
				break;	
			case 'sidebar-center':
				add_action( 'genesis_before_loop', 'egwp_display_featured_image_post' );
				break;	
		}		
	}
	
	if ( !empty( $options[ 'featured_image_pages' ] ) ) {
		$setting = $options[ 'featured_image_pages' ];
		switch ( $setting ) { 
			case 'top':
				add_action( 'genesis_before_content_sidebar_wrap', 'egwp_display_featured_image_page' );
				break;
			case 'top-top':
				add_action( 'genesis_before_content_sidebar_wrap', 'egwp_display_featured_image_page' );
				break;	
			case 'top-center':
				add_action( 'genesis_before_content_sidebar_wrap', 'egwp_display_featured_image_page' );
				break;					
			case 'sidebar':
				add_action( 'genesis_before_loop', 'egwp_display_featured_image_page' );
				break;	
			case 'sidebar-top':
				add_action( 'genesis_before_loop', 'egwp_display_featured_image_page' );
				break;	
			case 'sidebar-center':
				add_action( 'genesis_before_loop', 'egwp_display_featured_image_page' );
				break;	
		}		
	}
	if ( !empty( $options[ 'custom_search_box_text' ] ) ) {
		add_filter( 'genesis_search_text', 'egwp_custom_search_box', 20 );
	}
	if ( !empty( $options[ 'custom_search_button_text' ] ) ) {
		add_filter( 'genesis_search_button_text', 'egwp_custom_search_button', 20 );
	}
	if ( !empty( $options[ 'custom_google_fonts_text' ] ) ) {
		add_action( 'wp_enqueue_scripts', 'egwp_custom_google_fonts', 20 );
	}
	if ( !empty( $options[ 'custom_read_more_text' ] ) ) {
		add_filter( 'the_content_more_link', 'egwp_custom_read_more_text', 20 );
		add_filter( 'get_the_content_more_link', 'egwp_custom_read_more_text', 20 );
	}
	if ( !empty( $options[ 'custom_after_post_text' ] ) ) {
		add_action( 'genesis_after_entry_content', 'egwp_custom_after_post', 20 );
		add_action( 'genesis_after_post_content', 'egwp_custom_after_post', 20 );
	}
	if ( !empty( $options[ 'custom_entry_meta_above' ] ) ) {
		add_filter( 'genesis_post_info', 'egwp_post_info_filter', 20 );
	}
	if ( !empty( $options[ 'custom_entry_meta_below' ] ) ) {
		add_filter( 'genesis_post_meta', 'egwp_post_meta_filter', 20 );
	}

	/***** APPLY CUSTOM FOOTER TO BOTH GENESIS FOOTER AND CREDITS TEXT / THEMES VARY AS TO WHICH THEY USE *****/

	if ( !empty( $options[ 'custom_footer_output' ] ) ) {
		add_filter( 'genesis_footer_creds_text', 'egwp_footer_output_filter', 20 );
		add_filter( 'genesis_footer_output', 'egwp_footer_output_filter', 20 );
	}
	if ( !empty( $options[ 'custom_comments_area_text' ] ) OR !empty( $options[ 'comment_title_wrap' ] ) AND function_exists ( 'egwp_comments_title_filter' ) ) {
		add_filter( 'genesis_title_comments', 'egwp_comments_title_filter', 20 );
	}
	if ( !empty( $options[ 'add_genesis_author_boxes_to_all' ] ) ) {
		add_filter( 'add_genesis_author_boxes_to_all', 'egwp_add_genesis_author_boxes', 20 );
	}			
	if ( !empty( $options[ 'custom_no_comments_text' ] ) AND function_exists ( 'egwp_custom_no_comments_text' ) ) {
		add_filter( 'genesis_no_comments_text', 'egwp_custom_no_comments_text', 20 );
	}			
	if ( !empty( $options[ 'custom_comments_closed_text' ] ) AND function_exists ( 'egwp_custom_comments_closed_text' ) ) {
		add_filter( 'genesis_comments_closed_text', 'egwp_custom_comments_closed_text', 20 );
	}			
	if ( !empty( $options[ 'custom_pings_title' ] ) AND function_exists ( 'egwp_custom_pings_title' ) ) {
		add_filter( 'genesis_title_pings', 'egwp_custom_pings_title', 20 );
	}	
	if ( !empty( $options[ 'custom_avatar_size' ] ) ) {
		add_filter( 'genesis_author_box_gravatar_size', 'egwp_custom_avatar_size' );
	}		
	if ( !empty( $options[ 'custom_avatar_size_comment' ] ) AND function_exists ( 'egwp_custom_avatar_size_comment' ) ) {
		add_filter( 'genesis_comment_list_args', 'egwp_custom_avatar_size_comment' );
	}	
	if ( !empty( $options[ 'custom_author_says_text' ] ) AND function_exists ( 'egwp_custom_author_says_text' ) ) {
		add_filter( 'comment_author_says_text', 'egwp_custom_author_says_text' );
	}		
	if ( !empty( $options[ 'custom_comment_waiting_mod_text' ] ) AND function_exists ( 'egwp_custom_comment_waiting_mod_text' ) ) {
		add_filter( 'genesis_comment_awaiting_moderation', 'egwp_custom_comment_waiting_mod_text' );
	}
	if ( !empty( $options[ 'add_featured_image_size_array' ] ) ) {
		egwp_add_custom_image_sizes( $options[ 'add_featured_image_size_array' ] );
	}
	if ( !empty( $options[ 'custom_favicon_url' ] ) ) {
		add_filter( 'genesis_pre_load_favicon', 'egwp_custom_favicon' );
	}
	if ( !empty( $options[ 'custom_gravatar_url' ] ) ) {
		add_filter( 'avatar_defaults', 'egwp_custom_gravatar' );
		update_option( 'avatar_default', $options[ 'custom_gravatar_url' ] );
	}	
	if ( function_exists ( 'egwp_breadcrumb_args' ) ) {
		add_filter( 'genesis_breadcrumb_args', 'egwp_breadcrumb_args' );
	}
	if ( !empty( $options[ 'remove_header'] ) ) {
		remove_action('genesis_header','genesis_do_header');
	}
	if ( !empty( $options[ 'remove_primary_nav'] ) ) {
		remove_action( 'genesis_after_header', 'genesis_do_nav' );
	}
	if ( !empty( $options[ 'remove_secondary_nav'] ) ) {
		remove_action( 'genesis_after_header', 'genesis_do_subnav' );
	}
	if ( !empty( $options[ 'custom_back_to_top_text'] ) AND function_exists ( 'egwp_back_to_top_filter' ) ) {
		add_filter( 'genesis_footer_backtotop_text', 'egwp_back_to_top_filter' );
	}
	if ( !empty( $options[ 'display_content_on_blog'] )AND function_exists ( 'egwp_add_content_to_blog' ) ) {
		add_action ( 'genesis_loop', 'egwp_add_content_to_blog', 1 );
	}
	if ( !empty( $options[ 'custom_nav_html_before'] ) AND function_exists ( 'egwp_add_nav_html_before' ) ) {
		add_filter( 'genesis_nav_items', 'egwp_add_nav_html_before' );
		add_filter( 'wp_nav_menu_items', 'egwp_add_nav_html_before' );
	}
	if ( !empty( $options[ 'custom_nav_html_after'] ) AND function_exists ( 'egwp_add_nav_html_after' ) ) {
		add_filter( 'genesis_nav_items', 'egwp_add_nav_html_after' );
		add_filter( 'wp_nav_menu_items', 'egwp_add_nav_html_after' );
	}
	if ( !empty( $options[ 'add_post_navigation'] ) AND function_exists ( 'egwp_prev_next_post_nav' ) ) {
		add_action( 'genesis_entry_footer', 'egwp_prev_next_post_nav' );
	}	
	if ( !empty( $options[ 'custom_breadcrumb_location'] ) ) {
		remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
		foreach ( $options[ 'custom_breadcrumb_location'] as $hook ) {
			add_action( $hook, 'genesis_do_breadcrumbs' );
		}
	}
	if ( !empty( $options[ 'custom_primary_nav_location'] ) ) {
		remove_action( 'genesis_after_header', 'genesis_do_nav' );
		foreach ( $options[ 'custom_primary_nav_location'] as $hook ) {
			add_action( $hook, 'genesis_do_nav' );
		}
	}
	if ( !empty( $options[ 'custom_secondary_nav_location'] ) ) {
		remove_action( 'genesis_after_header', 'genesis_do_subnav' );
		foreach ( $options[ 'custom_secondary_nav_location'] as $hook ) {
			add_action( $hook, 'genesis_do_subnav' );
		}
	}
	if ( !empty( $options[ 'custom_next_archive_label' ] ) AND function_exists ( 'egwp_archive_next_text' ) ) {
		$genesis_options = get_option ( 'genesis-settings' );
		$genesis_options[ 'posts_nav' ] = 'prev-next';
		update_option ( 'genesis-settings',  $genesis_options);
		add_filter( 'genesis_next_link_text', 'egwp_archive_next_text' );
	}		
	if ( !empty( $options[ 'custom_previous_archive_label' ] ) AND function_exists ( 'egwp_archive_prev_text' ) ) {
		$genesis_options = get_option ( 'genesis-settings' );
		$genesis_options[ 'posts_nav' ] = 'prev-next';
		update_option ( 'genesis-settings',  $genesis_options);
		add_filter( 'genesis_prev_link_text', 'egwp_archive_prev_text' );
	}			
}


/***** EXECUTE CUSTOMIZATIONS ON GENESIS_BEFORE_POST AND GENESIS_BEFORE_ENTRY OR GENESIS_BEFORE HOOK - DEPENDING ON THEME *****/

function egwp_title_toggle () {
	$post_title_setting = get_post_meta( get_the_ID(), 'egwp_title_toggle', true );
	$options = get_option( 'egwp_option_array' );
	
	if ( ( !empty( $options[ 'remove_titles_from_pages' ] ) && is_page() AND $post_title_setting != 'show' ) OR $post_title_setting == 'hide' ) {
		remove_action( 'genesis_post_title', 'genesis_do_post_title' );
		remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
		remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
		remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );
	}
	
	if ( ( !empty( $options[ 'remove_titles_from_posts' ] ) && is_single() AND $post_title_setting != 'show' ) OR $post_title_setting == 'hide' ) {
		remove_action( 'genesis_post_title', 'genesis_do_post_title' );
		remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
		remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
		remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );
	}
}

/***** EXECUTE CUSTOMIZATIONS ON GENESIS_META HOOK *****/

function egwp_genesis_meta () {
	$options = get_option( 'egwp_option_array' );
	if ( !empty( $options[ 'remove_favicon' ] ) && empty( $options[ 'custom_favicon_url' ] ) ) {
		remove_action( 'genesis_meta', 'genesis_load_favicon' );
	}
}

/***** EXECUTE CUSTOMIZATIONS ON WP_HEAD HOOK *****/

function egwp_wp_head() {
	$options = get_option( 'egwp_option_array' );
	if ( !empty( $options[ 'remove_subnav_from_top_of_header' ] ) ) {
		remove_action( 'genesis_before', 'genesis_do_subnav' );
	}
	if ( !empty( $options[ 'add_subnav_to_bottom_of_header' ] ) ) {
		add_action( 'genesis_after_header', 'genesis_do_subnav' );
	}
	if ( !empty( $options[ 'remove_favicon' ] ) ) {
		remove_action( 'wp_head', 'genesis_load_favicon' );
	}
}

/***** FEATURED IMAGE FUNCTIONS *****/

function egwp_display_featured_image_page() {
	global $post;
	$featured_image_array = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );

	if ( is_page ( $post ) AND !empty ( $featured_image_array ) ) {
	
		$options = get_option( 'egwp_option_array' );
		$setting = $options [ 'featured_image_pages'];
		$img_url = $featured_image_array[0];
		$heading = '';
		$mode = '';
		
		switch ( $setting ) { 
			case 'top':
				$heading = '';
				$mode = 'no-heading';
				break;
			case 'top-top':
				$heading = get_the_title();
				$mode = 'above';
				break;	
			case 'top-center':
				$heading = get_the_title();
				$mode = 'center';
				break;					
			case 'sidebar':
				$heading = '';
				$mode = 'no-heading';
				break;	
			case 'sidebar-top':
				$heading = get_the_title();
				$mode = 'above';
				break;	
			case 'sidebar-center':
				$heading = get_the_title();
				$mode = 'center';
				break;	
		}
		
		echo '<div class="egwp_featured_image" style="position:relative;">';
		if ( !empty ( $heading ) AND ($mode == 'above') ) {
			echo "<h1 class='featured-image' style='text-align: center'>$heading</h1>";
		}
		echo "<img src='$img_url'>";
		if ( !empty ( $heading ) AND ($mode == 'center') ) {
			echo "<h1 class='featured-image' style='left: 0; position:absolute; text-align:center; top: 45%; left: 0; width: 100%; color: white;'>$heading</h1>";
		}
		echo '</div>';
	}
}

function egwp_display_featured_image_post() {
	global $post;
	$featured_image_array = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
	
	if ( is_single( $post ) AND !empty ( $featured_image_array ) ) {
	
		$options = get_option( 'egwp_option_array' );
		$setting = $options [ 'featured_image_posts'];
		$img_url = $featured_image_array[0];
		$heading = '';
		$mode = '';
		
		switch ( $setting ) { 
			case 'top':
				$heading = '';
				$mode = 'no-heading';
				break;
			case 'top-top':
				$heading = get_the_title();
				$mode = 'above';
				break;	
			case 'top-center':
				$heading = get_the_title();
				$mode = 'center';
				break;					
			case 'sidebar':
				$heading = '';
				$mode = 'no-heading';
				break;	
			case 'sidebar-top':
				$heading = get_the_title();
				$mode = 'above';
				break;	
			case 'sidebar-center':
				$heading = get_the_title();
				$mode = 'center';
				break;	
		}
		
		echo '<div class="egwp_featured_image" style="position:relative;">';
		if ( !empty ( $heading ) AND ($mode == 'above') ) {
			echo "<h1 class='featured-image' style='text-align: center'>$heading</h1>";
		}
		echo "<img src='$img_url'>";
		if ( !empty ( $heading ) AND ($mode == 'center') ) {
			echo "<h1 class='featured-image' style='left: 0; position:absolute; text-align:center; top: 45%; left: 0; width: 100%; color: white;'>$heading</h1>";
		}
		echo '</div>';
	}
}

/***** CUSTOM TEXT FUNCTIONS *****/

function egwp_custom_read_more_text( $text ) {
	$options = get_option( 'egwp_option_array' );
	if ( !empty( $options[ 'custom_read_more_text' ] ) ) {
		$text = $options[ 'custom_read_more_text' ];
		return "<a class='more-link' href='" . get_permalink() . "'>" . esc_attr( $text ) . '</a>';
	}
}

function egwp_custom_search_box( $text ) {
	$options = get_option( 'egwp_option_array' );
	if ( !empty( $options[ 'custom_search_box_text' ] ) ) {
		$search_box_text = $options[ 'custom_search_box_text' ];
		return esc_attr( $search_box_text );
	}
}

function egwp_custom_search_button( $text ) {
	$options = get_option( 'egwp_option_array' );
	if ( !empty( $options[ 'custom_search_button_text' ] ) ) {
		$search_button_text = $options[ 'custom_search_button_text' ];
		return esc_attr( $search_button_text );
	}
}

function egwp_custom_google_fonts( $text ) {
	$options = get_option( 'egwp_option_array' );
	if ( !empty( $options[ 'custom_google_fonts_text' ] ) ) {
		$google_fonts_text = $options[ 'custom_google_fonts_text' ];
		wp_enqueue_style( 'google-font', esc_url( $google_fonts_text ), array(), PARENT_THEME_VERSION );
	}
}

function egwp_custom_after_post( $text ) {
	if ( is_single() ) {
		$options = get_option( 'egwp_option_array' );
		if ( !empty( $options[ 'custom_after_post_text' ] ) ) {
			$after_post_text = $options[ 'custom_after_post_text' ];
			echo '<div>' . do_shortcode( $after_post_text ) . '</div>';
		}
	}
}

/***** CUSTOM FILTERS *****/
	
function egwp_post_info_filter( $text ) {
	$options = get_option( 'egwp_option_array' );
	return $options[ 'custom_entry_meta_above' ];
}

function egwp_post_meta_filter( $text ) {
	$options = get_option( 'egwp_option_array' );
	return $options[ 'custom_entry_meta_below' ];
}

function egwp_footer_output_filter( $text ) {
	$options = get_option( 'egwp_option_array' );
	$content = do_shortcode ( $options[ 'custom_footer_output' ] );
	return $content;
}

function egwp_add_genesis_author_boxes( $text ) {
	add_filter( 'get_the_author_genesis_author_box_single', '__return_true' );
	add_filter( 'get_the_author_genesis_author_box_archive', '__return_true' );
}

function egwp_custom_avatar_size() {
	$options = get_option( 'egwp_option_array' );
    return intval( $options[ 'custom_avatar_size' ] );

}

/***** CUSTOM META BOX SAVE FUNCTION, FOR PER PAGE / POST TITLE OVERRIDE SETTING *****/

function egwp_title_toggle_post_metabox_save ( $post_id ){
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$value = empty ($_POST[ 'egwp_title_toggle_post' ]) ? 'default' : $_POST[ 'egwp_title_toggle_post' ];
	update_post_meta( $post_id, 'egwp_title_toggle', $value );
}

/***** FILTER FOR FAVICON *****/

function egwp_custom_favicon( $favicon_url ) {
	$options = get_option( 'egwp_option_array' );
	return $options[ 'custom_favicon_url' ];
}

/***** FILTER FOR GRAVATAR *****/

function egwp_custom_gravatar( $avatar_defaults ) {
	$options = get_option( 'egwp_option_array' );
	$avatar_url = $options[ 'custom_gravatar_url' ];
    $avatar_defaults[$avatar_url] = "Easy Genesis Custom Gravatar";
    return $avatar_defaults;
}

/***** ADD NEW FEATURED IMAGE SIZE *****/

function egwp_admin_custom_sizes( $sizes ) {
	$options = get_option( 'egwp_option_array' );
	$sizes_to_add = $options[ 'add_featured_image_size_array' ];
	foreach ( $sizes_to_add as $size ) {
		$name = 'egwp-custom-' . $size;
		$sizes[ $name ] = 'Custom (' . $size . ')';
	}
    return $sizes;
}

function egwp_admin_notice() {
	global $egwp_errors;
	global $egwp_notices;
	if ( !empty ( $egwp_notices ) ) {
		echo "<div class='updated'><p>$egwp_notices</p></div>";
	}
	if ( !empty ( $egwp_errors ) ) {
		echo "<div class='error'><p>$egwp_errors</p></div>";
	}
}	

/***** AJAX SAVE CURRENT TAB *****/

function egwp_set_current_tab() {
	$success = update_user_option( $_REQUEST[ 'egwp_user_id' ], 'egwp_current_tab', $_REQUEST[ 'egwp_current_tab' ] );
	if ($success) {
		echo 'current tab updated';
	} else {
		echo 'failure or unchanged';
	}
	die();
}

/***** ADD CUSTOM IMAGE SIZES FROM SAVED ARRAY *****/

function egwp_add_custom_image_sizes( $types ) {
	foreach ( $types as $image_type_string ) {
		$pieces = explode( "x", $image_type_string );
		$width = $pieces[0];
		$height = $pieces[1];
		$name_string = 'egwp-custom-' . $image_type_string;
		add_image_size( $name_string, $width, $height, true );
	}
	add_filter( 'image_size_names_choose', 'egwp_admin_custom_sizes' );
}

function egwp_upgrade_check() {
	$old_options = array (
		'ewp_gsc_remove_post_info'  => 'remove_post_info',
		'ewp_gsc_remove_post_meta' => 'remove_post_meta',
		'ewp_gsc_remove_footer' => 'remove_footer',
		'ewp_gsc_remove_edit_link' => 'remove_edit_link',
		'ewp_gsc_add_featured_image_support_to_pages' => 'add_featured_image_support_to_pages',
		'ewp_gsc_display_category_descriptions' => 'display_category_descriptions',
		'ewp_gsc_custom_search_box_text' => 'custom_search_box_text',
		'ewp_gsc_custom_search_button_text' => 'custom_search_button_text',
		'ewp_gsc_custom_google_fonts_text' => 'custom_google_fonts_text',
		'ewp_gsc_custom_more_tag_read_more_link_text' => 'custom_read_more_text',
		'ewp_gsc_custom_show_content_limit_read_more_link_text' => 'custom_read_more_text',
		'ewp_gsc_custom_after_post_text' => 'custom_after_post_text',
		'ewp_gsc_remove_favicon' => 'remove_favicon',
		'ewp_gsc_remove_subnav_from_top_of_header' => 'remove_subnav_from_top_of_header',
		'ewp_gsc_add_subnav_to_bottom_of_header' => 'add_subnav_to_bottom_of_header',
	);
	$new_options = array();
	$genesis_options = get_option ( 'genesis-settings' );
	foreach ( $old_options as $key => $value ) {
		if ( !empty ( genesis_get_option( $key ) ) ) {
			$new_options[ $value ] = genesis_get_option( $key );
			unset( $genesis_options[ $key ] );
		}
	}
	
	/***** SPECIAL CHECKS FOR FEATURED IMAGES -> NOW ITS ONE SETTING FOR POSTS AND ONE FOR PAGES *****/
	
	if ( !empty ( genesis_get_option( 'ewp_gsc_display_featured_image_above_page_content_with_h1' ) ) ) {
		$new_options[ 'featured_image_pages' ] = 'top-heading';
		unset( $genesis_options[ 'ewp_gsc_display_featured_image_above_page_content_with_h1' ] );
	}
	if ( !empty ( genesis_get_option( 'ewp_gsc_display_featured_image_above_page_content_without_h1' ) ) ) {
		$new_options[ 'featured_image_pages' ] = 'top';
		unset( $genesis_options[ 'ewp_gsc_display_featured_image_above_page_content_without_h1' ] );
	}
	if ( !empty ( genesis_get_option( 'ewp_gsc_display_featured_image_above_post_content_with_h1' ) ) ) {
		$new_options[ 'featured_image_posts' ] = 'top-heading';
		unset( $genesis_options[ 'ewp_gsc_display_featured_image_above_post_content_with_h1' ] );
	}
	if ( !empty ( genesis_get_option( 'ewp_gsc_display_featured_image_above_post_content_without_h1' ) ) ) {
		$new_options[ 'featured_image_posts' ] = 'top';
		unset( $genesis_options[ 'ewp_gsc_display_featured_image_above_post_content_without_h1' ] );
	}		

	if ( !empty ( $new_options ) ) {
		$success = update_option( 'egwp_option_array', $new_options );
		global $egwp_notices;
		$success ? $egwp_notices .= 'Imported Options from Genesis Simple Customizations' : "";
		
		/***** CLEAN UP / REMOVE OLD KEYS FROM GENESIS OPTIONS TABLE *****/
		
		update_option ( 'genesis-settings',  $genesis_options );
	}
}

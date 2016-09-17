<?php
/*****
Plugin Name: Genesis Customizations (formerly Easy Genesis)
Plugin URI: http://efficientwp.com/plugins/easy-genesis
Description: Easily make certain customizations to your Genesis-powered site in the Genesis Theme Settings menu. You must be using the Genesis theme framework.
Version: 2.4
Author: Doug Yuen
Author URI: http://efficientwp.com
License: GPLv2
*****/

$gcwp_version = '2.4';

/***** BASIC SECURITY *****/

defined( 'ABSPATH' ) or die( __( 'Unauthorized Access!', 'genesis-simple-customizations' ) );

/***** ACTIVATION & INIT HOOKS *****/

function gcwp_activation() {
	if ( get_template() != 'genesis' ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( __( 'This plugin requires the Genesis theme framework.', 'genesis-simple-customizations' ) .
			"<p><a class='button button-large' href='" . admin_url( 'plugins.php' ) . "'>" . __( 'Back to plugins page', 'genesis-simple-customizations' ) . '</a></p>'
		);
	}	
	gcwp_upgrade_check();
}
register_activation_hook( __FILE__, 'gcwp_activation' );

function gcwp_init() {
	define( 'gcwp_plugin_active', TRUE );
}	
add_action ( 'init', 'gcwp_init' );

/***** DEACTIVATION HOOK *****/

function gcwp_deactivation () {

	if ( is_plugin_active( 'genesis-customizations-pro/genesis-customizations-pro.php' ) ) {
		add_action('update_option_active_plugins', 'gcpwp_deactivate');
	}
}
register_deactivation_hook( __FILE__, 'gcwp_deactivation' );

/***** ADD MENUS TO ADMIN BAR *****/

function gcwp_add_menus() {
	$handle = add_menu_page( __( 'Genesis Customizations', 'genesis-simple-customizations' ), __( 'Genesis Customizations', 'genesis-simple-customizations' ), 'edit_themes', 'gcwp_easy_genesis', 'gcwp_main_page_callback', 'dashicons-admin-generic', '58.9950000000121' );
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'gcwp_plugin_action_links' );
}
add_action( 'admin_menu', 'gcwp_add_menus' ); 

function gcwp_plugin_action_links( $links ) {
   $links[] = '<a href="'. admin_url() . '?page=gcwp_easy_genesis' .'">Settings</a>';
   return $links;
}

/***** REGISTER META SETTINGS FIELDS, SCRIPTS, CSS *****/

function gcwp_register_settings() {

	/***** REGISTER JS AND CSS *****/
	
	wp_register_style( 'gcwp_admin_stylesheet', plugins_url( 'includes/admin.css', __FILE__ ) );
	wp_register_script( 'gcwp_admin_js', plugins_url( 'includes/admin.js', __FILE__ ) );
			
	/***** ADD AND REGISTER SETTINGS BOXES AND FIELDS *****/
	
	add_settings_section( 'gcwp_basic_setting_section', __( 'Basic Settings', 'genesis-simple-customizations' ), 'gcwp_section_callback', 'gcwp_main_settings_page' );
	add_settings_section( 'gcwp_page_setting_section', __( 'Page Settings', 'genesis-simple-customizations' ), 'gcwp_section_callback', 'gcwp_main_settings_page' );
	
	/***** MAKE SURE OUR ARRAY EXISTS *****/
	
	if ( !get_option( 'egwp_option_array' ) ) {
		add_option( 'egwp_option_array' );
	}

	/***** SET UP ARRAY THAT HOLDS OPTIONS AND DESCRIPTIONS *****/
	
	//$array gcwp_options_array = [ str 'setting', str 'friendlyText', str/fn 'fn_callBack', str 'settingsArea' ]
	
	$gcwp_options_array = array(	
		/***** MAIN TAB *****/
		array( 'remove_edit_link', __( 'Remove "(Edit)" Link from Frontend', 'genesis-simple-customizations' ), 'gcwp_checkbox_callback', 'gcwp_basic_setting_section' ),
		array( 'featured_image_pages', __( 'Display Featured Image on Pages', 'genesis-simple-customizations' ), 'gcwp_radio_featured_image_callback', 'gcwp_basic_setting_section' ),
		array( 'featured_image_posts', __( 'Display Featured Image on Posts', 'genesis-simple-customizations' ), 'gcwp_radio_featured_image_callback', 'gcwp_basic_setting_section' ),
		array( 'custom_search_box_text', __( 'Custom Search Box Text', 'genesis-simple-customizations' ), 'gcwp_text_box_callback', 'gcwp_basic_setting_section', __('Search this website ...', 'genesis-simple-customizations') ),
		array( 'custom_search_button_text', __( 'Custom Search Button Text', 'genesis-simple-customizations' ), 'gcwp_text_box_callback', 'gcwp_basic_setting_section', __('Search', 'genesis-simple-customizations') ),
		array( 'custom_read_more_text', __( 'Custom "Read More" Text', 'genesis-simple-customizations' ), 'gcwp_text_box_callback', 'gcwp_basic_setting_section', __('Read more...', 'genesis-simple-customizations') ),
		array( 'custom_after_post_text', __( 'Custom After Post Code (shortcodes allowed)', 'genesis-simple-customizations' ), 'gcwp_text_box_callback', 'gcwp_basic_setting_section' ),
		array( 'remove_post_info', __( 'Remove Post Info (above content)', 'genesis-simple-customizations' ), 'gcwp_checkbox_callback', 'gcwp_basic_setting_section' ),
		array( 'remove_post_meta', __( 'Remove Post Meta (below content)', 'genesis-simple-customizations' ), 'gcwp_checkbox_callback', 'gcwp_basic_setting_section' ),	
		array( 'remove_footer', __( 'Remove Footer Entirely', 'genesis-simple-customizations' ), 'gcwp_checkbox_callback', 'gcwp_basic_setting_section' ),		
		array( 'remove_subnav_from_top_of_header', __( 'Remove Secondary Navigation from Top of Header', 'genesis-simple-customizations' ), 'gcwp_checkbox_callback', 'gcwp_basic_setting_section' ),
		array( 'add_subnav_to_bottom_of_header', __( 'Add Secondary Navigation to Bottom of Header', 'genesis-simple-customizations' ), 'gcwp_checkbox_callback', 'gcwp_basic_setting_section' ),
		array( 'remove_favicon', __( 'Remove Genesis Favicon', 'genesis-simple-customizations' ), 'gcwp_checkbox_callback', 'gcwp_basic_setting_section' ),	
		array( 'custom_favicon_url', __( 'Custom Favicon (URL)', 'genesis-simple-customizations' ), 'gcwp_media_library_callback', 'gcwp_basic_setting_section' ),	
		array( 'custom_gravatar_url', __( 'Custom Default Gravatar (URL)', 'genesis-simple-customizations' ), 'gcwp_media_library_callback', 'gcwp_basic_setting_section' ),
		array( 'custom_google_fonts_text', __( 'Custom Google Fonts (URL)', 'genesis-simple-customizations' ), 'gcwp_text_box_callback', 'gcwp_basic_setting_section' ),	
		array( 'add_featured_image_size_array', __( 'Add Custom Image Sizes', 'genesis-simple-customizations' ), 'gcwp_custom_image_sizes_callback', 'gcwp_basic_setting_section' ),
	);
	
	/***** FILTER SO OTHER EXTENSIONS CAN HOOK IN SETTINGS *****/
	
	$gcwp_options_array = apply_filters( 'gcwp_option_filter', $gcwp_options_array );
			
	/***** ADD OPTIONS FOR EACH TYPE IN THE ARRAYS ABOVE
		$array gcwp_options_array = [ str 'setting', str 'friendlyText', str/fn 'fn_callBack', str 'settingsArea' ]
		add_settings_field( $id, $title, $callback, $page, $section, $args );
	*****/
	
	foreach ( $gcwp_options_array as $option ) {
		$setting_name = $option[0];
		$friendly_text = $option[1];
		$callback_function = $option[2];
		$setting_heading = $option[3];
		add_settings_field( "egwp_option_array[$setting_name]", $friendly_text, $callback_function, 'gcwp_main_settings_page', $setting_heading, $option);
	}	
	
	register_setting( 'gcwp_main_settings', 'egwp_option_array' );
}
add_action( 'admin_init', 'gcwp_register_settings' ); 

/***** IMPORT/EXPORT SETTING FEATURE *****/

function gcwp_process_import_export() {
	global $gcwp_errors;
	global $gcwp_notices;
	
	if ( !empty ( $_POST[ 'gcwp_export' ] ) ) {
		$verify = wp_verify_nonce( $_POST[ 'gcwp_nonce' ], 'gcwp_export' );
		if ( !$verify ) {
			$gcwp_errors .= __( 'Could not verify user, try logging in again.', 'genesis-simple-customizations' ) . '\n';
			return;
		}
	} else if ( !empty ( $_POST[ 'gcwp_import' ] ) ) {
		$verify = wp_verify_nonce( $_POST[ 'gcwp_nonce' ], 'gcwp_import' );
			if ( !$verify ) {
			$gcwp_errors .= __( 'Could not verify user, try logging in again.', 'genesis-simple-customizations' ) . '\n';
			return;
		}
	} else {
		return;
	}
	
	if ( !empty ( $_POST[ 'gcwp_export' ] ) ) {
		global $gcwp_version;
		$options = get_option( 'egwp_option_array' );
		$options[ 'gcwp_version' ] = $gcwp_version;
		
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=egwp-settings-export-' . date( __( 'm-d-Y', 'genesis-simple-customizations' ) ) . '.json' );
		header( "Expires: 0" );

		echo json_encode( $options );
		exit;
	
	}
	
	if ( !empty ( $_POST[ 'gcwp_import' ] ) ) {
		$import_file = $_FILES[ 'import_file' ][ 'tmp_name' ];
		if( empty( $import_file ) ) {
			wp_die( __( 'Please upload a file to import' ) );
		}
		
		$options = json_decode( file_get_contents( $import_file ), true );
		
		if ( !empty( $options[ 'gcwp_version' ] ) ||  !empty( $options[ 'egwp_version' ] ) ) {
			$success = update_option( 'egwp_option_array', $options );
			if ( $success ) {
				$gcwp_notices .= __( 'Imported Settings', 'genesis-simple-customizations') . '\n';
			} else {
				$gcwp_errors .= __( 'Failed to Import Settings', 'genesis-simple-customizations') . '\n';
			}
		} else {
			$gcwp_errors .= __( 'Invalid file type', 'genesis-simple-customizations') . '\n';
		}
	}
	
}
add_action( 'admin_init', 'gcwp_process_import_export' );

/***** REGISTER ADMIN NOTICES FUNCTION *****/

$gcwp_notices = '';
$gcwp_errors = '';
function gcwp_admin_notice() {
	global $gcwp_errors;
	global $gcwp_notices;
	if ( !empty( $gcwp_notices ) ) {
		echo "<div class='updated'><p>$gcwp_notices</p></div>";
	}
	if ( !empty( $gcwp_errors ) ) {
		echo "<div class='error'><p>$gcwp_errors</p></div>";
	}
}	
add_action( 'admin_notices', 'gcwp_admin_notice' ); 


/***** AJAX SAVE CURRENT TAB *****/

function gcwp_set_current_tab() {
	$success = update_user_option( $_REQUEST[ 'gcwp_user_id' ], 'gcwp_current_tab', $_REQUEST[ 'gcwp_current_tab' ] );
	if ( $success ) {
		echo 'current tab updated';
	} else {
		echo 'failure or unchanged';
	}
	die();
}
add_action( 'wp_ajax_gcwp_set_current_tab', 'gcwp_set_current_tab' );

/***** CHANGE THE WP MEDIA UPLOADER'S TEXT "INSERT INTO POST" TO "USE THIS IMAGE" *****/

function gcwp_change_insert_post_text( $safe_text, $text ) {
	return str_replace( __( 'Insert into Post' ), __( 'Use this image' ), $text );
}
add_filter( 'esc_attr', 'gcwp_change_insert_post_text', 10, 2 );

/***** ADD FRONT-END HOOKS *****/

if ( get_template() == 'genesis' ) {
	add_action( 'genesis_init', 'gcwp_genesis_init' , 20 );
	add_action( 'genesis_meta', 'gcwp_genesis_meta' , 20 );
	add_action( 'wp_head', 'gcwp_wp_head' , 20 );
	
	if ( current_theme_supports( 'post-formats' ) ) {
		add_action( 'genesis_before_post', 'gcwp_title_toggle', 20 );
		add_action( 'genesis_before_entry', 'gcwp_title_toggle', 20 );
	} else {
		add_action( 'genesis_before', 'gcwp_title_toggle' );
	}
}

/***** CALLBACK FUNCTIONS TO DRAW FIELDS -- BY TYPE *****/

/***** RENDER CHECKBOX OPTIONS *****/

function gcwp_checkbox_callback( $args ) {
	$option_name = $args[0];
	$options = get_option( 'egwp_option_array' );
	
	/***** SET TO DEFAULT IF THIS OPTION HAS NEVER BEEN SET *****/
	
	if ( !isset ( $options[ $option_name ] ) ) {
		$options[ $option_name ] = 0;
	}

	$html = "<div class='gcwp_checkbox'><input type='checkbox' id='egwp_option_array[$option_name]' name='egwp_option_array[$option_name]' value='1' " . checked( 1, $options[ $option_name ], false ) . '/>'; 
	$html .= "<label for='egwp_option_array[$option_name]'></label></div>"; 
	echo $html;
}

/***** RENDER TEXT BOX OPTIONS *****/

function gcwp_text_box_callback( $args ) {
	$option_name = $args[0];
	$placeholder = empty( $args[4] ) ? '' : $args[4];
	$options = get_option( 'egwp_option_array' );
	
	/***** SET TO DEFAULT IF THIS OPTION HAS NEVER BEEN SET *****/
	
	if ( !isset ( $options[ $option_name ] ) ) {
		$value = $options[ $option_name ] = '';
	} else {
		$value = esc_textarea( $options[ $option_name ] );
	}
	$html = "<input type='text' class='gcwp_text' id='egwp_option_array[$option_name]' name='egwp_option_array[$option_name]' value='$value' placeholder='$placeholder' />"; 
	//$html .= "<label for='egwp_option_array[$args[0]]'> " . $args[1] . '</label>'; 
	echo $html;
}

function gcwp_editor_callback( $args ) {
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
		'editor_class' => 'gcwp_tinymce'
	);

	wp_editor( $value, $editor_id, $settings );
}

/***** RENDER NUMBER INPUT OPTIONS *****/

function gcwp_number_callback( $args ) {
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

	$html = "<input type='number' class='gcwp_number' id='egwp_option_array[$option_name]' name='egwp_option_array[$option_name]' value='$value' placeholder='$placeholder' min='1' max='10000' /> px"; 
	//$html .= "<label for='egwp_option_array[$args[0]]'> " . $args[1] . '</label>'; 
	echo $html;
}

/***** RENDER MEDIA UPLOAD CALLBACK (WP MEDIA LIBRARY) *****/

function gcwp_media_library_callback( $args ) {
	$option_name = $args[0];
	$options = get_option( 'egwp_option_array' );
	
	/***** SET TO DEFAULT IF THIS OPTION HAS NEVER BEEN SET *****/
	
	if ( !isset ( $options[ $option_name ] ) ) {
		$value = $options[ $option_name ] = '';
	} else {
		$value = $options[ $option_name ];
	}
	
	$html = "<input type='text' class='gcwp_text' id='egwp_option_array[$option_name]' name='egwp_option_array[$option_name]' value='$value'/>";
	$html .= "<input class='egwp-upload-button button' type='button' value='" . __( 'Upload Image', 'genesis-simple-customizations' ) . "' />";
	echo $html;
}

/***** RENDER MULTISELECTS (OF GENESIS HOOKS) *****/

function gcwp_multiselect_callback ( $args ) {
	$option_name = $args[0];
	$options = get_option( 'egwp_option_array' );
	
	$gcwp_basic_genesis_hooks = array ( 
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
	
	$size = count( $gcwp_basic_genesis_hooks );
	$html = "<select class='gcwp_select' name='egwp_option_array[$option_name][]' id='egwp_option_array[$option_name]' multiple='multiple' size='$size'>";
			
	foreach ( $gcwp_basic_genesis_hooks as $hook => $text ) { 
		$selected = in_array( $hook, $selected_options ) ? 'selected' : '';
		$html .= "<option value='$hook' $selected>$text</option>";
	}					

	$html .= '</select>';
	$html .= "<br><label for='egwp_option_array[$option_name][]'><i>" . __( 'Hold control key to select more than one.', 'genesis-simple-customizations' ) . "</i></label>";
	echo $html;
}

/***** RENDER RADIO OPTIONS (OF GENESIS LAYOUTS) *****/

function gcwp_radio_layout_callback( $args ) {

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
		$html .= "<input class='gcwp_radio' type='radio' name='egwp_option_array[$option_name]' id='egwp_option_array[$option_name]' value='$layout' $checked>";
	}
	$html .= '</fieldset>';
	echo $html;
}

/***** RENDER RADIO OPTIONS (OF FEATURED IMAGE OPTIONS) *****/

function gcwp_radio_featured_image_callback( $args ) {
	$option_name = $args[0];
	$options = get_option( 'egwp_option_array' );
	
	/***** SET TO DEFAULT IF THIS OPTION HAS NEVER BEEN SET *****/
	
	if ( !isset ( $options[ $option_name ] ) ) {
		$options[ $option_name ] = '';
	}
	
	$theme_root = get_theme_root();
	$path = $theme_root . '/genesis/lib/admin/images/layouts/';
	$plugin_dir = plugin_dir_url( __FILE__ );
	$asset_dir = $plugin_dir . 'assets/';
	
	$layouts = array(
		'' => $asset_dir . 'disabled.png',
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
								
		$html .= "<input class='gcwp_radio' type='radio' name='egwp_option_array[$option_name]' id='egwp_option_array[$option_name]' value='$layout' $checked>";
	}
	
	$html .= '</fieldset>';
	echo $html;
}

/***** RENDER CUSTOM IMAGE SIZE SAVE BOX *****/

function gcwp_custom_image_sizes_callback( $args ) {
	$option_name = $args[0];
	$options = get_option( 'egwp_option_array' );
	
	if ( isset ( $options[ $option_name ] ) ) {
		$selected_options = $options[ $option_name ];	
	} else {
		$selected_options = array();
	}

	$html = "<input name='gcwp_add_image_width' type='number' min='1' max='10000' id='gcwp_add_image_width' placeholder='" . __( 'Width (px)', 'genesis-simple-customizations' ) . "'/> x ";
	$html .= "<input name='gcwp_add_image_height' type='number' min='1' max='10000' id='gcwp_add_image_height' placeholder='" . __( 'Height (px)', 'genesis-simple-customizations' ) . "'/>";
	$html .= "<input name='gcwp_add_image_type' type='button' id='gcwp_add_image_type' class='button-primary' value='" . __( 'Add', 'genesis-simple-customizations' ) . "' />";
	$html .= "<br><br><div id='gcwp_custom_image_sizes'></div>";
	$html .= "<br><select name='egwp_option_array[$option_name][]' id='egwp_option_array[$option_name]' multiple='multiple' hidden readonly'>";
		
	foreach ( $selected_options as $option ) { 
		$html .= "<option value='$option' selected>$option</option>";
	}					

	$html .= '</select>';
	echo $html;
}

/***** CONFIGURE WHAT SHOULD OUTPUT AT THE TOP OF THIS SECTION (MAIN SECTION) *****/

function gcwp_section_callback( $args ) {
	echo "<input type='hidden' id='" . $args[ 'id' ] . "'>";
} 

/***** CREATE MAIN SETTING PAGE *****/

function gcwp_main_page_callback() {
	wp_enqueue_style( 'gcwp_admin_stylesheet' );
	wp_enqueue_script( 'gcwp_admin_js' );
	wp_enqueue_script('jquery');
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');

	$current_tab = get_user_option( 'gcwp_current_tab', get_current_user_id() );

	/***** SEND CURRENT TAB AND AJAX URL DATA TO JS *****/

	$js_data = array(	
		'ajax_url' => admin_url('admin-ajax.php'),	
		'current_tab' => empty( $current_tab ) ? '' : $current_tab,
		'user_id' => get_current_user_id(),
	);

	wp_localize_script( 'gcwp_admin_js', 'gcwp_data', $js_data );
	?>
	<h1><?php _e( 'Genesis Customizations', 'genesis-simple-customizations' ) ?></h1>

	<div id='gcwp_sidebar'>
		<h3>Upgrade To Premium</h3>

		<ul>
			<li><div class="dashicons dashicons-yes"></div> Feature</li>
			<li><div class="dashicons dashicons-yes"></div> Feature</li>
			<li><div class="dashicons dashicons-yes"></div> Feature</li>
			<li><div class="dashicons dashicons-yes"></div> Feature</li>
		</ul>

		<p>
			<a href="https://efficientwp.com/" target="_blank" class="button button-primary button-large">Upgrade Now</a>
		</p> 
	</div>
	<div id='gcwp_main_page'>
		
		<form method='post' action='options.php' id='gcwp_main_form' style='display:none;'>
	
			<input name='submit' type='submit' id='submit' class='button-primary' value='<?php _e( 'Save Changes', 'genesis-simple-customizations' ) ?>' />

			<h2 class='nav-tab-wrapper'>
				<a class='nav-tab nav-tab-active' id='gcwp_basic_setting_section_nav' href='#'><?php _e( 'Main', 'genesis-simple-customizations' ); ?></a>
				<!-- <a class='nav-tab' id='gcwp_addons_section_nav' href='#'><?php _e( 'Extensions', 'genesis-simple-customizations' ); ?></a> -->
				
				<a class="nav-tab" id="gcwp_page_setting_section_nav" href="#"><?php _e( 'Pages', 'genesis-simple-customizations' ); ?></a>
				<?php do_action( 'gcwp_menu' ); ?>
				<a class='nav-tab' id='gcwp_import_export_setting_section_nav' href='#'><?php _e( 'Import/Export', 'genesis-simple-customizations' ); ?></a>
				
			</h2>
			
			<!-- IMPORT/EXPORT SETTINGS 'PAGE' -->

			<h2><?php _e( 'Import/Export Settings', 'genesis-simple-customizations' ); ?></h2>
			<input type='hidden' id='gcwp_import_export_setting_section'>
			<table class='form-table' id='gcwp_import_export_setting_table'>
				<tr>
					<td>
						<form method='post' enctype='multipart/form-data'>
							<input type='file' name='import_file' id='gcwp_import_setting_file'/>
							<br>
							<label for='import_file'><i><?php _e( 'Select a Genesis Customizations settings file and click the Import button.', 'genesis-simple-customizations' ); ?></i>
							<br>
							<br>
							<input name='gcwp_import' type='submit' id='gcwp_import' class='button-secondary' value='<?php _e( 'Import', 'genesis-simple-customizations' ); ?>' disabled />
							<?php wp_nonce_field( 'gcwp_import', 'gcwp_nonce' ); ?>
						</form>
					</td>
				</tr>
				<tr>
					<td>
						<form method='post'>
							<input name='gcwp_export' type='submit' id='gcwp_export' class='button-secondary' value='<?php _e( 'Export', 'genesis-simple-customizations' ); ?>' />
							<?php wp_nonce_field( 'gcwp_export', 'gcwp_nonce' ); ?>
						</form>
					</td>
				</tr>
			</table>
			
			<!-- EXTENSIONS 'PAGE' -->
			<h2><?php _e( 'Extensions', 'genesis-simple-customizations' ) ?></h2>
			<input type='hidden' id='gcwp_addons_section'>
			<table class="extensions-table" cellspacing="10" cellpadding="10">
				<tr>
					<td>
						<h3><?php _e( 'Extras', 'genesis-simple-customizations' ); ?></h3>
						<p><b><?php _e( 'Premium Extension', 'genesis-simple-customizations' ); ?></b><br /></p>
						<p><?php _e( 'This extension allows you to customize the display of your navigation menus, add content to the beginning and end of your navigation, edit your footer, force sidebar layouts for different page templates, and customize your breadcrumbs.', 'genesis-simple-customizations' ); ?><br /><br /></p>
						<p><a class="cta-button" href="https://efficientwp.com/downloads/easy-genesis-extras-extension" target="_blank"><?php _e( 'Learn More', 'genesis-simple-customizations' ); ?></a><br /></p>
					</td>
					<td>
						<h3><?php _e( 'Blog', 'genesis-simple-customizations' ); ?></h3>
						<p><b><?php _e( 'Premium Extension', 'genesis-simple-customizations' ); ?></b><br /></p>
						<p><?php _e( 'This extension allows you to make changes to your post info, post meta, in-post navigation text, archive navigation text, display featured images, display content above your blog page template, and more.', 'genesis-simple-customizations' ); ?><br /><br /></p>
						<p><a class="cta-button" href="https://efficientwp.com/downloads/easy-genesis-blog-extension" target="_blank"><?php _e( 'Learn More', 'genesis-simple-customizations' ); ?></a><br /></p>
					</td>
				</tr>
			</table>
			<?php
				settings_fields( 'gcwp_main_settings' );
				do_settings_sections( 'gcwp_main_settings_page' );
			?>
						
			<h2 id='gcwp_footer_shortcodes' style="margin: 2em 0;"><?php _e( 'Available Footer Shortcodes:', 'genesis-simple-customizations' ); ?><br /><br />
				<p>[footer_copyright] [footer_childtheme_link] [footer_genesis_link] [footer_studiopress_link] [footer_wordpress_link] [footer_loginout]</p>
			
				
			<h2 id='gcwp_post_shortcodes' style="margin: 2em 0;"><?php _e( 'Available Post Shortcodes:', 'genesis-simple-customizations' ); ?><br /><br />
				<p>[post_date] [post_time] [post_author] [post_author_link] [post_author_posts_link] [post_comments] [post_tags] [post_categories] [post_edit] [post_terms]</p>
			</h2>
			
			<br>
			<hr>
			<br>
			<input name='submit' type='submit' id='submit_bottom' class='button-primary' value='<?php _e( 'Save Changes', 'genesis-simple-customizations' ); ?>' /> <input name='gcwp_reset' type='button' id='gcwp_reset' class='button-secondary' value='<?php _e( 'Reset All', 'genesis-simple-customizations' ) ?>' />
		</form>
	</div>
<?php }

/***** EXECUTE CUSTOMIZATIONS ON GENESIS_INIT HOOK *****/

function gcwp_genesis_init() {

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
				add_action( 'genesis_before_content_sidebar_wrap', 'gcwp_display_featured_image_post' );
				break;
			case 'top-top':
				add_action( 'genesis_before_content_sidebar_wrap', 'gcwp_display_featured_image_post' );
				break;	
			case 'top-center':
				add_action( 'genesis_before_content_sidebar_wrap', 'gcwp_display_featured_image_post' );
				break;					
			case 'sidebar':
				add_action( 'genesis_before_loop', 'gcwp_display_featured_image_post' );
				break;	
			case 'sidebar-top':
				add_action( 'genesis_before_loop', 'gcwp_display_featured_image_post' );
				break;	
			case 'sidebar-center':
				add_action( 'genesis_before_loop', 'gcwp_display_featured_image_post' );
				break;	
		}		
	}
	
	if ( !empty( $options[ 'featured_image_pages' ] ) ) {
		$setting = $options[ 'featured_image_pages' ];
		switch ( $setting ) { 
			case 'top':
				add_action( 'genesis_before_content_sidebar_wrap', 'gcwp_display_featured_image_page' );
				break;
			case 'top-top':
				add_action( 'genesis_before_content_sidebar_wrap', 'gcwp_display_featured_image_page' );
				break;	
			case 'top-center':
				add_action( 'genesis_before_content_sidebar_wrap', 'gcwp_display_featured_image_page' );
				break;					
			case 'sidebar':
				add_action( 'genesis_before_loop', 'gcwp_display_featured_image_page' );
				break;	
			case 'sidebar-top':
				add_action( 'genesis_before_loop', 'gcwp_display_featured_image_page' );
				break;	
			case 'sidebar-center':
				add_action( 'genesis_before_loop', 'gcwp_display_featured_image_page' );
				break;	
		}		
	}
	if ( !empty( $options[ 'custom_search_box_text' ] ) ) {
		add_filter( 'genesis_search_text', 'gcwp_custom_search_box', 20 );
	}
	if ( !empty( $options[ 'custom_search_button_text' ] ) ) {
		add_filter( 'genesis_search_button_text', 'gcwp_custom_search_button', 20 );
	}
	if ( !empty( $options[ 'custom_google_fonts_text' ] ) ) {
		add_action( 'wp_enqueue_scripts', 'gcwp_custom_google_fonts', 20 );
	}
	if ( !empty( $options[ 'custom_read_more_text' ] ) ) {
		add_filter( 'the_content_more_link', 'gcwp_custom_read_more_text', 20 );
		add_filter( 'get_the_content_more_link', 'gcwp_custom_read_more_text', 20 );
	}
	if ( !empty( $options[ 'custom_after_post_text' ] ) ) {
		add_action( 'genesis_after_entry_content', 'gcwp_custom_after_post', 20 );
		add_action( 'genesis_after_post_content', 'gcwp_custom_after_post', 20 );
	}
	if ( !empty( $options[ 'custom_entry_meta_above' ] ) ) {
		add_filter( 'genesis_post_info', 'gcwp_post_info_filter', 20 );
	}
	if ( !empty( $options[ 'custom_entry_meta_below' ] ) ) {
		add_filter( 'genesis_post_meta', 'gcwp_post_meta_filter', 20 );
	}

	/***** APPLY CUSTOM FOOTER TO BOTH GENESIS FOOTER AND CREDITS TEXT / THEMES VARY AS TO WHICH THEY USE *****/

	if ( !empty( $options[ 'custom_footer_output' ] ) ) {
		add_filter( 'genesis_footer_creds_text', 'gcwp_footer_output_filter', 20 );
		add_filter( 'genesis_footer_output', 'gcwp_footer_output_filter', 20 );
	}
	if ( !empty( $options[ 'custom_comments_area_text' ] ) OR !empty( $options[ 'comment_title_wrap' ] ) AND function_exists( 'gcwp_comments_title_filter' ) ) {
		add_filter( 'genesis_title_comments', 'gcwp_comments_title_filter', 20 );
	}
	if ( !empty( $options[ 'add_genesis_author_boxes_to_all' ] ) ) {
		add_filter( 'add_genesis_author_boxes_to_all', 'gcwp_add_genesis_author_boxes', 20 );
	}			
	if ( !empty( $options[ 'custom_no_comments_text' ] ) AND function_exists( 'gcwp_custom_no_comments_text' ) ) {
		add_filter( 'genesis_no_comments_text', 'gcwp_custom_no_comments_text', 20 );
	}			
	if ( !empty( $options[ 'custom_comments_closed_text' ] ) AND function_exists( 'gcwp_custom_comments_closed_text' ) ) {
		add_filter( 'genesis_comments_closed_text', 'gcwp_custom_comments_closed_text', 20 );
	}			
	if ( !empty( $options[ 'custom_pings_title' ] ) AND function_exists( 'gcwp_custom_pings_title' ) ) {
		add_filter( 'genesis_title_pings', 'gcwp_custom_pings_title', 20 );
	}	
	if ( !empty( $options[ 'custom_avatar_size' ] ) ) {
		add_filter( 'genesis_author_box_gravatar_size', 'gcwp_custom_avatar_size' );
	}		
	if ( !empty( $options[ 'custom_avatar_size_comment' ] ) AND function_exists( 'gcwp_custom_avatar_size_comment' ) ) {
		add_filter( 'genesis_comment_list_args', 'gcwp_custom_avatar_size_comment' );
	}	
	if ( !empty( $options[ 'custom_author_says_text' ] ) AND function_exists( 'gcwp_custom_author_says_text' ) ) {
		add_filter( 'comment_author_says_text', 'gcwp_custom_author_says_text' );
	}		
	if ( !empty( $options[ 'custom_comment_waiting_mod_text' ] ) AND function_exists( 'gcwp_custom_comment_waiting_mod_text' ) ) {
		add_filter( 'genesis_comment_awaiting_moderation', 'gcwp_custom_comment_waiting_mod_text' );
	}
	if ( !empty( $options[ 'add_featured_image_size_array' ] ) ) {
		gcwp_add_custom_image_sizes( $options[ 'add_featured_image_size_array' ] );
	}
	if ( !empty( $options[ 'custom_favicon_url' ] ) ) {
		add_filter( 'genesis_pre_load_favicon', 'gcwp_custom_favicon' );
	}
	if ( !empty( $options[ 'custom_gravatar_url' ] ) ) {
		add_filter( 'avatar_defaults', 'gcwp_custom_gravatar' );
		update_option( 'avatar_default', $options[ 'custom_gravatar_url' ] );
	}	
	if ( function_exists( 'gcpwp_breadcrumb_args' ) ) {
		add_filter( 'genesis_breadcrumb_args', 'gcpwp_breadcrumb_args' );
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
	if ( !empty( $options[ 'custom_back_to_top_text'] ) AND function_exists( 'gcpwp_back_to_top_filter' ) ) {
		add_filter( 'genesis_footer_backtotop_text', 'gcpwp_back_to_top_filter' );
	}
	if ( !empty( $options[ 'display_content_on_blog'] )AND function_exists( 'gcpwp_add_content_to_blog' ) ) {
		add_action ( 'genesis_loop', 'gcpwp_add_content_to_blog', 1 );
	}
	if ( !empty( $options[ 'custom_nav_html_before'] ) AND function_exists( 'gcpwp_add_nav_html_before' ) ) {
		add_filter( 'genesis_nav_items', 'gcpwp_add_nav_html_before' );
		add_filter( 'wp_nav_menu_items', 'gcpwp_add_nav_html_before' );
	}
	if ( !empty( $options[ 'custom_nav_html_after'] ) AND function_exists( 'gcpwp_add_nav_html_after' ) ) {
		add_filter( 'genesis_nav_items', 'gcpwp_add_nav_html_after' );
		add_filter( 'wp_nav_menu_items', 'gcpwp_add_nav_html_after' );
	}
	if ( !empty( $options[ 'add_post_navigation'] ) AND function_exists( 'gcpwp_prev_next_post_nav' ) ) {
		add_action( 'genesis_entry_footer', 'gcpwp_prev_next_post_nav' );
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
	if ( !empty( $options[ 'custom_secondary_nav_location' ] ) ) {
		remove_action( 'genesis_after_header', 'genesis_do_subnav' );
		foreach ( $options[ 'custom_secondary_nav_location'] as $hook ) {
			add_action( $hook, 'genesis_do_subnav' );
		}
	}
	if ( !empty( $options[ 'custom_next_archive_label' ] ) AND function_exists( 'gcpwp_archive_next_text' ) ) {
		$genesis_options = get_option ( 'genesis-settings' );
		$genesis_options[ 'posts_nav' ] = 'prev-next';
		update_option( 'genesis-settings', $genesis_options );
		add_filter( 'genesis_next_link_text', 'gcpwp_archive_next_text' );
	}		
	if ( !empty( $options[ 'custom_previous_archive_label' ] ) AND function_exists( 'gcpwp_archive_prev_text' ) ) {
		$genesis_options = get_option ( 'genesis-settings' );
		$genesis_options[ 'posts_nav' ] = 'prev-next';
		update_option( 'genesis-settings', $genesis_options );
		add_filter( 'genesis_prev_link_text', 'gcpwp_archive_prev_text' );
	}			
}


/***** EXECUTE CUSTOMIZATIONS ON GENESIS_BEFORE_POST AND GENESIS_BEFORE_ENTRY OR GENESIS_BEFORE HOOK - DEPENDING ON THEME *****/

function gcwp_title_toggle() {
	$post_title_setting = get_post_meta( get_the_ID(), 'gcwp_title_toggle', true );
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

function gcwp_genesis_meta() {
	$options = get_option( 'egwp_option_array' );
	if ( !empty( $options[ 'remove_favicon' ] ) && empty( $options[ 'custom_favicon_url' ] ) ) {
		remove_action( 'genesis_meta', 'genesis_load_favicon' );
	}
}

/***** EXECUTE CUSTOMIZATIONS ON WP_HEAD HOOK *****/

function gcwp_wp_head() {
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

function gcwp_display_featured_image_page() {
	global $post;
	$featured_image_array = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );

	if ( is_page( $post ) AND !empty( $featured_image_array ) ) {
	
		$options = get_option( 'egwp_option_array' );
		$setting = $options [ 'featured_image_pages' ];
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
		
		echo '<div class="gcwp_featured_image" style="position:relative;">';
		if ( !empty ( $heading ) AND ( $mode == 'above' ) ) {
			echo "<h1 class='featured-image' style='text-align: center'>$heading</h1>";
		}
		echo "<img src='$img_url'>";
		if ( !empty ( $heading ) AND ( $mode == 'center' ) ) {
			echo "<h1 class='featured-image' style='left: 0; position:absolute; text-align:center; top: 45%; left: 0; width: 100%; color: white;'>$heading</h1>";
		}
		echo '</div>';
	}
}

function gcwp_display_featured_image_post() {
	global $post;
	$featured_image_array = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
	
	if ( is_single( $post ) AND !empty( $featured_image_array ) ) {
	
		$options = get_option( 'egwp_option_array' );
		$setting = $options [ 'featured_image_posts' ];
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
		
		echo '<div class="gcwp_featured_image" style="position:relative;">';
		if ( !empty( $heading ) AND ( $mode == 'above' ) ) {
			echo "<h1 class='featured-image' style='text-align: center'>$heading</h1>";
		}
		echo "<img src='$img_url'>";
		if ( !empty( $heading ) AND ( $mode == 'center' ) ) {
			echo "<h1 class='featured-image' style='left: 0; position:absolute; text-align:center; top: 45%; left: 0; width: 100%; color: white;'>$heading</h1>";
		}
		echo '</div>';
	}
}

/***** CUSTOM TEXT FUNCTIONS *****/

function gcwp_custom_read_more_text( $text ) {
	$options = get_option( 'egwp_option_array' );
	if ( !empty( $options[ 'custom_read_more_text' ] ) ) {
		$text = $options[ 'custom_read_more_text' ];
		return "<a class='more-link' href='" . get_permalink() . "'>" . esc_attr( $text ) . '</a>';
	}
}

function gcwp_custom_search_box( $text ) {
	$options = get_option( 'egwp_option_array' );
	if ( !empty( $options[ 'custom_search_box_text' ] ) ) {
		$search_box_text = $options[ 'custom_search_box_text' ];
		return esc_attr( $search_box_text );
	}
}

function gcwp_custom_search_button( $text ) {
	$options = get_option( 'egwp_option_array' );
	if ( !empty( $options[ 'custom_search_button_text' ] ) ) {
		$search_button_text = $options[ 'custom_search_button_text' ];
		return esc_attr( $search_button_text );
	}
}

function gcwp_custom_google_fonts( $text ) {
	$options = get_option( 'egwp_option_array' );
	if ( !empty( $options[ 'custom_google_fonts_text' ] ) ) {
		$google_fonts_text = $options[ 'custom_google_fonts_text' ];
		wp_enqueue_style( 'google-font', esc_url( $google_fonts_text ), array(), PARENT_THEME_VERSION );
	}
}

function gcwp_custom_after_post( $text ) {
	if ( is_single() ) {
		$options = get_option( 'egwp_option_array' );
		if ( !empty( $options[ 'custom_after_post_text' ] ) ) {
			$after_post_text = $options[ 'custom_after_post_text' ];
			echo '<div>' . do_shortcode( $after_post_text ) . '</div>';
		}
	}
}

/***** CUSTOM FILTERS *****/
	
function gcwp_post_info_filter( $text ) {
	$options = get_option( 'egwp_option_array' );
	return $options[ 'custom_entry_meta_above' ];
}

function gcwp_post_meta_filter( $text ) {
	$options = get_option( 'egwp_option_array' );
	return $options[ 'custom_entry_meta_below' ];
}

function gcwp_footer_output_filter( $text ) {
	$options = get_option( 'egwp_option_array' );
	$content = do_shortcode( $options[ 'custom_footer_output' ] );
	return $content;
}

function gcwp_add_genesis_author_boxes( $text ) {
	add_filter( 'get_the_author_genesis_author_box_single', '__return_true' );
	add_filter( 'get_the_author_genesis_author_box_archive', '__return_true' );
}

function gcwp_custom_avatar_size() {
	$options = get_option( 'egwp_option_array' );
	return intval( $options[ 'custom_avatar_size' ] );

}

/***** CUSTOM META BOX SAVE FUNCTION, FOR PER PAGE / POST TITLE OVERRIDE SETTING *****/

function gcwp_title_toggle_post_metabox_save ( $post_id ){
	if ( !current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$value = empty ($_POST[ 'gcwp_title_toggle_post' ]) ? 'default' : $_POST[ 'gcwp_title_toggle_post' ];
	update_post_meta( $post_id, 'gcwp_title_toggle', $value );
}
if ( !has_action( 'save_post', 'gcwp_title_toggle_post_metabox_save' ) ) {
	add_action( 'save_post', 'gcwp_title_toggle_post_metabox_save' );
}

/***** FILTER FOR FAVICON *****/

function gcwp_custom_favicon( $favicon_url ) {
	$options = get_option( 'egwp_option_array' );
	return $options[ 'custom_favicon_url' ];
}

/***** FILTER FOR GRAVATAR *****/

function gcwp_custom_gravatar( $avatar_defaults ) {
	$options = get_option( 'egwp_option_array' );
	$avatar_url = $options[ 'custom_gravatar_url' ];
	$avatar_defaults[$avatar_url] = "Genesis Customizations Custom Gravatar";
	return $avatar_defaults;
}

/***** ADD NEW FEATURED IMAGE SIZE *****/

function gcwp_admin_custom_sizes( $sizes ) {
	$options = get_option( 'egwp_option_array' );
	$sizes_to_add = $options[ 'add_featured_image_size_array' ];
	foreach ( $sizes_to_add as $size ) {
		$name = 'egwp-custom-' . $size;
		$sizes[ $name ] = 'Custom (' . $size . ')';
	}
	return $sizes;
}

/***** ADD CUSTOM IMAGE SIZES FROM SAVED ARRAY *****/

function gcwp_add_custom_image_sizes( $types ) {
	foreach ( $types as $image_type_string ) {
		$pieces = explode( "x", $image_type_string );
		$width = $pieces[0];
		$height = $pieces[1];
		$name_string = 'egwp-custom-' . $image_type_string;
		add_image_size( $name_string, $width, $height, true );
	}
	add_filter( 'image_size_names_choose', 'gcwp_admin_custom_sizes' );
}

/***** BACKWARD COMPATIBILITY *****/

function gcwp_upgrade_check() {
	$old_options = array (
		'ewp_gsc_remove_post_info' => 'remove_post_info',
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
	$genesis_options = get_option( 'genesis-settings' );
	foreach ( $old_options as $key => $value ) {
		if ( genesis_get_option( $key ) !== FALSE ) {
			$new_options[ $value ] = genesis_get_option( $key );
			unset( $genesis_options[ $key ] );
		}
	}
	
	/***** SPECIAL CHECKS FOR FEATURED IMAGES -> NOW ITS ONE SETTING FOR POSTS AND ONE FOR PAGES *****/
	
	if ( genesis_get_option( 'ewp_gsc_display_featured_image_above_page_content_with_h1' ) !== FALSE ) {
		$new_options[ 'featured_image_pages' ] = 'top-heading';
		unset( $genesis_options[ 'ewp_gsc_display_featured_image_above_page_content_with_h1' ] );
	}
	if ( genesis_get_option( 'ewp_gsc_display_featured_image_above_page_content_without_h1' ) !== FALSE ) {
		$new_options[ 'featured_image_pages' ] = 'top';
		unset( $genesis_options[ 'ewp_gsc_display_featured_image_above_page_content_without_h1' ] );
	}
	if ( genesis_get_option( 'ewp_gsc_display_featured_image_above_post_content_with_h1' ) !== FALSE ) {
		$new_options[ 'featured_image_posts' ] = 'top-heading';
		unset( $genesis_options[ 'ewp_gsc_display_featured_image_above_post_content_with_h1' ] );
	}
	if ( genesis_get_option( 'ewp_gsc_display_featured_image_above_post_content_without_h1' ) !== FALSE ) {
		$new_options[ 'featured_image_posts' ] = 'top';
		unset( $genesis_options[ 'ewp_gsc_display_featured_image_above_post_content_without_h1' ] );
	}		

	if ( $new_options !== FALSE ) {
		$success = update_option( 'egwp_option_array', $new_options );
		global $gcwp_notices;
		$success ? $gcwp_notices .= __( 'Imported Options from Genesis Simple Customizations', 'genesis-simple-customizations' ) : '';
		
		/***** CLEAN UP / REMOVE OLD KEYS FROM GENESIS OPTIONS TABLE *****/
		
		update_option( 'genesis-settings', $genesis_options );
	}
}
/***** ADD PAGE OPTIONS *****/

function gcwp_add_to_option_filter_pages( $array ) {
	$array[] = array( 'remove_titles_from_pages', __( 'Remove Titles from Pages', 'genesis-simple-customizations' ), 'gcwp_checkbox_callback', 'gcwp_page_setting_section' );	
	$array[] = array( 'featured_image_pages', __( 'Display Featured Image on Pages', 'genesis-simple-customizations' ), 'gcwp_radio_featured_image_callback', 'gcwp_page_setting_section' );
	return $array;
}
add_filter( 'gcwp_option_filter', 'gcwp_add_to_option_filter_pages' );

/***** PER PAGE / POST TITLE OVERRIDE *****/

function gcwp_title_toggle_page_metabox_register() {
	add_meta_box( 'egwp-title-toggle', __( 'Page Title', 'genesis-simple-customizations' ), 'gcwp_title_toggle_page_metabox_render', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'gcwp_title_toggle_page_metabox_register' );

function gcwp_title_toggle_page_metabox_render() {
	
	$options = get_option( 'egwp_option_array' );
	empty( $options[ 'remove_titles_from_pages' ] ) ? $remove_titles_from_pages = __( 'Show', 'genesis-simple-customizations' ) : $remove_titles_from_pages = __( 'Hide', 'genesis-simple-customizations' );
	
	$post_setting = get_post_meta( get_the_ID(), 'gcwp_title_toggle', true );
	empty( $post_setting ) ? $post_setting = 'default' : '';
	
	echo '<p style="padding-top:10px;">';
 
	if ( $post_setting == 'default' ) {
		echo '<input name="gcwp_title_toggle_post" type="radio" value="default" checked>' . __( 'Default', 'genesis-simple-customizations' ) . ' (' . $remove_titles_from_pages . ')<br>';
	} else {
		echo '<input name="gcwp_title_toggle_post" type="radio" value="default">' . __( 'Default', 'genesis-simple-customizations' ) . ' (' . $remove_titles_from_pages . ')<br>';
	}
	
	if ( $post_setting == 'show' ) {
		echo '<input name="gcwp_title_toggle_post" type="radio" value="show" checked>' . __( 'Show', 'genesis-simple-customizations' ) . '<br>';
	} else {
		echo '<input name="gcwp_title_toggle_post" type="radio" value="show">' . __( 'Show', 'genesis-simple-customizations' ) . '<br>';
	}
	
	if ( $post_setting == 'hide' ) {
		echo '<input name="gcwp_title_toggle_post" type="radio" value="hide" checked>' . __( 'Hide', 'genesis-simple-customizations' ) . '<br>';
	} else {
		echo '<input name="gcwp_title_toggle_post" type="radio" value="hide">' . __( 'Hide', 'genesis-simple-customizations' ) . '<br>';
	}

	echo '</p>';
}

// TRANSLATION
function genesis_simple_customizations_load_textdomain() {
	load_plugin_textdomain( 'genesis-simple-customizations', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'genesis_simple_customizations_load_textdomain' );

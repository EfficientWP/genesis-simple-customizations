
/***** EGWP ADMIN JS *****/

jQuery( document ).ready(function ( $ ) {
	
	/***** LOAD VARIABLES *****/
	
	var ajax_url = gcwp_data.ajax_url;
	var current_tab = gcwp_data.current_tab;
	var user_id = gcwp_data.user_id;
	loadCustomImageSizes();
		
	/***** UNHIDE MAIN FORM WHEN LOADING IS DONE (HIDDEN DURING RENDER) *****/
	
	$('#gcwp_main_form').show();
	
	/***** SET DEFAULT TAB AS OPEN *****/
	
	$( 'h2:not(.nav-tab-wrapper), table' ).hide();
	if ( current_tab != '' && $( '#' + current_tab ).length > 0 ) {
		$( '.nav-tab-active' ).removeClass( 'nav-tab-active' );
		$( '#' + current_tab ).addClass( 'nav-tab-active' );
		
		/***** SPECIAL CASE FOR COMMENTS, HAS 2 SUB SECTIONS *****/
		
		switch( current_tab ) {
			case 'gcwp_comment_setting_section_nav':
				show_comment_setting_section( current_tab );	
				break;
			case 'gcwp_footer_setting_section_nav':
				show_footer_setting_section( current_tab );
				break;
			case 'gcwp_posts_setting_section_nav':
				show_posts_setting_section( current_tab );
				break;
			default:
				var id = current_tab.slice( 0, -4 );
				$( '#' + id ).prev().show();
				$( '#' + id ).next().show();
		}

		
	} else {
		var id = 'gcwp_basic_setting_section';
		$( '#' + id ).prev().show();
		$( '#' + id ).next().show();
	}
	
	/***** TAB CONTROLS *****/
	
	$( '.nav-tab' ).each( function() {
		$( this ).click( function() {
			$( '.nav-tab-active' ).removeClass( 'nav-tab-active' );
			$( this ).addClass( 'nav-tab-active' );
			$( 'h2:not(.nav-tab-wrapper), table' ).hide();
			
			var current_tab = $( this ).attr( 'id' );
			ajax_save_current_tab( current_tab );
			
			switch(current_tab) {
				case 'gcwp_comment_setting_section_nav':
					show_comment_setting_section();	
					break;
				case 'gcwp_footer_setting_section_nav':
					show_footer_setting_section();
					break;
				case 'gcwp_posts_setting_section_nav':
					show_posts_setting_section();
					break;
				default:
				var id = current_tab.slice( 0, -4 );
				$( '#' + id ).prev().show('fast');
				$( '#' + id ).next().show('fast');
			}
			
		});
	});
		
		/***** RESET ALL BUTTON *****/	
	
	$( '#gcwp_reset' ).click( function() {
		if ( confirm( 'Reset All Data?' ) ) {
			$( 'input:checkbox' ).prop( 'checked', false );
			$( 'input:text' ).prop( 'value', '' );
			$( '.egwp-layout-label' ).removeClass( 'egwp-layout-label-selected' );
			$( '.egwp-layout-label' ).next().prop( 'checked', false );
			$( '.genesis-layout-selector' ).find( ':first' ).addClass( 'egwp-layout-label-selected' );
			$( 'option:selected' ).removeAttr( 'selected' );
			$( '.genesis-layout-selector' ).find( ':first' ).next().prop( 'checked', true );
			gcwp_set_onoff_colors();
		} else {
		
			/***** DO NOTHING *****/
			
		}		
	});
	
	/***** SET CURRENT STATE OF ON/OFF CHECKBOX COLORS *****/
	
	function gcwp_set_onoff_colors() {
		$( '.gcwp_checkbox' ).each( function() {
			if ( $( this ).children( 'input:checkbox' ).is( ':checked' ) ) {
				$( this ).css( 'background-color', '#27ae60' );
				
			}else {
				$( this ).css( 'background-color', '#e74c3c' );
			}
		});
	}
	gcwp_set_onoff_colors();
	
	/***** TOGGLE CHECKBOX ON DIV CLICK AND SET COLORS *****/
	
	$( '.gcwp_checkbox' ).click( function() {
	
		/***** SYNC CHECKBOXES IF IT APPEARS IN MORE THAN 1 SPOT *****/
		
		var id = $( this ).children( 'input:checkbox' ).attr( 'id' );
		id = id.replace( '[','\\[' );
		id = id.replace( ']','\\]' );
	
		if ( $( this ).children( 'input:checkbox' ).is( ':checked' ) ) {
			$( '#' + id ).prop( 'checked', false );
			$( '#' + id ).parent( '.gcwp_checkbox' ).css( 'background-color', '#e74c3c' );
		} else {
			$( '#' + id ).prop( 'checked', true );
			$( '#' + id ).parent( '.gcwp_checkbox' ).css( 'background-color', '#27ae60' );
		}
	});
	
	/***** RADIO SELECTS (LAYOUTS) -> MAKE CLICKING THE IMAGE (LABEL) SELECT THE CHECKBOX, AND HIGHLIGHT THE IMAGE *****/
	$( '.egwp-layout-label' ).click( function() {
		var value = $( this ).next().attr( 'value' );
		var id = $( this ).next().attr( 'id' );
		id = id.replace( '[','\\[' );
		id = id.replace( ']','\\]' );
		
		$( '#' + id ).each(function( index ) {
			if ( $( this ).val() == value ) {
				$( this ).prev().siblings().removeClass( 'egwp-layout-label-selected' );
				$( this ).prev().addClass( 'egwp-layout-label-selected' );
				$( this ).prop( 'checked', true );
			}
		});
	});
	
	/***** PREVENT ACCIDENTAL NAVIGATION AWAY *****/
	
	$( 'input' ).not( '#gcwp_import_setting_file' ).bind( 'change', function() { 
		setConfirmUnload( true );
	}); 
	
	$( '#submit, #submit_bottom' ).click(function(){
		setConfirmUnload( false );
	});
	
	/***** SYNC TEXTBOXES IF IT APPEARS IN MORE THAN 1 SPOT *****/
	
	$( '.gcwp_text' ).keyup( function() {
		value = $( this ).val();
		var id = $( this ).attr( 'id' );
		id = id.replace( '[','\\[' );
		id = id.replace( ']','\\]' );
		if ( $( '#' + id ).length > 1 ) {
			$( '#' + id ).not( this ).val( value );
		}
		
	});
	
	/***** SPECIAL CASE FOR COMMENTS, HAS 2 SUB SECTIONS *****/
	
	function show_comment_setting_section() {
		var id = 'gcwp_comment_setting_section';
		$( '#' + id ).prev().show( 'fast' );
		$( '#' + id ).next().show( 'fast' );
		id = 'gcwp_comment_form_setting_section';
		$( '#' + id ).prev().show( 'fast' );
		$( '#' + id ).next().show( 'fast' );	
	}
	
	/***** SPECIAL CASE FOR FOOTER, SHOULD SHOW FOOTER SHORTCODES H2 *****/
	
	function show_footer_setting_section() {
		var id = 'gcwp_footer_setting_section';
		$( '#' + id ).prev().show( 'fast' );
		$( '#' + id ).next().show( 'fast' );

		$( '#gcwp_footer_shortcodes' ).show( 'fast' );	
	}
	
	/***** SPECIAL CASE FOR POSTS, SHOULD SHOW POST SHORTCODES H2 *****/
	
	function show_posts_setting_section() {
		var id = 'gcwp_posts_setting_section';
		$( '#' + id ).prev().show( 'fast' );
		$( '#' + id ).next().show( 'fast' );

		$( '#gcwp_post_shortcodes' ).show( 'fast' );	
	}
	
	/***** AJAX TO SAVE THE LAST TAB WE CLICKED ON *****/
	
	function ajax_save_current_tab( id ) {
		
		$.ajax({
			url: ajax_url,
			data: { 'action':'gcwp_set_current_tab', 'gcwp_current_tab':id, 'gcwp_user_id': user_id },
			type: 'POST',
			datatype: 'text'
		}).done( function( returnedData ) {
			console.log( 'egwp: ' + returnedData);		
		});

	}
	
	/***** CUSTOM IMAGE ADD/REMOVE CONTROLS *****/
	
	$( '#gcwp_add_image_type' ).click( function() {
		
		var newWidth = $( '#gcwp_add_image_width' ).val();
		var newHeight = $( '#gcwp_add_image_height' ).val();
		
		var name = newWidth + "x" + newHeight;
		var id = '#egwp_option_array[add_featured_image_size_array]';
		/***** JQUERY DOESN'T LIKE BRACKETS *****/
		id = id.replace( '[','\\[' );
		id = id.replace( ']','\\]' );
		if ( !isNaN( newWidth ) && !isNaN( newHeight ) ) {
			$( id ).append( '<option value="' + name + '" selected>' + name + '</option>' );
			$( '#gcwp_custom_image_sizes' ).append( "<p><span class='gcwp_image_size'>" + name + "</span><span class='dashicons dashicons-trash gcwp_delete_image_size_button'></span></p>" );
			addImageSizeClickHandler();
			$( '#gcwp_add_image_width' ).val('');
			$( '#gcwp_add_image_height' ).val('');
		}
		
	});
	
	function addImageSizeClickHandler () {
		$( '.gcwp_delete_image_size_button' ).click( function() {
			var toRemove = $( this ).prev( '.gcwp_image_size' ).html();
			var id = '#egwp_option_array[add_featured_image_size_array]';
			/***** JQUERY DOESN'T LIKE BRACKETS *****/
			id = id.replace( '[','\\[' );
			id = id.replace( ']','\\]' );
			$( id ).children().each(function() {
				if ( $( this ).val() == toRemove ) {
					$( this ).remove();
				}
			});
			
			$( this ).parent().remove();
			
			
		});
	
	}
	
	/***** LOAD CUSTOM IMAGE SIZES FRONT END DISPLAY *****/
	
	function loadCustomImageSizes() {
		var id = '#egwp_option_array[add_featured_image_size_array]';
		/***** JQUERY DOESN'T LIKE BRACKETS *****/
		id = id.replace( '[','\\[' );
		id = id.replace( ']','\\]' );
		$( id ).children().each(function() {
			$( '#gcwp_custom_image_sizes' ).append( "<p><span class='gcwp_image_size'>" + $( this ).val() + "</span><span class='dashicons dashicons-trash gcwp_delete_image_size_button'></span></p>" );
			
		});
		addImageSizeClickHandler();
	}
	
	/***** ENABLE THE UPLOAD BUTTON ONCE A FILE IS SELECTED *****/
	
	$( '#gcwp_import_setting_file' ).change( function() {
        $( '#gcwp_import' ).removeAttr('disabled');
    });
	

	/***** ADD CONFIRM DIALOG WHEN NAVIGATING AWAY *****/
	
	function setConfirmUnload( on ) {
    
		 window.onbeforeunload = ( on ) ? unloadMessage : null;

	}

	function unloadMessage() {
		
		 return 'You have entered new data on this page.' +
			' If you navigate away from this page without' +           
			' first saving your data, the changes will be' +
			' lost.';

	}
	
	/***** WP MEDIA LIBRARY SUPPORT *****/
	
	var formfield;
 
    /* user clicks button on custom field, runs below code that opens new window */
    $('.egwp-upload-button').click(function() {
        var formfield = $(this).prev('input'); //The input field that will hold the uploaded file url
		
		var image = wp.media({ 
			title: 'test',
			multiple: false
		}).open()
		.on('select', function(){
			// This will return the selected image from the Media Uploader, the result is an object
			var uploaded_image = image.state().get('selection').first()

			var image_url = uploaded_image.toJSON().url
			// Assign the url value to the input field
			formfield.val(image_url)
			
		})
    });

});
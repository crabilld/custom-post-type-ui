<?php
/**
 * This file controls all of the content from the Import/Export page
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add our settings page to the menu.
 *
 * @since  0.9
 *
 * @return mixed  new menu
 */
function cptui_importexport_admin_menu() {
	add_submenu_page( 'cptui_main_menu', __( 'Import/Export', 'cpt-plugin' ), __( 'Import/Export', 'cpt-plugin' ), 'manage_options', 'cptui_importexport', 'cptui_importexport' );
}
add_action( 'admin_menu', 'cptui_importexport_admin_menu' );

/**
 * Create our settings page output
 *
 * @since  0.9
 *
 * @return mixed  webpage
 */
function cptui_importexport() {

	if ( !empty( $_GET ) ) {
		if ( !empty( $_GET['action'] ) && 'taxonomies' == $_GET['action'] ) {
			$tab = 'taxonomies';
		} elseif ( !empty( $_GET['action'] ) && 'get_code' == $_GET['action'] ) {
			$tab = 'get_code';
		} else {
			$tab = 'post_types';
		}
	}

	if ( !empty( $_POST ) ) {
		cptui_import_types_taxes_settings();
	}

	echo '<div class="wrap">';

	//Display any success messages or errors.
	if ( $success = cptui_get_importexport_successes() ) {
		echo $success;
	}

	if ( $errors = cptui_get_importexport_errors() ) {
		echo $errors;
	}

	$ui = new cptui_admin_ui();

	//Create our tabs.
	cptui_settings_tab_menu( $page = 'importexport' );

	if ( 'post_types' == $tab || 'taxonomies' == $tab ) {
	?>

	<table class="form-table cptui-table">
		<?php if ( !empty( $_GET ) && empty( $_GET['action'] ) ) { ?>
		<tr>
			<td>
				<h3><?php _e( 'Import Post Types', 'cpt-plugin' ); ?></h3>
				<form method="post">
					<textarea class="cptui_post_import" placeholder="<?php esc_attr_e( 'Paste content here.', 'cpt-plugin' ); ?>" name="cptui_post_import"></textarea>
					<p><strong><?php _e( 'To import post types from a different WordPress site, paste the exported content from that site and click the "Import" button.', 'cpt-plugin' ); ?>
					<p><input class="button button-primary" type="submit" value="<?php esc_attr_e( 'Import', 'cpt-plugin' ); ?>"/></strong></p>
				</form>
			</td>
			<td>
				<h3><?php _e( 'Export Post Types', 'cpt-plugin' ); ?></h3>
				<?php
					$cptui_post_types = get_option( 'cptui_post_types', array() );
					if ( !empty( $cptui_post_types ) ) {
						$content = esc_html( json_encode( $cptui_post_types ) );
					} else {
						$content = __( 'No post types registered yet.', 'cpt-plugin' );
					}
				?>
				<textarea title="<?php esc_attr_e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'cpt-plugin' ); ?>" onclick="this.focus();this.select()" readonly="readonly" class="cptui_post_import"><?php echo $content; ?></textarea>
				<p><strong><?php _e( 'Use the content above to import current post types into a different WordPress site. You can also use this to simply back up your post type settings.', 'cpt-plugin' ); ?></strong></p>
			</td>
		</tr>
		<?php } elseif ( !empty( $_GET ) && 'taxonomies' == $_GET['action'] ) { ?>
		<tr>
			<td>
				<h3><?php _e( 'Import Taxonomies', 'cpt-plugin' ); ?></h3>
				<form method="post">
					<textarea class="cptui_tax_import" placeholder="<?php esc_attr_e( 'Paste content here.', 'cpt-plugin' ); ?>" name="cptui_tax_import"></textarea>
					<p><strong><?php _e( 'To import taxonomies from a different WordPress site, paste the exported content from that site and click the "Import" button.', 'cpt-plugin' ); ?>
					<p><input class="button button-primary" type="submit" value="<?php esc_attr_e( 'Import', 'cpt-plugin' ); ?>"/></strong></p>
				</form>
			</td>
			<td>
				<h3><?php _e( 'Export Taxonomies', 'cpt-plugin' ); ?></h3>
				<?php
					$cptui_taxonomies = get_option( 'cptui_taxonomies', array() );
					if ( !empty( $cptui_taxonomies ) ) {
						$content = esc_html( json_encode( $cptui_taxonomies ) );
					} else {
						$content = __( 'No taxonomies registered yet.', 'cpt-plugin' );
					}
				?>
				<textarea title="<?php esc_attr_e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'cpt-plugin' ); ?>" onclick="this.focus();this.select()" readonly="readonly" class="cptui_tax_import"><?php echo $content; ?></textarea>
				<p><strong><?php _e( 'Use the content above to import current taxonomies into a different WordPress site. You can also use this to simply back up your taxonomy settings.', 'cpt-plugin' ); ?></strong></p>
			</td>
		</tr>
		<?php } ?>
	</table>

	<?php
	} else { ?>
		<h2><?php _e( 'Get Post Type and Taxonomy Code', 'cpt-plugin' ); ?></h2>
	<?php
	}

	echo '</div><!-- End .wrap -->';

	cptui_footer();
}

/**
 * Fetch error message based on $_GET parameter
 *
 * @since  0.9
 *
 * @return mixed  false on no error, mixed when there is one.
 */
function cptui_get_importexport_errors() {
	if ( isset( $_GET['cpt_error'] ) ) :
		$error = $_GET['cpt_error'];

		$msg = '<div class="error">';

		if ( 1 == $error ) {
			$msg .= '<p>' . __( 'Taxonomy name is a required field.', 'cpt-plugin' ) . '</p>';
		}
		if ( 2 == $error ) {
			$msg .= '<p>' . __( 'You must assign your custom taxonomy to at least one post type.', 'cpt-plugin' ) . '</p>';
		}
		if ( 3 == $error ) {
			$msg .= '<p>' . __( 'Please do not use quotes in your taxonomy slug or rewrite slug.', 'cpt-plugin' ) . '</p>';
		}
		$msg .= '</div>';

		return $msg;

	endif;

	return false;
}

/**
 * Fetch success message based on $_GET parameter
 *
 * @since  0.9
 *
 * @return mixed  false on no parameter, mixed when there is one.
 */
function cptui_get_importexport_successes() {
	if ( isset( $_GET['cpt_msg'] ) ) :
		$success = $_GET['cpt_msg'];

		$msg = '<div id="message" class="updated">';
		//TODO: filters
		if ( 1 == $success ) {
			$msg .= '<p>' . __( 'Custom taxonomy created successfully.  You may need to refresh to view the new taxonomy in the admin menu.', 'cpt-plugin' ) . '</p>';
		}
		//TODO: ADD SUCCESS FOR DELETING TAXES
		$msg .= '</div>';

		return $msg;

	endif;

	return false;
}

//TODO: refactor.
function cptui_get_taxonomy_code( $taxonomy ) {
	$custom_tax = '';
	$custom_tax = "add_action('init', 'cptui_register_my_taxes_" . $cpt_tax_type['name'] . "');\n";
	$custom_tax .= "function cptui_register_my_taxes_" . $cpt_tax_type['name'] . "() {\n";

	if ( !$cpt_tax_type["label"] ) {
		$cpt_label = esc_html( $cpt_tax_type["name"] );
	} else {
		$cpt_label = esc_html( $cpt_tax_type["label"] );
	}

	//check if singular label was filled out
	if ( !$cpt_tax_type["singular_label"] ) {
		$cpt_singular_label = esc_html( $cpt_tax_type["name"] );
	} else {
		$cpt_singular_label = esc_html( $cpt_tax_type["singular_label"] );
	}

	$labels = var_export( array(
		'search_items' => ( !empty( $cpt_tax_type["singular_label"] ) ) ? esc_html( $cpt_tax_type["singular_label"] ) : '',
		'popular_items' => ( !empty( $cpt_tax_type[0]["popular_items"] ) ) ? esc_html( $cpt_tax_type[0]["popular_items"] ) : '',
		'all_items' => ( !empty( $cpt_tax_type[0]["all_items"] ) ) ? esc_html( $cpt_tax_type[0]["all_items"] ) : '',
		'parent_item' => ( !empty( $cpt_tax_type[0]["parent_item"] ) ) ? esc_html( $cpt_tax_type[0]["parent_item"] ) : '',
		'parent_item_colon' => ( !empty( $cpt_tax_type[0]["parent_item_colon"] ) ) ? esc_html( $cpt_tax_type[0]["parent_item_colon"] ) : '',
		'edit_item' => ( !empty( $cpt_tax_type[0]["edit_item"] ) ) ? esc_html( $cpt_tax_type[0]["edit_item"] ) : '',
		'update_item' => ( !empty( $cpt_tax_type[0]["update_item"] ) ) ? esc_html( $cpt_tax_type[0]["update_item"] ) : '',
		'add_new_item' => ( !empty( $cpt_tax_type[0]["add_new_item"] ) ) ? esc_html( $cpt_tax_type[0]["add_new_item"] ) : '',
		'new_item_name' => ( !empty( $cpt_tax_type[0]["new_item_name"] ) ) ? esc_html( $cpt_tax_type[0]["new_item_name"] ) : '',
		'separate_items_with_commas' => ( !empty( $cpt_tax_type[0]["separate_items_with_commas"] ) ) ? esc_html( $cpt_tax_type[0]["separate_items_with_commas"] ) : '',
		'add_or_remove_items' => ( !empty( $cpt_tax_type[0]["add_or_remove_items"] ) ) ? esc_html( $cpt_tax_type[0]["add_or_remove_items"] ) : '',
		'choose_from_most_used' => ( !empty( $cpt_tax_type[0]["choose_from_most_used"] ) ) ? esc_html( $cpt_tax_type[0]["choose_from_most_used"] ) : ''
	), true );

	$cpt_post_types = ( !$cpt_tax_type[1] ) ? $cpt_tax_type["cpt_name"] : var_export( $cpt_tax_type[1], true );

	//register our custom taxonomies
	$custom_tax .= "register_taxonomy( '" . $cpt_tax_type["name"] . "',";
	$custom_tax .= $cpt_post_types . ",\n";
	$custom_tax .= "array( 'hierarchical' => " . disp_boolean( $cpt_tax_type["hierarchical"] ) . ",\n";
	$custom_tax .= "\t'label' => '" . $cpt_label . "',\n";
	$custom_tax .= "\t'show_ui' => " . disp_boolean( $cpt_tax_type["show_ui"] ) . ",\n";
	$custom_tax .= "\t'query_var' => " . disp_boolean( $cpt_tax_type["query_var"] ) . ",\n";
	if ( !empty( $cpt_tax_type["rewrite_slug"] ) ) {
		$custom_tax .= "\t'rewrite' => array( 'slug' => '" . $cpt_tax_type["rewrite_slug"] . "' ),\n";
	}

	if ( version_compare( CPTUI_WP_VERSION, '3.5', '>' ) ) {
		$custom_tax .= "\t'show_admin_column' => " . disp_boolean( $cpt_tax_type["show_admin_column"] ) . ",\n";
	}

	if ( !empty( $labels ) )
		$custom_tax .= "\t'labels' => " . $labels . "\n";

	$custom_tax .= ") ); \n}";

}

//TODO: refactor.
function cptui_get_post_type_code( $post_type ) {
	// Begin the display for the "Get code" feature
	//display register_post_type code
	$custom_post_type   = '';
	$cpt_support_array  = '';
	$cpt_tax_array      = '';

	$cpt_label = ( empty( $cpt_post_type["label"] ) ) ? esc_html($cpt_post_type["name"]) : esc_html($cpt_post_type["label"]);
	$cpt_singular = ( empty( $cpt_post_type["singular_label"] ) ) ? $cpt_label : esc_html($cpt_post_type["singular_label"]);
	$cpt_rewrite_slug = ( empty( $cpt_post_type["rewrite_slug"] ) ) ? esc_html($cpt_post_type["name"]) : esc_html($cpt_post_type["rewrite_slug"]);
	$cpt_menu_position = ( empty( $cpt_post_type["menu_position"] ) ) ? null : intval($cpt_post_type["menu_position"]);
	$cpt_menu_icon = ( !empty( $cpt_post_type["menu_icon"] ) ) ? esc_url($cpt_post_type["menu_icon"]) : null;

	if ( true == $cpt_post_type["show_ui"] ) {
		$cpt_show_in_menu = ( $cpt_post_type["show_in_menu"] == 1 ) ? 1 : 0;
		$cpt_show_in_menu = ( $cpt_post_type["show_in_menu_string"] ) ? '\''.$cpt_post_type["show_in_menu_string"].'\'' : $cpt_show_in_menu;
	} else {
		$cpt_show_in_menu = 0;
	}

	//set custom label values
	$cpt_labels['name'] = $cpt_label;
	$cpt_labels['singular_name'] = $cpt_post_type["singular_label"];
	$cpt_labels['menu_name'] = ( $cpt_post_type[2]["menu_name"] ) ? $cpt_post_type[2]["menu_name"] : $cpt_label;
	$cpt_labels['add_new'] = ( $cpt_post_type[2]["add_new"] ) ? $cpt_post_type[2]["add_new"] : 'Add ' .$cpt_singular;
	$cpt_labels['add_new_item'] = ( $cpt_post_type[2]["add_new_item"] ) ? $cpt_post_type[2]["add_new_item"] : 'Add New ' .$cpt_singular;
	$cpt_labels['edit'] = ( $cpt_post_type[2]["edit"] ) ? $cpt_post_type[2]["edit"] : 'Edit';
	$cpt_labels['edit_item'] = ( $cpt_post_type[2]["edit_item"] ) ? $cpt_post_type[2]["edit_item"] : 'Edit ' .$cpt_singular;
	$cpt_labels['new_item'] = ( $cpt_post_type[2]["new_item"] ) ? $cpt_post_type[2]["new_item"] : 'New ' .$cpt_singular;
	$cpt_labels['view'] = ( $cpt_post_type[2]["view"] ) ? $cpt_post_type[2]["view"] : 'View ' .$cpt_singular;
	$cpt_labels['view_item'] = ( $cpt_post_type[2]["view_item"] ) ? $cpt_post_type[2]["view_item"] : 'View ' .$cpt_singular;
	$cpt_labels['search_items'] = ( $cpt_post_type[2]["search_items"] ) ? $cpt_post_type[2]["search_items"] : 'Search ' .$cpt_label;
	$cpt_labels['not_found'] = ( $cpt_post_type[2]["not_found"] ) ? $cpt_post_type[2]["not_found"] : 'No ' .$cpt_label. ' Found';
	$cpt_labels['not_found_in_trash'] = ( $cpt_post_type[2]["not_found_in_trash"] ) ? $cpt_post_type[2]["not_found_in_trash"] : 'No ' .$cpt_label. ' Found in Trash';
	$cpt_labels['parent'] = ( $cpt_post_type[2]["parent"] ) ? $cpt_post_type[2]["parent"] : 'Parent ' .$cpt_singular;

	if( is_array( $cpt_post_type[0] ) ) {
		$counter = 1;
		$count = count( $cpt_post_type[0] );
		foreach ( $cpt_post_type[0] as $cpt_supports ) {
			//build supports variable
			$cpt_support_array .= '\'' . $cpt_supports . '\'';
			if ( $counter != $count ) {
				$cpt_support_array .= ',';
			}

			$counter++;
		}
	}

	if( is_array( $cpt_post_type[1] ) ) {
		$counter = 1;
		$count = count( $cpt_post_type[1] );
		foreach ( $cpt_post_type[1] as $cpt_taxes ) {
			//build taxonomies variable
			$cpt_tax_array .= '\''.$cpt_taxes.'\'';
			if ( $counter != $count ) {
				$cpt_tax_array .= ',';
			}
			$counter++;
		}
	}

	$custom_post_type = "add_action('init', 'cptui_register_my_cpt_" . $cpt_post_type["name"] . "');\n";
	$custom_post_type .= "function cptui_register_my_cpt_" . $cpt_post_type["name"] . "() {\n";
	$custom_post_type .= "register_post_type('" . $cpt_post_type["name"] . "', array(\n'label' => '" . $cpt_label . "',\n";
	$custom_post_type .= "'description' => '" . $cpt_post_type["description"] . "',\n";
	$custom_post_type .= "'public' => " . disp_boolean( $cpt_post_type["public"]) . ",\n";
	$custom_post_type .= "'show_ui' => " . disp_boolean( $cpt_post_type["show_ui"]) . ",\n";
	$custom_post_type .= "'show_in_menu' => " . disp_boolean( $cpt_show_in_menu) . ",\n";
	$custom_post_type .= "'capability_type' => '" . $cpt_post_type["capability_type"] . "',\n";
	$custom_post_type .= "'map_meta_cap' => " . disp_boolean( '1' ) . ",\n";
	$custom_post_type .= "'hierarchical' => " . disp_boolean( $cpt_post_type["hierarchical"] ) . ",\n";

	if ( !empty( $cpt_post_type["rewrite_slug"] ) ) {
		$custom_post_type .= "'rewrite' => array('slug' => '" . $cpt_post_type["rewrite_slug"] . "', 'with_front' => " . $cpt_post_type['rewrite_withfront'] . "),\n";
	} else {
		if( empty( $cpt_post_type['rewrite_withfront'] ) ) {
			$cpt_post_type['rewrite_withfront'] = 1;
		}
		$custom_post_type .= "'rewrite' => array('slug' => '" . $cpt_post_type["name"] . "', 'with_front' => " . disp_boolean( $cpt_post_type['rewrite_withfront'] ) . "),\n";
	}

	$custom_post_type .= "'query_var' => " . disp_boolean($cpt_post_type["query_var"]) . ",\n";

	if ( !empty( $cpt_post_type["has_archive"] ) ) {
		$custom_post_type .= "'has_archive' => " . disp_boolean( $cpt_post_type["has_archive"] ) . ",\n";
	}

	if ( !empty( $cpt_post_type["exclude_from_search"] ) ) {
		$custom_post_type .= "'exclude_from_search' => " . disp_boolean( $cpt_post_type["exclude_from_search"] ) . ",\n";
	}

	if ( !empty( $cpt_post_type["menu_position"] ) ) {
		$custom_post_type .= "'menu_position' => '" . $cpt_post_type["menu_position"] . "',\n";
	}

	if ( !empty( $cpt_post_type["menu_icon"] ) ) {
		$custom_post_type .= "'menu_icon' => '" . $cpt_post_type["menu_icon"] . "',\n";
	}

	$custom_post_type .= "'supports' => array(" . $cpt_support_array . "),\n";

	if ( !empty( $cpt_tax_array ) ) {
		$custom_post_type .= "'taxonomies' => array(" . $cpt_tax_array . "),\n";
	}

	if ( !empty( $cpt_labels ) ) {
		$custom_post_type .= "'labels' => " . var_export( $cpt_labels, true ) . "\n";
	}

	$custom_post_type .= ") ); }";

}

/*
TODO: verify this works. Check on array_values() part.
 */
/**
 * Import the posted JSON data from a separate export.
 *
 * @since  0.9
 *
 * @return void
 */
function cptui_import_types_taxes_settings() {

	if ( !empty( $_POST['cptui_post_import'] ) ) {
		$data = stripslashes_deep( trim( $_POST['import'] ) );
		$settings = json_decode( $data, true );

		if ( $settings ) {
			$settings = array_values( $settings );

			update_option( 'cptui_post_types', $settings );
		}

  	} elseif ( !empty( $_POST['cptui_tax_import'] ) ) {
  		$data = stripslashes_deep( trim( $_POST['cptui_tax_import'] ) );
		$settings = json_decode( $data, true );

		if ( $settings ) {
			$settings = array_values( $settings );

			update_option( 'cptui_taxonomies', $settings );
		}

  	}
	//Make them immediately available.
	flush_rewrite_rules();
}
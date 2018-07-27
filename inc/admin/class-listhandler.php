<?php

namespace Xe_SuperAcessManager\Inc\Admin;

class ListHandler {

	public function __construct() {

		// Load the listed item
		add_action('wp',array($this,'init_managesortablecolumns'));

	}

	/**
	 * Add filter and hook to every post type
	 */
	public function init_managesortablecolumns (){
		$post_types = get_post_types();
		foreach( $post_types as $post_type ){
			add_filter( 'manage_' . $post_type . '_posts_columns', array($this,'ST4_columns_head') );
			add_action('manage_' . $post_type . '_posts_custom_column', array($this,'list_sub_colomn'), 10, 2);
		}
	}

	// ADD NEW COLUMN
	public function ST4_columns_head($defaults) {
		// Add a head
		$defaults['sam_security'] = __('Protected by SuperAccessManager','xeweb_sam');

		return $defaults;
	}

	// Show the list with subscriptions
	public function list_sub_colomn($column_name, $post_ID) {

		if($column_name == 'sam_security') {

			$post_meta = get_post_meta( $post_ID, 'txsc_allowed_users', true );

			// Add protected if post has protected info
			if($post_meta){
				echo '<span class="dashicons dashicons-lock"></span>';
			}

		}

	}

}

?>
<?php

/**
 * Class Xeweb_sam_main
 */
class Xeweb_sam_main
{

	/**
	 * @var bool
	 */
	private static $initiated = false;

	/**
	 * @var array
	 */
	private static $userpages;

	private static $personalpages = array();

	private static $category_counter = array();

	/**
	 * Specific_content constructor.
	 */
	public function __construct()
	{
		global $wpdb;
		$this->wpdb = $wpdb;


	}

	/**
	 * Init
	 */
	public static function init()
	{
		if (!self::$initiated) {
			self::init_hooks();
		}
	}

	/**
	 * Load all the wordpress hooks
	 */
	private static function init_hooks()
	{
		self::$initiated = true;

		// Load JS & CSS (Backend)
		add_action('admin_enqueue_scripts', array(self::class,'loadJS'));
		add_action( 'admin_enqueue_scripts', array(self::class,'loadCss'));
		// Load frontend CSS
		add_action( 'wp_enqueue_scripts', array(self::class,'loadCss'));

		if ( is_admin() ) {
		    // load admin hooks
			self::hooks_admin();
		}else{
            // load frontend hooks
			self::frontend_hooks();

		}

	}

	/**
	 * Load frontend hooks
	 */
	private static function frontend_hooks(){

		self::$personalpages = self::get_personal_user_pages();

		// add shortcode to load all pages
		add_shortcode("xeweb-sam_user_pages",array(self::class,"show_all_user_pages"));
		// Legacy support
		add_shortcode("txsc_all_pages",array(self::class,"show_all_user_pages"));

		// add action
		add_filter( 'the_content', array(self::class,'check_access') );

		add_filter( 'the_posts', array(self::class,'filter_posts') );

		add_filter( 'get_terms', array(self::class,'filter_categorys'), 10, 4 );


	}

	/**
	 * Hooks specific for admins
	 */
	private static function hooks_admin(){

		/* Add meta boxes on the 'add_meta_boxes' hook. */
		add_action( 'add_meta_boxes', array(self::class,'add_custom_meta_box') );

		// save meta
		add_action( 'save_post', array(self::class,'save_custom_meta_box'), 0, 2 );

	}

	/**
	 * Load ness. JS
	 */
	public static function loadJS(){

        wp_enqueue_script( 'jquery');
		wp_enqueue_script( 'select2_xeweb-sam', plugin_dir_url( __FILE__ ) . 'js/select2/select2.min.js');
	}

	/**
	 * Load css
	 */
	public static function loadCss() {
		wp_enqueue_style( 'xeweb-sam', plugin_dir_url( __FILE__ )  . 'css/style.min.css' );
		wp_enqueue_style( 'select2_style', plugin_dir_url( __FILE__ )  . 'js/select2/select2.min.css' );

	}

	/**
	 * Add a custom meta box for user Access control
	 */
    public static function add_custom_meta_box(){

        add_meta_box(
            'xeweb_sam-allowed_users',      // Unique ID
	        __("User Access","xeweb_sam"),    // Title
            array(self::class,'post_custom_meta_box'),   // Callback function
	        get_option('xeweb-sam_allowed_post_types'),         // Admin page (or post type)
            'normal',         // Context
            'default'         // Priority
        );

    }


    /** Display the post meta box.
	 * @param $post
	 */
    public static function post_custom_meta_box( $post ) { ?>


		<p>
            <label for="xeweb_sam-allowed_users"><?php echo __("Choose roles or/and users that have access to the post. If empty, post is accessable for everyone.","xeweb_sam")?></label>
            <br />
		</p>
		<p>
			<?php
			// get users
			$users = get_users();
			// get allowed users
			$post_meta = get_post_meta( $post->ID, 'xeweb_sam-allowed_users', true );
			// get roles
			$user_roles = get_editable_roles();
			?>

            <select name="xeweb_sam-allowed_users[]" id="xeweb_sam-allowed_users" class="multiple_js_search" multiple="multiple" style="width: 100%" >
                <?php

                // Empty value
				echo '<option value=""></option>';


				// check user roles
				echo '<option disabled><b>---- Roles ----</b></option>';

				// foreach role
				foreach ($user_roles as $role){
					echo '<option value="'.$role["name"].'"';

					if($post_meta) {
						// for every item in array
						foreach ($post_meta as $meta) {
							if ($meta == $role["name"]) { // check if item is currently selected
								echo ' selected ';
							}
						}
					}


					echo '>'.$role["name"].'</option>';
				}



				// check users
				echo '<option disabled>---- Users ----</option>';

				foreach($users as $user) {

                    echo '<option value="'.$user->ID.'"';

				if($post_meta) {
					// for every item in array
					foreach ($post_meta as $meta) {
						if ($meta == $user->ID) { // check if item is currently selected
							echo ' selected ';
						}
					}
				}


                    echo '>'.$user->first_name.' '.$user->last_name.' ('.$user->user_email.' - '.$user->user_login.')</option>';

                }



                ?>
            </select>


        </p>

		<script type="text/javascript">
			(function($){
				$(".multiple_js_search").select2();
			})(jQuery);
		</script>

    <?php }


	/**
	 * Save meta box
	 * @param $post_id
	 * @param $post
	 */
	public static function save_custom_meta_box( $post_id, $post ){

		/* Get the post type object. */
		$post_type = get_post_type_object( $post->post_type );

		/* Get the posted data and sanitize it for use as an HTML class. */
		$new_meta_value = ( isset( $_POST['xeweb_sam-allowed_users'] ) ? $_POST['xeweb_sam-allowed_users']  : '' );

		/* Get the meta key. */
		$meta_key = 'xeweb_sam-allowed_users';

		/* Get the meta value of the custom field key. */
		$meta_value = get_post_meta( $post_id, $meta_key, true );

		// Update the meta
		update_post_meta( $post_id, $meta_key, $new_meta_value,$meta_value );

	}

	/**
	 * Check if user has access to current content
	 * @param $content
	 *
	 * @return mixed
	 */
	public static function check_access($content){

		global $post;

		/* Get the meta value of the custom field key. */
		$meta_value = get_post_meta( $post->ID, 'xeweb_sam-allowed_users', true );

		// Only do something is access has been set specificly
		if(!empty($meta_array[0])) {

			if ( is_array( $meta_value ) && in_array( $post->post_type, get_option( 'xeweb-sam_allowed_post_types' ) ) ) { // check if there are access restrictions & user login

				if ( is_user_logged_in() ) {

					// standard no access
					$has_access = false;
					// current user
					$current_user = wp_get_current_user();
					// compare roles
					$role_matches = array_intersect( array_map( 'strtolower', $meta_value ), array_map( 'strtolower', $current_user->roles ) );


					if ( in_array( $current_user->ID, $meta_value ) OR isset( $role_matches[0] ) && $role_matches[0] != null ) { // check if user has access
						// check user id and role
						$has_access = true;

					}

					// only if admin mode on
					if ( get_option( 'xeweb-sam_admin_see_all_pages' ) == "on" && current_user_can( 'manage_options' ) ) {

						// admin message
						echo '<p><i>' . __( "You see this page because you are an Administrator", "xeweb_sam" ) . '</i></p>';

						$has_access = true;
					}

				} else { // not logged in, so no access at all.

					// SETTINGS are set, so guests not allowed
					if ( ! empty( $meta_array[0] ) ) {

						self::go_404(); // no access for current user
					}

				}

				// check if user got access
				if ( $has_access != true ) {
					self::go_404(); // No access for current user
				}

			}
		}

		return $content;

	}

	/**
	 * Filter out posts that are not allowed for the user
	 * @param $posts
	 * @return array
	 */
	public static function filter_posts($posts){

	    // Get the current user
		$current_user = wp_get_current_user();

		// no user, no id
		if(!isset($current_user)): $current_user = "-10"; endIF;

		// For each post
		foreach ($posts as $post){

		    // Check if the post has a meta array
			$meta_array = get_post_meta($post->ID,"xeweb_sam-allowed_users",true);

			// no settings, nothing to check, so prob public post
			if(empty($meta_array) OR empty($meta_array[0])){

				$postarray[] = $post;

			}else{ // Post has specific access settings

				$rolecheck = false;
				$usercheck = false;

                // check for roles
				if(!empty($current_user->roles) && is_array($meta_array)) {

					// check for user
					$usercheck = in_array($current_user->ID, $meta_array);



					// check roles
					foreach ( $current_user->roles as $role ) {

						if ( in_array( ucfirst ($role), $meta_array ) ) {

							$rolecheck = true;

						}

					}
				}

				if ($usercheck == true) { // check if user has posts

					if($meta_array != 0) { // check if post id is not zero
						$postarray[] = $post;
					}

				}elseif($rolecheck == true && $usercheck != true){ // check roles

					$postarray[] = $post;

				}elseif(get_option('xeweb-sam_admin_see_all_pages') == "on" && current_user_can('manage_options')){ // check if admin

					$postarray[] = $post;

				}
			}


		}

		if(!empty($postarray)) {
			return $postarray;
		}

	}

	/**
	 * Get all id's from personal user pages
	 * @param null $category
	 * @param string $limit
	 *
	 * @return array
	 */
	public static function get_personal_user_pages($category = null,$limit = '4'){

		global $wpdb;

		    $postarray = array();

		    // get current user
			$current_user = wp_get_current_user();

			// get all meta data from this plugin
			$metas = $wpdb->get_results(
				$wpdb->prepare("SELECT meta_value,post_id FROM $wpdb->postmeta where meta_key = %s ORDER BY post_id DESC", 'xeweb_sam-allowed_users')
			);


				// check if restricted pages excists
				if ( $metas ) {

					// check every meta if user has access to page
					foreach ( $metas as $access ) {

						$postdata = get_post_field( 'post_status', $access->post_id );

						if ( $postdata == 'publish' ) {

							// unset meta value
							$access->meta_value = unserialize( $access->meta_value );

							// Do not add to personal pages, if access managment is empty
							if ( empty( $access->meta_value ) ) {

								// Is public page, so add to available
								self::category_count( $access->post_id, true );
								// go to next post
								continue;
							}

							if($current_user->ID <= 0) {
								// Not a logged in user, PAGE HAS ACCESS restrictions so, not for guests
								self::category_count( $access->post_id, false );
								// go to next post
								continue;
							}


							// check for user
							$usercheck = in_array( $current_user->ID, $access->meta_value );
							$rolecheck = false;


							// check roles
							foreach ( $current_user->roles as $role ) {

								if ( in_array( $role, $access->meta_value ) ) {

									$rolecheck = true;

								}

							}


							if ( $usercheck != true OR $rolecheck != true ) { // check if user has posts

								// If is admin, let him trough if needed
								if ( get_option( 'xeweb-sam_admin_see_all_pages' ) != "on" && ! current_user_can( 'manage_options' ) ) { // check if admin

									// Not available counter
									self::category_count( $access->post_id, false );
									continue;

								}

							}


							// Add category counter
							self::category_count( $access->post_id, true );

							// User has passed, so push array
							array_push( $postarray, $access->post_id );

						}

					}

					return $postarray;

				}

	}

	private static function category_count($postid,$available = true){

		// Get category and add to array
		$cats = get_the_terms($postid,'category');

		// Available or not
		if($available == true){
			$available = "available";
        }else{
		    $available = "remove";
        }

		// Count the posts inside categorys
		if(isset($cats)){
			foreach ($cats as $c){

				// Add one to category counter, available or not
				if(isset(self::$category_counter[$c->term_id][$available])){
					self::$category_counter[$c->term_id][$available]++;
				}else{
					self::$category_counter[$c->term_id][$available] = 1;
				}

			}
		}

		// Return the category counter
		return self::$category_counter;

    }
	/**
	 * Show all pages that are accessable by current user
	 * @return string
	 */
	public static function show_all_user_pages(){

	    $return = '';

		// get personal pages
		$all_posts = self::$personalpages;

		// admin message
		if(current_user_can('manage_options')){
			$return .=  '<p>'.__("You see this page because you are an Administrator, public pages are not listed.","xeweb_sam").'</p>';
		}

		// get al post links
		if(!empty($all_posts)) {
			$return .= get_option('xeweb-sam_list_posts_text').'';
			foreach ($all_posts as $current_post) {
				$current_post = get_post($current_post);

				$return .= '<a href="' . get_permalink($current_post->ID) . '">' . $current_post->post_title . '</a><br />';
			}
		}else{ // user has no personal posts
			$return .= get_option('xeweb-sam_message_no_posts');
		}

		return $return;
	}

	/**
     *  Filter the category count to only count pages available to user
	 * @param $terms
	 * @param $taxonomies
	 * @param $args
	 * @param $term_query
	 *
	 * @return mixed
	 */
	public static function filter_categorys($terms,$taxonomies,$args,$term_query){

	    $new_terms = array();

	    $ccounter = self::$category_counter;


		if(!empty($terms)) {

		    foreach ($terms as $term) {

			    // Remove the amount of unavilable pages from the counter
			    if(isset($term->term_id) && isset($ccounter[$term->term_id]["remove"])){
				    $term->count = $term->count - $ccounter[$term->term_id]["remove"];
			    }

			    // Add to new array
			    $new_terms[] = $term;

			    if(isset($term->count)) {
				    // Remove category from array if needed
				    if ( $term->count <= 0 && get_option( 'xeweb-sam_admin_remove_empty_cats' ) == "on" ) {
					    array_pop( $new_terms );
				    }
			    }
		    }

		}

	    return $new_terms;

    }


	/**
	 * Go to 404 page
	 */
	private static function go_404(){

	    // load 404 template & headers
		status_header( 404 );
		nocache_headers();
		include( get_query_template( '404' ) );
		die();

	}






}
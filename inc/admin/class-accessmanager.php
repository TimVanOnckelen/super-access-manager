<?php
/**
 *
 */
namespace Xe_SuperAcessManager\Inc\Admin;

/**
 * Class AccessManager
 * @package Xe_SuperAcessManager\Inc\Admin
 */
class AccessManager {

	/**
	 * @var bool
	 */
	private $initiated = false;

	/**
	 * @var array
	 */
	private $userpages;

	private $personalpages = array();

	private $category_counter = array();

	private $currentPost = 0;

	/**
	 * Specific_content constructor.
	 */
	public function __construct()
	{
		global $wpdb;
		$this->wpdb = $wpdb;

		$this->init();
	}

	/**
	 * Init
	 */
	private function init()
	{
		if (!$this->initiated) {
			$this->init_hooks();
		}
	}

	/**
	 * Load all the wordpress hooks
	 */
	private function init_hooks()
	{
		$this->initiated = true;

		// Load JS & CSS (Backend)
		add_action('admin_enqueue_scripts', array($this,'loadJS'));
		add_action( 'admin_enqueue_scripts', array($this,'loadCss'));
		// Load frontend CSS
		add_action( 'wp_enqueue_scripts', array($this,'loadCss'));

		if ( is_admin() ) {
			// load admin hooks
			$this->hooks_admin();
		}else{
			// load frontend hooks
			$this->frontend_hooks();

		}

	}

	/**
	 * Load frontend hooks
	 */
	private function frontend_hooks(){

	    // Get the users personal pages
		$this->personalpages = $this->get_personal_user_pages();

		// add shortcode to load all pages
		add_shortcode("xeweb-sam_user_pages",array($this,"show_all_user_pages"));
		// Legacy support
		add_shortcode("txsc_all_pages",array($this,"show_all_user_pages"));

		// Filter out the posts
		add_filter( 'the_posts', array($this,'filter_posts') );

		// Filter out categorys
		add_filter( 'get_terms', array($this,'filter_categorys'), 10, 4 );

		// Filter menu's
        add_filter('wp_get_nav_menu_items',array($this,'filter_menu'),10,3);


	}

	/**
	 * Hooks specific for admins
	 */
	private function hooks_admin(){

		/* Add meta boxes on the 'add_meta_boxes' hook. */
		add_action( 'add_meta_boxes', array($this,'add_custom_meta_box') );

		// save meta
		add_action( 'save_post', array($this,'save_custom_meta_box'), 0, 2 );

	}

	/**
	 * Load ness. JS
	 */
	public function loadJS(){

		wp_enqueue_script( 'jquery');
		wp_enqueue_script( 'select2_xeweb-sam', XE_SAM_PLUGIN_DIR . 'assets/js/select2/select2.min.js');
	}

	/**
	 * Load css
	 */
	public function loadCss() {
		wp_enqueue_style( 'xeweb-sam', XE_SAM_PLUGIN_DIR  . 'assets/css/style.min.css' );
		wp_enqueue_style( 'select2_style', XE_SAM_PLUGIN_DIR  . 'assets/js/select2/select2.min.css' );

	}

	/**
	 * Add a custom meta box for user Access control
	 */
	public function add_custom_meta_box(){

		add_meta_box(
			'txsc_allowed_users',      // Unique ID
			__("User Access","xeweb_sam"),    // Title
			array($this,'post_custom_meta_box'),   // Callback function
			get_option('xeweb-sam_allowed_post_types'),         // Admin page (or post type)
			'normal',         // Context
			'default'         // Priority
		);

	}


	/** Display the post meta box.
	 * @param $post
	 */
	public function post_custom_meta_box( $post ) { ?>


		<p>
			<label for="txsc_allowed_users"><?php echo __("Choose roles or/and users that have access to the post. If empty, post is accessable for everyone.","xeweb_sam")?></label>
			<br />
		</p>
		<p>
			<?php
			// get users
			$users = get_users();
			// get allowed users
			$post_meta = get_post_meta( $post->ID, 'txsc_allowed_users', true );
			// get roles
			$user_roles = get_editable_roles();

			$only_vistors_selected = '';
			$only_users_selected = '';

			if(is_array($post_meta)) {
				// Add selected to only vistors if selected
				if ( in_array( 'onlyvistors', $post_meta ) ) {
					$only_vistors_selected = 'selected';
				}

				// Add selected to only users if selected
				if ( in_array( 'onlyusers', $post_meta ) ) {
					$only_users_selected = 'selected';
				}
			}

			?>

			<select name="txsc_allowed_users[]" id="txsc_allowed_users" class="multiple_js_search" multiple="multiple" style="width: 100%" >
				<?php

				// Empty value
				echo '<option value=""></option>';


				// check user roles
				echo '<option disabled><b>---- '.__('Authentication','xeweb_sam').' ----</b></option>';
				echo '<option value="onlyvistors" '.$only_vistors_selected.'>'.__('Only Visitors','xeweb_sam').'</option>';
				echo '<option value="onlyusers" '.$only_users_selected.'>'.__('Only Registerd Users','xeweb_sam').'</option>';

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
	public function save_custom_meta_box( $post_id, $post ){

		/* Get the post type object. */
		$post_type = get_post_type_object( $post->post_type );

		/* Get the posted data and sanitize it for use as an HTML class. */
		$new_meta_value = ( isset( $_POST['txsc_allowed_users'] ) ? $_POST['txsc_allowed_users']  : '' );

		/* Get the meta key. */
		$meta_key = 'txsc_allowed_users';

		/* Get the meta value of the custom field key. */
		$meta_value = get_post_meta( $post_id, $meta_key, true );

		// Update the meta
		update_post_meta( $post_id, $meta_key, $new_meta_value,$meta_value );

	}


	/**
	 * Filter out posts that are not allowed for the user
	 * @param $posts
	 * @return array
	 */
	public function filter_posts($posts){

	    $postarray = array();

		// Get the current user
		$current_user = wp_get_current_user();
		$amount_of_posts = count($posts);

		// no user, no id
		if(!isset($current_user)): $current_user = "-10"; endIF;

		// For each post
		foreach ($posts as $post){

		    // Add post if user can access
            if($this->userCanAccess($post->ID,$current_user->ID) === true){
                $postarray[] = $post;
            }

		}

		// Are there still posts?
		if(!empty($postarray) && $amount_of_posts > 1) {
			return $postarray;
		}elseif(empty($postarray) && $amount_of_posts == 1){
		    $postarray[] = $this->go_404();
		    return $postarray;
        }

        // No posts, then whe go 404
        return $postarray;

	}

	/**
     * Check if a user can access
	 * @param $post
	 * @param $current_user
	 *
	 * @return bool
	 */
	private function userCanAccess($post,$current_user){

	    // Get the current user
	    $current_user = get_user_by('ID',$current_user);

		// Check if the post has a meta array
		$meta_array = get_post_meta($post,"txsc_allowed_users",true);

		// no settings, nothing to check, so prob public post
		if(empty($meta_array) OR empty($meta_array[0])){

			return true;

		}else{ // Post has specific access settings

			$rolecheck = false;
			$usercheck = false;

			// check if only available vistors
            if(in_array('onlyvistors',$meta_array)){

                // Vistors can see or Admins  - Not available for other users
                if(!isset($current_user->ID) OR get_option('xeweb-sam_admin_see_all_pages') == "on" && current_user_can('manage_options')){
                    return true;
                }

                // Not available for other users
                return null;

            }

			// check if only registerd users
			if(in_array('onlyusers',$meta_array)){

				// Check if user is set, if user, page is available
				if(isset($current_user->ID)){
					return true;
				}

				// Not available for other users
				return null;

			}



			// check for roles
			if(!empty($current_user->roles) && is_array($meta_array)) {

				// check for user
				$usercheck = in_array($current_user, $meta_array);

				// check roles
				foreach ( $current_user->roles as $role ) {

					if ( in_array( ucfirst ($role), $meta_array ) ) {

						$rolecheck = true;

					}

				}
			}

			if ($usercheck == true) { // check if user has posts

				if($meta_array != 0) { // check if post id is not zero
					return true;
				}

			}elseif($rolecheck == true && $usercheck != true){ // check roles

				return true;

			}elseif(get_option('xeweb-sam_admin_see_all_pages') == "on" && current_user_can('manage_options')){ // check if admin

				return true;

			}
		}

		return null;

	}

	/**
	 * Get all id's from personal user pages
	 * @param null $category
	 * @param string $limit
	 *
	 * @return array
	 */
	public function get_personal_user_pages($category = null,$limit = '4'){

		global $wpdb;

		$postarray = array();

		// get current user
		$current_user = wp_get_current_user();

		// get all meta data from this plugin
		$metas = $wpdb->get_results(
			$wpdb->prepare("SELECT meta_value,post_id FROM $wpdb->postmeta where meta_key = %s ORDER BY post_id DESC", 'txsc_allowed_users')
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
						$this->category_count( $access->post_id, true );
						// go to next post
						continue;
					}

					if($current_user->ID <= 0) {
						// Not a logged in user, PAGE HAS ACCESS restrictions so, not for guests
						$this->category_count( $access->post_id, false );
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


					if ( $usercheck != true && $rolecheck != true) { // check if user has posts

						if(get_option( 'xeweb-sam_admin_see_all_pages' ) == "on" && current_user_can( 'manage_options' )){

							// No user check, but admin so valid page!
							// Add category counter
							$this->category_count( $access->post_id, true );

						}else{

							// Not available counter
							$this->category_count( $access->post_id, false );
							continue;

						}


					}else{

						// Add category counter
						$this->category_count( $access->post_id, true );

					}




					// User has passed, so push array
					array_push( $postarray, $access->post_id );

				}

			}

			return $postarray;

		}

	}

	/**
	 * Count the current post category if available or not
	 * @param $postid
	 * @param $available
	 *
	 * @return array
	 */
	private function category_count($postid,$available){

		// Get category and add to array
		$cats = get_the_terms($postid,'category');

		// Available or not
		if($available == true){
			$available = "available";
		}else{
			$available = "remove";
		}

		// Count the posts inside categorys
		if(is_array($cats)){
			foreach ($cats as $c){

				// Add one to category counter, available or not
				if(isset($this->category_counter[$c->term_id][$available])){
					$this->category_counter[$c->term_id][$available]++;
				}else{
					$this->category_counter[$c->term_id][$available] = 1;
				}

			}
		}

		// Return the category counter
		return $this->category_counter;

	}
	/**
	 * Show all pages that are accessable by current user
	 * @return string
	 */
	public function show_all_user_pages(){

		$return = '';

		// get personal pages
		$all_posts = $this->personalpages;

		// admin message
		if(current_user_can('manage_options')){
			$return .=  '<p>'.__("You see this page because you are an Administrator, public pages are not listed.","xeweb_sam").'</p>';
		}

		$max_posts = get_option('xeweb-sam_post_limit_widget');
		$counter = 0;

		// get al post links
		if(!empty($all_posts)) {
			$return .= get_option('xeweb-sam_list_posts_text').'';
			foreach ($all_posts as $current_post) {

				$current_post = get_post($current_post);

				$return .= '<a href="' . get_permalink($current_post->ID) . '">' . $current_post->post_title . '</a><br />';

				$counter++;

				// Skip loop if counter has made it
				if($counter == $max_posts){
					break;
				}



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
	public function filter_categorys($terms,$taxonomies,$args,$term_query){

		$new_terms = array();

		$ccounter = $this->category_counter;

		/*
		if(get_current_user_id() == "71"){
			print_r($ccounter);
		}
		*/

		if(!empty($terms)) {

			foreach ($terms as $term) {

				// If only user pages in counter, than show available
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

		return $terms;

	}

	/**
     * Filter out the non accessable items from the menu
	 * @param $items
	 * @param $menu
	 * @param $args
	 *
	 * @return mixed
	 */
	public function filter_menu($items, $menu, $args){

	    // Only go on when enabled
	    if(get_option('xeweb-sam_auto_menu_remove') != "on")
	        return $items;

	    // Loop over every item
		foreach ( $items as $key => $item ) {

		    // get the cyrrent user
		    $current_user = get_current_user_id();

			// Check if current user can access
			$usercan = $this->userCanAccess($item->object_id ,$current_user);

			// If not, remove from list
			if ( $usercan != true ) unset( $items[$key] );

		}


		return $items;

    }

	/**
	 * Go to 404 page
	 */
	private function go_404(){

	    $userset_page = get_option('xeweb-sam_redirect_page');

	    if(!$userset_page){ // No user given page id, so user standard 404

	        return '-1';


	    }else{ // redirect by given page id

            return $userset_page;

        }

	}

}
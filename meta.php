<?php

/**
 * Does all the showing/saving of the custom meta items for the members CPT
 * in the WP admin area.
 *
 * @since 1.0
 * @author SFNdesign, Curtis McHale
 */
class BCIT_WPD_Restrict_Purchase_Meta{

	function __construct(){

		add_action( 'load-post.php', array( $this, 'metaboxes_setup' ) );
		add_action( 'load-post-new.php', array( $this, 'metaboxes_setup' ) );

	} // __construct

	/**
	 * Adds our actions so that we can start the build out of the post metaboxes
	 *
	 * @since   1.0
	 * @author  SFNdesign, Curtis McHale
	 */
	public function metaboxes_setup(){

		// adds the action which actually adds the meta boxes
		add_action( 'add_meta_boxes', array( $this, 'add_post_metaboxes' ) );

		// all the saving actions go here
		add_action( 'save_post', array( $this, 'save_post_meta' ), 10, 2 );

	} // metaboxes_setup

	/**
	 * Adds the actual metabox
	 *
	 * @uses    add_meta_box
	 *
	 * @since   1.0
	 * @author  SFNdesign, Curtis McHale
	 */
	public function add_post_metaboxes(){

		add_meta_box(
			'bcit-wpd-restrict-content',         // $id - HTML 'id' attribute of the edit screen section
			'Restrict Content',                  // $title - Title that will show at the top of the metabox
			array( $this, 'display_metaboxes' ), // $callback - The function that will display the metaboxes
			'product',                           // $posttype - The registered name of the post type you want to show on
			'side',                              // $context - Where it shows on the page. Possibilities are 'normal', 'advanced', 'side'
			'high'                               // $priority - How high should this display?
			//'$callback_args'                    // any extra params that the callback should get. It will already get the $post_object
		);

	} // add_post_meta

	/**
	 * Builds out the photo by metabox
	 *
	 * @param   object  $object     req     The whole post object for the metabox
	 * @param   array   $box        rex     Array of the box arguements
	 *
	 * @uses    get_post_meta
	 * @uses    wp_nonce_field
	 *
	 * @since   1.0
	 * @author  SFNdesign, Curtis McHale
	 */
	public function display_metaboxes( $post_object, $box ){

		wp_nonce_field( basename( __FILE__ ), 'bcit_wpd_meta_nonce'.$post_object->ID );

		$check_value = get_post_meta( $post_object->ID, '_bcit_wpd_restrict_purchase', true ) ? 1 : 0;
	?>

		<p>
			<label for="bcit-wpd-restrict-content-check">Should we restrict Purchase?</label><br />
			<input class="widefat" type="checkbox" id="bcit-wpd-restrict-content-check" name="bcit-wpd-restrict-content-check" <?php checked( $check_value, 1 ); ?> value="1" size="30" />
		</p>

		<p>
			<label for="bcit-wpd-restrict-content-message">Restrict Content Message</label>
			<input class="widefat" type="text" id="bcit-wpd-restrict-content-message" name="bcit-wpd-restrict-content-message" value="<?php echo esc_attr( get_post_meta( $post_object->ID, '_bcit_wpd_restrict_content_message', true ) ); ?>" size="30" />
			<span class="description">Add a message if we want a custom message</span>
		</p>

	<?php
	} // display_metaboxes

	/**
	 * Saves the metaboxes
	 *
	 * @param   int     $post_id    req     The ID of the post we're saving8
	 * @param   object  $post       req     The whole post object
	 * @return  mixed
	 *
	 * @uses    wp_verify_nonce
	 * @uses    get_post_type_object
	 * @uses    delete_post_meta
	 * @uses    update_post_meta
	 *
	 * @since   1.0
	 * @author  SFNdesign, Curtis McHale
	 */
	public function save_post_meta( $post_id, $post ){

		// check the nonce before we do any processing
		if ( ! isset ( $_POST[ 'bcit_wpd_meta_nonce'.$post_id ] ) ) {
			return $post_id;
		}

		if ( ! wp_verify_nonce( $_POST[ 'bcit_wpd_meta_nonce'.$post_id ], basename( __FILE__ ) ) ){
			return $post_id;
		}

		// get the post type object
		$post_type = get_post_type_object( $post->post_type );

		// make sure current user can save
		if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return $post_id;
		}

		if (  empty( $_POST['bcit-wpd-restrict-content-message'] ) ) {
			delete_post_meta( $post_id, '_bcit_wpd_restrict_content_message' );
		} else {
			$value = strip_tags( $_POST['bcit-wpd-restrict-content-message'] );
			update_post_meta( $post_id, '_bcit_wpd_restrict_content_message', esc_attr( $value ) );
		}

		if (  empty( $_POST['bcit-wpd-restrict-content-check'] ) ) {
			delete_post_meta( $post_id, '_bcit_wpd_restrict_purchase' );
		} else {
			$value = $_POST['bcit-wpd-restrict-content-check'];
			update_post_meta( $post_id, '_bcit_wpd_restrict_purchase', (bool) $value );
		}

	}

} // BCIT_WPD_Restrict_Purchase_Meta

new BCIT_WPD_Restrict_Purchase_Meta();

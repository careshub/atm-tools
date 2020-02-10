<?php
/**
 * All Things Missouri Tools CPT
 *
 * @package   ATM_Tools_CPT
 * @author    dcavins
 * @license   GPL-2.0+
 * @link      https://engagementnetwork.org
 * @copyright 2018 CARES Network
 */

namespace ATM_Tools\CPT_Tax;

class Tools_CPT {

	private $post_type = '';

	private $nonce_value = '';
	private $nonce_name = '';

	/**
	 * Initialize the extension class
	 *
	 * @since     1.6.0
	 */
	public function __construct() {
		$this->post_type   = \ATM_Tools\get_cpt_name();
		$this->nonce_value = $this->post_type . '_meta_box_nonce';
		$this->nonce_name  = $this->post_type . '_meta_box';
	}

	/**
	 * Initialize the extension class
	 *
	 * @since     1.6.0
	 */
	public function add_hooks() {

		// Register Policy custom post type
		add_action( 'init', array( $this, 'register_cpt' ) );

		// Meta changes
		add_action( 'init', array( $this, 'register_meta' ) );

		add_filter( 'manage_edit-' . $this->post_type . '_columns', array( $this, 'add_admin_table_columns') );
		add_filter( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'populate_custom_admin_table_columns'), 10, 2 );
		add_filter( 'manage_edit-' . $this->post_type . '_sortable_columns', array( $this, 'register_sortable_admin_table_columns' ) );

		add_action( 'pre_get_posts', array( $this, 'sortable_columns_orderby' ) );

		//make this visible in main loop
		add_action('pre_get_posts', array( $this, 'atm_query_post_type' ) );

		// Administration of this CPT
		// Add a meta box to the data vis tools edit screen
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		// Save meta box input
		add_action( 'save_post', array( $this, 'meta_box_save' ) );

		// Modify the permalinks for Tool CPTs.
		add_filter( 'post_type_link', array( $this, 'cpt_permalink_filter'), 12, 2);
	}

	/**
	 * Generate ATM Tools custom post type
	 *
	 * @since    1.0.0
	 */
	function register_cpt() {

	    $labels = array(
	        'name' => _x( 'Maps & Tools', 'cares-atm-tools' ),
	        'singular_name' => _x( 'Map/Tool', 'cares-atm-tools' ),
	        'add_new' => _x( 'Add New', 'cares-atm-tools' ),
	        'add_new_item' => _x( 'Add New Map/Tool', 'cares-atm-tools' ),
	        'edit_item' => _x( 'Edit Map/Tool', 'cares-atm-tools' ),
	        'new_item' => _x( 'New Map/Tool', 'cares-atm-tools' ),
	        'view_item' => _x( 'View Map/Tool', 'cares-atm-tools' ),
	        'search_items' => _x( 'Search Tools', 'cares-atm-tools' ),
	        'not_found' => _x( 'No tools found', 'cares-atm-tools' ),
	        'not_found_in_trash' => _x( 'No tools found in Trash', 'cares-atm-tools' ),
	        'parent_item_colon' => _x( 'Parent Tool:', 'cares-atm-tools' ),
	        'menu_name' => _x( 'Maps & Tools', 'cares-atm-tools' ),
	    );

	    $args = array(
	        'labels' => $labels,
	        'hierarchical' => false,
	        'description' => 'Explore additional data, tools, and resources supported by the Center for Applied Research and Engagement Systems.',
	        'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'excerpt' ),
	        'taxonomies' => array( 'category','post_tag' ),
	        'public' => true,
	        'show_ui' => true,
	        'show_in_menu' => true,
	        'menu_position' => 5,
	        'show_in_nav_menus' => true,
	        'publicly_queryable' => true,
	        'exclude_from_search' => false,
	        'has_archive' => true,
	        'query_var' => true,
	        'can_export' => true,
	        'rewrite' => array( 'slug' => 'tools' ),
	        'capability_type' => 'post',
	        // Allow this post type to be exposed via WP JSON REST API
	        'show_in_rest' => true,
	         //'map_meta_cap'    => true
			'menu_icon' => 'dashicons-location-alt',

	    );

	    register_post_type( $this->post_type, $args );
	}

	/**
	 * Change behavior of the SA Policies overview table by adding taxonomies and custom columns.
	 * - Add Type and Stage columns (populated from post meta).
	 *
	 * @since    2.0.0
	 *
	 * @return   array of columns to display
	 */
	public function register_meta() {
		register_meta( 'post', 'alt_link', array(
			'sanitize_callback' => 'esc_url_raw',
			// 'auth_callback' => '',
			'type' => 'string',
			'description' => 'The alternative URL to use.',
			'single' => true,
			'show_in_rest' => true,
		) );

		// Shows in Slider
		register_meta( 'post', 'slider', array(
			'sanitize_callback' => 'absint',
			// 'auth_callback' => '',
			'type' => 'integer',
			'description' => 'Whether this item shows in Front Page Slider.',
			'single' => true,
			'show_in_rest' => true,
		) );

		// Shows in Map Gallery
		register_meta( 'post', 'gallery', array(
			'sanitize_callback' => 'absint',
			// 'auth_callback' => '',
			'type' => 'integer',
			'description' => 'Whether this item shows in Gallery.',
			'single' => true,
			'show_in_rest' => true,
		) );

		// Is Map
		register_meta( 'post', 'map', array(
			'sanitize_callback' => 'absint',
			// 'auth_callback' => '',
			'type' => 'integer',
			'description' => 'Is Map?',
			'single' => true,
			'show_in_rest' => true,
		) );
		
		// Is Map
		register_meta( 'post', 'story-map', array(
			'sanitize_callback' => 'absint',
			// 'auth_callback' => '',
			'type' => 'integer',
			'description' => 'Is Story Map?',
			'single' => true,
			'show_in_rest' => true,
		) );

		// I'm guessing this will be used to give weight to certain tools, like within an issue.
		register_meta( 'post', 'display_order', array(
			'sanitize_callback' => 'absint',
			// 'auth_callback' => '',
			'type' => 'integer',
			'description' => 'How heavily to weight this tool.',
			'single' => true,
			'show_in_rest' => true,
		) );
	}

	/**
	 * Add a meta box to the data vis tools edit screen
	 *
	 * @since    1.0.0
	 */
	function add_meta_box() {
		add_meta_box( 'tools-meta-box', 'Map/Tool Info', array( $this, 'render_meta_box' ), $this->post_type, 'normal', 'high' );
	}

	/**
	 * Add a meta box to the data vis tools edit screen
	 *
	 * @since    1.0.0
	 */
	function render_meta_box( $post ) {
		$display_order 	= get_post_meta( $post->ID, 'display_order', true );
		$link         	= get_post_meta( $post->ID, 'alt_link', true );
		$gallery      	= get_post_meta( $post->ID, 'gallery', true );
		$slider      	= get_post_meta( $post->ID, 'slider', true );
		$is_map      	= get_post_meta( $post->ID, 'map', true );
		$is_story_map	= get_post_meta( $post->ID, 'story-map', true );
		// Add a nonce field so we can check for it later.
		wp_nonce_field( $this->nonce_name, $this->nonce_value );
		?>

		<p style="margin-top:2em;">
			<label for="display_order">Display Order</label>
			<input type="text" name="display_order" id="display_order" value="<?php echo absint( $display_order ); ?>" style="width:100%"/>
			<em>Input a number for the priority to give to this tool. Use small numbers for important tools, like 1 or 10, and larger numbers for less important tools, like 250 or 800.</em>
		</p>
		<p style="margin-top:.2em;">
			<input type="checkbox" name="map" id="map" <?php checked( $is_map ); ?> />
			<label for="map">Is Map</label>
		</p>
		<p style="margin-top:.2em;">
			<input type="checkbox" name="story-map" id="story-map" <?php checked( $is_story_map ); ?> />
			<label for="story-map">Is Story Map</label>
		</p>
		<p style="margin-top:.2em;">
			<input type="checkbox" name="slider" id="slider" <?php checked( $slider ); ?> />
			<label for="slider">Shows in Front Page Slider</label>
		</p>
		<p style="margin-top:.2em;">
			<input type="checkbox" name="gallery" id="gallery" <?php checked( $gallery ); ?> />
			<label for="gallery">Shows in Gallery</label>
		</p>
		<p style="margin-top:2em;">
			<label for="alt_link">Link to open tool</label>
			<input type="text" name="alt_link" id="alt_link" value="<?php echo esc_url( $link ); ?>" style="width:100%"/>
			<em>This value should look like http://datavis...</em>
		</p>
	<?php
	}

	/**
	 * Save the data entered in our meta box.
	 *
	 * @since    1.0.0
	 */
	function meta_box_save( $post_id ) {
		// Bail if we're doing an auto save
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( get_post_type( $post_id ) != $this->post_type ) {
			return;
		}

		if ( ! isset( $_POST[ $this->nonce_value ] ) || ! wp_verify_nonce( $_POST[ $this->nonce_value ], $this->nonce_name ) ) {
			return false;
		}

		// if our current user can't edit this post, bail
		if ( ! current_user_can( 'edit_post' ) ) {
			return;
		}

		if ( isset( $_POST['display_order'] ) ) {
			update_post_meta( $post_id, 'display_order', absint( $_POST['display_order'] ) );
		} else {
			// Default to a large number.
			update_post_meta( $post_id, 'display_order', 1000 );
		}

		if ( isset( $_POST['alt_link'] ) ) {
			update_post_meta( $post_id, 'alt_link', esc_url_raw( $_POST['alt_link'] ) );
		}

		$chk = ( isset( $_POST['gallery'] ) && $_POST['gallery'] ) ? '1' : '0';
		update_post_meta( $post_id, 'gallery', $chk );
		
		$chk = ( isset( $_POST['slider'] ) && $_POST['slider'] ) ? '1' : '0';
		update_post_meta( $post_id, 'slider', $chk );
		
		$chk = ( isset( $_POST['map'] ) && $_POST['map'] ) ? '1' : '0';
		update_post_meta( $post_id, 'map', $chk );
		
		$chk = ( isset( $_POST['story-map'] ) && $_POST['story-map'] ) ? '1' : '0';
		update_post_meta( $post_id, 'story-map', $chk );
	}

	/**
	 * Change behavior of the SA Policies overview table by adding taxonomies and custom columns.
	 * - Add Type and Stage columns (populated from post meta).
	 *
	 * @since    2.0.0
	 *
	 * @return   array of columns to display
	 */
	public function add_admin_table_columns( $columns ) {
		// Last two columns are always Comments and Date.
		// We want to insert our new columns just before those.
		$entries = count( $columns );
		$opening_set = array_slice( $columns, 0, $entries - 1 );
		$closing_set = array_slice( $columns, - 1 );

		$insert_set = array(
			'gallery' => __( 'Gallery', 'cares-atm-tools' ),
			'slider' => __( 'Slider', 'cares-atm-tools' ),
			'display_order' => __( 'Display Order', 'cares-atm-tools' )
			);

		$columns = array_merge( $opening_set, $insert_set, $closing_set );

		return $columns;
	}

	/**
	 * Change behavior of the CPT overview table by adding taxonomies and custom columns.
	 * - Handle Output for custom columns columns (populated from post meta).
	 *
	 * @since    2.0.0
	 *
	 * @return   string content of custom columns
	 */
	public function populate_custom_admin_table_columns( $column, $post_id ) {
			switch( $column ) {
				case 'gallery' :
					echo ( get_post_meta( $post_id, 'gallery', true ) ) ? 'yes' : '';
					break;
				case 'slider' :
					echo ( get_post_meta( $post_id, 'slider', true ) ) ? 'yes' : '';
					break;
				case 'display_order' :
					echo ( $order = get_post_meta( $post_id, 'display_order', true ) ) ? intval( $order ) : '';
					break;
			}
	}

	/**
	 * Change behavior of the CPT overview table by adding taxonomies and custom columns.
	 *
	 * @since    2.0.0
	 *
	 * @return   array of columns to display
	 */
	public function register_sortable_admin_table_columns( $columns ) {
		$columns['gallery'] = 'gallery';
		$columns['slider'] = 'slider';
		$columns['display_order'] = 'display_order';
		return $columns;
	}
	/**
	 * Change behavior of the SA Policies overview table by adding taxonomies and custom columns.
	 * - Define sorting query for Type and Stage columns.
	 *
	 * @since    2.0.0
	 *
	 * @return   alters $query variable by reference
	 */
	function sortable_columns_orderby( $query ) {
			if ( ! is_admin() ) {
				return;
			}

			$orderby = $query->get( 'orderby');

			switch ( $orderby ) {
				case 'gallery':
					$query->set( 'meta_key','gallery' );
					$query->set( 'orderby','meta_value' );
					break;
				case 'slider':
					$query->set( 'meta_key','slider' );
					$query->set( 'orderby','meta_value' );
					break;
				case 'display_order':
					$query->set( 'meta_key','display_order' );
					$query->set( 'orderby','meta_value_num' );
					break;
			}
	}

	/**
	 * Modify the permalinks for Tools. POint traffic to the alt-link if one has been set.
	 *
	 * @since    1.0.0
	 */
	public function cpt_permalink_filter( $permalink, $post ) {
		$post_type = get_post_type( $post );

		if ( $this->post_type !== $post_type ) {
			return $permalink;
		}

		// If a custom link has been set, use it preferentially.
		if ( $link = get_post_meta( $post->ID, 'alt_link', true ) ) {
			// If the link is incomplete, complete it.
			if ( is_null( parse_url( $link, PHP_URL_SCHEME ) ) ) {
				$link = site_url( $link );
			}

			$permalink = $link;
		}

	    return $permalink;
	}

	function atm_query_post_type($query) {
		if( $query->is_main_query()
			&& ( is_category() ) ) {
				$query->set( 'post_type', array( 'post','atm_tool','atm_issue' ) );
		}
	}

}

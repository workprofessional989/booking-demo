<?php
if ( ! class_exists( 'BookingRequest' ) ) {
    class BookingRequest {
        public function __construct() {
            add_action( 'init', array( $this, 'create_post_type' ) );           
			add_action( 'init',  array( $this, 'cptui_register_my_cpts_vehicle' ) );
			add_action( 'init', array( $this, 'cptui_register_my_taxes_vehicle_type' ) );
        }
        
        public function create_post_type() {
            register_post_type( 'wp_booking_request',
                array(
                    'labels' => array(
                        'name' => __( 'Booking Requests' ),
                        'singular_name' => __( 'Requests' ),
                    ),
                    'public' => false,
                    'has_archive' => false,
                    'show_ui' => true,
                    'show_in_nav_menus' => false,
                    'menu_position' => 25,
                    'menu_icon' => 'dashicons-list-view',
                    'supports' => array( 'title', 'editor', 'custom-fields' ),
                )
            );
        }
		
		
		function cptui_register_my_cpts_vehicle() {

		/**
		* Post Type: Vehicles.
		*/

		$labels = [
			"name" => __( "Vehicles", "twentytwentyone" ),
			"singular_name" => __( "Vehicle", "twentytwentyone" ),
		];

		$args = [
			"label" => __( "Vehicles", "twentytwentyone" ),
			"labels" => $labels,
			"description" => "",
			"public" => true,
			"publicly_queryable" => true,
			"show_ui" => true,
			"show_in_rest" => true,
			"rest_base" => "",
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"has_archive" => false,
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"delete_with_user" => false,
			"exclude_from_search" => false,
			"capability_type" => "post",
			"map_meta_cap" => true,
			"hierarchical" => false,
			"rewrite" => [ "slug" => "vehicles", "with_front" => true ],
			"query_var" => true,
			"supports" => [ "title", "editor", "thumbnail" ],
		];

			register_post_type( "vehicles", $args );
		}

		
		
		
		function cptui_register_my_taxes_vehicle_type() {

		/**
		* Taxonomy: Vehicle Types.
		*/

		$labels = [
			"name" => __( "Vehicle Types", "twentytwentyone" ),
			"singular_name" => __( "Vehicle Type", "twentytwentyone" ),
		];

		$args = [
			"label" => __( "Vehicle Types", "twentytwentyone" ),
			"labels" => $labels,
			"public" => true,
			"publicly_queryable" => true,
			"hierarchical" => true,
			"show_ui" => true,
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"query_var" => true,
			"rewrite" => [ 'slug' => 'vehicles_type', 'with_front' => true, ],
			"show_admin_column" => true,
			"show_in_rest" => true,
			"rest_base" => "vehicles_type",
			"rest_controller_class" => "WP_REST_Terms_Controller",
			"show_in_quick_edit" => true,
		];
			register_taxonomy( "vehicles_type", [ "vehicles" ], $args );
		}
		

  
		
    }
	
	
	add_action( 'admin_menu', 'booking_add_metabox' );

	function booking_add_metabox() {
	 
		add_meta_box(
			'book_metabox', // metabox ID
			'Booking Status', // title
			'book_metabox_callback', // callback function
			'wp_booking_request' ,
			'normal', // position (normal, side, advanced)
			'default' // priority (default, low, high, core)
		);
	 
	}
	 
	function book_metabox_callback( $post ) {
		// Add meta for booking status
		wp_nonce_field( 'somerandomstr', '_mishanonce' );
	 
		echo '<table class="form-table">
			<tbody>			
				<tr>
					<th><label for="book_status">Status</label></th>
					<td>
						<select id="seo_robots" name="seo_robots">
							<option value="">Select...</option>
							<option value="Pending">Pending</option>
							<option value="Approved">Approved</option>
							<option value="Reject">Reject</option>
							<option value="way">On the way</option>
							<option value="Complete">Complete</option>
						</select>
					</td>
				</tr>
			</tbody>
		</table>';
	 
	}
	
}

$bookingrequest = new BookingRequest;
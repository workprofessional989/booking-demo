<?php
/**
 * Plugin Name: booking vehicle
 * Plugin URI:  https://www.upwork.com/freelancers/~018623d8a349080dbf
 * Description: A simple Booking System
 * Version:     1.0
 * Author:      Mohit Sharma
 */

require_once( __DIR__ . '/includes/class-formMaker.php' );
require_once( __DIR__ . '/admin/class-BookingRequest.php' );



// Main Plugin Class
if ( ! class_exists( 'BookingVehicle' ) ) {
    class BookingVehicle {
        public function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_shortcode( 'bookingbehicle', array( $this, 'form' ) );
            add_action( 'admin_post_nopriv_wp_booking_request', array( $this, 'form_handler' ) ); 			
			add_action('wp_ajax_nopriv_getvehicles', array( $this, 'getvehicles' ));
			add_action('wp_ajax_getvehicles', array( $this, 'getvehicles' ) );
			add_action('wp_ajax_nopriv_getvehiclesprice', array( $this, 'getvehiclesprice' ));
			add_action('wp_ajax_getvehiclesprice', array( $this, 'getvehiclesprice' ) );
			
        }
        
        public function enqueue_scripts() {
            wp_enqueue_style( 'bookingbehicle', plugins_url( '/public/css/style.css', __FILE__ ), array(), 0.1 );
			wp_enqueue_script('jquery'); 
			wp_enqueue_script( 'ajax-script', plugins_url( 'js/getvehicle.js', __FILE__ ), array('jquery'), 999, true);
			 wp_localize_script( 'ajax-script', 'my_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
        }
        
        public function form($atts) {
			global $post;
			
			$atts = shortcode_atts(
			array(
			  'add_honeypot' => false,
			), $atts, 'bookingbehicle' );
          
		  
			$vehicle_types = get_terms( array(
				'taxonomy' => 'vehicles_type',
				'hide_empty' => false,
			) );
		  
			$vtypes = array();	
			if(!empty($vehicle_types)){
				$vtypes[0] = "Select Vehicle Type";
				foreach($vehicle_types as $vehicle_type){
					$key = $vehicle_type->slug;
					$name = $vehicle_type->name;
					$vtypes[$key] = $name;
				}
			}
		  
			// Instantiate form class
			$form = new FormMaker();
			
			// Set form options
			$form->set_att( 'action', esc_url( admin_url( 'admin-post.php' ) ) );
		   
			
			// Add form inputs
			$form->add_input( 'action', array(
				  'type' => 'hidden',
				  'value' => 'wp_booking_request',
				  ), 'action' );
				
			$form->add_input( 'wp_nonce', array(
				'type' => 'hidden',
				'value' => wp_create_nonce( 'submit_wp_simple_form' ),
				), 'wp_nonce' );
				
			$form->add_input( 'redirect_id', array(
				'type' => 'hidden',
				'value' => $post->ID,
				), 'redirect_id' );
				
			$form->add_input( __( 'First Name', 'wp-simple-booking' ), array(
				'type' => 'text',
				'placeholder' => __( 'Enter your First name', 'wp-simple-booking' ),
				'required' => true,
				), 'fname' );
			
			$form->add_input( __( 'Last Name', 'wp-simple-booking' ), array(
				'type' => 'text',
				'placeholder' => __( 'Enter your Last name', 'wp-simple-booking' ),
				'required' => true,
				), 'lname' );

			
			$form->add_input( __( 'Email', 'wp-simple-booking' ), array(
				'type' => 'email',
				'placeholder' => __( 'Enter your email address', 'wp-simple-booking' ),
				'required' => true,
				), 'email' );
				
				
			$form->add_input( __( 'Phone', 'wp-simple-booking' ), array(
				'type' => 'text',
				'placeholder' => __( 'Enter your Phone', 'wp-simple-booking' ),
				'required' => true,
				), 'phone' );	
				
				
			$form->add_input( __( 'Vehicle Type', 'wp-simple-booking' ), array(
				'type' => 'select',
				'placeholder' => __( 'Select your Vehicle Type', 'wp-simple-booking' ),
				'required' => true,
				'id' => 'vehicletype',
				'options' => $vtypes,
				), 'type' );

			 $form->add_input( __( 'Vehicle ', 'wp-simple-booking' ), array(
				'type' => 'select',
				'placeholder' => __( '', 'wp-simple-booking' ),
				'id' => 'vehicleselect',
				'required' => true,
				'options' => array('value' => 'Select Vehicle'),
				), 'vehicle' );


				
			 $form->add_input( __( 'Vehicle Price ', 'wp-simple-booking' ), array(
				'type' => 'text',
				'placeholder' => __( '', 'wp-simple-booking' ),
				'required' => true,
				), 'price' );   
		 
				
			$form->add_input( __( 'Message', 'wp-simple-booking' ), array(
				'type' => 'textarea',
				'placeholder' => __( 'Enter your message', 'wp-simple-booking' ),
				'required' => true,
				), 'message' );
				
			// Shortcodes should not output data directly
			ob_start(); 
			
			// Status message
			$status = filter_input( INPUT_GET, 'status', FILTER_VALIDATE_INT );
			
			if ( $status == 1 ) {
				printf( '<div class="wp-simpleform message success"><p>%s</p></div>', __( 'Submitted successfully!', 'wp-simple-booking' ) );
			}
			
			// Build the form
			$form->build_form();
			
			// Return and clean buffer contents
			return ob_get_clean();
        }
        
        public function form_handler() {
            $post = $_POST;
            
            // Verify nonce
            if ( ! isset( $post['wp_nonce'] ) || ! wp_verify_nonce( $post['wp_nonce'], 'submit_wp_simple_form') ) {
                wp_die( __( "Cheatin' uh?", 'wp-simple-booking' ) );
            }
            
            // Verify required fields
            $required_fields = array( 'fname', 'lname', 'email', 'phone' );
            
            foreach ( $required_fields as $field ) {
                if ( empty( $post[$field] ) ) {
                    wp_die( __( "fields are required.", 'wp-simple-booking' ) );
                }
            }
            
            // Build post arguments
            $postarr = array(
                'post_author' => 1,
                'post_title' => sanitize_text_field( $post['fname'] .' '. $post['lname']),
                'post_content' => sanitize_textarea_field( $post['message'] ),
                'post_type' => 'wp_booking_request',
                'post_status' => 'publish',
                'meta_input' => array(
                    'booking_email' => sanitize_email( $post['email'] ),
                    'booking_phone' => sanitize_text_field( $post['phone'] ),
                    'booking_vehicle_type' => sanitize_text_field( $post['type'] ),
                    'booking_vehicle' => sanitize_text_field( $post['vehicle'] ),
                    'booking_price' => sanitize_text_field( $post['price'] ),
                )
            );
            
            // Insert the post
            $postid = wp_insert_post( $postarr, true );

            if ( is_wp_error( $postid ) ) {
                wp_die( __( "There was problem with your submission. Please try again.", 'wp-simple-booking' ) );
            }
            
            // Send emails to admins
            $to = array();
            $post_edit_url = sprintf( '%s?post=%s&action=edit', admin_url( 'post.php' ), $postid );
            $admins = get_users( array( 'role' => 'administrator' ) );
            
            foreach ( $admins as $admin ) {
                $to[] = $admin->user_email;
            }
            
            // Build the email
            $subject = __( 'New Booking!', 'wp-simple-booking' );
            $message = sprintf( '<p>%s</p>', __( 'Here are the details:', 'wp-simple-booking' ) ) ;
            $message .= sprintf( '<p>%s: %s<br>', __( 'Name', 'wp-simple-booking' ), sanitize_text_field( $post['fname'].' '.$post['lname']) );
            $message .= sprintf( '<p>%s: %s<p>', __( 'Email', 'wp-simple-booking' ), sanitize_textarea_field( $post['email'] ) );
            $message .= sprintf( '<p>%s: <a href="%s">%s</a>', __( 'View/edit the full details here', 'wp-simple-booking' ), $post_edit_url, $post_edit_url );
            $headers = array('Content-Type: text/html; charset=UTF-8');
            
            // Send the email
            wp_mail( $to, $subject, $message, $headers );
            
            // Redirect back to page
            wp_redirect( add_query_arg( 'status', '1', get_permalink( $post['redirect_id'] ) ) );
        }
		
		
		
		 
		function getvehicles(){		
			$data = $_REQUEST;	
			$vehicletype = $data['vehicletype'];
			$myposts = get_posts(array(
			'numberposts' => -1,
			'post_type' => 'vehicles',	
				'tax_query' => array(
					array(
						'taxonomy' => 'vehicles_type',
						'field' => 'slug',
						'terms' => $vehicletype,
					),
				),			
			));
			$options = '<option value="" >Select Vehicle</option>';
			foreach( $myposts as $vehicles){

			$options .=  '<option value="'.$vehicles->ID.'" >'.$vehicles->post_title.'</option>';
			}
			echo $options; die();
			exit;

		}
		 
		 function getvehiclesprice(){		
			$data = $_REQUEST;	
			$vehicleid = $data['vehicle'];
			echo $price = get_post_meta( $vehicleid , 'vehicle_price', true );	die();	

		}
		 
		 
		 
		 
		 
		 
		 
		 
		
    }
	
	
}


// Calling Obeject of a class
$bookingbehicle = new BookingVehicle;
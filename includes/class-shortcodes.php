<?php 

/**
* Load the base class
*/
class Planning_Center_WP_Shortcodes {
	
	function __construct()	{
		
		add_shortcode( 'pcwp_upcoming_service_details', array( $this, 'upcoming_service_details' ) );

	}

	public function upcoming_service_details( $atts )
	{
		$args = shortcode_atts( array(
			'method'	=> '',
			'parameters'		=> '',
		), $atts );

		$api = new PCO_PHP_API;
		$result = $api->get_upcoming_service_details( $args );
		ob_start(); ?>

		<?php
			echo $result;
		?>

		<?php $content = ob_get_contents();
		ob_end_clean();

		return apply_filters('planning_center_wp_services_shortcode_output', $content );
	}
	
}


			
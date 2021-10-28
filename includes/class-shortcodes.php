<?php 

/**
* Load the base class
*/
class Planning_Center_WP_Shortcodes {
	
	function __construct()	{
		
		add_shortcode( 'pcwp_checkins', array( $this, 'checkins' ) );
		add_shortcode( 'pcwp_giving', array( $this, 'giving' ) );
		add_shortcode( 'pcwp_people', array( $this, 'people' ) );
		add_shortcode( 'pcwp_services', array( $this, 'services' ) );
		add_shortcode( 'pcwp_upcoming_services', array( $this, 'upcoming_services' ) );
		add_shortcode( 'pcwp_upcoming_service_details', array( $this, 'upcoming_service_details' ) );

	}

	public function people( $atts ) 
	{
		$args = shortcode_atts( array(
			'method' 	=> '',
			'parameters'	=> '',
    	), $atts );

    	$api = new PCO_PHP_API;
    	$people = $api->get_people( $args );

    	ob_start(); ?>

    	<?php 
    		echo '<h3 class="planning-center-wp-title">' . ucwords( $args['method'] ) . ' in People</h3>';
    		if ( is_array( $people ) ) {
				
				echo '<ul class="planning-center-wp-list planning-center-wp-people-list">';
				foreach( $people as $person ) {
					
					echo '<li>' . $person->attributes->first_name . ' ' . $person->attributes->last_name . '</li>';
				}
				echo '</ul>';
			} else {
				echo '<p class="planning-center-wp-not-found">No results found.</p>';
			}
		?>
	
		<?php  $content = ob_get_contents();
		ob_end_clean();

		return apply_filters('planning_center_wp_people_shortcode_output', $content ); 	

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
			// echo '<h2 style="text-align: center; font-size: 40px; color: #f9f5f1;" data-fusion-font="true">';
			echo $result;
			// echo '</h2>';
		?>

		<?php $content = ob_get_contents();
		ob_end_clean();

		return apply_filters('planning_center_wp_services_shortcode_output', $content );
	}

	public function upcoming_services( $atts )
	{
		$args = shortcode_atts( array(
			'method'	=> '',
			'parameters'		=> '',
		), $atts );

		$api = new PCO_PHP_API;
		$upcoming_services = $api->get_upcoming_services( $args );
		ob_start(); ?>

		<?php
			echo '<h3 class="planning-center-wp-title">' . 'Our upcoming services</h3>';
			if ( is_array( $upcoming_services ) && !empty( $upcoming_services ) ) {
				echo '<ul class="planning-center-wp-list planning-center-wp-services-list">';
				$plans = $upcoming_services[0];
				$dates = $upcoming_services[1];
				$times = $upcoming_services[2];
				$titles = $upcoming_services[3];
				$series = $upcoming_services[4];
				$series_art = $upcoming_services[5];
				$count = count($plans);
				foreach( range(0,$count-1) as $i) {
					echo '<li>' . $dates[$i] . ' ' . $times[$i] . ' ' . $titles[$i] . ' ' . $series[$i] . ' ' . $series_art[$i] . '</li>';
				}
				// foreach( $upcoming_services as $upcoming_service ) {
					// echo '<li>' . $upcoming_service->attributes->title . ' ' . $upcoming_service->attributes->dates . '</li>';
				// }
				echo '</ul>';
			} else {
				echo '<p class="planning-center-wp-not-found">No results found.</p>';
			}
		?>
	
		<?php  $content = ob_get_contents();
		ob_end_clean();

		return apply_filters('planning_center_wp_services_shortcode_output', $content ); 	
	}

	public function services( $atts ) 
	{
		$args = shortcode_atts( array(
			'method' 	=> '',
			'parameters'	=> '',
    	), $atts );

    	$api = new PCO_PHP_API;
    	$services = $api->get_services( $args );

    	ob_start(); ?>

    	<?php 
    		echo '<h3 class="planning-center-wp-title">' . ucwords( $args['method'] ) . ' in Services</h3>';
    		if ( is_array( $services ) && !empty( $services ) ) {
				// @todo load a certain view based on the method passed
				echo '<ul class="planning-center-wp-list planning-center-wp-services-list">';
				foreach( $services as $service ) {
					echo '<li>' . $service->attributes->first_name . ' ' . $service->attributes->last_name . '</li>';
				}
				echo '</ul>';
			} else {
				echo '<p class="planning-center-wp-not-found">No results found.</p>';
			}
		?>
	
		<?php  $content = ob_get_contents();
		ob_end_clean();

		return apply_filters('planning_center_wp_services_shortcode_output', $content ); 	
	}

	public function donations( $atts ) 
	{
		$a = shortcode_atts( array(
	        'foo' => 'something',
	        'bar' => 'something else',
    	), $atts );

    	$api = new PCO_PHP_API;
    	$donations = $api->get_donations();

    	ob_start(); ?>

    	<?php 

    		if ( is_array( $donations ) ) {
				echo '<h3>Donations</h3>';
				echo '<ul>';
				foreach( $donations as $item ) {
					echo '<pre>';
					print_r( $item );
					echo '</pre>';
					// echo '<li>' . $item->attributes->first_name . ' ' . $item->attributes->last_name . '</li>';
				}
				echo '</ul>';
			} else {
				echo '<p>' . $donations . '</p>';
			}
		?>
	
		<?php  $content = ob_get_contents();
		ob_end_clean();

		return $content; 	
	}
	
}


			
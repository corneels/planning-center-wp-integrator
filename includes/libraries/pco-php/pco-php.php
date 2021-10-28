<?php 

/**
* Load the base class for the PCO PHP API
* // http://planningcenter.github.io/api-docs/#schedules

*/


class PCO_PHP_API {
	
	protected $app_id;
	protected $secret;

	function __construct()	{
		
		$options = get_option('planning_center_wp');

		$this->app_id = $options['app_id'];
		$this->secret = $options['secret'];
		$this->number_of_services = 3;
		$this->transient_time = $options['refresh_interval'];
		$this->speaker_position_name = 'Preacher';
		$this->backup_artwork_url = $options['backup_artwork_url'];
		$this->scripture_prefix = $options['scripture_prefix'];

	}
	
	

	public function get_people( $args = '') 
	{	

		$method = $args['method'];

		$people = new PCO_PHP_People($args);
		$url = $people->$method();

		$response = wp_remote_get( $url, $this->get_headers() );
		$result = '';

		if( is_array($response) ) {
		  $header = $response['headers']; // array of http header lines
		  $body = json_decode( $response['body'] ); // use the content

		  if ( isset( $body->errors[0]->detail ) ) {
		  	$result = $body->errors[0]->detail;
		  } else {
		  	$result = apply_filters( 'planning_center_wp_get_people_body', $body->data, $body );
		  }

		}

		return $result;

	}

	public function get_upcoming_service_details ( $args = '' )
	{
		$method = $args['method'];
		if ( false === ( $plan_id = get_transient( 'plan_id' ) ) ) {
			$services = new PCO_PHP_Services($args);
			$url = $services->upcoming_services(1);
			$response = wp_remote_get( $url, $this->get_headers() );
			
			if( is_array($response) ) {
				$header = $response['headers'];
				$body = json_decode( $response['body'] );
				if ( isset( $body->errors[0]->detail ) ) {
					$results = $body->errors[0]->detail;
				} else {
					$results = apply_filters( 'planning_center_wp_get_upcoming_services_body', $body->data, $body );
					$inclusions = apply_filters( 'planning_center_wp_get_upcoming_services_body', $body->included, $body );
				}
			}

			// PLAN ID
			$plan = $results[0];
			$plan_id = $plan->id;
			set_transient( 'plan_id',  $plan_id, $this->transient_time - 10);

			// SERVICE TIME
			$dttm = $plan->attributes->sort_date;
			$time = date('g:i a', strtotime($dttm));
			set_transient( 'time',  $time, $this->transient_time );

			// SERVICE TITLE
			$title = $plan->attributes->title;
			set_transient( 'title',  $title, $this->transient_time );

			// SERIES TITLE
			$series = $plan->attributes->series_title;
			set_transient( 'series',  $series, $this->transient_time );

			// SERIES ARTWORK
			if( $plan->attributes->series_title != '' ) {
				$artwork_url = $inclusions[0]->attributes->artwork_original;
			} else {
				$artwork_url = $this->backup_artwork_url;
			}
			$series_art = '<img src="' . $artwork_url . '">';
			set_transient( 'series_art', $series_art, $this->transient_time );

			// SCRIPTURE READING
			$url = $services->plan_items($plan_id);
			$plan_items = wp_remote_get( $url, $this->get_headers() );

			if( is_array($plan_items) ) {
				$body = json_decode( $plan_items['body'] );
				if ( isset( $body->errors[0]->detail ) ) {
					$results = $body->errors[0]->detail;
				} else {
					$results = apply_filters( 'planning_center_wp_get_team_members_body', $body->data, $body );
				}
			}
			$scripture_prefix = $this->scripture_prefix;
			foreach( $results as $plan_item ) {
				$item_title = $plan_item->attributes->title;
				$prefix_length = strlen($scripture_prefix);
				if ( substr($item_title, 0, $prefix_length) === $scripture_prefix ) {
					$reference = substr($item_title, $prefix_length);
				}
			}
			if ( $reference === ' [insert]' ) {
				$reference = '';
			}
			set_transient( 'scripture', $reference, $this->transient_time );

			// // SPEAKER
			// $url = $services->team_members($plan_id);
			// $team_members = wp_remote_get( $url, $this->get_headers() );
			// if( is_array($team_members) ) {
			// 	$body = json_decode( $team_members['body'] );
			// 	if ( isset( $body->errors[0]->detail ) ) {
			// 		$results = $body->errors[0]->detail;
			// 	} else {
			// 		$results = apply_filters( 'planning_center_wp_get_team_members_body', $body->data, $body );
			// 	}
			// }
			// foreach( $results as $team_member ) {
			// 	if ( $team_member->attributes->team_position_name == $this->speaker_position_name ) {
			// 		$speaker = $team_member->attributes->name;
			// 	}
			// }
			// set_transient( 'speaker', $speaker, $this->transient_time );

		} else {
			$time = get_transient( 'time' );
			$title = get_transient( 'title' );
			$series = get_transient( 'series' );
			$series_art = get_transient( 'series_art' );
			$scripture = get_transient( 'scripture' );
			// $speaker = get_transient( 'speaker' );
		}
		$result = '';
		switch( $method ) {
			case 'time':
				$result = $time;
				break;
			case 'title':
				$result = $title;
				break;
			case 'series':
				$result = $series;
				break;
			case 'series_art':
				$result = $series_art;
				break;
			// case 'speaker':
			// 	$result = $speaker;
			// 	break;
			case 'scripture':
				$result = $scripture;
				break;
			default:
				$result = "Invalid method provided. Valid methods are time, speaker, title, series_art, series_name, date, scripture.";
			}
		return $result;

	}

	public function get_upcoming_services( $args = '' )
	{

		$upcoming_plans = array();
		$upcoming_dates = array();
		$upcoming_times = array();
		$upcoming_titles = array();
		$upcoming_series = array();
		$upcoming_series_art_urls = array();
		$upcoming_speakers = array();
		$upcoming_scripture_references = array();
		$result = '';

		$method = $args['method'];
		$services = new PCO_PHP_Services($args);
		$url = $services->upcoming_services(1);
		$response = wp_remote_get( $url, $this->get_headers() );
		$upcoming_services = '';
		
		
		if( is_array($response) ) {
			$header = $response['headers']; // array of http header lines
			$body = json_decode( $response['body'] ); // use the content
			if ( isset( $body->errors[0]->detail ) ) {
				$results = $body->errors[0]->detail;
			} else {
				$results = apply_filters( 'planning_center_wp_get_upcoming_services_body', $body->data, $body );
				$inclusions = apply_filters( 'planning_center_wp_get_upcoming_services_body', $body->included, $body );
			}
		}

		foreach( $results as $result ) {
			$plan_id = $result->id;
			$upcoming_plans[] = $plan_id;
			$upcoming_dates[] = $result->attributes->dates;
			$upcoming_times[] = $result->attributes->sort_date;
			$upcoming_titles[] = $result->attributes->title;
			$upcoming_series[] = $result->attributes->series_title;
			if( $result->attributes->series_title != '' ) {
				foreach( $inclusions as $inclusion ) {
					if( $inclusion->id == $result->relationships->series->data->id ) {
						$artwork_url = $inclusion->attributes->artwork_original;
					}
				}
			} else {
				$artwork_url = '';
			}
			$upcoming_series_art_urls[] = $artwork_url;

			// Get speaker name
			$url = $services->team_members($plan_id);
			$response = wp_remote_get( $url, $this->get_headers() );

			if( isarray($response) ) {
				$body = json_decode( $response['body'] );
				if ( isset( $body->errors[0]->detail ) ) {
					$results = $body->errors[0]->detail;
				} else {
					$results = apply_filters( 'planning_center_wp_get_team_members_body', $body->data, $body );
				}
			}
			// get speaker name
			
			// get speaker picture??

			// foreach( $

			// Get scripture reference


		}
		$result = array($upcoming_plans, $upcoming_dates, $upcoming_times, $upcoming_titles, $upcoming_series, $upcoming_series_art_urls);
		return $result;

	}



	public function get_services( $args = '' ) 
	{
		
		$method = $args['method'];

		$services = new PCO_PHP_Services($args);
		$url = $services->$method();

		$response = wp_remote_get( $url, $this->get_headers() );
		$result = '';

		if( is_array($response) ) {
		  $header = $response['headers']; // array of http header lines
		  $body = json_decode( $response['body'] ); // use the content

		  if ( isset( $body->errors[0]->detail ) ) {
		  	$result = $body->errors[0]->detail;
		  } else {
		  	$result = apply_filters( 'planning_center_wp_get_services_body', $body->data, $body );
		  }

		}

		return $result;

	}

	public function get_donations() 
	{
		
		$response = wp_remote_get( 'https://api.planningcenteronline.com/giving/v2/donations', $this->get_headers() );

		if( is_array($response) ) {
		  $header = $response['headers']; // array of http header lines
		  $body = json_decode( $response['body'] ); // use the content

		  echo '<pre>';
		  print_r( $body );
		  echo '</pre>';

		  $donations = $body->data;

		} else {
			$donations = 'Could not be found.';
		}

		return $donations;

	}

	public function get_headers() 
	{
		return array(
		  'headers' => array(
		    'Authorization' => 'Basic ' . base64_encode( $this->app_id . ':' . $this->secret )
		  )
		);
	}

}
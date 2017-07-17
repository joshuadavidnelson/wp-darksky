<?php
/**
 * A helper class for the darksky.net API.
 *
 * Inspired by forecast-php by Guilherm Uhelski: https://github.com/guhelski/forecast-php
 *
 * @see https://darksky.net/dev/docs
 * 
 * @link https://github.com/joshuadavidnelson/wp-darksky
 *
 * @version 1.1.1
 *
 * @author Joshua David Nelson, josh@joshuadnelson.com
 * 
 * @license GPL v2.0+
 */

namespace DarkSky;

class Forecast {
	
	/**
	 * The API base url.
	 * 
	 * @since 1.0.0
	 * @since 1.1.0 - Updated to Dark Sky API from Forecast.io
	 */
	const API_ENDPOINT = 'https://api.darksky.net/forecast/';
	
	/**
	 * The arguments.
	 * 
	 * @since 1.0.0
	 */
	private $args = array();
	
	/**
	 * The default arguments.
	 * 
	 * @since 1.0.0
	 */
	private $defaults = array(
		'api_key'		=> null,
		'latitude'		=> null,
		'longitude'		=> null,
		'time'			=> null, // Time in seconds
		'cache_prefix'	=> 'api_', // careful here, md5 is used on the request url to generate the transient name. You are limited to an 8 character prefix before the combined total exceeds the transient name limit
		'cache_enabled'	=> true,
		'cache_time'	=> 21600, // Time in seconds, defaults to 6 hours
		'clear_cache'	=> false, // set to true to force the cache to clear
		'query'			=> array(),
	);
	
	/**
	 * The final request url.
	 * 
	 * @since 1.0.0
	 */
	public $request_url;
	
	/**
	 * The stored response.
	 * 
	 * @since 1.0.0
	 */
	private $response = array();
	
	/**
	 * Build it.
	 * 
	 * @uses wp_array_slice_assoc()
	 * @uses wp_parse_args()
	 * 
	 * @since 1.0.0
	 * 
	 * @param array $args The array of arguments.
	 */
	public function __construct( $args = array() ) {
		
		if( ! isset( $args['api_key'], $args['latitude'], $args['longitude'] ) )
			return false;
		
		// Limit the arguments keys listed in the default array, then parse the submitted arguments
		$limit_keys = array_keys( $this->defaults );
		$args = wp_array_slice_assoc( $args, $limit_keys );
		$this->args = wp_parse_args( $args, $this->defaults );
		
		// Build the query string for the forecast url
		$query_string = is_array( $this->query ) ? http_build_query( $this->query ) : '';
		
		// if we have a query string, set it up for the url
		$query = !empty( $query_string ) ? '?' .$query_string : '';
		
		// Build the request url
		$this->request_url = self::API_ENDPOINT . esc_attr( $this->api_key ) . '/' . floatval( $this->latitude ) . ',' . floatval( $this->longitude ) . ( ( is_null( $this->time ) ) ? '' : ','. $this->time ) . $query;
		
		// Get and save the response
		$this->response = $this->get_response( $this->clear_cache );
	}
	
	/**
	 * Set the transient name.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $url The request url.
	 * 
	 * @return string $transient_name
	 */
	private function transient_name( $url ) {
		return $this->cache_prefix . md5( $url );
	}
	
	/**
	 * Get the response, either via a transient or via a call.
	 * 
	 * @uses get_transient()
	 * @uses set_transient()
	 * 
	 * @since 1.0.0
	 * 
	 * @param boolean $clear_cache Set true to reset the transient.
	 * 
	 * @return array $response
	 */
	public function get_response( $clear_cache = false ) {
		
		// If caching is enabled, let's check the cache.
		if( $this->cache_enabled ) {
			
			// Get the transient
			$transient_name = $this->transient_name( $this->request_url );
			$transient = get_transient( $transient_name );
			
			// Grab the response
			if( ! $transient || $clear_cache ) {
				$response = $this->request();
				
				if( $response )
					set_transient( $transient_name, $response, $this->cache_time );
				
			} else {
				$response = $transient;
			}
		
		// If no caching, just grab the response.
		} else {
			$response = $this->request();
		}
		
		return $response;
	}
	
	/**
	 * Refresh the response, returns a new API response.
	 * 
	 * @since 1.0.0
	 * 
	 * @return array $response
	 */
	public function refresh_response() {
		return $this->get_response( true );
	}
	
	/**
	 * Execute the request
	 * 
	 * @uses wp_remote_get
	 * @uses wp_remote_retrieve_body
	 * 
	 * @since 1.0.0
	 * 
	 * return array $json The JSON response.
	 */
	private function request() {
		
		$response = wp_remote_get( esc_url($this->request_url, 'https', 'api') );
		try {
			$json = json_decode( wp_remote_retrieve_body( $response ), true );
		} catch ( Exception $ex ) {
			$json = array();
		}
		
		return $json;
	}
	
	/**
	 * Magical method to grab either a response argument or a class argument.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $property
	 * 
	 * @return mixed 
	 */
	public function __get( $property ) {
		// check if the property exists in the response first
		if( array_key_exists( $property, $this->response ) ) {
			return $this->response[ $property ];
			
		// check if this is an option we're looking for
		} elseif( array_key_exists( $property, $this->args ) ) {
			return $this->args[ $property ];
			
		// when all else false, return null.
		} else {
			return null;
		}
	}
	
	/**
	 * Set a class argument with the magic method.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $key The argument key.
	 * @param mixed $value The argument value, typically a string or array.
	 * 
	 * @return mixed $value The updated value, false on failure.
	 */
	public function __set( $key, $value ) {
		if( array_key_exists( $key, $this->args ) ) {
			$this->args[ $key ] = $value;
			return $this->args[ $key ];
		} else {
			return false;
		}
	}
}
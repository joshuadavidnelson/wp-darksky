<?php
/**
 * A helper class for the forecast.io app.
 *
 * Inspired by forecast-php by Guilherm Uhelski: https://github.com/guhelski/forecast-php
 *
 * @link https://developer.forecast.io/docs/v2
 *
 * @version 1.0.0
 *
 * @author Joshua David Nelson, josh@joshuadnelson.com
 */

namespace Forecast;

class Forecast {
	
	/**
	 * The forecast API base url.
	 * 
	 * @since 1.0.0
	 */
	const API_ENDPOINT = 'https://api.forecast.io/forecast/';
	
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
		'time'			=> null,
		'cache_prefix'	=> 'forecast_api_request_',
		'cache_enabled'	=> true,
		'cache_time'	=> 6 * HOUR_IN_SECONDS,
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
	 * @since 1.0.0
	 */
	public function __construct( $args = array() ) {
		
		if( ! isset( $args['api_key'], $args['latitude'], $args['longitude'] ) )
			return false;
		
		// Limit the arguments keys listed in the default array, then parse the submitted arguments
		$limit_keys = array_keys( $this->defaults );
		$args = wp_array_slice_assoc( $args, $limit_keys );
		$this->args = wp_parse_args( $args, $this->defaults );
		
		// Build the query arguments for the forecast url
		$query = ( !empty( $this->query ) && is_array( $this->query ) ) ? '?'. http_build_query( $this->query ) : '';
		
		// Build the request url
		$this->request_url = self::API_ENDPOINT . esc_attr( $this->api_key ) . '/' . floatval( $this->latitude ) . ',' . floatval( $this->longitude ) . ( ( is_null( $this->time ) ) ? '' : ','. $this->time ) . $query;
		
		// Get and save the response
		$this->response = $this->get_response();
	}
	
	/**
	 * Set the transient name.
	 * 
	 * @since 1.0.0
	 */
	private function transient_name( $url ) {
		return $this->cache_prefix . md5( $url );
	}
	
	/**
	 * Get the response, either via a transient or via a call.
	 * 
	 * @since 1.0.0
	 */
	public function get_response() {
		if( $this->cache_enabled ) {
			$transient_name = $this->transient_name( $this->request_url );
			$transient = get_transient( $transient_name );
			
			if( ! $transient || $this->clear_cache ) {
				$response = $this->request();
				
				if( $response )
					set_transient( $this->transient_name, $response, $this->cache_time );
				
			} else {
				$response = $transient;
			}
			
		} else {
			$response = $this->request();
		}
		
		return $response;
	}
	
	/**
	 * Execute the request
	 * 
	 * @uses wp_remote_get
	 * @uses wp_remote_retrieve_body
	 * 
	 * @since 1.0.0
	**/
	private function request() {
		
		$response = wp_remote_get( esc_url( $this->request_url ) );
		try {
			$json = json_decode( wp_remote_retrieve_body( $response ), true );
		} catch ( Exception $ex ) {
			$json = null;
		}
		
		return $json;
	}
	
	/**
	 * Magical method to grab either a response argument or a class argument.
	 * 
	 * @since 1.0.0
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
	 * Set a class argument with the matgic method.
	 * 
	 * @since 1.0.0
	 */
	public function __set( $key, $value ) {
		return $this->args[ $key ];
	}
}

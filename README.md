# WP-Forecast-io
A helper class for using forecast.io in WordPress. Requires a latitude, longitude, and Forecast.io API Key. It's set up to use a transient by default, refreshed every 6 hours, to limit the number of API calls made in a day (the first 1,000/day are free).

See more about the API in the [Forecast API Documentation](https://developer.forecast.io/docs/v2).

#### Example

You can call this class and output the data of the response fairly easily. Here's an example of a call to get the current day's temperature.

```php

$args = array(
	'api_key' 	=> $api_key,
	'latitude'	=> $lat,
	'longitude'	=> $long,
	'query'		=> array( 'units' => 'us', 'exclude' => 'flags' )
);
$forecast = new Chelan\Forecast( $args );

// Get the current forecast data for the daily forecast, which provides the next 7 days
$daily = isset( $forecast->daily['data'] ) ? $forecast->daily['data'] : '';

// Pull out the current day's forecast
if( is_array( $daily ) ) {
	$date_format = 'n/j/Y';
	$time_now = date( $date_format, current_time( 'timestamp' ) );
	foreach( $daily as $day ) {
		if( isset( $day['time'] ) && $time_now == date( $date_format, $day['time'] ) ) {
			echo number_format( $day['temperatureMin'], 0 ) . ' / ' . number_format( $day['temperatureMax'], 0 );
			break;
		}
	}
}
```

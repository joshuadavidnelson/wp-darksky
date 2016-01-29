# WP-Forecast-io
A helper class for using forecast.io in WordPress. Requires a latitude, longitude, and Forecast.io API Key. It's set up to use a transient by default, refreshed every 6 hours, to limit the number of API calls made in a day (the first 1,000/day are free).

See more about the API in the [Forecast API Documentation](https://developer.forecast.io/docs/v2).

#### Arguments

There are a few arguments available with this class to extend it further. 

- `api_key` This is required, sign up for one at https://developer.forecast.io/
- `latitude` Required.
- `longitude` Required.
- `time`	Optional. Pass a timestamp for the forecast at a specific time.
- `cache_prefix` The transient prefix, defaults to 'forecast_api_request_'.
- `cache_enabled`	Boolean, default to true.
- `cache_time` Time to store the transient in seconds, defaults to 6 hours.
- `clear_cache` Boolean, default false. Set to true to force the cache to clear.
- `query`	An array of url query arguments, refer to the Forecast.io documentation.

#### Example

You can call this class and output the data of the response fairly easily. Here's an example of a call to get the current day's temperature.

```php
$args = array(
	'api_key' 	=> '', // Enter your API key
	'latitude'	=> '', // enter the longitude
	'longitude'	=> '', // enter the latitude
	'query'		=> array( 'units' => 'us', 'exclude' => 'flags' )
);
$forecast = new Forecast\Forecast( $args );

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

The above will result with a min / max temperature forecast for the current day, similar to: 45 / 70 &deg;. You can take any element of the Forecast response in a similar fashion and output into your site.

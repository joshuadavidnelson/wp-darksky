# WP-DarkSky
A helper class for using darksky.net in WordPress. Requires a latitude, longitude, and Dark Sky API Key. It's set up to use a transient by default, refreshed every 6 hours, to limit the number of API calls made in a day (the first 1,000/day are free).

See more about the API in the [Dark Sky API Documentation](https://darksky.net/dev/docs).

**Current Version:** 1.1.1

**Minimum PHP:** PHP 5.3.0

**Minimum WordPress:** 3.1.0

#### Arguments

There are a few arguments available with this class to extend it further. 

- `api_key` This is required, sign up for one at https://darksky.net/dev/register
- `latitude` Required.
- `longitude` Required.
- `time`	Optional. Pass a timestamp for the forecast at a specific time.
- `cache_prefix` The transient prefix, defaults to 'api_'. Note that the transient name is this prefix plus an [md5](http://php.net/manual/en/function.md5.php) on the request url, so you're limited to an 8 character limit on the prefix or the transient will not save.
- `cache_enabled`	Boolean, default to true.
- `cache_time` Time to store the transient in seconds, defaults to 6 hours.
- `clear_cache` Boolean, default false. Set to true to force the cache to clear.
- `query`	An array of url query arguments, refer to the Dark Sky API documentation.

#### Example

You can call this class and output the data of the response fairly easily. Here's an example of a call to get the current day's temperature.

```php
$args = array(
	'api_key' 	=> '', // Enter your API key
	'latitude'	=> '', // enter the longitude
	'longitude'	=> '', // enter the latitude
	'query'		=> array( 'units' => 'us', 'exclude' => 'flags' )
);
$forecast = new DarkSky\Forecast( $args );

// Get the current forecast data for the daily forecast, which provides the next 7 days
$daily = isset( $forecast->daily['data'] ) ? $forecast->daily['data'] : false;

// Pull out the current day's forecast
if( $daily ) {
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

#### Want more?

See the collection of examples in [this gist](https://gist.github.com/joshuadavidnelson/d6fa0c17faf3f0ea0192), as well as a basic Walkthrough in this [post](https://joshuadnelson.com/weather-in-wordpress-with-forecast-io/).

##### Weather Icons

Integrate [Eric Flowers'](https://github.com/erikflowers) awesome [Weather Icons](https://github.com/erikflowers/weather-icons) with this handy [helper class](https://gist.github.com/joshuadavidnelson/12e9915ad81d62a6991c).
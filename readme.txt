=== Pew Research Charts ===
Contributors: anekola, kingkool68
Tags: charts, data visualization
Requires at least: 4.0.1
Tested up to: 4.3
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Interactive charts within WordPress is as easy as a custom post type; from the Pew Research Center

== Description ==

The Pew Research Charts WordPress plugin creates a custom post type which allows producers to insert an HTML table of data and transform it into an interactive chart.

At the Pew Research Center, we had been building our interactive graphics with Highcharts for some time. We wanted a scalable way to create these that involved minimal developer time. This plugin makes it easy for producers to add data and have a chart that conforms with global styles, yet also provides the flexibility to customize each chart to an extent.

== Table format ==
Each chart post type is created with default table html. The `<table>` element must include the class `pew-chart`. Easily convert your Excel data into HTML rows using [Tableizer](http://tableizer.journalistopia.com/).

== Table options ==
You may add a `data-color` attribute to any `<th>` element in order to override the color for that series. The value must be a hex code.

== Chart Settings ==
* **Chart types**: Supported types include line, bar, column, area, pie and scatter.
* **Chart Height**: Default is 400px
* **x-Axis type**: Default is linear, for categories. Also Date/Time.
* **Allow iFrame**: May be set be default in site-wide settings. Will add an "embed" tab above chart providing iframe code.
* **Custom Args**: Will override all defaults and settings; must be valid JSON. Keys and string values must be in double quotes. Functions will not be accepted as valid values. See [the Highcharts API](http://api.highcharts.com/highcharts) for acceptable values.

```
{"plotOptions":{"series":{"connectNulls":false}}}
```

== Site-wide Settings ==
These may be found in the WordPress admin under "Settings -> Chart Settings."
* **Credits** will add a default source attribution to all charts. Particularly useful if an organization creates it's own data.
* **iFrames** will add an "embed" tab above every chart next to the "data" tab. This tab will provide iframe code so users may embed the chart on their own websites.
* **Waypoints** will draw charts when they enter the user's viewport. Only applies on non-chart post types; most useful when adding several shortcodes to a single post.
* **Default args** will set the default styling for all the charts on the site. Must be valid JSON. Keys and string values must be in double quotes. Functions will not be accepted as valid values. See [the Highcharts API](http://api.highcharts.com/highcharts) for acceptable values.

== Shortcode ==
Charts may be embedded within the same WordPress site using the shortcode:

```
[chart slug="this-is-my-chart"]
[chart id="4076"]
```

An additional attribute of "classes" will add additional classes to the chart. The built-in class of "noborders" will remove the top and bottom borders from embedded charts.

```
[chart slug="my-chart" classes="noborders"]
```

Note that "chart slug" only works if the chart has been published previously or is live on the site. If the chart is in Draft, use the "chart id" of the chart (found in the URL when in the admin while in the chart editor).

== Available filters ==
* **chart_addl_classes** may change or add classes to shortcode-embedded charts.
* **chart_shortcode_title** may modify the title on shortcode-embedded charts.
* **pew_chart_options** is called after applying all the Highcharts settings and arg fields. It also includes an `html` field which is used to insert tabs and text around a chart. Default values include:
```
$chart_options['html'] = array(
		'waypoints' => false,
		'data_tab' => _('Data'),
		'chart_tab' => _('Chart'),
		'height' => '400px',
		'iframe' => false,
		'iframe_tab' => _('Embed'),
		'iframe_text' => _('Copy and paste the below iframe code into your own website to embed this chart.'),
		'URL' => get_permalink( $chart->ID ),
		'id' => $chart->ID,
		'domain' => get_site_url(),
		'credits' => '',
		'creditsURL' => '',
		'creditText' => _('Source: ')
	);
```

== Common Problems ==
**Legends floating above chart**: As of HighCharts version 4.1.4, setting the legend property align:'top' causes the legends to float above the chart itself. The fix is to remove any align:'top' values.

== Included javascript ==
The Pew Charts plugin uses and includes several javascript libraries.
* [Highcharts](http://www.highcharts.com/) is used to draw the charts. The kit is free for non-commercial use, but otherwise requires a license.
* [Tinysort](http://tinysort.sjeiti.com/) is applied to data tables, allowing users to sort columns.
* [Waypoints](http://imakewebthings.com/waypoints/) may be activated in the site-wide settings to draw charts when they enter the user's viewport.

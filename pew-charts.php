<?php
/*
Plugin Name: Pew Research Charts
Description: Interactive charts within WordPress are as easy as a custom post type
Version: 	 0.5
Author: 	 Adam Nekola and Russell Heimlich
Author URI:  http://www.pewresearch.org
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/



/**
 * Clear rewrite rules on activation
 */

function pew_charts_plugin_activate() {
	pew_charts_init();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'pew_charts_plugin_activate' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );


/**
 * Compatability: array_replace_recursive() is PHP 5.3 and greater
 * For more: http://php.net/manual/en/function.array-replace-recursive.php
 */

if (!function_exists('array_replace_recursive')){
  function array_replace_recursive($array, $array1){
  	if (!function_exists('recurse')){
	    function recurse($array, $array1){
	      foreach ($array1 as $key => $value)
	      {
	        if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key])))
	        {
	          $array[$key] = array();
	        }
	        if (is_array($value))
	        {
	          $value = recurse($array[$key], $value);
	        }
	        $array[$key] = $value;
	      }
	      return $array;
	    }
	}
    $args = func_get_args();
    $array = $args[0];
    if (!is_array($array))
    {
      return $array;
    }
    for ($i = 1; $i < count($args); $i++)
    {
      if (is_array($args[$i]))
      {
        $array = recurse($array, $args[$i]);
      }
    }
    return $array;
  }
}


global $pew_chart_options;
$pew_chart_options = array();

/* ------------------------- */
/* Create a custom post type */
/* ------------------------- */

function pew_charts_init() {

	$labels = array(
		'name'                => 'Charts',
		'singular_name'       => 'Chart',
		'menu_name'           => 'Charts',
		'parent_item_colon'   => 'Parent Chart:',
		'all_items'           => 'All Charts',
		'view_item'           => 'View Chart',
		'add_new_item'        => 'Add New Chart',
		'add_new'             => 'Add New',
		'edit_item'           => 'Edit Chart',
		'update_item'         => 'Update Chart',
		'search_items'        => 'Search Chart',
		'not_found'           => 'Not found',
		'not_found_in_trash'  => 'Not found in Trash',
	);
	$args = array(
		'label'               => 'charts',
		'description'         => 'charts',
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author'),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 60,
		'menu_icon'           => 'dashicons-chart-area',
		'can_export'          => true,
		'has_archive'         => 'charts',
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
		'rewrite' => array(
			'pages' => false
		)
	);
	register_post_type( 'chart', $args );
}
add_action( 'init', 'pew_charts_init', 0 );


/** 
 * Making charts accessible with iframes
 */

function pew_charts_rewrite( $rules ) {
	$new = array(
		'chart/([^/]+)/iframe/?$' => 'index.php?chart=$matches[1]&iframe=1'
	);
	return array_merge($new, $rules);
}
add_filter( 'rewrite_rules_array', 'pew_charts_rewrite' );


/** 
 * New query vars for iframe rewrite rule Making these accessible with iframes
 */

function pew_charts_query_vars($query_vars) {
	if (!in_array('iframe', $query_vars)) $query_vars[] = 'iframe';
	return $query_vars;
}
add_filter( 'query_vars', 'pew_charts_query_vars' );


/* ----------------------------- */
/* Template heirarchy for iframe */
/* ----------------------------- */

function pew_charts_template( $template_path ) {
    if ( get_post_type() == 'chart' ) {
        if ( get_query_var('iframe') ) {
            if ( $theme_file = locate_template( array ( 'single-chart-iframe.php' ) ) ) {
                $template_path = $theme_file;
            } else {
                $template_path = plugin_dir_path( __FILE__ ) . 'templates/single-chart-iframe.php';
            }
        }
    }
    return $template_path;
}
add_filter( 'template_include', 'pew_charts_template', 1 );


/**
 * Header for iframe 
 */

function get_pew_charts_header( $name = null ) {
	do_action( 'get_header', $name);

	if ( $theme_file = locate_template( array ( 'header-iframe.php' ) ) ) {
        $template_path = $theme_file;
    } else {
		$template_path = plugin_dir_path( __FILE__ ) . 'templates/header-iframe.php';
	}
	load_template( $template_path );
}


/* -------------------------------------- */
/* Register script, enqueue on chart post */
/* -------------------------------------- */

function pew_charts_scripts(){
	if ( !wp_script_is( 'highcharts', 'registered' ) ){
		$site_options = get_option( 'pew_charts' );
		if ( $site_options['highcharts'] != '' ) {
			$highcharts_url = $site_options['highcharts'];
		} else {
			$highcharts_url = 'http://code.highcharts.com/4.1.8/highcharts.js';	
		}
		wp_register_script('highcharts', $highcharts_url, array('jquery'), false, true);
	}
	if ( !wp_script_is( 'tinysort', 'registered' ) )
		wp_register_script('tinysort', plugin_dir_url( __FILE__ ) . 'js/tinysort.min.js', array('jquery'), false, true);

	if ( !wp_script_is( 'waypoints', 'registered' ) )
		wp_register_script('waypoints', plugin_dir_url( __FILE__ ) . 'js/waypoints.min.js', array('jquery'), false, true);

	if ( !wp_script_is( 'highcharts-regression', 'registered' ) )
		wp_register_script('highcharts-regression', plugin_dir_url( __FILE__ ) . 'js/highcharts-regression.min.js', array('highcharts'), false, true);

	wp_register_script('pew-charts', plugin_dir_url( __FILE__ ) . 'js/pew-charts.js', array('highcharts','tinysort'), false, true);
	wp_register_style('pew-charts', plugin_dir_url( __FILE__ ) . 'css/pew-charts.css');

	if ( get_post_type() == 'chart' ) {
		wp_enqueue_script('pew-charts');
		wp_enqueue_style('pew-charts');
	}

}
add_action('wp_enqueue_scripts', 'pew_charts_scripts');


/* ----------------------------------- */
/* Shortcode for charts on other posts */
/* ----------------------------------- */

function pew_chart_shortcode( $atts ) {
	global $add_chart_script;

	$a = shortcode_atts( array(
        'id' => 0,
        'slug' => false,
        'title' => false,
        'classes' => false,
        'width' => 'none',
        'float' => 'none'
    ), $atts );

	$id = $a['id'];
	$slug = $a['slug'];
	$classes = $a['classes'];

	if ( !$id && !$slug ) {
		//No ID or slug. We can't do anything with that so bail...
		return '';
	}

	$args = array(
		'post_type' => 'chart',
		'post_status' => 'publish',
		'showposts' => 1
	);
	if( $id ) {
		$args['p'] = $id;
	}

	if( $slug ) {
		$args['name'] = $slug;
	}

	if( is_preview() || is_user_logged_in() ) {
		$args['post_status'] = array('publish', 'pending', 'draft', 'future', 'private');
	}

	$charts = get_posts( $args );
	if( is_wp_error($charts) || !is_array($charts) || empty($charts) ) {
		return '';
	}

	$chart = $charts[0];
	pew_chart_prep_chart_options( $chart );

	// This is make the script echo in the footer
	$add_chart_script[] = $chart->ID;

	// Ability to customize
	$classes = explode(',', $classes);
	$classes = array_merge($classes, array('embedded_chart', 'chart' . $chart->ID));
	$chart_addl_classes = apply_filters('chart_addl_classes', $classes, $atts);
	$chart_shortcode_title = apply_filters('chart_shortcode_title', ($a['title'] ? $a['title'] : get_the_title($chart->ID)));
	$margin = ($a['float'] == 'left' ? '0 30px 0 0' : ($a['float'] == 'right' ? '0 0 0 30px' : '0'));
	$chart_contain_style = apply_filters('chart_contain_style', 'max-width:'.$a['width'].'; float:'.$a['float'].'; margin:'.$margin);

  // Now we print the title and the content
  $html = '<div class="' . implode(' ', $chart_addl_classes) . '" style="'.$chart_contain_style.'">';

	if ( current_user_can('edit_post', $chart->ID) ) {
		$chart_shortcode_title .= ' | <a href="' . get_edit_post_link( $chart->ID ) . '" target="_blank">Edit</a>';
	}

	$html .= '<h3>' . $chart_shortcode_title . '</h3>';
	$html .= wpautop(get_post_field('post_content', $chart->ID)); //Potential for infinite loop if the chart body has a [chart] shortcode in it.

	$html .= '</div>';

	$site_options = get_option( 'pew_charts' );
	if( isset( $site_options['waypoints'] ) && !empty( $site_options['waypoints'] ) ) {
		wp_enqueue_script('waypoints');
	}

	wp_enqueue_script('pew-charts');
	wp_enqueue_style('pew-charts');

	return $html;
}
add_shortcode( 'chart', 'pew_chart_shortcode' );

/*
 * If it's a single chart page we need to be sure to prep the chart options properly and add them to the bottom of the page for the JavaScript
 */

function pew_chart_prep_single_chart_page() {
	if( get_post_type() == 'chart' && is_single() ) {
		pew_chart_prep_chart_options();
	}
}
add_action( 'wp_head', 'pew_chart_prep_single_chart_page' );


function get_pew_chart_meta( $chart_id = false ) {
	if( !$chart_id ) {
		$post = get_post();
		$chart_id = $post->ID;
	}

	// Use WordPress' caching functionality so if the same $chart_id is requested we can return the previous work we did...
	$cache_key = 'pew_chart_options_' . $chart_id;
	$data = wp_cache_get( $cache_key );

	if( !$data ) {
		$data = get_post_meta( $chart_id, 'chart_meta', true );
		if ( !is_array($chart_meta) ) {
			$chart_meta = array();
		}
		$whitelisted = array('credits', 'credits_link', 'chartheight', 'charttype', 'chartsubtitle', 'xaxistype', 'xaxislabel', 'yaxismax', 'yaxislabel', 'zoomtype', 'inverted', 'iframe', 'hidemarkers', 'args');
		foreach( $whitelisted as $key ) {
			if( !isset( $data[ $key ] ) ) {
				$data[ $key ] = '';
			}
		}

		wp_cache_set( $cache_key, $data );
	}

	return $data;
}


function pew_chart_prep_chart_options( $chart = FALSE ) {
	global $pew_chart_options;

	//Get site wide defaults
	$site_options = get_option( 'pew_charts' );
	if( isset( $site_options['defaults'] ) && !empty( $site_options['defaults'] ) ) {
		$default_options = json_decode( $site_options['defaults'] , true);
	}
	if( !$default_options ) {
		$default_options = array();
	}

	//$chart is a $post object
	if( !$chart ) {
		$site_options['waypoints'] = false;
		$chart = get_post();
	}

	//Get chart options
	$options = get_pew_chart_meta( $chart->ID );
	if( isset( $options['args'] ) && !empty( $options['args'] ) ) {
		if(version_compare(phpversion(), '5.3.0', '>=')) {
	        $custom_chart_options = json_decode($options['args'], true, 10);
	    }
	    else {
	        $custom_chart_options = json_decode($options['args'], true);
	    }
	}
	if( !$custom_chart_options ) {
		$custom_chart_options = array();
	}

	$chart_options = array(
		'chart' => array(),
		'subtitle' => array(),
		'xAxis' => array(
			'title' => array()
		),
		'yAxis' => array(
			'title' => array()
		),
		'plotOptions' => array(
			'line' => array(
				'marker' => array(
					'enabled' => true
				)
			),
			'area' => array(
				'marker' => array(
					'enabled' => true
				)
			),
			'bar' => array(
				'stacking' => null ),
			'column' => array(
				'stacking' => null ),
			'pie' => array(
				'visible' => true,
				'dataLabels' => array(
					'enabled' => true
				)
			)
		)
	);
	if( $options['charttype'] ) {
		$chart_options['chart']['type'] = $options['charttype'];
	}
	if( $options['zoomtype'] && $options['zoomtype'] != 'none' ) {
		$chart_options['chart']['zoomType'] = $options['zoomtype'];
	}
	if( $options['chartsubtitle'] && $options['chartsubtitle'] != 'none' ) {
		$chart_options['subtitle']['text'] = $options['chartsubtitle'];
	}
	if( $options['seriesstacking'] == true ) {
		$chart_options['plotOptions']['area']['stacking'] = 'normal';
		$chart_options['plotOptions']['bar']['stacking'] = 'normal';
		$chart_options['plotOptions']['column']['stacking'] = 'normal';
		$chart_options['plotOptions']['line']['stacking'] = 'normal';
	}
	if( $options['inverted'] && $options['inverted'] != 'none' ) {
		$chart_options['chart']['inverted'] = true;
	}
	if( $options['xaxislabel'] && $options['xaxislabel'] != 'none' ) {
		$chart_options['xAxis']['title']['text'] = $options['xaxislabel'];
	}
	if( $options['yaxislabel'] && $options['yaxislabel'] != 'none' ) {
		$chart_options['yAxis']['title']['text'] = $options['yaxislabel'];
	}
	if( $options['yaxismax'] && $options['yaxismax'] != 'none' ) {
		$chart_options['yAxis']['max'] = $options['yaxismax'];
	}
	if( $options['xaxistype'] && $options['xaxistype'] != 'none' ) {
		$chart_options['xAxis']['type'] = $options['xaxistype'];
	}
	if( $options['hidemarkers'] ) {
		$chart_options['plotOptions']['line']['marker']['enabled'] = false;
		$chart_options['plotOptions']['area']['marker']['enabled'] = false;
	}

	$chart_options['html'] = array(
		'waypoints' => $site_options['waypoints'],
		'data_tab' => _('Data'),
		'chart_tab' => _('Chart'),
		'height' => ( $options['chartheight'] && preg_match('/[0-9]+([px]{2}|)$/i', $options['chartheight']) ? str_replace(array('px','PX'),'',$options['chartheight']).'px' : '400px'),

		'iframe' => $options['iframe'],
		'iframe_tab' => _('Embed'),
		'iframe_text' => _('Copy and paste the below iframe code into your own website to embed this chart.'),

		'URL' => get_permalink( $chart->ID ),
		'id' => $chart->ID,
		'domain' =>	get_site_url(),

		'credits' => ($options['credits'] ? $options['credits'] : $site_options['credits']),
		'creditsURL' => ($options['credits_link'] ? $options['credits_link'] : $site_options['credits_link']),
		'creditText' => _('Source: ')
	);

	$js_options = array_replace_recursive( (array) $default_options, $chart_options, (array) $custom_chart_options );
	$js_options = apply_filters( 'pew_chart_options', $js_options, $chart );

	$pew_chart_options[] = json_encode( $js_options );
}

function print_pew_chart_options() {
	global $pew_chart_options;
	$post = get_post();

	if( is_admin() ||
		is_home() ||
		!is_single() &&
		get_post_type() != 'charts' &&
		!has_shortcode( $post->post_content, 'chart' )
	) {
		return;
	}

?>
<script>
jQuery(document).ready(function($) {
	var pewChartOptions = [<?php echo implode( $pew_chart_options, ', ' ); ?>];
	$('table.pew-chart').each(function(index, table) {

		//Having the debugbar plugin enabled can pickup extra tables and wreak havoc...
		if( index >= pewChartOptions.length ) {
			return true;
		}

		$table = $(table);
		if( pewChartOptions[index].chart.type == 'none' ) {
			return true; //Skip this iteration and go on to the next one.
		}

		new Highcharts.visualize( $table, pewChartOptions[index] );
	});

});
</script>
<?php }
add_action( 'wp_footer', 'print_pew_chart_options', 99 );


/* 
 * Include the admin code.
 */

if( is_admin() ) {
	include plugin_dir_path( __FILE__ ) . '/pew-charts-admin.php';
	include plugin_dir_path( __FILE__ ) . '/pew-charts-csv-import.php';
}

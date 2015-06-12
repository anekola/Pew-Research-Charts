<?php
/* ----------------------- */
/* Admin Related Functions */
/* ----------------------- */

//For new Chart Posts start off with a simple table that is in the correct structure we need.
function pew_charts_default_body_content( $content ) {
	global $post_type;

	if( $post_type == 'chart' ) {
		$sample_date = date('n/Y');
		$content .= "<table class=\"pew-chart\"><thead><tr><th>Date</th><th>Column Name</th></tr></thead><tbody><tr><td>$sample_date</td><td>100</td></tr></tbody></table>";
	}
	return $content;
}
add_filter( 'default_content', 'pew_charts_default_body_content' );


/* -------------------------------------------- */
/* Create meta boxes for custom fields in admin */
/* -------------------------------------------- */

function pew_chart_metabox() {
    add_meta_box(
        'pew_chart_metabox',
        __( 'Create a Chart', 'pew_chart_text' ),
        'pew_chart_metabox_content',
        'chart', // post type
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'pew_chart_metabox' );

function pew_chart_metabox_content() {
	global $post;
	wp_nonce_field( plugin_basename( __FILE__ ), 'pew_chart_metabox_content' );
	//$post_meta = get_post_meta( $post->ID );
	$post_meta = get_pew_chart_meta( $post->ID ); ?>

    <div class="custom_metabox">

    	<table class="form-table">
    	<tr><td>
		<label for="charttype" class="prfx-row-title">Chart Type</label>
		</td><td>
	    <select name="charttype" id="charttype">
	    	<option value="line" <?php selected('line', $post_meta['charttype'])?>>Line</option>
	        <option value="bar" <?php selected('bar', $post_meta['charttype'])?>>Bar</option>
	        <option value="column" <?php selected('column', $post_meta['charttype'])?>>Column</option>
	        <option value="area" <?php selected('area', $post_meta['charttype'])?>>Area</option>
	        <option value="pie" <?php selected('pie', $post_meta['charttype'])?>>Pie</option>
	        <option value="scatter" <?php selected('scatter', $post_meta['charttype'])?>>Scatter</option>
	    </select>
		</td></tr>
		<tr><td>
		<label for="chartheight" class="prfx-row-title">Chart Height</label>
		</td><td>
		<input type="text" name="chartheight" id="chartheight" value="<?php echo $post_meta['chartheight']?>" placeholder="Default: 400px" />
		</td></tr>
		<tr><td>
		<label for="chartsubtitle" class="prfx-row-title">Chart Subtitle</label>
		</td><td>
		<input type="text" name="chartsubtitle" id="chartsubtitle" value="<?php echo $post_meta['chartsubtitle']?>" />
		</td></tr>
		<tr><td>
		<label for="xaxistype">x-Axis Type</label>
		</td><td>
		<select name="xaxistype" id="xaxistype">
			<option value="linear" <?php selected('linear', $post_meta['xaxistype'])?>>Linear</option>
	    	<option value="datetime" <?php selected('datetime', $post_meta['xaxistype'])?>>Date/Time</option>
		</select>
		</td></tr>
		<tr><td>
	    <label for="xaxislabel">x-Axis Label</label>
		</td><td>
		<input type="text" name="xaxislabel" id="xaxislabel" value="<?php echo $post_meta['xaxislabel']?>" />
		</td></tr>
		<tr><td>
	    <label for="yaxislabel">y-Axis Label</label>
		</td><td>
		<input type="text" name="yaxislabel" id="yaxislabel" value="<?php echo $post_meta['yaxislabel']?>" />
	    </td></tr>
		<tr><td>
	    <label for="yaxismax">y-Axis Max Value</label>
		</td><td>
		<input type="text" name="yaxismax" id="yaxismax" value="<?php echo $post_meta['yaxismax']?>" />
	    </td></tr>
		<tr><td>
		<label for="zoomtype">Chart Zooming</label>
	    </td><td>
	    <select name="zoomtype" id="zoomtype">
			<option value="none" <?php selected('none', $post_meta['zoomtype'])?>>None</option>
	    	<option value="x" <?php selected('x', $post_meta['zoomtype'])?>>x-Axis Only</option>
			<option value="y" <?php selected('y', $post_meta['zoomtype'])?>>y-Axis Only</option>
			<option value="xy" <?php selected('xy', $post_meta['zoomtype'])?>>x &amp; y-Axis</option>
	    </select>
		</td></tr>
		<tr><td>
	    <label for="credits">Source Credits</label>
		</td><td>
		<input type="text" name="credits" id="credits" value="<?php echo $post_meta['credits']?>" />
		</td></tr>
		<tr><td>
	    <label for="credits_link">Source Link</label>
		</td><td>
		<input type="text" name="credits_link" id="credits_link" value="<?php echo $post_meta['credits_link']?>" />
		</td></tr>
		<tr><td colspan="2">
	    <label for="inverted"><input type="checkbox" name="inverted" id="inverted" value="true" style="width:auto;" <?php checked( 'true', $post_meta['inverted'] ); ?> /> Inverted</label>
	    <p>Whether to invert the axes so that the x axis is horizontal and y axis is vertical.</p>
	    </td></tr>
		<tr><td colspan="2">
	    <label for="seriesstacking"><input type="checkbox" name="seriesstacking" id="seriesstacking" value="true" style="width:auto;" <?php checked( 'true', $post_meta['seriesstacking'] ); ?> /> Stack multiple series?</label>
	    <p>If not stacking, bar and column charts will show series next to each other; area charts will overlap.</p>
	    </td></tr>
		<tr><td colspan="2">
		<label for="hidemarkers"><input type="checkbox" name="hidemarkers" id="hidemarkers" value="true" style="width:auto;" <?php checked( 'true', $post_meta['hidemarkers'] ); ?> /> Hide Markers</label>
	    <p>When checked, the circular points on a line graph will be hidden. Reccomended if there are a lot of data points.</p>
	    </td></tr>
		<tr><td colspan="2">
		<label for="iframe"><input type="checkbox" name="iframe" id="iframe" value="true" style="width:auto;" <?php checked( 'true', $post_meta['iframe'] ); ?> /> Allow iframe?</label>
	    <p>This will display an extra tab above the chart displaying embed code; it can be set my default in the <a href="<?php echo admin_url( 'options-general.php?page=pew-charts'); ?>">plugin settings</a>.</p>
	    </td></tr>
	    <?php // If is_admin ?>
		<tr><td colspan="2">
		<p>
		<label for="args">Custom Args</label>
		</p>
		<p>
		<?php $json = '';
		if ($post_meta['args'] != '') {
			if(version_compare(phpversion(), '5.3.0', '>=')) {
		        $array = json_decode($post_meta['args'], true, 10);
		    }
		    else {
		        $array = json_decode($post_meta['args'], true);
		    }
			if ( is_array($array) ){
				if(version_compare(phpversion(), '5.4.0', '>=')) {
			        $json = json_encode($array, JSON_PRETTY_PRINT);
			    }
			    else {
			        $json = json_encode($array);
			    }
			}
		} ?>
	    <textarea name="args" id="args" style="width:100%; height:100px;" <?php if ( !current_user_can('edit_theme_options') ) echo 'readonly'; ?>><?php echo $json; ?></textarea>
    	<?php if ( $post_meta['args'] && !is_array($array) ) {
    		echo '<div class="error"><p>The custom JSON in this chart is not properly formatted. Please make sure all keys and values are in double quotes.</p></div>';
    	} else {

    	} ?>
	    </p>
	    </td></tr>
	    </table>
    </div>
    <?php
}


/* -------------------------- */
/* Save the custom meta input */
/* -------------------------- */

function pew_chart_meta_save( $post_id ) {
 	if ( 'chart' == get_post_type() ){
		// Checks save status
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST[ 'pew_chart_metabox_content' ] ) && wp_verify_nonce( $_POST[ 'pew_chart_metabox_content' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

		// Exits script depending on save status
		if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
			return;
		}

		$expected_fields = array('credits', 'credits_link', 'chartheight', 'charttype', 'chartsubtitle', 'seriesstacking', 'xaxistype', 'xaxislabel', 'yaxismax', 'yaxislabel', 'zoomtype', 'inverted', 'iframe', 'hidemarkers', 'args');

		foreach ( $_POST as $k => $v ){
			if ( in_array($k, $expected_fields) ){
				if ( $k != 'args' ) $v = sanitize_text_field($v);
				$chart_meta[$k] = $v;
			}
		}
		update_post_meta($post_id, 'chart_meta', $chart_meta);
	}
}
add_action( 'save_post', 'pew_chart_meta_save' );


/* ---------------------------------------- */
/* Admin Settings Page
	- URL for HighCharts file
	- Default HighCharts args */


class ChartSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'Chart Settings',
            'manage_options',
            'pew-charts',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'pew_charts' );
        ?>
        <div class="wrap">
            <h2>Chart Settings</h2>
            <p>Set your sitewide default settings here. Visit the <a href="http://api.highcharts.com/highcharts">HighCharts API documentation</a> to explore the custom arguments available. <b>For commercial use, HighCharts <a href="http://shop.highsoft.com/highcharts.html">requires a license</a>.</b></p>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'pew_charts_group' );
                do_settings_sections( 'pew-charts' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'pew_charts_group', // Option group
            'pew_charts', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            '', // Title
            array( $this, 'print_section_info' ), // Callback
            'pew-charts' // Page
        );

	        add_settings_field(
	            'credits', // ID
	            'Credits', // Title
	            array( $this, 'credits_callback' ), // Callback
	            'pew-charts', // Page
	            'setting_section_id' // Section
	        );

	        add_settings_field(
	            'iframe',
	            'iframes',
	            array( $this, 'iframe_callback' ),
	            'pew-charts',
	            'setting_section_id'
	        );

	        add_settings_field(
	            'waypoints',
	            'Waypoints',
	            array( $this, 'waypoints_callback' ),
	            'pew-charts',
	            'setting_section_id'
	        );

	        add_settings_field(
	            'defaults',
	            'Default Args',
	            array( $this, 'defaults_callback' ),
	            'pew-charts',
	            'setting_section_id'
	        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['credits'] ) )
            $new_input['credits'] = sanitize_text_field( $input['credits'] );

        if( isset( $input['iframe'] ) )
            $new_input['iframe'] = sanitize_text_field( $input['iframe'] );

        if( isset( $input['defaults'] ) )
            $new_input['defaults'] = sanitize_text_field( $input['defaults'] );

        if( isset( $input['waypoints'] ) )
            $new_input['waypoints'] = $input['waypoints'];

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function credits_callback()
    {
        printf(
            '<input type="text" id="credits" name="pew_charts[credits]" value="%s" />',
            isset( $this->options['credits'] ) ? esc_attr( $this->options['credits']) : ''
        );
    }

    public function iframe_callback()
    { ?>
        <select id="iframe" name="pew_charts[iframe]">
        	<option <?php selected('disable', $this->options['iframe']); ?> value="disabled">Disabled by default</option>
        	<option <?php selected('enable', $this->options['iframe']); ?> value="enabled">Enabled by default</option>
        </select>
    <?php }

    public function waypoints_callback()
    { ?>
        <input type="checkbox" name="pew_charts[waypoints]" id="waypoints" value="true" style="width:auto;" <?php checked( 'true', $this->options['waypoints'] ); ?> /> <br/>Activate to use <a href="http://imakewebthings.com/waypoints/">Waypoints</a> to draw shortcode charts when they are visible to user.
    <?php }

    public function defaults_callback()
    {
     	$json = '';
		if ($this->options['defaults'] != '') {
			$json = $this->options['defaults'];
			if(version_compare(phpversion(), '5.3.0', '>=')) {
		        $array = json_decode($this->options['defaults'], true, 10);
		    }
		    else {
		        $array = json_decode($this->options['defaults'], true);
		    }
			if ( is_array($array) ) {
				if(version_compare(phpversion(), '5.4.0', '>=')) {
			        $json = json_encode($array, JSON_PRETTY_PRINT);
			    }
			    else {
			        $json = json_encode($array);
			    }
			}
		}
		printf(
            '<textarea id="defaults" style="width: 100&#37;; height: 450px;" name="pew_charts[defaults]">%s</textarea>',
            isset( $this->options['defaults'] ) ? esc_attr( $json) : ''
        );
        if ( $this->options['defaults'] && !is_array($array) ) {
    		echo '<div class="error"><p>The custom JSON in this chart is not properly formatted. Please make sure all keys and values are in double quotes.</p></div>';
    	}
    }
}
$charts_settings = new ChartSettingsPage();



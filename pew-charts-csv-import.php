<?php
function pew_charts_csv_import_admin_menu() {
	add_submenu_page( 'edit.php?post_type=chart', 'Pew Chart CSV Import', 'CSV Import', 'edit_published_posts', 'pew-charts-csv-import', 'pew_charts_csv_import_menu' );
}
add_action('admin_menu', 'pew_charts_csv_import_admin_menu');

function pew_charts_csv_import_menu() {
	?>
	<h1>CSV Import for the Chart Builder</h1>
	<form method="post" enctype="multipart/form-data" action="<?php echo get_admin_url(); ?>">
		<p><label for="chart-title">Chart Title</label><br>
		<input type="text" id="chart-title" name="chart-title" value=""></p>
		
		<p><input type="file" name="chart-csv"></p>
		
		<p><label><input type="checkbox" value="true" name="chart-first-row-header" checked> First row is table headers</label></p>
		
		<?php wp_nonce_field( 'pew-chart-csv-import' ); ?> 
		<input type="hidden" name="pew-chart-csv-import-check" value="1">
		
		<input type="submit" value="Import Chart Data" class="button button-primary">
	</form>
	<?php
}

function pew_charts_do_csv_import() {
	if( !isset( $_POST['pew-chart-csv-import-check'] ) || !check_admin_referer( 'pew-chart-csv-import' ) ) {
		return;
	}
	
	$key = 'chart-csv';
	
	if( $_FILES[ $key ]['error'] != 0 ) {
		wp_die( 'Upload Error: ' . $_FILES[ $key ]['error'] );
	}
	
	if( !strstr( $_FILES[ $key ]['name'], '.csv' ) || $_FILES[ $key ]['type'] != 'text/csv' ) {
		wp_die( 'Error: Not a CSV' );
	}
	
	$chart_title = 'Imported Chart on ' . current_time( 'F j, Y g:i a' );
	if( isset( $_POST['chart-title'] ) && !empty( $_POST['chart-title'] ) ) {
		$chart_title = $_POST['chart-title'];
	}
	
	ini_set('auto_detect_line_endings', true);
	$tmp_name = $_FILES[ $key ]['tmp_name'];
	$csv = array();
	$fp = fopen( $tmp_name, 'rb');
	while( !feof($fp) ) {
    	$row = fgetcsv($fp);
    	//$id = array_shift($row);
    	//$data[$id] = $row;
		$csv[] = $row; 
	}
	
	fclose( $fp );
	
	$header = array();
	if( $_POST['chart-first-row-header'] == 'true' ) {
		$headers = array_shift( $csv );
	} else {
		foreach( $csv[0] as $header ) {
			$headers[] = ' ';
		}
	}
	
	$table = array();
	$table[] = '<table class="pew-chart">';
		$table[] = '<thead>';
			$table[] = '<tr>';
				$table[] = '<th>' . implode('</th><th>', $headers) . '</th>';
			$table[] = '</tr>';
		$table[] = '</thead>';
		
		$table[] = '<tbody>';
		foreach( $csv as $row ) {
			$table[] = '<tr>';
				$table[] = '<td>' . implode('</td><td>', $row) . '</td>';
			$table[] = '</tr>';
		}
		$table[] = '</tbody>';
		
	$table[] = '</table>';
	
	$new_post = array(
		'post_title' => $chart_title,
		'post_content' => implode('', $table ),
		'post_status' => 'draft',
		'post_type' => 'chart'
	);
	
	$post_id = wp_insert_post( $new_post );
	if( $post_id ) {
		wp_safe_redirect( get_admin_url() . '/post.php?post=' . $post_id . '&action=edit' );
		die();
	}
	
	wp_redirect( get_admin_url() . '/edit.php?post_type=chart&page=pew-charts-csv-import' );
}
add_action( 'admin_init', 'pew_charts_do_csv_import' );
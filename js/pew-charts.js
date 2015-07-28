function setupPewChartHTML( $table, num, $ ) {
	$table.wrap('<figure class="pew-chart" />');
	var beforeHTML = '<ol class="chart_tabs tabs horizontal">';
	beforeHTML += '<li class="active"><a href="#chart-' + num + '">Chart</a></li>';
	beforeHTML += '<li><a href="#data-' + num + '">Data</a></li>';
	beforeHTML += '</ol>';
	beforeHTML += '<div id="chart-' + num + '" class="chart"></div>';
	$table.removeClass('pew-chart').before(beforeHTML).wrap('<div id="data-' + num + '" class="data" />');

	//With the tabs in place, let's trigger the active tab
	$('.chart_tabs').each(function() {
		//If there are no tabs with an active class set, then add 'active' to the first tab
		if( $(this).find('.active a').length < 1 ) {
			$(this).find(':first-child').addClass('active');
		}
	});
	$('.chart_tabs .active a').click();
}

// Global object for determing what date format to display in the tooltip.
var tooltipFormatFor = {};
function tooltipDateFormat( ms ) {
	format = tooltipFormatFor[ms]
	if( !format ) {
		format = '%b %Y';
	}

	return Highcharts.dateFormat(format, ms);
}

function stringToMs(text) { //Converts text to milliseconds since the EPOCH.
	var pieces = text.split('/');
	var ms = Date.parse(text);
	if( isNaN(ms) ) {
		if( pieces.length == 2 && pieces[1].length == 4 ) { //assuming mm/yyyy
			month = parseInt(pieces[0]) - 1;
			day = '01';
			year = pieces[1];
		} else if( pieces.length == 3 && pieces[2].length == 4 ) { //assuming mm/dd/yyyy
			month = pieces[0];
			day = pieces[1];
			year = pieces[2];
		} else if( pieces.length == 1 && pieces[0].length == 4 ) { //assuming yyyy which IE7 has trouble parsing.
			month = '01';
			day = '01';
			year = pieces[0];
		}
		if( day.length == 1 ) {
			day = '0' + day;
		}
		if( month.length == 1 ) {
			month = '0' + month;
		}
		ms = new Date(year, month, day).getTime();
		if( isNaN(ms) ) {
			ms = null;
		}
	}

	if( ms === 0 ) {
		//If you're trying to plot '1970' you need to use a slightly later time. 1970 is the epoch or 0 milliseconds. Highcharts doesn't like a date of 0.
		ms = 4320000000; //This is the equivalent of Date.UTC(1970, 1, 20) or 2/20/1970 because Chrome doesn't like a smaller value.
	}

	if( pieces.length == 1 ) {
		tooltipFormat = '%Y';
	} else if( pieces.length == 3 ) {
		tooltipFormat = '%b %e, %Y';
	} else {
		tooltipFormat = '%b %Y';
	}

	tooltipFormatFor[ms] = tooltipFormat;

	return ms;
}

function chart_build_html(options){
	var iframeScript = '';
	var beforeHTML = '<ol class="chart_tabs tabs horizontal">\
		<li class="active"><a href="#chart">'+options.chart_tab+'</a></li>\
		<li><a href="#data">'+options.data_tab+'</a></li>\
		<li class="iframe hidden"><a href="#iframe">'+options.iframe_tab+'</a></li>\
		</ol>\
		<div id="chart" class="chart_toggle_content" style="height:'+options.height+'" data-id="'+options.id+'"></div>\
		<div id="iframe" class="hidden chart_toggle_content">\
		<p>'+options.iframe_text+'</p>\
		<textarea readonly>&lt;iframe src="'+options.URL+'iframe/" id="pew'+options.id+'" scrolling="no" width="100%" height="100px" frameborder="0"&gt;&lt;/iframe&gt; &lt;script type=\'text/javascript\'id=\'pew-iframe\'&gt;(function(){function async_load(){var s=document.createElement(\'script\');s.type=\'text/javascript\';s.async=true;s.src=\''+options.domain+'/wp-content/plugins/pew-scripts/js/iframeResizer.min.js\';s.onload=s.onreadystatechange=function(){var rs=this.readyState;try{iFrameResize([],\'iframe#pew'+options.id+'\')}catch(e){}};var embedder=document.getElementById(\'pew-iframe\');embedder.parentNode.insertBefore(s,embedder)}if(window.attachEvent)window.attachEvent(\'onload\',async_load);else window.addEventListener(\'load\',async_load,false)})();&lt;/script&gt;\
		</textarea>\
		</div>';
	return beforeHTML;
}


var option = [];

jQuery(document).ready(function($) {


	function get_th_color( $th, index, options ) {
		// If a color data attribute is set in the markup then modify our colors array to include this custom color instead of whatever the defaults are.
		var color = $th.data('color');
		if( !color ) {
			return false;
		}
		options.colors[index - 1] = color;

		return color;
	}


	function process_date_data($table, options) {

		options.xAxis.type = 'datetime';
		var columns = [];

		$( 'thead th', $table).each(function(count) {
			$this = $(this);
			//Get the text value of the table header which will be used as the columns name.
			var text = $.trim( $this.text() );
			get_th_color( $this, count, options );

			columns[count] = {
				'name': text,
				'id': 'column' + count,
				'data': [],
				'displayData': []
			};

			columns[count].color = options.colors[count - 1];
		});

		$('tbody tr', $table).each(function(row, tr){
			$('th, td', tr).each( function(i) {

				var cell = $.trim( this.innerHTML );
				columns[i].displayData.push( cell );
				cell = cell.replace(/[><%,\$¢£#!@^&*\+\(\)]/g, '');

				if( i == 0 ) {
					xValue = stringToMs(cell);
				} else {
					cell = parseFloat(cell);
					if ( isNaN(cell) ) {
						cell = null;
					}

					if( cell < 0 && !isNaN( cell ) ) {
						options.hasNegativeValues = true;
					}

					columns[i].data.push( [xValue, cell] ); //Each point consists of two values [Date, Value]. c represents which column we're dealing with.
				}
			});
		});

		//The first column of the table is dates so we can just split that data right off.
		columns.splice(0, 1);
		options.series = columns;

		options.tooltip.formatter = function() {
			var string = '<strong>'+ tooltipDateFormat(this.x) +'</strong><br/>';
			if ( this.points.length > 1 ) {
				points = this.points;
				for(index = 0; index < points.length; index += 1) {
					value = points[index].y;
					if (points[index].y == null) value = 'N/A';
					string = string + '<span style="color:' + points[index].series.color + ';">' + points[index].series.name + '</span>' + ': ' + value+'<br/>';
				}
			} else {
				var i = this.series.data.indexOf( this.point );
				var colorIndex = this.series.index;
				string = string + '<span style="color:' + options.colors[ colorIndex ] + ';">' + this.series.name + '</span>' + ': ' + this.series.options.displayData[i];
			}
			return string;
		}

		return options;
	}

	function process_pie_chart_data($table, options) {

		options.xAxis.categories = [];
		$('tbody tr td:first-child', $table).each( function(i) {
			options.xAxis.categories.push( $(this).text() );
		});

		// the data series
		options.series = [];
		$('tr', $table).each( function(row) {
			var $tr = $(this);

			var label = '';
			$tr.children().each(function(column, cell) {
				$cell = $(cell);
				if( row == 0 ) {
					if( column > 0 ) {
						options.series.push({
							name: $cell.text(),
							data: [],
							displayData: []
						});
					}
				} else {
					if( column == 0 ) {
						label = $cell.text();
						get_th_color( $cell, row, options );
						color = options.colors[row - 1];

						selected = Boolean( $cell.data('selected') );
						if( !selected ) {
							selected = false;
						}
					} else {

						//Store the raw value as displayData
						options.series[column - 1].displayData.push( $.trim( this.innerHTML ) );

						//Get a number that Highcharts can work with stripping out fancy formatting cruft
						var cell = this.innerHTML.replace(/[><%,\$¢£#!@^&*\+\(\)]/g, '');
						 if( parseFloat(cell) ) {
							value = parseFloat(cell);
						 } else {
							value = null;
						}

						options.series[column - 1].data.push({
							name: label,
							y: value,
							color: color,
							sliced: selected
						});
					}
				}
			});
		});

		options.plotOptions.pie.dataLabels = {
			connectorWidth: 1,
			formatter: function() {
				var i = this.series.data.indexOf( this.point );

				return '<strong>' + this.point.name + '</strong> ' + this.series.options.displayData[i] + '</span>';
			}
		};

		return options;
	}

	function process_category_data($table, options) {

		options.xAxis.categories = [];
		$('tbody tr td:first-child', $table).each( function(i) {
			options.xAxis.categories.push( $(this).text() );
		});

		// the data series
		options.series = [];
		$('tr', $table).each( function(row) {
			var tr = this;
			$('th, td', tr).each( function(column) {
				colIndex = column - 1;

				if (row == 0) { // get the name and init the series
					var $this = $(this);

					get_th_color( $this, column, options );
					color = options.colors[colIndex];

					options.series[colIndex] = {
						name: this.innerHTML,
						color: color,
						data: [],
						displayData: []
					};

				} else { // add values
					//Store the raw value as displayData
					options.series[colIndex].displayData.push( $.trim(this.innerHTML) );

					 //Get a number that Highcharts can work with stripping out fancy formatting cruft
					 var cell = this.innerHTML.replace(/[><%,\$¢£#!@^&*\+\(\)]/g, '');
					 if( parseFloat(cell) ) {
						//The number is a legit number (a Float)
						if( cell < 0 && !isNaN( cell ) ) {
							options.hasNegativeValues = true;
						}
						options.series[colIndex].data.push( parseFloat(cell) );
					} else {
						//Not a legit number so we push null to the data point in this series.
						options.series[colIndex].data.push(null);
					}
				}
			});
		});

		options.tooltip.formatter = function() {
				var string = '<strong>'+ this.x +'</strong><br/>';
				if ( this.points.length > 1 ) {
					points = this.points;
					for(index = 0; index < points.length; index += 1) {
						value = points[index].y;
						if (points[index].y == null) value = 'N/A';
						string = string + '<span style="color:' + points[index].series.color + ';">' + points[index].series.name + '</span>' + ': ' + value+'<br/>';
					}
				} else {
					var i = this.series.data.indexOf( this.point );
					var colorIndex = this.series.index;
					string = string + '<span style="color:' + options.colors[ colorIndex ] + ';">' + this.series.name + '</span>' + ': ' + this.series.options.displayData[i];
				}
				return string;
		}

		return options;
	}


	function process_scatter_data($table, options) {

		// the data series
		options.series = [];
		ourSeries = [];
		$('tr', $table).each( function(row) {
			var tr = this;
			var point = [];

			if (row == 0) return true;

			$('th, td', tr).each( function(column) {
				point['series'] = 'All';
				// Name of point
				if (column == 0) {
					point['name'] = $.trim(this.innerHTML);
				}
				// Name of series
				else if (column == 3) {
					point['series'] = $.trim(this.innerHTML);
				}
				// Coordinates
				else {
					// Get a number that Highcharts can work with stripping out fancy formatting cruft
					var cell = this.innerHTML.replace(/[><%,\$¢£#!@^&*\+\(\)]/g, '');
					if( parseFloat(cell) ) {
						// The number is a legit number (a Float)
						if( cell < 0 && !isNaN( cell ) ) {
							options.hasNegativeValues = true;
						}
						value = parseFloat(cell);
					} else {
						// Not a legit number so we push null to the data point in this series.
						value = null;
					}
					// X
					if (column == 1) {
						point['x'] = value;
					}
					// Y
					else {
						point['y'] = value;
					}
				}
			});

			// Find series in category list
			seriesID = $.inArray( point['series'], ourSeries );
			if ( seriesID == -1 ){
				ourSeries.push(point['series']);
				options.series.push( {
					name: point['series'],
					data: [{
						name: point['name'],
						x: point['x'],
						y: point['y']
					}]
				} );
			}
			// Add the point
			else {
				var pointData = {
					name: point['name'],
					x: point['x'],
					y: point['y']
				};
				options.series[seriesID].data.push( pointData );
			}

		});
console.log(options);
		return options;
	}




	Highcharts.visualize = function($table, options) {

		if( !options.html ) {
			options.html = {
				chart_tab: 'Chart',
				data_tab: 'Data',
				iframe_tab: 'Embed',
				waypoints: false
			};
		}

		var beforeHTML = chart_build_html(options.html);
		var afterHTML = '';
		if (options.html.creditsURL) options.html.credits = '<a href="'+options.html.creditsURL+'">'+options.html.credits+'</a>';
		if (options.html.credits) afterHTML = '<p class="chart_credits">'+options.html.creditText+options.html.credits+'</p>';
		$table.before(beforeHTML).after(afterHTML).wrap('<div id="data" class="hidden chart_toggle_content" />');
		if (options.html.iframe) $table.parent().siblings('.chart_tabs').find('li.iframe').removeClass('hidden');

		var $chart = $table.parent().siblings('.chart');

		var default_options = {
			chart: {
				ignoreHiddenSeries: false,
				//width: $('#chart').width(),
				spacingBottom: 40,

			},
			title: {
				text: null,
				align: 'left',
				x: -9,
				style: {
					fontFamily: 'Georgia, "Times New Roman", Times, serif',
					fontSize: '20px',
					color: '#333'
				}
			},
			subtitle: {
				align: 'left',
				x: -9,
				style: {
					fontFamily: 'Georgia, "Times New Roman", Times, serif',
					fontSize: '16px',
					fontStyle: 'italic',
					color: '#999',
					paddingBottom: '10px'
				}
			},
			yAxis: {
				gridLineDashStyle: 'dot',
				min: 0
			},
			xAxis: {
				tickColor: '#ffffff'
			},
			plotOptions: {
            	bar: {
					shadow: false
				},
				column: {
					shadow: false
            	},
				series: {
					animation: {
						duration: 1000
					},
					connectNulls: true
				},
				area: {
					events: {
						legendItemClick: function () {
							return false; // <== returning false will cancel the default action
											// Doing this because the replotting of content is flawed
						}
					}
				}
        	},
			tooltip: {
				formatter: function() {
						return '<b>'+ this.x +'</b><br/>'+
						this.series.name + ': ' + this.y;
				},
				borderWidth: 0
			},
			legend: {
				borderWidth: 0
			},
			series: [],
			credits: {
				enabled: false
			},
			symbols: ['circle', 'circle', 'circle', 'circle', 'circle', 'circle'],
			colors: ['#7cb5ec', '#434348', '#90ed7d', '#f7a35c', '#8085e9', '#f15c80', '#e4d354', '#2b908f', '#f45b5b', '#91e8e1']
		};


		if( options.xAxis.type == 'datetime' ) {

			options = process_date_data($table, options);
			//$('#data th a').eq(0).addClass('datetime');

		} else if( options.chart.type == 'pie' ) {
			//Pie charts need the data to be formatted a certain way.
			options = process_pie_chart_data($table, options);
			options.tooltip.enabled = false;
		} else if( options.chart.type == 'scatter' ){
			options = process_scatter_data($table, options);
		} else {
			options = process_category_data($table, options);
		}

		if( options.series.length < 2 ) {
			if ( !options.legend ) options.legend = [];
			options.legend.enabled = false;
		}

		if( options.hasNegativeValues ) {
			//If the data has negative values then we need to let HighCharts figure out the yAxis range and not leave it set to 0.
			options.yAxis.min = null;
		}

		$target = $table.parent().siblings('.chart_toggle_content').eq(0);

		options = $.extend(true, default_options, options);

		// Draw the chart
		if (options.html.waypoints) {
			option[options.html.id] = options;

			$target.waypoint( function(direction) {
				var chartid = $(this).data('id');
				if (direction == 'down'){
					$(this).highcharts(option[chartid]);
				}
			}, { triggerOnce: true, offset: '75%' });
		} else {
			$target.highcharts(options);
		}

		if( options.chart.zoomType && options.chart.zoomType != 'none' ) {
			var zoomText = document.ontouchstart === undefined ? 'Click and drag in the plot area to zoom in' : 'Drag your finger over the plot to zoom in';
			$target.find('div').before( '<p class="zoom-instructions meta">' + zoomText + '</p>' );
		}

		//Set-up Sortable Data Table Events
		$( 'thead th', $table).wrapInner('<a href="#"/>');
		$( 'thead a', $table).click(function(e) {
			e.preventDefault();
			var column = $(this).parent().index();
			sortTable( column, $(this).hasClass('datetime') );
			$('th a', $table).removeClass('asc desc');
			$(this).addClass( columnOrder[column] );
		});
	}











	/* Tabs */
	/* Sample Markup:
		<ul class="tabs">
			<li><a href="#id">Tab Label</a></li>
		</ul>
		<div id="id">...</div>
	*/
	$('body').on('click', '.chart_tabs a', function(e) {
		e.preventDefault();

		var $tabs = $(this).parents('.chart_tabs');
		$tabs.find('.active').removeClass('active');
		$(this).parent().addClass('active');

		$(this).parents('.chart_tabs').find('a').each(function() {
			var id = this.href.split('#')[1];
			$tabs.siblings('#' + id).addClass('hidden');
		});

		var id = this.href.split('#')[1];
		$tabs.siblings('#' + id).removeClass('hidden');
	});


	/* Sortable Data Tables Sorting Function */
	var columnOrder = ['desc', 'desc'];
	function sortTable(i, datetime) {
		columnOrder[i] = columnOrder[i] == 'asc' ? 'desc' : 'asc';
		var options = {
			order: columnOrder[i]
		};

		// If it is a date/time based column then we need to tell tinysort to order based on the data-sort attribute of the <td>'s.
		if( datetime ) {
			options.data = 'sort';
		}
		$('.data tbody>tr').tsort('td:eq(' + i + ')', options );
	}
});

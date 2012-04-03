<?php
	wp_enqueue_style('jqplot_style');
	wp_enqueue_script('jqplot');
	wp_enqueue_script('jqplot_plugin_categoryAxisRenderer');
	wp_enqueue_script('jqplot_plugin_highlighter');
	wp_enqueue_script('jqplot_plugin_barRenderer');
	if(!$wpdb) global $wpdb;

	$countReservated = '';
	$countApproved = '';
	$countRejected = '';
	$countPending = '';
	$daysOptions = '';
	$daysOptionsPast = '';
	for($ii = 0; $ii < 8; $ii++){
		$daysOptions .= "'".date("D", time()+($ii*86400))."<br>".date("d.m", time()+($ii*86400))."', ";
		$daysOptionsPast .= "'".date("D", time()-604800+($ii*86400))."<br>".date("d.m", time()-604800+($ii*86400))."', ";
		$day=date("Y-m-d", time()+($ii*86400));
		$dayPastAnf=date("Y-m-d H:i:s", strtotime(date("d.m.Y", time()))-604800+($ii*86400));
		$dayPastEnd=date("Y-m-d H:i:s", (strtotime(date("d.m.Y", time()))+86399)-604800+($ii*86400));
		$countReservated .=  $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE reservated BETWEEN '$dayPastAnf' AND '$dayPastEnd'")).', '; // number of total rows in the database
		$countApproved .=  $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND '$day' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY)")).', '; // number of total rows in the database
		$countRejected .= $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='no' AND '$day' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY)")).', '; // number of total rows in the database
		$countPending .= $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='' AND '$day' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY)")).', '; // number of total rows in the database
	}
?><script type="text/javascript">
<?php if( $show['show_upcoming'] == 1 ){ ?>

	jQuery(document).ready(function(){
		var s1 = [<?php echo $countApproved; ?>];
		var s2 = [<?php echo $countRejected; ?>];
		var s3 = [<?php echo $countPending; ?>];
		// Can specify a custom tick Array.
		// Ticks should match up one for each y value (category) in the series.
		var ticks = [<?php echo $daysOptions; ?>];

		var plot1 = jQuery.jqplot('container', [s1, s2, s3], {
			// The "seriesDefaults" option is an options object that will
			// be applied to all series in the chart.
			stackSeries: true,
			seriesDefaults:{
				renderer:jQuery.jqplot.BarRenderer,
				shadow: false,
				rendererOptions: {
                    barWidth: 25,
                    barPadding: -15,
					},
				highlightMouseOver: true,
			},
			// Custom labels for the series are specified with the "label"
			// option on the series option.  Here a series option object
			// is specified for each series.
			grid: {
				drawBorder: false,
				shadow: false,
				background: "white",
                plotBands: {
                    show: true,
                    interval: 2
                },
				borderWidth: 0          // pixel width of border around grid.
			},
			gridPadding: {top:0, right:0, bottom:30, left:0},
			series:[
				{label:'Approved', color: '#89A54E' },
				{label:'Rejected', color: '#AA4643' },
				{label:'Pending', color: '#4572A7' }
			],
			// Show the legend and put it outside the grid, but inside the
			// plot container, shrinking the grid to accomodate the legend.
			// A value of "outside" would not shrink the grid and allow
			// the legend to overflow the container.
			legend: {
				show: true,
				placement: 'insideGrid',
				background: 'none',
				border: '0px'
			},
			highlighter: {
				show: true,
				lineWidthAdjust: 0,   // pixels to add to the size line stroking the data point marker
				sizeAdjust: -9,          // pixels to add to the size of filled markers when drawing highlight.
				showTooltip: true,      // show a tooltip with data point values.
				tooltipLocation: 'n',  // location of tooltip: n, ne, e, se, s, sw, w, nw.
				fadeTooltip: false,      // use fade effect to show/hide tooltip.
				tooltipFadeSpeed: "fast",// slow, def, fast, or a number of milliseconds.
				tooltipOffset: 5,       // pixel offset of tooltip from the highlight.
				tooltipAxes: 'y',    // which axis values to display in the tooltip, x, y or both.
				tooltipSeparator: ', ',  // separator between values in the tooltip.
				useAxesFormatters: false, // use the same format string and formatters as used in the axes to
				tooltipFormatString: '%d' // sprintf format string for the tooltip.  only used if
			},
			axes: {
				// Use a category axis on the x axis and use our custom ticks.
				xaxis: {
					renderer: jQuery.jqplot.CategoryAxisRenderer,
					ticks: ticks,
					tickOptions: {showGridline: false}
				},
				// Pad the y axis just a little so bars can get close to, but
				// not touch, the grid boundaries.  1.2 is the default padding.
				yaxis: {
					show: true,
					pad: 1.1,
					showTicks: false,        // wether or not to show the tick labels,
					showTickMarks: false,    // wether or not to show the tick marks
					tickOptions: {formatString: '%d'}
				}
			}
		});
	});

	<?php } if($show['show_new'] == 1 ){ ?>

	jQuery(document).ready(function(){
		var ticks = [<?php echo $daysOptionsPast; ?>];
		var s1 = [<?php echo $countReservated; ?>];
		var plot3 = jQuery.jqplot('container2', [ s1 ], {
			series:[{showMarker:false, color:"#575757",shadow:false}],
			gridPadding: {top:0, right:0, bottom:30, left:0},
			grid: {
				drawBorder: false,
				shadow: false,
				background: "white",
                plotBands: {
                    show: true,
                    interval: 2
                },
				borderWidth: 0	// pixel width of border around grid.
			},
			seriesDefaults: {
				rendererOptions: {
					smooth: true
				}
			},
			highlighter: {
				show: true,
				sizeAdjust: 4,
				tooltipFadeSpeed: "fast",// slow, def, fast, or a number of milliseconds.
				tooltipOffset: 9,
				useAxesFormatters: false, // use the same format string and formatters as used in the axes to
				tooltipFormatString: '%d', // sprintf format string for the tooltip.  only used if
				tooltipAxes: 'y'
			},
			axes:{
				xaxis:{
					renderer: jQuery.jqplot.CategoryAxisRenderer,
					ticks: ticks,
					drawMajorGridlines: false,
					tickOptions: {showGridline: false}
				},
				yaxis:{
					labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
					showTicks: false,        // wether or not to show the tick labels,
					showTickMarks: false    // wether or not to show the tick marks
				}
			}
		});
	});
<?php } ?>
</script>
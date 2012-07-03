<?php
function reservation_statistics_page(){
	global $wpdb, $the_rooms_intervals_array;
	
	wp_enqueue_style('jqplot_style');
	wp_enqueue_script('jqplot');
	wp_enqueue_script('jqplot_plugin_categoryAxisRenderer');
	wp_enqueue_script('jqplot_plugin_highlighter');
	wp_enqueue_script('jqplot_plugin_barRenderer');
	wp_enqueue_script('jqplot_plugin_pieRenderer');
	wp_enqueue_script('jqplot_plugin_dateAxisRenderer');

	$countallreservations = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes'"); // number of total rows in the database
	if($countallreservations > 0){
	$nr=0;
	$guestcountyearly="";
	while( $nr < 365*1.5){

		$date=mktime(0, 0, 0, 1, 10, 2011)+(86400*$nr);
		$nr++;
		$lol=date("Y-m-d", $date);

		$sql_A = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND '$lol' BETWEEN arrival AND departure");
		$guestcountyearly.=' [ "'.$lol.'" , '.$sql_A.' ], ';
	}

	$countallreservationsall = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations"); // number of total rows in the database
	$countallreservationsfuture = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND departure > NOW()"); // number of total rows in the database
	$countallreservationspast = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND departure < NOW()"); // number of total rows in the database
	$countallreservationsreject = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='no'"); // number of total rows in the database
	$countallreservationstrash = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='del'"); // number of total rows in the database
	$countallreservationspending = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve=''"); // number of total rows in the database

	$roomquery='';
	global $the_rooms_array;
	$my_posts = $the_rooms_array;
	foreach($my_posts as $my_post){
		$countroom = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$my_post->ID' "); // number of total rows in the database
		$percent=round(100/$countallreservations*$countroom, 2);
		$roomquery.="['".__($my_post->post_title)."', ".$percent."], ";
	}

	$sql_personnights = "SELECT id, number, arrival, departure, room FROM ".$wpdb->prefix ."reservations WHERE approve='yes'";
	$results_personnights = $wpdb->get_results($sql_personnights );
	$pricesall=0;
	$personsall=0;
	$nightsall=0;
	foreach($results_personnights as $results_personnight){
		$personsall+=$results_personnight->number;
		$nightsall+=easyreservations_get_nights($the_rooms_intervals_array[$results_personnight->room], strtotime($results_personnight->arrival), strtotime($results_personnight->departure), 1);
		$pricecalculation=easyreservations_price_calculation($results_personnight->id, '');
		$pricesall+=$pricecalculation['price'];
	}
	
	$sql_pricesfuture = "SELECT id, number, departure FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND departure >= NOW()";
	$results_pricesfuture = $wpdb->get_results($sql_pricesfuture );
	$pricesfuture=0;
	foreach($results_pricesfuture as $results_pricefuture){
		$pricecalculation=easyreservations_price_calculation($results_pricefuture->id, '');
		$pricesfuture+=$pricecalculation['price'];
	}

	$sql_pricespast = "SELECT id, number, departure FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND departure < NOW() ";
	$results_pricespast = $wpdb->get_results($sql_pricespast );
	$pricespast=0;
	foreach($results_pricespast as $results_pricepast){
		$pricecalculation=easyreservations_price_calculation($results_pricepast->id, '');
		$pricespast+=$pricecalculation['price'];
	}

	$personsperreservation=$personsall/$countallreservations;
	$nightsperreservation=$nightsall/$countallreservations;
	$priceperreservation1=$pricesall/$countallreservations;
	$priceperreservation=reservations_format_money($priceperreservation1);
	$countApproved=""; $countRejected=""; $countPending=""; $daysOptions="";
	for($ii = 0; $ii < 30; $ii++){
		$daysOptions .= "'".date("d", time()+($ii*86400))."<br>".date("M", time()+($ii*86400))."', ";
		$day=date("Y-m-d", time()+($ii*86400));
		$countApproved .= $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND '$day' BETWEEN arrival AND departure").', '; // number of total rows in the database
		$countRejected .= $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='no' AND '$day' BETWEEN arrival AND departure").', '; // number of total rows in the database
		$countPending .= $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='' AND '$day' BETWEEN arrival AND departure").', '; // number of total rows in the database
	}
?>
<script type="text/javascript">

	jQuery(document).ready(function(){
		var s1 = [<?php echo $countApproved; ?>];
		var s2 = [<?php echo $countRejected; ?>];
		var s3 = [<?php echo $countPending; ?>];
		var s4 = [<?php echo $countApproved; ?>];
		var ticks = [<?php echo $daysOptions; ?>];
		 
		var plot1 = jQuery.jqplot('nextdays', [s1, s2, s3,s4], {
			stackSeries: true,
			seriesDefaults:{
				highlightMouseOver: true
			},
			grid: {
				drawBorder: false,
				shadow: false,
				background: "white",
				linePattern: 'dashed',
                plotBands: {
                    show: true,
                    interval: 2
                },
				borderWidth: 0          // pixel width of border around grid.
			},
			gridPadding: {top:0, right:0, bottom:30, left:0},
			series:[
				{ label:'Approved', color: '#89A54E',
					renderer:jQuery.jqplot.BarRenderer,
					shadow: false,
					rendererOptions: {
						barMargin: 10,
						barWidth:25
					}
				},
				{ label:'Rejected', color: '#AA4643',
					renderer:jQuery.jqplot.BarRenderer,
					shadow: false,
					rendererOptions: {
						barMargin: 10,
						barWidth:25 
					}
				},{ 
					label:'Pending', 
					color: '#4572A7',
					renderer:jQuery.jqplot.BarRenderer,
					shadow: false,
					rendererOptions: {
						barMargin: 10,
						barWidth:25 
					}
				},{
					label:'Guests', 
					color: '#00000',
					lineWidth:4,
					rendererOptions: {
						smooth: true
					}
				}
			],
			legend: {
				show: true,
				placement: 'insideGrid',
				background: 'none',
				border: '0px'
			},
			highlighter: {
				show: true,
				sizeAdjust: -9,          // pixels to add to the size of filled markers when drawing highlight.
				showTooltip: true,      // show a tooltip with data point values.
				tooltipLocation: 'n',  // location of tooltip: n, ne, e, se, s, sw, w, nw.
				fadeTooltip: false,      // use fade effect to show/hide tooltip.
				tooltipFadeSpeed: "fast",// slow, def, fast, or a number of milliseconds.
				tooltipAxes: 'y'    // which axis values to display in the tooltip, x, y or both.
			},
			axes: {
				xaxis: {
					renderer: jQuery.jqplot.CategoryAxisRenderer,
					ticks: ticks,
					tickOptions: {showGridline: false}
				},
				yaxis: {
					show: true,
					pad: 1,
					showTicks: false,        // wether or not to show the tick labels,
					showTickMarks: false,    // wether or not to show the tick marks
					tickOptions: {formatString: '%d'}
				}
			}
		});

		var data = [<?php echo $roomquery; ?>];

		var plot2 = jQuery.jqplot ('percentrooms', [data], {
			grid: {
				drawBorder: false,
				shadow: false,
				background: "white"
			},
			seriesDefaults: {
				renderer: jQuery.jqplot.PieRenderer,
				shadow:false,
				rendererOptions: {
					// Turn off filling of slices.
					fill: true,
					showDataLabels: true, 
					// Add a margin to seperate the slices.
					sliceMargin: 4, 
					// stroke the slices with a little thicker line.
					lineWidth: 5
				}
			}, 
			legend: { show:true, location: 'e' }
		});
	
			var data = [ <?php echo $guestcountyearly; ?> ];

			var plot1 = jQuery.jqplot("container", [data ], {
				seriesColors: [ "#575757"],
				highlighter: {
					show: true,
					sizeAdjust: 1,
					tooltipOffset: 9
				},
				grid: {
					background: 'rgba(57,57,57,0.0)',
					drawBorder: false,
					shadow: false,
					borderWidth: 0
				},
				seriesDefaults: {
					shadow:false,
					color: '#507AAA',
					rendererOptions: {
						smooth: true
					},
					showMarker: false
				},
				axesDefaults: {
					rendererOptions: {
						baselineWidth: 1.5,
						baselineColor: '#444444',
						drawBaseline: false
					}
				},
				gridPadding: {top:0, right:0, bottom:30, left:0},
				axes: {
					xaxis: {
						renderer: jQuery.jqplot.DateAxisRenderer,
						tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
						tickOptions: {
							formatString: '%b %y',
							markSize: 4,
							angle: -30,
							textColor: '#dddddd'
						},
						numberTicks: 10,
						min: "2011-10-01",
						max: "2012-07-01",
						tickInterval: "7 days",
						drawMajorGridlines: false
					},
					yaxis: {
						pad: 0,
						showTicks: false,        // wether or not to show the tick labels,
						showTickMarks: false,    // wether or not to show the tick marks

						tickOptions: {
							formatString: "%'d Guests",
							showMark: false,
							forceTickAt0: true, forceTickAt100: true 
						}
					}
				}
			});
		});
	</script>
		<h2>
			<?php echo __( 'Reservations Statistics' , 'easyReservations' );?>
		</h2>
		<table  class="<?php echo RESERVATIONS_STYLE; ?>" style="width: 99%">
			<thead>
				<tr>
					<th>
						 <?php echo __( 'Upcoming Reservations' , 'easyReservations' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="margin:0px; padding:0px;background-color:#fff;">
						<div id="nextdays" style="margin: 0px;width: 99%"></div>
					</td>
				</tr>
			</tbody>
		</table>
		<br>
		<table style="width: 99%;" cellpadding="0">
			<tr>
				<td style="width: 71%;"><div id="percentrooms"></div></td>
				<td style="width: 29%;">
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width: 99%;" >
						<thead>
							<tr>
								<th colspan="2"><?php printf ( __( 'Detailed Statistics' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
							<tr style="font-size:13px;">
								<td><?php printf ( __( 'Total reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationsall; ?> </b></td>
							</tr>
							<tr class="alternate" style="font-size:13px;">
								<td style="font-size:13px"> - <?php printf ( __( 'Future approved reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationsfuture; ?></b></td>
							</tr>
							<tr style="font-size:13px;">
								<td style="font-size:13px;"> - <?php printf ( __( 'Past approved reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationspast; ?></b></td>
							</tr>
							<tr class="alternate" style="font-size:13px;">
								<td> - <?php printf ( __( 'Pending reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationspending; ?></b></td>
							</tr>
							<tr>
								<td style="font-size:13px;"> - <?php printf ( __( 'Rejected reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationsreject; ?></b></td>
							</tr>
							<tr  class="alternate"  style="font-size:13px;">
								<td> - <?php printf ( __( 'Trashed reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationstrash; ?></b></td>
							</tr>
							<tr style="font-size:13px;">
								<td>&#216; <?php printf ( __( 'Guests per reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo round($personsperreservation, 2); ?></b></td>
							</tr>
							<tr  class="alternate" style="font-size:13px;">
								<td>&#216; <?php echo __( 'Times per reservations' , 'easyReservations' );?>:</td>
								<td style="text-align:right;"><b><?php echo round($nightsperreservation, 2); ?></b></td>
							</tr><br>
							<tr style="font-size:13px;">
								<td><?php printf ( __( 'Revenue of all reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo reservations_format_money($pricesall).' &'.RESERVATIONS_CURRENCY.';'; ?></b></td>
							</tr>
							<tr  class="alternate"  style="font-size:13px;">
								<td> - <?php printf ( __( 'Revenue of future reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo reservations_format_money($pricesfuture).' &'.RESERVATIONS_CURRENCY.';'; ?></b></td>
							</tr>
							<tr style="font-size:13px;">
								<td> - <?php printf ( __( 'Revenue of past reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo reservations_format_money($pricespast).' &'.RESERVATIONS_CURRENCY.';'; ?></b></td>
							</tr>
							<tr class="alternate" style="font-size:13px;">
								<td> &#216; <?php printf ( __( 'Revenue per reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $priceperreservation.' &'.RESERVATIONS_CURRENCY.';'; ?></b></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td style="width: 35%;"></td>
			</tr>
		</table>
		<br>
		<table  class="<?php echo RESERVATIONS_STYLE; ?>" style="width: 99%">
			<thead>
				<tr>
					<th>
						 <?php echo __( 'Upcoming Reservations' , 'easyReservations' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="margin:0px; padding:0px;background-color:#fff">
						<div id="container" style="margin:0px; padding:0px;"></div>
					</td>
				</tr>
			</tbody>
		</table>
<?php 
	} else echo '<br><div class="error"><p>'.__( 'Add reservations first' , 'easyReservations' ).'</p></div>';
} ?>
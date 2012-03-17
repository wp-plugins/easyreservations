<?php

function reservation_statistics_page() { //Statistic Page
		global $wpdb;
		$countallreservations = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes'")); // number of total rows in the database
		if($countallreservations > 0){
		$nr=0;
		$days="";$guestcount="";
		while( $nr < 20) {
			$date=time()+(86400*$nr);
			$days .= '\''.date("d.m", $date).'\', ';
			$nr++;
			$lol=date("Y-m-d", $date);
			$sql_A = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND '$lol' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - INTERVAL 1 DAY"));				
			$guestcount.="".$sql_A.", ";
		}

		$nr=0;
		$guestcountyearly="";
		$guestcountyearly="";
		while( $nr < 365*2) {

			$date=mktime(0, 0, 0, 1, 1, 2011)+(86400*$nr);
			$days .= '\''.date("d.m", $date).'\', ';
			$nr++;
			$lol=date("Y-m-d", $date);

			$sql_A = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND '$lol' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - INTERVAL 1 DAY"));
			$guestcountyearly.="".$sql_A.", ";
		}
			$countallreservationsall = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations")); // number of total rows in the database
			$countallreservationsfuture = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND DATE_ADD(arrivalDate, INTERVAL nights DAY) > NOW()")); // number of total rows in the database
			$countallreservationspast = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND DATE_ADD(arrivalDate, INTERVAL nights DAY) < NOW()")); // number of total rows in the database
			$countallreservationsreject = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='no'")); // number of total rows in the database
			$countallreservationstrash = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='del'")); // number of total rows in the database
			$countallreservationspending = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve=''")); // number of total rows in the database

			$roomsexactly=0;
			$roomquery='';
			$my_posts = easyreservations_get_rooms();
			foreach($my_posts as $my_post){
				$roomsexactly=$roomsexactly+get_post_meta($my_post->ID, 'roomcount', true);
				$countroom = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$my_post->ID' ")); // number of total rows in the database
				$percent=round(100/$countallreservations*$countroom, 2);
				$roomquery.="['".__($my_post->post_title)."', ".$percent."], ";
			}

			$countwithoutspecial = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND special='0' ")); // number of total rows in the database
			$noneofferpercent=round(100/$countallreservations*$countwithoutspecial, 2);
			$offerquery="['None', ".$noneofferpercent."], ";
			$myoffers = easyreservations_get_offers();
			foreach($myoffers as $myoffer){
				$countspecial = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND special='$myoffer->ID' ")); // number of total rows in the database
				$percents=round(100/$countallreservations*$countspecial, 2);
				$offerquery.="['".__($myoffer->post_title)."', ".$percents."], ";
			}

			$sql_personnights = "SELECT id, number, nights FROM ".$wpdb->prefix ."reservations WHERE approve='yes'";
			$results_personnights = $wpdb->get_results($sql_personnights );
			$pricesall=0;
			$personsall=0;
			$nightsall=0;
			foreach($results_personnights as $results_personnight){
				$personsall+=$results_personnight->number;
				$nightsall+=$results_personnight->nights;
				$pricecalculation=easyreservations_price_calculation($results_personnight->id, '');
				$pricesall+=$pricecalculation['price'];
			}
			
			$sql_pricesfuture = "SELECT id, number, nights FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND DATE_ADD(arrivalDate, INTERVAL nights DAY) >= NOW()";
			$results_pricesfuture = $wpdb->get_results($sql_pricesfuture );
			$pricesfuture=0;
			foreach($results_pricesfuture as $results_pricefuture){
				$pricecalculation=easyreservations_price_calculation($results_pricefuture->id, '');
				$pricesfuture+=$pricecalculation['price'];
			}
			
			$sql_pricespast = "SELECT id, number, nights FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND DATE_ADD(arrivalDate, INTERVAL nights DAY) < NOW() ";
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
				$daysOptions .= "'<b>".date("d", time()+($ii*86400))."<br>".date("M", time()+($ii*86400))."</b>', ";
				$day=date("Y-m-d", time()+($ii*86400));
				$countApproved .= mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND '$day' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY)")).', '; // number of total rows in the database
				$countRejected .= mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='no' AND '$day' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY)")).', '; // number of total rows in the database
				$countPending .= mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='' AND '$day' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY)")).', '; // number of total rows in the database
			}
?>
<script type="text/javascript">
	Highcharts.theme = { colors: ['#4572A7'] };// prevent errors in default theme
	var highchartsOptions = Highcharts.getOptions(); 
		var chart;
		jQuery(document).ready(function() {
			chart = new Highcharts.Chart({
				chart: {
					renderTo: 'nextdays',
					zoomType: 'xy',
						backgroundColor: null,
					margin: [0,0,30,0]
				},
				title: {
						text: '',
						margin: 0
				},
				xAxis: [{
					categories: [<?php echo $daysOptions; ?>]
				}],
				yAxis: [{ // Primary yAxis
					labels: {
						formatter: function() {
							return this.value +'°C';
						},
						style: {
							color: '#89A54E'
						}
					},
					title: {
						text: 'Temperature',
						style: {
							color: '#89A54E'
						}
					}
				}, { // Secondary yAxis
					title: {
						text: 'Rainfall',
						style: {
							color: '#4572A7'
						}
					},
					labels: {
						formatter: function() {
							return this.value +' mm';
						},
						style: {
							color: '#4572A7'
						}
					},
					opposite: true
				}],
					tooltip: {
						formatter: function() {
							return '<b>'+ this.x +'</b><br/>'+
								 this.series.name +': '+ this.y +'<br/>'+
								 'Total: '+ this.point.stackTotal;
						}
					},

				legend: {
					align: 'center',
					verticalAlign: 'top',
						floating: true,
						backgroundColor: null,
						borderWidth: 0,
						shadow: false
				},
				plotOptions: {
					column: {
						stacking: 'normal',
						borderWidth: 0,
						shadow: false,
						pointPadding: 0,
						dataLabels: {
							enabled: false,
							color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
						}
					}
				},

				series: [{
						name: 'Pending',
						type: 'column',
						data: [<?php echo $countPending; ?>]
					}, {
						name: 'Rejected',
						type: 'column',
						data: [<?php echo $countRejected; ?>]
					}, {
						name: 'Approved',
						type: 'column',
						data: [<?php echo $countApproved; ?>]
					}, {
					name: 'Guests',
					color: '#456600',
					type: 'spline',
					data: [<?php echo $countApproved; ?>]
				}]
			});
	});

			var chart;
			jQuery(document).ready(function() {
				chart = new Highcharts.Chart({
					chart: {
						renderTo: 'percentrooms',
						plotBackgroundColor: null,
						plotBorderWidth: null,
						plotShadow: false
					},
					title: {
						text: 'Reservated Rooms'
					},
					tooltip: {
						formatter: function() {
							return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
						}
					},
					plotOptions: {
						pie: {
							allowPointSelect: true,
							cursor: 'pointer',
							dataLabels: {
								enabled: true,
								color: '#000000',
								connectorColor: '#000000',
								formatter: function() {
									return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
								}
							}
						}
					},
				    series: [{
						type: 'pie',
						data: [
						<?php echo $roomquery; ?>
						]
					}]
				});
			});

			var chart;
			jQuery(document).ready(function() {
				chart = new Highcharts.Chart({
					chart: {
						renderTo: 'percentspecials',
						plotBackgroundColor: null,
						plotBorderWidth: null,
						plotShadow: false
					},
					title: {
						text: 'Reservated Offers'
					},
					tooltip: {
						formatter: function() {
							return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
						}
					},
					plotOptions: {
						pie: {
							allowPointSelect: true,
							cursor: 'pointer',
							dataLabels: {
								enabled: true,
								color: '#000000',
								connectorColor: '#000000',
								formatter: function() {
									return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
								}
							}
						}
					},
				    series: [{
						type: 'pie',
						data: [
						<?php echo $offerquery; ?>
						]
					}]
				});
			});
				var data = [
				<?php echo $guestcountyearly; ?>
			];
			jQuery.noConflict()
			var masterChart,
				detailChart;
			jQuery(document).ready(function() {
				// create the master chart
				function createMaster() {
					masterChart = new Highcharts.Chart({
						chart: {
							renderTo: 'master-container',
							reflow: false,
							borderWidth: 0,
							backgroundColor: null,
							marginLeft: 50,
							marginRight: 20,
							zoomType: 'x',
							events: {
								// listen to the selection event on the master chart to update the 
								// extremes of the detail chart
								selection: function(event) {
									var extremesObject = event.xAxis[0],
										min = extremesObject.min,
										max = extremesObject.max,
										detailData = [],
										xAxis = this.xAxis[0];
									// reverse engineer the last part of the data
									jQuery.each(this.series[0].data, function(i, point) {
										if (point.x > min && point.x < max) {
											detailData.push({
												x: point.x,
												y: point.y
											});
										}
									});
									// move the plot bands to reflect the new detail span
									xAxis.removePlotBand('mask-before');
									xAxis.addPlotBand({
										id: 'mask-before',
										from: Date.UTC(2011, 0, 1),
										to: min,
										color: 'rgba(0, 0, 0, 0.2)'
									});
									
									xAxis.removePlotBand('mask-after');
									xAxis.addPlotBand({
										id: 'mask-after',
										from: max,
										to: Date.UTC(2012, 11, 31),
										color: 'rgba(0, 0, 0, 0.2)'
									});
									detailChart.series[0].setData(detailData);
									return false;
								}
							}
						},
						title: {
							text: null
						},
						xAxis: {
							type: 'datetime',
							showLastTickLabel: true,
							maxZoom: 14 * 24 * 3600000, // fourteen days
							plotBands: [{
								id: 'mask-before',
								from: Date.UTC(2011, 0, 1),
								to: Date.UTC(2012, 11, 30),
								color: 'rgba(0, 0, 0, 0.2)'
							}],
							title: {
								text: null
							}
						},
						yAxis: {
							gridLineWidth: 0,
							labels: {
								enabled: false
							},
							title: {
								text: null
							},
							min: 0.6,
							showFirstLabel: false
						},
						tooltip: {
							formatter: function() {
								return false;
							}
						},
						legend: {
							enabled: false
						},
						credits: {
							enabled: false
						},
						plotOptions: {
							series: {
								fillColor: {
									linearGradient: [0, 0, 0, 70],
									stops: [
										[0, '#4572A7'],
										[1, 'rgba(0,0,0,0)']
									]
								},
								lineWidth: 1,
								marker: {
									enabled: false
								},
								shadow: false,
								states: {
									hover: {
										lineWidth: 1						
									}
								},
								enableMouseTracking: false
							}
						},
						series: [{
							type: 'area',
							name: 'USD to EUR',
							pointInterval: 24 * 3600 * 1000,
							pointStart: Date.UTC(2011, 0, 01),
							data: data
						}],
						exporting: {
							enabled: false
						}
					}, function(masterChart) {
						createDetail(masterChart)
					});
				}
				// create the detail chart
				function createDetail(masterChart) {
					// prepare the detail chart
					var detailData = [],
						detailStart = Date.UTC(2011, 05, 30);
					jQuery.each(masterChart.series[0].data, function(i, point) {
						if (point.x >= detailStart) {
							detailData.push(point.y);
						}
					});
					// create a detail chart referenced by a global variable
					detailChart = new Highcharts.Chart({
						chart: {
							marginBottom: 120,
							renderTo: 'detail-container',
							reflow: false,
							marginLeft: 50,
							marginRight: 20,
							style: {
								position: 'absolute'
							}
						},
						credits: {
							enabled: false
						},
						title: {
							text: 'Guests of 2011 - 2012'
						},
						subtitle: {
							text: 'Select an area by dragging across the lower chart'
						},
						xAxis: {
							type: 'datetime'
						},
						yAxis: {
							title: '',
							maxZoom: 0.1
						},
						tooltip: {
							formatter: function() {
								var point = this.points[0];
								return ''+
									'<b>'+ Highcharts.dateFormat('%A, %e %B %Y', this.x) + '</b><br/>'+
									'<b>'+ Highcharts.numberFormat(point.y, 0) +'</b> Guests';
							},
							shared: true
						},
						legend: {
							enabled: false
						},
						plotOptions: {
							series: {
								marker: {
									enabled: false,
									states: {
										hover: {
											enabled: true,
											radius: 3
										}
									}
								}
							}
						},
						series: [{
							name: 'USD to EUR',
							pointStart: detailStart,
							pointInterval: 24 * 3600 * 1000,
							data: detailData
						}],
						exporting: {
							enabled: false
						}
					});
				}
				// make the container smaller and add a second container for the master chart
				var $container = jQuery('#container')
					.css('position', 'relative');
				var $detailContainer = jQuery('<div id="detail-container">')
					.appendTo($container);
				var $masterContainer = jQuery('<div id="master-container">')
					.css({ position: 'absolute', top: 300, height: 80, width: '100%' })
					.appendTo($container);
				// create master and in its callback, create the detail chart
				createMaster();
			});
		</script>
		<h2>
			<?php echo __( 'Reservations Statistics' , 'easyReservations' );?>
		</h2>
		<!-- 3. Add the container -->
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
						<div id="nextdays" style="margin: 5px 0px 0px 0px;"></div>
					</td>
				</tr>
			</tbody>
		</table>
		<br>
		<table style="width: 99%;" cellpadding="0">
			<tr>
				<td style="width: 35%;"><div id="percentrooms"></div></td>
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
								<td>&#216; <?php printf ( __( 'Nights per reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo round($nightsperreservation, 2); ?></b></td>
							</tr><br>
							<tr style="font-size:13px;">
								<td><?php printf ( __( 'Revenue of all reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo reservations_format_money($pricesall).' &'.get_option('reservations_currency').';'; ?></b></td>
							</tr>
							<tr  class="alternate"  style="font-size:13px;">
								<td> - <?php printf ( __( 'Revenue of future reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo reservations_format_money($pricesfuture).' &'.get_option('reservations_currency').';'; ?></b></td>
							</tr>
							<tr style="font-size:13px;">
								<td> - <?php printf ( __( 'Revenue of past reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo reservations_format_money($pricespast).' &'.get_option('reservations_currency').';'; ?></b></td>
							</tr>
							<tr class="alternate" style="font-size:13px;">
								<td> &#216; <?php printf ( __( 'Revenue per reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $priceperreservation.' &'.get_option('reservations_currency').';'; ?></b></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td style="width: 35%;"><div id="percentspecials"></div></td>
			</tr>
		</table><br>
		<div id="container" style="width: 99%; height: 400px;"></div>
<?php 
} else echo '<br><div class="error"><p>'.__( 'Add reservations first' , 'easyReservations' ).'</p></div>';
} ?>
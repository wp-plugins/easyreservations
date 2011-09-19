<?php

function reservation_statistics_page() { //Statistic Page
		global $wpdb;
		$countallreservations = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes'")); // number of total rows in the database
		if($countallreservations > 0){
		$nr=0;
		while( $nr < 20) {
			$date=time()+(86400*$nr);
			$days .= '\''.date("d.m", $date).'\', ';
			$nr++;
			$lol=date("Y-m-d", $date);
			
				$sql_A = mysql_num_rows(mysql_query("SELECT * FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND '$lol' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY) - INTERVAL 1 DAY"));				
				$guestcount.="".$sql_A.", ";
		}

		$nr=0;
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

			$args=array(
				'category' => get_option('reservations_room_category'),
				'post_type' => 'post',
				'post_status' => 'publish|private',
			);

			$roomsexactly=0;
			$my_posts = get_posts($args);
			foreach($my_posts as $my_post){
				$roomsexactly=$roomsexactly+get_post_meta($my_post->ID, 'roomcount', true);
				$countroom = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$my_post->ID' ")); // number of total rows in the database
				$percent=round(100/$countallreservations*$countroom, 2);
				$roomquery.="['".__($my_post->post_title)."', ".$percent."], ";
			}

			$argsoffer=array(
				'category' => get_option('reservations_special_offer_cat'),
				'post_type' => 'post',
				'post_status' => 'publish|private',
			);

			$countwithoutspecial = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND special='0' ")); // number of total rows in the database
			$noneofferpercent=round(100/$countallreservations*$countwithoutspecial, 2);
			$offerquery.="['None', ".$noneofferpercent."], ";
			$myoffers = get_posts($argsoffer);
			foreach($myoffers as $myoffer){
				$countspecial = mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND special='$myoffer->ID' ")); // number of total rows in the database
				$percents=round(100/$countallreservations*$countspecial, 2);
				$offerquery.="['".__($myoffer->post_title)."', ".$percents."], ";
			}

			$sql_personnights = "SELECT id, number, nights FROM ".$wpdb->prefix ."reservations WHERE approve='yes'";
			$results_personnights = $wpdb->get_results($sql_personnights );
			foreach($results_personnights as $results_personnight){
				$personsall+=$results_personnight->number;
				$nightsall+=$results_personnight->nights;
				$pricecalculation=easyreservations_price_calculation($results_personnight->id);
				$pricesall+=$pricecalculation['price'];
			}
			
			$sql_pricesfuture = "SELECT id, number, nights FROM ".$wpdb->prefix ."reservations WHERE DATE_ADD(arrivalDate, INTERVAL nights DAY) >= NOW() AND approve='yes' ";
			$results_pricesfuture = $wpdb->get_results($sql_pricesfuture );
			foreach($results_pricesfuture as $results_pricefuture){
				$pricecalculation=easyreservations_price_calculation($results_pricefuture->id);
				$pricesfuture+=$pricecalculation['price'];

			}
			
			$sql_pricespast = "SELECT id, number, nights FROM ".$wpdb->prefix ."reservations WHERE DATE_ADD(arrivalDate, INTERVAL nights DAY) < NOW() AND approve='yes' ";
			$results_pricespast = $wpdb->get_results($sql_pricespast );
			foreach($results_pricespast as $results_pricepast){
				$pricecalculation=easyreservations_price_calculation($results_pricepast->id);
				$pricespast+=$pricecalculation['price'];
			}

			$personsperreservation=$personsall/$countallreservations;
			$nightsperreservation=$nightsall/$countallreservations;
			$priceperreservation1=$pricesall/$countallreservations;
			$priceperreservation=reservations_format_money($priceperreservation1);
?>
		<script type="text/javascript">
		
			var chart;
			$(document).ready(function() {
				chart = new Highcharts.Chart({
					chart: {
						renderTo: 'nextdays',
						defaultSeriesType: 'areaspline'
					},
					title: {
						text: 'Guests of the next month'
					},
					legend: {
						layout: 'vertical',
						align: 'left',
						verticalAlign: 'top',
						x: 150,
						y: 100,
						floating: true,
						borderWidth: 1,
						backgroundColor: '#FFFFFF'
					},
					xAxis: {
						categories: [
							<?php echo $days; ?>
						],
						plotBands: [{ // visualize the weekend
							from: 4.5,
							to: 6.5,
							color: 'rgba(68, 170, 213, .2)'
						}]
					},
					yAxis: {
						title: {
							text: 'Guests'
						}
					},
					tooltip: {
						formatter: function() {
				                return ''+
								this.x +': '+ this.y +' Guests';
						}
					},
					credits: {
						enabled: false
					},
					plotOptions: {
						areaspline: {
							fillOpacity: 0.5
						}
					},
					series: [{
						name: 'Guests',
						data: [<?php echo $guestcount; ?>] 
					}]
				});
				
				
			});
			
			var chart;
			$(document).ready(function() {
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
						name: 'Browser share',
						data: [
						<?php echo $roomquery; ?>
						]
					}]
				});
			});
			
			var chart;
			$(document).ready(function() {
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
						name: 'Browser share',
						data: [
						<?php echo $offerquery; ?>
						]
					}]
				});
			});
				
				
				var data = [
				<?php echo $guestcountyearly; ?>
			];
			
			var masterChart,
				detailChart;
				
			$(document).ready(function() {
				
				
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
							text: 'Guests of 2011-2012'
						},
						subtitle: {
							text: 'Select an area by dragging across the lower chart'
						},
						xAxis: {
							type: 'datetime'
						},
						yAxis: {
							title: null,
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
				var $container = $('#container')
					.css('position', 'relative');
				
				var $detailContainer = $('<div id="detail-container">')
					.appendTo($container);
				
				var $masterContainer = $('<div id="master-container">')
					.css({ position: 'absolute', top: 300, height: 80, width: '100%' })
					.appendTo($container);
					
				// create master and in its callback, create the detail chart
				createMaster();
				
			});
		</script>

	<div id="icon-options-general" class="icon32"><br></div><h2><?php printf ( __( 'Statistics' , 'easyReservations' ));?></h2>
		<!-- 3. Add the container -->
		<div id="nextdays" style="width: 99%; height: 400px; margin: 0 auto"></div><br>
		<table style="width: 99%;" cellpadding="0">
			<tr>
				<td style="width: 35%;"><div id="percentrooms"></div></td>
				<td style="width: 29%;">
					<table class="widefat" style="width: 99%;" >
						<thead>
							<tr>
								<th colspan="2"><?php printf ( __( 'Detailed Statistics' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
							<tr style="font-size:13px;">
								<td><?php printf ( __( 'Total Reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationsall; ?> </b></td>
							</tr>
							<tr class="alternate" style="font-size:13px;">
								<td style="font-size:13px"> - <?php printf ( __( 'Future approved Reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationsfuture; ?></b></td>
							</tr>
							<tr style="font-size:13px;">
								<td style="font-size:13px;"> - <?php printf ( __( 'Past approved Reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationspast; ?></b></td>
							</tr>
							<tr class="alternate" style="font-size:13px;">
								<td> - <?php printf ( __( 'Pending Reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationspending; ?></b></td>
							</tr>
							<tr>
								<td style="font-size:13px;"> - <?php printf ( __( 'Rejected Reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationsreject; ?></b></td>
							</tr>
							<tr  class="alternate"  style="font-size:13px;">
								<td> - <?php printf ( __( 'Trashed Reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationstrash; ?></b></td>
							</tr>
							<tr style="font-size:13px;">
								<td>&#216; <?php printf ( __( 'Guests per Reservation' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo round($personsperreservation, 2); ?></b></td>
							</tr>
							<tr  class="alternate" style="font-size:13px;">
								<td>&#216; <?php printf ( __( 'Nights per Reservation' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo round($nightsperreservation, 2); ?></b></td>
							</tr><br>
							<tr style="font-size:13px;">
								<td><?php printf ( __( 'Revenue of all Reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo reservations_format_money($pricesall).'&'.get_option('reservations_currency').';'; ; ?></b></td>
							</tr>
							<tr  class="alternate"  style="font-size:13px;">
								<td> - <?php printf ( __( 'Revenue of future Reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo reservations_format_money($pricesfuture).'&'.get_option('reservations_currency').';'; ; ?></b></td>
							</tr>
							<tr style="font-size:13px;">
								<td> - <?php printf ( __( 'Revenue of past Reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo reservations_format_money($pricespast).'&'.get_option('reservations_currency').';'; ; ?></b></td>
							</tr>
							<tr class="alternate" style="font-size:13px;">
								<td> &#216; <?php printf ( __( 'Revenue per Reservation' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $priceperreservation.'&'.get_option('reservations_currency').';'; ; ?></b></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td style="width: 35%;"><div id="percentspecials"></div></td>
			</tr>
		</table><br>
		<div id="container" style="width: 99%; height: 400px;"></div>
		

<?php 
} else echo  __( 'Add Reservations first' , 'easyReservations' ); 
} ?>
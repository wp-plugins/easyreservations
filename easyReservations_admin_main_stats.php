<?php
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
		$countReservated .= mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE reservated BETWEEN '$dayPastAnf' AND '$dayPastEnd'")).', '; // number of total rows in the database
		$countApproved .= mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND '$day' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY)")).', '; // number of total rows in the database
		$countRejected .= mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='no' AND '$day' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY)")).', '; // number of total rows in the database
		$countPending .= mysql_num_rows(mysql_query("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='' AND '$day' BETWEEN arrivalDate AND DATE_ADD(arrivalDate, INTERVAL nights DAY)")).', '; // number of total rows in the database
	}
?>
<script type="text/javascript">
	var chart;
	jQuery(document).ready(function() {
		chart = new Highcharts.Chart({
			chart: {
				renderTo: 'container',
				defaultSeriesType: 'column',
				width: 340,
				height:  240,
				backgroundColor: "#fff",
				margin: [0, 0, 30, 0]
			},
			title: {
				text: '',
				margin: 0
			},
			yAxis: {
				min: 0,
				title: {
					text: ''
				},
				stackLabels: {
					enabled: true,
					style: {
						fontWeight: 'bold',
						color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
					}
				}
			},
			legend: {
				align: 'center',
				verticalAlign: 'top',
				y: -10,
				floating: true,
				backgroundColor: null,
				borderWidth: 0,
				shadow: false
			},
			tooltip: {
				formatter: function() {
					return '<b>'+ this.x +'</b><br/>'+
						 this.series.name +': '+ this.y +'<br/>'+
						 'Total: '+ this.point.stackTotal;
				}
			},
			yAxis: {
				alternateGridColor: '#F4F4F4',
				gridLineColor: '#E0E0E0', 
				gridLineDashStyle: 'Dash',
				minorGridLineColor: '#E0E0E0',
				minorGridLineDashStyle: 'Dash',
				minorGridLineWidth: 1,
				minorTickLength: 0,
				minorTickInterval: 'auto'
			},
			xAxis: {
				categories: [<?php echo $daysOptions; ?>]
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
			exporting: {
				buttons: {
					printButton: { 
						enabled: false 
					}
				}
			},
			series: [{
				name: 'Pending',
				data: [<?php echo $countPending; ?>]
			}, {
				name: 'Rejected',
				data: [<?php echo $countRejected; ?>]
			}, {
				name: 'Approved',
				data: [<?php echo $countApproved; ?>]
			}]
			
		});
	});
	
	var chart;
	jQuery(document).ready(function() {
		chart = new Highcharts.Chart({
			chart: {
				renderTo: 'container2',
				defaultSeriesType: 'line',
				width: 340,
				height:  240,
				backgroundColor: "#fff",
				margin: [0, 0, 30, 0]
			},
			title: {
				text: '',
				margin: 0
			},
			legend: {
				align: 'center',
				verticalAlign: 'top',
				y: -10,
				floating: true,
				backgroundColor: null,
				borderWidth: 0,
				shadow: false
			},
			yAxis: {
				alternateGridColor: '#F4F4F4',
				gridLineColor: '#E0E0E0', 
				gridLineDashStyle: 'Dash',
				minorTickLength: 0,
				minorTickInterval: 'auto'
			},
			xAxis: {
				categories: [<?php echo $daysOptionsPast; ?>]
			},
			plotOptions: {
			spline: {
				lineWidth: 3,
				states: {
					hover: {
						lineWidth: 4
					}
				},
				marker: {
					enabled: false,
					states: {
						hover: {
							enabled: true,
							symbol: 'circle',
							radius: 5,
							lineWidth: 1
						}
					}	
				}
			}
		},
		exporting: {
			buttons: {
				printButton: { 
					enabled: false 
				}
			}
		},
			series: [{
				name: 'Reservations',
				type: 'spline',
				color: '#575757',
				data: [<?php echo $countReservated; ?>],
			}]
		});
	});
</script>
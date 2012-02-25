<?php
	require('../../../../../wp-blog-header.php');

	$val_from = strtotime($_POST['from']);
	$val_fromdate = date("d.m.Y", $val_from);
	$val_to = strtotime($_POST['to']);
	$val_nights = ( $val_to - $val_from ) / 86400;
	$val_room = $_POST['room'];
	$val_offer = $_POST['offer'];
	$val_name = $_POST['thename'];
	$val_email = $_POST['email'];
	$val_persons = $_POST['persons'];
	$error = "";

	if((strlen($val_name) > 30 OR strlen($val_name) <= 3) AND $val_name != ""){ /* check name */
		$error.=  __( 'Please enter a correct name' , 'easyReservations' ).'<br>';
	}

	if($val_from-(strtotime(date("d.m.Y", time()))) < 0){ /* check arrival Date */
		$error.=  __( 'The arrival Date has to be in future' , 'easyReservations' ).'<br>';
	}

	if($val_to-time() < 0){ /* check departure Date */
		$error.=  __( 'The depature Date has to be in future' , 'easyReservations' ).'<br>';
	}
	
	if($val_to < $val_from){ /* check difference between arrival and departure date */
		$error.=  __( 'The arrival Date has to be before the depature Date' , 'easyReservations' ).'<br>';
	}

	if($val_to == $val_from){ /* check difference between arrival and departure date */
		$error.=  __( 'The depature Date has to be after the arrival Date' , 'easyReservations' ).'<br>';
	}

	$pattern_mail = "/^[a-zA-Z0-9-_.]+@[a-zA-Z0-9-_.]+\.[a-zA-Z]{2,4}$/";
	if(!preg_match($pattern_mail, $_POST['email']) AND $val_email != ""){ /* check email */
		$error.=  __( 'Please enter a correct eMail' , 'easyReservations' ).'<br>'; 
	}

	if (!is_numeric($_POST['persons'])){ /* check persons */
		$error.=  __( 'Persons has to be a number' , 'easyReservations' ).'<br>';
	}
	
	if($val_offer > 0){  /* check offers & rooms availability */

		$numbererrors=reservations_check_availibility($val_offer, $val_fromdate, $val_nights, $val_room); /* check offers availability */
		if($numbererrors > 0){
			$error.= '('.$numbererrors.'x) '.__( 'The offer isn\'t available at' , 'easyReservations' ).' '.$val_fromdate.'<br>';
		}

		$numbererrors=reservations_check_availibility($val_room, $val_fromdate, $val_nights, $val_room);  /* check rooms availability */
		if($numbererrors > 0){
			$error.= '('.$numbererrors.'x) '.__( 'The room isn\'t available at' , 'easyReservations' ).' '.$val_fromdate.'<br>';
		}

	} else { /* check rooms availability */

		$numbererrors=reservations_check_availibility($val_room, $val_fromdate, $val_nights, $val_room); /* check rooms availability */
		if($numbererrors > 0){
			$error.= '('.$numbererrors.'x) '.__( 'The room isn\'t available at' , 'easyReservations' ).' '.$val_fromdate.'<br>';
		}

	}
	if( $error != '' ) echo $error;
?>
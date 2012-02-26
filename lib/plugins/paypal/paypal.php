<?php

	add_action('er_set_tab_add', 'easyreservations_add_settings_tab');

	function easyreservations_add_settings_tab(){ 
		
		if(isset($_GET['site']) AND $_GET['site'] == "pay") $current = 'current'; else $current = '';
		$tab = '<li ><a href="admin.php?page=settings&site=pay" class="'.$current.'"><img style="vertical-align:text-bottom ;" src="'.RESERVATIONS_IMAGES_DIR.'/dollar.png"> '. __( 'Pay' , 'easyReservations' ).'</a></li>';

		echo $tab;

	}

	add_action('er_set_save', 'easyreservations_pay_save_settings');

	function easyreservations_pay_save_settings(){
		if(isset($_GET['site']) AND $_GET['site'] == "pay"){
			if(isset($_POST['action']) AND $_POST['action'] == "reservation_pay_settings"){
				$options = array( 'modus' => $_POST['er_pay_modus'], 'title' => $_POST['er_pay_title'], 'owner' => $_POST['er_pay_owner'] );
				update_option('reservations_paypal_options', $options);
			}
		}
	}

	add_action('er_set_add', 'easyreservations_pay_add_settings');

	function easyreservations_pay_add_settings(){

		if(isset($_GET['site']) AND $_GET['site'] == "pay"){
			$options = get_option('reservations_paypal_options'); 
			if(empty($options)) $options = array('title' => '[room] for [nights] days | [arrivalDate] - [depatureDate]', 'owner' => 'feryaz_1319406050_biz@googlemail.com', 'modus' => 'sandbox')?>
			<form method="post" action="admin.php?page=settings&site=pay"  id="reservation_pay_settings" name="reservation_pay_settings">
				<input type="hidden" name="action" value="reservation_pay_settings">
				<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:30%;">
					<thead>
						<tr>
							<th colspan="2"><?php printf ( __( 'Paypal settings' , 'easyReservations' ));?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td style="width:15%;"><?php printf ( __( 'Mode' , 'easyReservations' ));?></td>
							<td><select name="er_pay_modus"><option <?php selected( $options['modus'], 'off' ); ?> value="off"><?php printf ( __( 'Off' , 'easyReservations' ));?></option><option <?php selected( $options['modus'], 'sandbox' ); ?> value="sandbox"><?php printf ( __( 'Sandbox' , 'easyReservations' ));?></option><option <?php selected( $options['modus'], 'on' ); ?> value="on"><?php printf ( __( 'On' , 'easyReservations' ));?></option></select></td>
						</tr>
						<tr class="alternate">
							<td style="width:15%;"><?php printf ( __( 'Title' , 'easyReservations' ));?></td>
							<td><input type="text" name="er_pay_title" id="er_pay_title" style="width:99%;" value="<?php echo $options['title']; ?>"><br>
								<code style="cursor:pointer" onclick="document.getElementById('er_pay_title').value += '[arrivalDate]';">[arrivalDate]</code> <code style="cursor:pointer" onclick="document.getElementById('er_pay_title').value += '[depatureDate]';">[depatureDate]</code> <code style="cursor:pointer" onclick="document.getElementById('er_pay_title').value += '[nights]';">[nights]</code> <code style="cursor:pointer" onclick="document.getElementById('er_pay_title').value += '[room]';">[room]</code> <code style="cursor:pointer" onclick="document.getElementById('er_pay_title').value += '[offer]';">[offer]</code></td>
						</tr>
						<tr>
							<td style="width:15%;"><?php printf ( __( 'Owner' , 'easyReservations' ));?></td>
							<td><input type="text" name="er_pay_owner" value="<?php echo $options['owner']; ?>"></td>
						</tr>
					</tbody>
				</table>
				<input type="button" value="<?php printf ( __( 'Save Changes' , 'easyReservations' ));?>" onclick="document.getElementById('reservation_pay_settings').submit(); return false;" style="margin-top:7px;" class="easySubmitButton-primary" >
			</form><?php
		}

	}

	function easyreservations_generate_paypal_button($theID, $thePrice, $arrivalStamp, $theNights, $roomID, $offerID, $theEmail){
		//return 'paypal! :) ';
		//$finalform .= easyreservations_generate_paypal_button($newID, $thePrice, strtotime($arrivaldate_form), $nights_form, $room_form, $specialoffer_form);

		$paypalOptions = array();

		/*
			Title Options
			----------------

			[arrivalDate]  ~ 20.10.2011
			[depatureDate] ~ 25.10.2011
			[nights] ~ 5
			[room] ~ title of room
			[offer] ~ title of offer

		*/

		$theTitle = $paypalOptions['title'];

		preg_match_all(' /\[.*\]/U', $theTitle, $matchers); 
		$mergearrays=array_merge($matchers[0], array());
		$edgeoneremoave=str_replace('[', '', $mergearrays);
		$edgetworemovess=str_replace(']', '', $edgeoneremoave);

		foreach($edgetworemovess as $fieldsx){
			if($fieldsx=="room"){
				$theTitle=str_replace('['.$fieldsx.']', __(get_the_title($roomID)), $theTitle);
			} elseif($fieldsx=="offer"){
				$theTitle=str_replace('['.$fieldsx.']', __(get_the_title($offerID)), $theTitle);
			} elseif($fieldsx=="nights"){
				$theTitle=str_replace('['.$fieldsx.']', $theNights, $theTitle);
			} elseif($fieldsx=="arrivalDate"){
				$theTitle=str_replace('['.$fieldsx.']', date("d.m.Y", $arrivalStamp), $theTitle);
			} elseif($fieldsx=="depatureDate"){
				$theTitle=str_replace('['.$fieldsx.']', date("d.m.Y", $arrivalStamp+(86400*$theNights)), $theTitle);
			}
		}
		
		

		$theItemNameField = '<input type="hidden" name="item_name" value="'.$theTitle.'">';
		$theBuisnessField = '<input type="hidden" name="invoice" value="'.$theID.'">';
		$thePriceField = '<input type="hidden" name="amount" value="'.$thePrice.'">';
		$theInvoiceField = '<input type="hidden" name="business" value="'.$paypalOptions['owner'].'">';

		$theCurrency  = get_option('reservations_currency');

		if($theCurrency == 'euro') $paypalCurrency = 'EUR';
		elseif($theCurrency == 'dollar') $paypalCurrency = 'EUR';
		elseif($theCurrency == 'pound') $paypalCurrency = 'GBP'; 
		elseif($theCurrency == 'yen') $paypalCurrency = 'JPY'; 
		elseif($theCurrency == 'fnof') $paypalCurrency = 'HUF';

		$theCurrencyField = '<input type="hidden" name="currency_code" value="'.$paypalCurrency.'">';

		if($paypalOptions['modus']=='live') $theModusURL = 'https://www.paypal.com/cgi-bin/webscr';
		else $theModusURL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

		/*
			How to turn on Return and payment validation
			-------------------

			Paypal -> Login -> Profile -> Website Payment Preferences -> Auto Return for Website Payments -> Radio Button ON

		*/

		$editPageURL = get_option('reservations_edit_url');
		if(isset($editPageURL) AND !empty($editPageURL)){
			$theReturnURL = $editPageURL.'?id='.$theID.'?email='.$theEmail.'?paid=1';
			$theReturnField = '<input type="hidden" name="return" value="'.$theReturnURL.'">';
		} else $theReturnField = '';

		$paypalButton = '<form name="_xclick" action="'.$theModusURL.'" method="post">';
		$paypalButton.= ' <input type="hidden" name="cmd" value="_xclick">';
		$paypalButton.= '<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="Pay with Paypal!">';
		$paypalButton.= '<input type="hidden" name="return" value=".get_option">';
		$paypalButton .= $theItemNameField;
		$paypalButton .= $theBuisnessField;
		$paypalButton .= $thePriceField;
		$paypalButton .= $theInvoiceField;
		$paypalButton .= $theReturnField;
		$paypalButton .= $theCurrencyField;
		$paypalButton .= '</form>';

		return $paypalButton;
	}

	function easyreservations_validate_payment(){


	}

	function easyreservations_succes_payment(){


	}

?>
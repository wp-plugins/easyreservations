<?php

	function easyreservations_generate_paypal_button($theID, $thePrice, $arrivalStamp, $theNights, $roomID, $offerID, $theEmail){
		//return 'paypal! :) ';
		//$finalform .= easyreservations_generate_paypal_button($newID, $thePrice, strtotime($arrivaldate_form), $nights_form, $room_form, $specialoffer_form);

		$paypalOptions = array('title' => '[room] for [nights] days | [arrivalDate] - [depatureDate]', 'owner' => 'feryaz_1319406050_biz@googlemail.com', 'modus' => 'sandbox');

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
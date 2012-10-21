<?php
	function easyreservations_tinymce_tutorial() {
		$settting = get_user_setting('easy_tutorial', '');
		$settting = $settting.'Xtinymce';
		$handler = array('#easyreservation_type_select','#easyreservation_form_chooser', '#easyreservation_form_submit_message','#easyreservation_type_select', '#easyreservation_calendar_room', '#easyreservation_calendar_monthesx');
		$content = array(
				'<h3>Select type of shortcode</h3><p>Select the type of content you want to add to your website.</p>',
				'<h3>Formular</h3><p>To add a reservation formular to the page. Select form template here.</p>',
				'<h3>Message</h3><p>This message will be shown after successful reservations.</p>',
				'<h3>Calendar</h3><p>To add a calendar that shows the availability of your resources to your guests.</p>',
				'<h3>Calendars resource</h3><p>Select the resource for that the availability should get shown after loading. It changes automatically on select in widget or forms.</p>',
				'<h3>Multiple monthes</h3><p>Show multiple monthes in one calendar. The Interval defines how many monthes will be skipped at next and prev month.</p>',
		);
		$offset = array('-55 0', '-75 0', '-75 0', '-75 0', '-75 0', '-75 0');
		$at = array('center bottom', 'center bottom', 'center bottom', 'center bottom', 'center bottom', 'center bottom');
		$execute = array('jQuery(\'#easyreservation_type_select\').attr(\'value\', \'form\');jumpto(\'form\');', '', 'jQuery(\'#easyreservation_type_select\').attr(\'value\', \'calendar\');jumpto(\'calendar\');', '', '', '');
		$nr = 6;

		if(function_exists('easyreservations_send_search_callback')){
			$handler[] = '#easyreservation_type_select';
			$content[] = '<h3>Search Form</h3><p>This shortcode will let your guest search for available resources.</p>';
			$offset[] =  '-75 0';
			$at[] = 'center bottom';
			end($execute);
			$execute[key($execute)] = 'jQuery(\'#easyreservation_type_select\').attr(\'value\', \'search\');jumpto(\'search\');';
			$execute[] = '';

			$handler[] = '#easyreservation_search_form';
			$content[] = '<h3>Form URL</h3><p>Enter the URL of a page or post with a form in it. The informations from the search bar and the selected resource will be automatically inserted into it.</p>';
			$offset[] =  '-75 0';
			$at[] = 'center bottom';
			$execute[] = '';

			$handler[] = '#easyreservation_search_exclude';
			$content[] = '<h3>Exclude resources</h3><p>Enter comma seperated IDs to exclude resources from the result. Example: 342,213,43</p>';
			$offset[] =  '-75 0';
			$at[] = 'center bottom';
			$execute[] = 'jQuery(window).scrollTop(jQuery(window).height()+50);';

			$handler[] = '#easyreservation_search_cal_days';
			$content[] = '<h3>One Column</h3><p>Enable this to show your guests the availability of the resources. This is even more useful if you show unavail resources too. The cell can have the values price, left space and empty.</p>';
			$offset[] =  '-75 0';
			$at[] = 'center bottom';
			$execute[] = 'jQuery(window).scrollTop(0);';
			$nr += 4;
		}
		if(function_exists('easyreservations_send_hourlycal_callback')){
			$handler[] = '#easyreservation_type_select';
			$content[] = '<h3>Hourly Calendar</h3><p>This will insert a hourly calendar to your page or post. The settings are quite the same as for the normal calendar.</p>';
			$offset[] =  '-75 0';
			$at[] = 'center bottom';
			end($execute);
			$execute[key($execute)] .= 'jQuery(\'#easyreservation_type_select\').attr(\'value\', \'hourlycalendar\');jumpto(\'hourlycalendar\');';
			$execute[] = '';
			$nr++;
		}
		echo easyreservations_execute_pointer($nr, $handler, $content, $offset, $at, $execute);
	}
?>
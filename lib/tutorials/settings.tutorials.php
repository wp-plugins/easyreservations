<?php
	function easyreservations_settings_tutorial() {
		$handler = array( 'input[name="reservations_support_mail"]', 'select[name="reservations_currency"]', 'select[name="reservations_date_format"]', 'select[name="reservations_resourcemerge"]', 'input[name="reservations_tutorial"]', '#easy_search_bar', '#idpermission', '#iddocumentation', '#idbugreport', '#idpremium','#idrate');
		$content = array(
			'<h3>Support email</h3><p>The email you set here will be the sender of each mail to guests and the receiver of the mails to admin.</p>',
			'<h3>Currency sign</h3><p>Select your currency sign. It just uses the sign to display, so it doesnt has to be named correctly in this select.</p>',
			'<h3>Date format</h3><p>Select your date format. Look that the date in the select is today.</p>',
			'<h3>Availability</h3><p>For some setups you need to have multiple resources for different prices and requirements, but want that your guests can reservate only one of it at the same time. For example a spa with different types of messages but with only one masseur.</p>',
			'<h3>Tutorial</h3><p>If deactivated this tutorial messages wont pop up at all. Else they will appear till you read the whole page once. Reset to read them again. This history gets saved per user. Premium will add messages to some pages, so its recommended to reset after installing it.</p>',
			'<h3>Search Bar</h3><p>Like in forms this area represents the seach bar of the searchForm. With the [tags] you can define the way how your guest can search for available resources.</p>',
			'<h3>Permissions</h3><p>Define the required capabilitys for the different easyReservations Pages. You can set permissions for the resources too.</p>',
			'<h3>Documentation</h3><p>Read even more about the plugin here.</p>',
			'<h3>Report bug</h3><p>Please report every little bug here.</p>',
			'<h3>Premium</h3><p>Support the development and get alot of new functions!</p>',
			'<h3>Rate</h3><p>Rate the plugin and recommend it.</p>',
		);
		$offset = array('-75 0', '-75 0', '-75 0',  '-75 0', '-65 0', '-75 0', '-75 0', '-75 0', '-75 0', '-75 0', '-75 0', '-75 0', '-75 0');
		$at = array('top', 'top', 'top', 'top', 'top',  'top', 'top', 'top', 'top', 'top', 'top');

		echo easyreservations_execute_pointer(10, $handler, $content, $offset, $at);
	}
?>
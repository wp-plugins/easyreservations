<?php
	function easyreservations_emails_tutorial() {
		$handler = array('#wpbody a.current', '#idemailhead:first', '#idactive:first', '#idsubj:first', '#idmsg:first', '#idtags', '#idtagcustom');
		$content = array(
				'<h3>Emails</h3><p>The emails get generated by templates you define below. Each trigger has its own template. The emails get send in plain/text and HTML isn\'t available.</p>',
				'<h3>Receiver</h3><p>Here you can see who is the receiver of the template.</p>',
				'<h3>Active</h3><p>You can de-activate each template on its own.</p>',
				'<h3>Subject</h3><p>This will be the subject of the email.</p>',
				'<h3>Message</h3><p>This is the message of the email in that you can use the [tags] from the right box to insert the reservations information.</p>',
				'<h3>Tags</h3><p>Use this placeholders to insert the reservations information in the emails. <b>[adminmessage]</b> or <b>[changelog]</b> will only work in related kind of emails.</p>',
				'<h3>Custom and price fields</h3><p>To insert the information from one custom information or price directly you can use <b>[custom title]</b> and <b>[prices title]</b>.</p>',
		);
		$offset = array('-75 0', '-65 0', '-65 0', '-75 0', '-75 0', '-48 0', '-75 0');
		$at = array('top', 'top', 'top', 'top', 'top', 'top', 'top');

		echo easyreservations_execute_pointer(7, $handler, $content, $offset, $at);
	}
?>
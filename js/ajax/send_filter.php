<?php

	require('../../../../../wp-blog-header.php'); 
	$filter = $_POST['filter'];
	$id = $_POST['id'];

	update_post_meta($id, 'reservations_filter',$filter);

?>
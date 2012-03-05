<?php
	global $wpdb;

	$table_name_new = $wpdb->prefix . "resources";

	$sql = "CREATE TABLE ".$wpdb->prefix ."resources(
	id int(10) NOT NULL AUTO_INCREMENT,
	category int(2) NOT NULL,
	titel text NOT NULL,
	description text NOT NULL,
	count int(5) NOT NULL,
	filter text NOT NULL,
	groundprice text NOT NULL,
	childprice text NOT NULL,
	infos text NOT NULL,
	access varchar(15) NOT NULL,
	parent int(10) NOT NULL,
	UNIQUE KEY id (id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	$args=array( 'category' => get_option('reservations_room_category'), 'post_type' => 'post', 'post_status' => 'publish|private', 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts'     => -1 );

	$getids = get_posts($args);
	foreach($getids as $getid){
		$id = $getid->ID;
		$title = $getid->post_title;
		$description = $getid->post_content;
		$roomcount = get_post_meta($id, 'roomcount', true);
		$groundprice = get_post_meta($id, 'reservations_groundprice', true);
		$child_price = get_post_meta($id, 'reservations_child_price', true);
		$filter = get_post_meta($id, 'reservations_filter', true);

		$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix ."resources(id, category, titel, description, count, filter, groundprice, childprice, infos, access, parent) 
		VALUES ('$id', '1', '$title', '$description', '$roomcount', '$filter', '$groundprice', '$child_price', '', 'manage_options', '' )"  ) ); 
	}

	$args=array( 'category' => get_option('reservations_special_offer_cat'), 'post_type' => 'post', 'post_status' => 'publish|private', 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts'     => -1 );

	$getids = get_posts($args);
	foreach($getids as $getid){
		$id = $getid->ID;
		$title = $getid->post_title;
		$description = $getid->post_content;
		$roomcount = get_post_meta($id, 'roomcount', true);
		$groundprice = get_post_meta($id, 'reservations_groundprice', true);
		$child_price = get_post_meta($id, 'reservations_child_price', true);
		$filter = get_post_meta($id, 'reservations_filter', true);

		$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix ."resources(id, category, titel, description, count, filter, groundprice, childprice, infos, access, parent) 
		VALUES ('$id', '2', '$title', '$description', '$roomcount', '$filter', '$groundprice', '$child_price', '', 'manage_options', '' )"  ) ); 
	}
?>
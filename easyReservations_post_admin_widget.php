<?php
/* Define the custom box */

// WP 3.0+
// add_action( 'add_meta_boxes', 'myplugin_add_custom_box' );

// backwards compatible
add_action( 'admin_init', 'reservations_add_custom_box', 1 );

/* Do something with the data entered */
add_action( 'save_post', 'reservations_save_postdata' );

/* Adds a box to the main column on the Post and Page edit screens */
function reservations_add_custom_box() {
    add_meta_box( 
        'myplugin_sectionid',
        __( 'Special Offers and Rooms', 'myplugin_textdomain' ),
        'reservations_post_widget',
            'post' 
            ,'side'
            ,'low'
		
    );
}

/* Prints the box content */
function reservations_post_widget() {

 global $post;
  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'reservation_noncename' );  
  
  // The actual fields for data entry add_post_meta($post_id, $meta_key, $meta_value, $unique)
 $reservations_current_value_fromto = get_post_meta($post->ID, 'fromto', TRUE);
 $reservations_current_value_percent = get_post_meta($post->ID, 'percent', TRUE);
 $reservations_current_value_short = get_post_meta($post->ID, 'short', TRUE);
 $reservations_current_value_price = get_post_meta($post->ID, 'price', TRUE);
 $reservations_current_room_count = get_post_meta($post->ID, 'roomcount', TRUE);
  ?>
  <small><?php printf ( __( 'Only fill this for Special Offers and Rooms' , 'easyReservations' ));?>; <a href=\"http://www.feryaz.de"><?php printf ( __( 'Help' , 'easyReservations' ));?></a></small><br><br>
 <b><?php printf ( __( 'For Special Offers only ' , 'easyReservations' ));?></b>
  <table>
  <tr>
  <td><?php printf ( __( 'From - To' , 'easyReservations' ));?></td>
  <td><input type="text" id="reservations_from_to" name="reservations_from_to" size="25" value="<?php echo $reservations_current_value_fromto; ?>" /></td>
  </tr>
  <tr>
  <td><?php printf ( __( 'Percent/Price' , 'easyReservations' ));?></td>
  <td><input type="text" id="reservations_percent" name="reservations_percent" size="25" value="<?php echo $reservations_current_value_percent; ?>" /></td>
  </tr>
  <tr>
  <td><?php printf ( __( 'Short Description' , 'easyReservations' ));?></td>
  <td><input type="text" id="reservations_short" name="reservations_short" size="25" value="<?php echo $reservations_current_value_short; ?>" /></td>
  </tr>
  </table><br>
 <b><?php printf ( __( 'For Special Offers and Rooms' , 'easyReservations' ));?></b>
  <table>
  <tr>
  <td width="50%"><?php printf ( __( 'Price' , 'easyReservations' ));?></td>
  <td width="50%"><input type="text" id="reservations_price" name="reservations_price" size="25" value="<?php echo $reservations_current_value_price; ?>" /></td>
  </tr>
  <tr>
  <td width="50%"><?php printf ( __( 'Room Count' , 'easyReservations' ));?></td>
  <td width="50%"><input type="text" id="reservations_room_count" name="reservations_room_count" size="25" value="<?php echo $reservations_current_room_count; ?>" /></td>
  </tr>
  </table>
  <?php
}

/* When the post is saved, saves our custom data */
function reservations_save_postdata( $post_id ) {
  // verify if this is an auto save routine. 
   global $post;
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if ( !wp_verify_nonce( $_POST['reservation_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  
  // Check permissions
  if ( 'post' == $_POST['post_type'] )  {
    if ( !current_user_can( 'edit_post', $post->ID ) )
        return;
  }

  // OK, we're authenticated: we need to find and save the data

  $reservations_from_to = $_POST['reservations_from_to'];
  $reservations_percent = $_POST['reservations_percent'];
  $reservations_short = $_POST['reservations_short'];
  $reservations_price = $_POST['reservations_price'];
  $reservations_room_count = $_POST['reservations_room_count'];

 $reservations_current_value_fromto = get_post_meta($post->ID, 'fromto', TRUE);
 $reservations_current_value_percent = get_post_meta($post->ID, 'percent', TRUE);
 $reservations_current_value_short = get_post_meta($post->ID, 'short', TRUE);
 $reservations_current_value_price = get_post_meta($post->ID, 'price', TRUE);
 $reservations_current_room_count = get_post_meta($post->ID, 'roomcount', TRUE);

	my_meta_clean($reservations_from_to);
	if ($reservations_current_value_fromto) {
		if($reservations_from_to == "") delete_post_meta($post->ID,'fromto');
		else update_post_meta($post->ID,'fromto',$reservations_from_to);
	}
	elseif($reservations_from_to != ""){
		 add_post_meta($post->ID,'fromto',$reservations_from_to,TRUE);
	}
	
	my_meta_clean($reservations_percent);
	if ($reservations_current_value_percent) {
		if($reservations_percent == "")  delete_post_meta($post->ID,'percent');
		else update_post_meta($post->ID,'percent',$reservations_percent);
	}
	elseif($reservations_percent != "") {
		add_post_meta($post->ID,'percent',$reservations_percent,TRUE);
	}

	my_meta_clean($reservations_short);
	if ($reservations_current_value_short) {
		if($reservations_short == "") delete_post_meta($post->ID,'short');
		else update_post_meta($post->ID,'short',$reservations_short);
	}
	elseif($reservations_short != "") {
		add_post_meta($post->ID,'short',$reservations_short,TRUE);
	}
	
	my_meta_clean($reservations_price);
	if ($reservations_current_value_price) {
		if($reservations_price == "")  delete_post_meta($post->ID,'price');
		else update_post_meta($post->ID,'price',$reservations_price);
	}
	elseif($reservations_price != "") {
		add_post_meta($post->ID, 'price', $reservations_price, TRUE);
	}

	my_meta_clean($reservations_room_count);
	if ($reservations_room_count) {
		if($reservations_room_count == "")  delete_post_meta($post->ID,'roomcount');
		else update_post_meta($post->ID,'roomcount',$reservations_room_count);
	}
	elseif($reservations_room_count != "") {
		add_post_meta($post->ID, 'roomcount', $reservations_room_count, TRUE);
	}

	// Do something with $mydata 
  // probably using add_post_meta(), update_post_meta(), or 
  // a custom table (see Further Reading section below)
}
function my_meta_clean(&$arr)
{
	if (is_array($arr))
	{
		foreach ($arr as $i => $v)
		{
			if (is_array($arr[$i])) 
			{
				my_meta_clean($arr[$i]);
 
				if (!count($arr[$i])) 
				{
					unset($arr[$i]);
				}
			}
			else 
			{
				if (trim($arr[$i]) == '') 
				{
					unset($arr[$i]);
				}
			}
		}
 
		if (!count($arr)) 
		{
			$arr = NULL;
		}
	}
}
?>
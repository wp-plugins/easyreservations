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
 $reservations_current_value_price = get_post_meta($post->ID, 'reservations_groundprice', TRUE);
 $reservations_current_room_count = get_post_meta($post->ID, 'roomcount', TRUE);
 $reservations_filter = get_post_meta($post->ID, 'reservations_filter', TRUE);
  ?><script src="<?php echo WP_PLUGIN_URL . '/easyreservations/js/filterhelp.js'; ?>"></script><small><?php printf ( __( 'Only fill this for Special Offers and Rooms' , 'easyReservations' ));?>; <a href=<?php echo '"http://www.feryaz.de/dokumentation/"'; ?>><?php printf ( __( 'Help' , 'easyReservations' ));?></a></small><br><br>
 <b><?php printf ( __( 'For Offers Box Style only ' , 'easyReservations' ));?></b>
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
 <b><?php printf ( __( 'For Offers and Rooms' , 'easyReservations' ));?></b>
  <table>
  <tr>
  <td width="50%"><?php printf ( __( 'Base Price' , 'easyReservations' ));?></td>
  <td width="50%"><input type="text" id="reservations_price" name="reservations_price" size="25" value="<?php echo $reservations_current_value_price; ?>" /></td>
  </tr>
  <tr>
  <td width="50%"><?php printf ( __( 'Number of Rooms' , 'easyReservations' ));?></td>
  <td width="50%"><input type="text" id="reservations_room_count" name="reservations_room_count" size="25" value="<?php echo $reservations_current_room_count; ?>" /></td>
  </tr>
  </table>
   <p><b><?php printf ( __( 'Set Filter' , 'easyReservations' ));?></b> [<a href="#" onclick="helpFilters()"/>Help</a>]</p>
<span id="Text"></span>
<select id="FirstNo"><option value="price">Price</option><option value="stay">Stay</option><option value="loyal">Loyal</option><option value="pers">Pers</option><option value="avail">Avail</option></select><input onfocus="if (this.value == 'Condition') this.value = '';"  style="border-color:#FF9393" type="text" id="SecondNo" name="cond" size="19" value="Condition" /><input  style="border-color:#AAFFC5" onfocus="if (this.value == 'Value') this.value = '';" type="text" id="ThirdNo" name="value" size="2" value="Value" /><input type="button" style="background-image: url(<?php echo RESERVATIONS_IMAGES_DIR; ?>/add.png); height:16px; width:16px;border:0px; vertical-align:middle;" onclick="Add()"/><br>
<input type="text" id="reservations_filter" name="reservations_filter" size="45" style="height:30px; font: 14px #000000; background:#FFFFE0; border: 1px solid #E6DB55" value="<?php echo $reservations_filter; ?>" />
 <?php }

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

  $reservations_filter = $_POST['reservations_filter'];
  $reservations_from_to = $_POST['reservations_from_to'];
  $reservations_percent = $_POST['reservations_percent'];
  $reservations_short = $_POST['reservations_short'];
  $reservations_price = $_POST['reservations_price'];
  $reservations_room_count = $_POST['reservations_room_count'];

 $reservations_current_value_filter = get_post_meta($post->ID, 'reservations_filter', TRUE);
 $reservations_current_value_fromto = get_post_meta($post->ID, 'fromto', TRUE);
 $reservations_current_value_percent = get_post_meta($post->ID, 'percent', TRUE);
 $reservations_current_value_short = get_post_meta($post->ID, 'short', TRUE);
 $reservations_current_value_price = get_post_meta($post->ID, 'reservations_groundprice', TRUE);
 $reservations_current_room_count = get_post_meta($post->ID, 'roomcount', TRUE);

	my_meta_clean($reservations_filter);
	if ($reservations_current_value_filter) {
		if($reservations_filter == "") delete_post_meta($post->ID,'reservations_filter');
		else update_post_meta($post->ID,'reservations_filter',$reservations_filter);
	}
	elseif($reservations_filter != ""){
		 add_post_meta($post->ID,'reservations_filter',$reservations_filter,TRUE);
	}
	
	my_meta_clean($reservations_from_to);
	if($reservations_current_value_fromto) {
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
		if($reservations_price == "")  delete_post_meta($post->ID,'reservations_groundprice');
		else update_post_meta($post->ID,'reservations_groundprice',$reservations_price);
	}
	elseif($reservations_price != "") {
		add_post_meta($post->ID, 'reservations_groundprice', $reservations_price, TRUE);
	}

	my_meta_clean($reservations_room_count);
	if ($reservations_room_count) {
		if($reservations_room_count == "")  delete_post_meta($post->ID,'roomcount');
		else update_post_meta($post->ID,'roomcount',$reservations_room_count);
	}
	elseif($reservations_room_count != "") {
		add_post_meta($post->ID, 'roomcount', $reservations_room_count, TRUE);
	}
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
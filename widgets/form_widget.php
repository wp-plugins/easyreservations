<?php
/**
 * Foo_Widget Class
 */
class easyReservations_form_widget extends WP_Widget {
	/** constructor */
	function __construct() {
		parent::WP_Widget( /* Base ID */'easyReservations_form_widget', /* Name */'easyReservations Widget', array( 'description' => 'easyReservations form and calendar widget' ) );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$calendar = esc_attr( $instance[ 'calendar' ] );
		$calendar_price = esc_attr( $instance[ 'calendar_price' ] );
		$calendar_width = esc_attr( $instance[ 'calendar_width' ] );
		$calendar_height = esc_attr( $instance[ 'calendar_height' ] );
		$form_date = esc_attr( $instance[ 'form_date' ] );
		$form_room = esc_attr( $instance[ 'form_room' ] );
		$form_offer = esc_attr( $instance[ 'form_offer' ] );
		$form_url = esc_attr( $instance[ 'form_url' ] );
		$form_button = esc_attr( $instance[ 'form_button' ] );
		
		if($calendar_price == "on") $showPrice = 1;
		else $showPrice = 0;
		
		if($calendar_width == 0 OR empty($calendar_width)) $calendar_width = 180;

		echo $before_widget;
		if($title) echo $before_title . $title . $after_title;
		if($calendar == "on"){?>
			<script language="JavaScript" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/lib/widgets/form_widget_calendar.js"></script>
			<input type="hidden" id="urlWidgetCalendar" value="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/send_calendar.php">
			<form name="widget_formular" id="widget_formular">
				<input type="hidden" name="room" onChange="easyRes_sendReq_widget_Calendar()" value="5">
				<input type="hidden" name="offer" onChange="easyRes_sendReq_widget_Calendar()" value="0">
				<input type="hidden" name="date" onChange="easyRes_sendReq_widget_Calendar()" value="0">
				<input type="hidden" name="size" value="<?php echo $calendar_width.','.$calendar_height.','.$showPrice; ?>">
			</form>
			<div id="show_widget_calendar"></div>
			<script>
				easyRes_sendReq_widget_Calendar();
			</script><?php 

		}  if(isset($form_url) AND !empty($form_url)){ ?>
		<form method="post" action="<?php echo $form_url; ?>" name="easy_widget_form">
			<?php
		} if($form_date == "on"){?>

			<link href="<?php echo WP_PLUGIN_URL;?>/easyreservations/css/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css"/>
			<script src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/jquery.min.js"></script>
			<script src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/jquery-ui.min.js"></script>
			<script>
				$(document).ready(function() {
					$("#easy-widget-datepicker-from").datepicker( { dateFormat: 'dd.mm.yy', style: 'font-size:1em' });
					$("#easy-widget-datepicker-to").datepicker( { dateFormat: 'dd.mm.yy' });
				});
			</script><br>
			<p nowrap><input type="text" style="width:<?php echo ($calendar_width/2)-10; ?>px;" name="from" id="easy-widget-datepicker-from" value="<?php echo date("d.m.Y", time()); ?>">
			<input type="text" style="width:<?php echo ($calendar_width/2)-10; ?>px;" name="to" id="easy-widget-datepicker-to" value="<?php echo date("d.m.Y", time()+(86400*7)); ?>"></p><?php
		} if($form_room == "on"){ ?>
				<select name="room" <?php if($calendar == "on"){ ?>onchange="document.widget_formular.room.value=this.value;easyRes_sendReq_widget_Calendar();"<?php } ?>><?php echo reservations_get_room_options(); ?></select>
			<?php
		} if($form_offer == "on"){ ?>
				<select name="offer" <?php if($calendar == "on" AND $showPrice == 1){ ?>onchange="document.widget_formular.offer.value=this.value;easyRes_sendReq_widget_Calendar();"<?php } ?>><option value="0"><?php echo __( 'None', 'easyReservations' ); ?></option><?php echo reservations_get_offer_options(); ?></select>
			<?php
		} if(isset($form_url) AND !empty($form_url)){ ?>
			<input type="submit" value="<?php echo $form_button; ?>">
		</form>
			<?php
		}
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['calendar'] = strip_tags($new_instance['calendar']);
		$instance['calendar_width'] = strip_tags($new_instance['calendar_width']);
		$instance['calendar_height'] = strip_tags($new_instance['calendar_height']);
		$instance['calendar_price'] = strip_tags($new_instance['calendar_price']);
		$instance['form_date'] = strip_tags($new_instance['form_date']);
		$instance['form_room'] = strip_tags($new_instance['form_room']);
		$instance['form_offer'] = strip_tags($new_instance['form_offer']);
		$instance['form_url'] = strip_tags($new_instance['form_url']);
		$instance['form_button'] = strip_tags($new_instance['form_button']);
		return $instance;
		?><script>alert(document.getElementById('<?php echo $this->get_field_id('calendar_price'); ?>').value);</script><?php
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance[ 'title' ] );
			$calendar_height = esc_attr( $instance[ 'calendar_height' ] );
			$calendar_width = esc_attr( $instance[ 'calendar_width' ] );
			$form_url = esc_attr( $instance[ 'form_url' ] );
			$form_button = esc_attr( $instance[ 'form_button' ] );
		}
		else {
			$title = __( 'New title', 'text_domain' );
			$calendar_width = 180;
			$calendar_height = 140;
			$form_url = __( 'type in URL to a form', 'text_domain' );
			$form_button = __( 'Reser now!', 'text_domain' );
		} //<?php checked( (bool) $instance['calendar_room'], true );
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'easyReservations'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('calendar'); ?>"><?php _e('Show calendar:', 'easyReservations'); ?>
		<input id="<?php echo $this->get_field_id('calendar'); ?>" <?php checked( (bool) $instance['calendar'], true ); ?> name="<?php echo $this->get_field_name('calendar'); ?>" type="checkbox" <?php if($this->get_field_name('calendar') == "on") echo 'checked'; ?> />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('calendar_room'); ?>"><?php _e('Default room:', 'easyReservations'); ?>
		<select id="<?php echo $this->get_field_id('calendar_room'); ?>" name="<?php echo $this->get_field_id('calendar_room'); ?>"><?php echo reservations_get_room_options(); ?></select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('calendar_width'); ?>"><?php _e('Calendar width:', 'easyReservations'); ?></label> 
		<input class="widefat" style="width:40px" id="<?php echo $this->get_field_id('calendar_width'); ?>" name="<?php echo $this->get_field_name('calendar_width'); ?>" type="text" value="<?php echo $calendar_width; ?>" /> px
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('calendar_height'); ?>"><?php _e('Calendar height:', 'easyReservations'); ?></label> 
		<input class="widefat" style="width:40px" id="<?php echo $this->get_field_id('calendar_height'); ?>" name="<?php echo $this->get_field_name('calendar_height'); ?>" type="text" value="<?php echo $calendar_height; ?>" /> px
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('calendar_price'); ?>"><?php _e('Show price in calendar:', 'easyReservations'); ?>
		<input id="<?php echo $this->get_field_id('calendar_price'); ?>" <?php checked( (bool) $instance['calendar_price'], true ); ?> name="<?php echo $this->get_field_name('calendar_price'); ?>" type="checkbox" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('form_date'); ?>"><?php _e('Date fields:', 'easyReservations'); ?>
		<input id="<?php echo $this->get_field_id('form_date'); ?>" <?php checked( (bool) $instance['form_date'], true ); ?> name="<?php echo $this->get_field_name('form_date'); ?>" type="checkbox" <?php if($this->get_field_name('form_date') == "on") echo 'checked'; ?> />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('form_room'); ?>"><?php _e('Rooms select:', 'easyReservations'); ?>
		<input id="<?php echo $this->get_field_id('form_room'); ?>" <?php checked( (bool) $instance['form_room'], true ); ?> name="<?php echo $this->get_field_name('form_room'); ?>" type="checkbox" <?php if($this->get_field_name('form_room') == "on") echo 'checked'; ?> />
		</p>
				<p>
		<label for="<?php echo $this->get_field_id('form_offer'); ?>"><?php _e('Offers select:', 'easyReservations'); ?>
		<input id="<?php echo $this->get_field_id('form_offer'); ?>" <?php checked( (bool) $instance['form_offer'], true ); ?> name="<?php echo $this->get_field_name('form_offer'); ?>" type="checkbox" <?php if($this->get_field_name('form_offer') == "on") echo 'checked'; ?> />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('form_url'); ?>"><?php _e('Form URL:', 'easyReservations'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('form_url'); ?>" name="<?php echo $this->get_field_name('form_url'); ?>" type="text" value="<?php echo $form_url; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('form_button'); ?>"><?php _e('Submit button:', 'easyReservations'); ?></label> 
		<input class="widefat" style="width:80px" id="<?php echo $this->get_field_id('form_button'); ?>" name="<?php echo $this->get_field_name('form_button'); ?>" type="text" value="<?php echo $form_button; ?>" />
		</p>

		<?php 
	}

} // class Foo_Widget
// register Foo_Widget widget
add_action( 'widgets_init', create_function( '', 'register_widget("easyReservations_form_widget");' ) );
 ?>
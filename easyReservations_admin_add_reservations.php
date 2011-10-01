<?php function reservation_add_reservaton() {
				global $wpdb;

				if(isset($_POST["action"])){
					$action = $_POST['action'];
				}

				if(isset($action) AND $action == "addreservation") {
					$error=0;
					$date=$_POST["date"];
					$name=$_POST["name"];
					$email=$_POST["email"];
					$room=$_POST["room"];
					$note=$_POST["note"];
					$nights=$_POST["nights"];
					$persons=$_POST["persons"];
					$specialoffer=$_POST["specialoffer"];
					$customFields='';
					
					echo $persons;

					$theInputPOSTs=array($_POST["date"], $_POST["name"], $_POST["email"], $_POST["room"], $_POST["note"], $_POST["nights"], $_POST["persons"], $_POST["specialoffer"]);

					foreach($theInputPOSTs as $input){
						if($input==''){ $error++; echo $input.'1 '; }
					}

					if((isset($_POST["customvalue1"]) AND isset($_POST["customtitle1"])) OR (isset($_POST["customvalue2"]) AND isset($_POST["customtitle22"])) OR (isset($_POST["customvalue3"]) AND isset($_POST["customtitle3"])) OR (isset($_POST["customvalue4"]) AND isset($_POST["customtitle4"]))){
						for($theCount = 1; $theCount < 16; $theCount++){
							if(isset($_POST["customvalue".$theCount]) AND isset($_POST["customtitle".$theCount])){
								$customFields.= $_POST["customtitle".$theCount].'&:&'.$_POST["customvalue".$theCount].'&;&';
							}
						}
					}
					
					echo $customFields;

					if($error == 0){
						$timestampanf=strtotime($date);
						$timestampend=strtotime($nights);
						$anznights=round(($timestampend-$timestampanf)/60/60/24);
						$dat=date("Y-m", $timestampanf);
						$rightdate=date("Y-m-d", $timestampanf);
						$rightdate2=date("Y-m-d", $timestampend);

						$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix ."reservations(arrivalDate, name, email, notes, nights, dat, room, number, special, approve, custom ) 
						VALUES ('$rightdate', '$name', '$email', '$note', '$anznights', '$dat', '$room', '$persons', '$specialoffer', '', '$customFields' )"  ) ); 

						$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Reservation added!' , 'easyReservations' ).'</p></div>';
						?><meta http-equiv="refresh" content="1; url=admin.php?page=reservations&typ=pending"><?php
					} else {
						$prompt='<div style="width: 97%; padding: 5px; margin: -11px 0 5px 0;" class="updated below-h2"><p>'.__( 'Please fill out all Fields' , 'easyReservations' ).'</p></div>';
					}
				}
				$room_category = get_option("reservations_room_category");
				$special_offer_cat = get_option("reservations_special_offer_cat");
			?>
			<script>
				$(document).ready(function() {
					$("#datepicker").datepicker({ altFormat: 'dd.mm.yyyy', beforeShow: function(){ setTimeout(function(){ $(".ui-datepicker").css("z-index", 99); }, 10); }});
					$("#datepicker2").datepicker({ altFormat: 'dd.mm.yyyy', beforeShow: function(){ setTimeout(function(){ $(".ui-datepicker").css("z-index", 99); }, 10); }});
				});
				$(function() {
					$( "#slider-range-min" ).slider({
						range: "min",
						value: 2,
						min: 1,
						max: 100,
						slide: function( event, ui ) {
							$( "#persons" ).val( ui.value );
						}
					});
					$( "#persons" ).val( $( "#slider-range-min" ).slider( "value" ) );
				});
				$(function() {
					$( "#room" ).buttonset();
					$( "#specialoffer" ).buttonset();
				});
			</script>
<script type="text/javascript">
var Add = 0;
function addtoForm(){ // Add field to the Form
	Add += 1;
	document.getElementById("testit").innerHTML += '<tr><td><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"> '+document.getElementById("customtitle").value+' <img style="vertical-align:middle;" onclick="delfromForm('+Add+',\''+document.getElementById("customtitle").value+'\',\''+document.getElementById("customvalue").value+'\')" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/delete.png"></td><td>'+document.getElementById("customvalue").value+'<input type="hidden" name="customtitle'+Add+'" value="'+document.getElementById("customtitle").value+'"><input type="hidden" name="customvalue'+Add+'" value="'+document.getElementById("customvalue").value+'"></td></tr>';
}
function delfromForm(add,x,y){
	var vormals = document.getElementById("testit").innerHTML;
	var string = '<tr><td><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"> '+x+' <img style="vertical-align:middle;" onclick="delfromForm('+add+',\''+x+'\',\''+y+'\')" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/delete.png"></td><td>'+y+'<input name="customtitle'+add+'" value="'+x+'" type="hidden"><input name="customvalue'+add+'" value="'+y+'" type="hidden"></td></tr>';
	var jetzt = vormals.replace(string, "");
	document.getElementById("testit").innerHTML = jetzt;
}
</script>
<link href="<?php echo  WP_PLUGIN_URL . '/easyreservations/css/jquery-ui.css'; ?>" rel="stylesheet" type="text/css"/>
<script src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/jquery.min.js"></script>
<script src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/js/jquery-ui.min.js"></script>
<div id="icon-options-general" class="icon32"><br></div><h2 style="font-family: Arial,sans-serif; font-weight: normal; font-size: 23px;"><?php printf ( __( 'Add Reservation' , 'easyReservations' ));?></h2>
<?php if(isset($prompt)) echo $prompt; ?>
<div id="wrap">
	<table style="width:99%">
		<tr>
			<td style="width:300px">
				<form method="post" action=""  id="addreservation" name="addreservation"><input type="hidden" name="action" value="addreservation">						
					<table class="widefat" style="width:300px;">
						<tbody>
							<tr valign="top" class="alternate">
								<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?></td>
								<td><input type="text" name="name" align="middle" class="regular-text" value="<?php if(isset($_POST["action"])) echo $name; ?>"></td>
							</tr>
							<tr valign="top">
								<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/day.png" > <?php printf ( __( 'Date' , 'easyReservations' ));?></td>
								<td style="vertical-align:middle;"><input type="text" id="datepicker" name="date" style="width:70px" class="regular-text" value="<?php if(isset($_POST["action"])) echo $date; ?>"> - <input type="text" id="datepicker2" name="nights" style="width:70px" class="regular-text" value="<?php if(isset($_POST["action"])) echo $nights; ?>"></td>
							</tr>
							<tr valign="top" style="vertical-align:middle" class="alternate">
								<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/email.png"> <?php printf ( __( 'eMail' , 'easyReservations' ));?></td>
								<td><input type="text" name="email"class="regular-text" value="<?php if(isset($_POST["action"])) echo $email; ?>"></td>
							</tr>
							<tr valign="top">
								<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?></td>
								<td><input type="text" id="persons" name="persons" style="width:30px;border:0;color: #004276; font-weight:bold;background: #F9F9F9" value="<?php if(isset($_POST["action"])) echo $persons; ?>"/><div id="slider-range-min" style="width: 260px; float:right; margin: 1px 12px 3px 0;"></div></td>
							</tr>
							<tr valign="top" class="alternate">
								<?php  
								$termin=reservations_get_room_ids();
								if(count($termin) <= 4){
									if($termin != ""){
										echo '<td nowrap colspan="2" style="text-align:center"><div id="room">';
										$nums=0;
										foreach ($termin as $nmbr => $inhalt){
											echo '<input type="radio" id="room'.($nums+1).'" name="room" value="'.$termin[$nums][0].'"/><label for="room'.($nums+1).'">'.$termin[$nums][1].' </label>';
											$nums++;
										} 
									} else {
										echo __( 'add Post to Room Category to add a Room' , 'easyReservations' ).'<br>';
									} ?>
								</div>
								<?php } else { ?>
								<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/room.png"> <?php printf ( __( 'Room' , 'easyReservations' ));?></td>
								<td><select id="room" name="room"><?php echo reservations_get_room_options(); ?></select>
								<?php } ?>
								</td>
							</tr>
							<tr valign="top">
								<?php  
								$termin=reservations_get_offer_ids();
								if(count($termin) <= 4){
									if($termin != ""){
										echo '<td nowrap colspan="2" style="text-align:center"><div id="specialoffer">';
										$nums=1;
										echo '<input type="radio" id="specialoffer'.($nums).'" name="specialoffer" value="0" checked="checked"/><label for="specialoffer'.($nums).'">'.__( 'None' , 'easyReservations' ).' </label>';
										foreach ($termin as $nmbr => $inhalt){
											echo '<input type="radio" id="specialoffer'.($nums+1).'" name="specialoffer" value="'.$termin[$nums-1][0].'"/><label for="specialoffer'.($nums+1).'">'.$termin[$nums-1][1].' </label>';
											$nums++;
										} 
									} else {
										echo __( 'add Post to Offer Category to add an Offer' , 'easyReservations' ).'<br>';
									} ?>
								</div>
								<?php } else { ?>
								<td nowrap><img style="vertical-align:text-bottom;"  src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/special.png" > <?php printf ( __( 'Special Offer' , 'easyReservations' ));?></td>
								<td><select name="specialoffer"><option value="0" select="selected"><?php printf ( __( 'None' , 'easyReservations' ));?></option><?php echo reservations_get_offer_options(); ?></select>
								<?php } ?></td>
							</tr>
							<tr valign="top" class="alternate">
								<td nowrap><img style="vertical-align:text-bottom;"  src="<?php echo RESERVATIONS_IMAGES_DIR; ?>/message.png"> <?php printf ( __( 'Notes' , 'easyReservations' ));?></td>
								<td><textarea name="note" cols="42" rows="10"><?php if(isset($_POST["action"])) echo $note; ?></textarea></td>
							</tr>
						</tbody>
						<tbody id="testit">
						</tbody>
					</table>
					<br>
					<a href="javascript:{}" onclick="document.getElementById('addreservation').submit(); return false;" class="button-primary"><span><?php printf ( __( 'Add Reservation' , 'easyReservations' ));?></span></a>
				</form>
			</td>
			<td></td>
			<td valign="top" style="text-align:left">
				<table class="widefat" style="width:200px;">
					<thead>
						<tr>
							<th><?php printf ( __( 'Add custom Field' , 'easyReservations' ));?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td nowrap><input type="text" name="customtitle" id="customtitle" style="width:260px" value="Title" onfocus="if (this.value == 'Title') this.value = '';" onblur="if (this.value == '') this.value = 'Title';"><br><textarea type="text" name="customvalue" id="customvalue" value="Value" style="width:260px;margin-top:2px;" onfocus="if (this.value == 'Value') this.value = '';" onblur="if (this.value == '') this.value = 'Value';">Value</textarea>
							<br><p style="margin-top:8px;"> <a href="javascript:{}" onclick="addtoForm();" class="button-secondary"><span><?php printf ( __( 'Add custom Field' , 'easyReservations' ));?></span></a></p></td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</table>			
<?php } ?>
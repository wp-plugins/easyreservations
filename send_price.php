<?php
require('../../../../wp-blog-header.php');
global $wpdb;

$from =  $_POST['from'];
$to =  $_POST['to'];
$email = $_POST['email'];
$room = $_POST['room'];
$offer = $_POST['offer'];
$persons = $_POST['persons'];

if(isset($_POST['customp'])){
	$customp = str_replace("%", "&", $_POST['customp']);
} else $customp = '';

if(isset($_POST['childs'])){
	$childs = $_POST['childs'];
} else $childs = 0;

if($email == "") $email = "test@test.de";
if($persons == "") $persons = 1;

$daysBetween = (strtotime($to)-strtotime($from))/86400;
$Array = array( 'arrivalDate' => $from, 'nights' => $daysBetween, 'reservated' => date("d.m.Y", time()), 'room' => $room, 'special' => $offer, 'number' => $persons, 'childs' => $childs, 'email' => $email, 'price' => '', 'customp' => $customp );
$obj = (object) $Array;
$resArray = array($obj);
$thePrice = easyreservations_price_calculation('', $resArray);
echo reservations_format_money($thePrice['price']);
?>
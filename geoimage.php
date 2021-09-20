#!/usr/bin/php
<?php
//Nicholas Ferreira - https://github.com/Nickguitar

//api key from LocationIQ
//you can get one for free at https://locationiq.com/register
//using a temporary email

$api = "YOUR_API_KEY_HERE";

if(!isset($argv[1])) die("Usage: ./geoimage.php image.jpg");

//string fraction to float
function frac($frac){
	$frac = explode("/",trim($frac));
	return $frac[0]/$frac[1];
}

//(degree, min, sec) to decimal degree
function deg2dec($deg,$min,$sec){
	return frac($deg)+(frac($min)/60)+(frac($sec)/3600);
}

//returns latitude and longitude from image
function gps($img){
	global $argv;
	// hemisphere
	if(!isset(exif_read_data($img)["GPSLatitudeRef"]) || !isset(exif_read_data($img)["GPSLongitudeRef"])) die("Error: No GPS metadata");
	$lat_ref = exif_read_data($argv[1])["GPSLatitudeRef"] == "W" ? 1 : -1;
	$lon_ref = exif_read_data($argv[1])["GPSLongitudeRef"] == "S" ? 1 : -1;

	$lat = exif_read_data($argv[1])["GPSLatitude"];
	$lon = exif_read_data($argv[1])["GPSLongitude"];

	$coords["lat"] = $lat_ref * deg2dec($lat[0],$lat[1],$lat[2]);
	$coords["lon"] = $lon_ref * deg2dec($lon[0],$lon[1],$lon[2]);

	return $coords;
}

//returns location from latitude and longitude
function location($gps){
	global $api;
	$lat = $gps['lat'];
	$lon = $gps['lon'];
	$endpoint = "https://us1.locationiq.com/v1/reverse.php?key=$api&lat=$lat&lon=$lon&format=json";
	echo "https://www.google.com/maps/search/?q=$lat,$lon\n";
	return file_get_contents($endpoint);
}

//yes shawn
function go($image){
	return json_decode(location(gps($image)),true)["address"];
}

print_r(go($argv[1]));


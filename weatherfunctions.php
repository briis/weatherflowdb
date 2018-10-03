<?php
// WeatherFlowDB is a Program to capture data using UDP from a WeatherFlow Weather Station
// All data is stored in a MYSQL database and can be retrieved from there

// Author: Bjarne Riis
// Last Updated: 28 jul 2018
// Filename: weatherfunctions.php
// - This file contains different Weather related functions used throughtout the program

// Convert EPOCH time format to the Format we use in this program
function convertEpoch($epochtime, $TZ){
  $dt =  new DateTime("@$epochtime");
  $dt->setTimeZone(new DateTimeZone($TZ));
  return date_format($dt,"Y-m-d H:i:s");
}
// Convert EPOCH time format to the Date Format we use in this program
function convertEpochDate($epochtime, $TZ){
  $dt =  new DateTime("@$epochtime");
  $dt->setTimeZone(new DateTimeZone($TZ));
  return date_format($dt,"Y-m-d");
}

//Convert Seconds to time - Used primarely for Uptime calculations
function secondsToTime($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes');
}

// Function for basic field validation (present and neither empty nor only white space
function IsNullOrEmptyString($str){
    return (!isset($str) || trim($str) === '');
}

//Return the Wind Direction String from bearing value
function getWindDirString($bearing){
  if (IsNullOrEmptyString($bearing)) {$bearing = 0;}
  $wDirs = array("N","NNE","NE","ENE","E","ESE","SE","SSE","S","SSW","SW","WSW","W","WNW","NW","NNW","N");
  return $wDirs[round($bearing/22.5)];
}

//Returns the Beaufart Scale value from Wind Speed
function getBeaufortValue($wSpeed){
  if ($wSpeed <= 0.3) {
    $beaufortvalue = 0;
  } elseif ($wSpeed <= 1.5) {
    $beaufortvalue = 1;
  } elseif ($wSpeed <= 3.3) {
    $beaufortvalue = 2;
  } elseif ($wSpeed <= 5.5) {
    $beaufortvalue = 3;
  } elseif ($wSpeed <= 7.9) {
    $beaufortvalue = 4;
  } elseif ($wSpeed <= 10.7) {
    $beaufortvalue = 5;
  } elseif ($wSpeed <= 13.8) {
    $beaufortvalue = 6;
  } elseif ($wSpeed <= 17.1) {
    $beaufortvalue = 7;
  } elseif ($wSpeed <= 20.7) {
    $beaufortvalue = 8;
  } elseif ($wSpeed <= 24.4) {
    $beaufortvalue = 9;
  } elseif ($wSpeed <= 28.4) {
    $beaufortvalue = 10;
  } elseif ($wSpeed <= 32.6) {
    $beaufortvalue = 11;
  } else {
    $beaufortvalue = 12;
  }
  return $beaufortvalue;
}

//Returns Dewpoint based on Pressure and Temperature
function getDewpoint($temp, $hum) {
  return ($temp - ((100 - $hum)/5));
}

//Returns m/s based on km/h
function getMS($speed) {
  return $speed *0.27777777777778;
}

//Returns Barometric Pressure
//- Input is:
//  Height of Station above sealevel in meters
//  Pressure in mb or hPa
function getBarometricPressure($height, $pressure) {
  //Convert hPA/mb to inHG
  $Pa = 0.0295300 * $pressure;
  return  ($Pa * pow(((288 - 0.0065 * -$height)/288),5.2561)) * 33.8639;
}

//Returns Heat Index based on humidity and tempeterature
//Note: Heat Index only used when Temperature above 26.67C
function getHeatIndex($temp, $hum) {
  return -8.784695 + 1.61139411 * $temp + 2.33854900 * $hum + -0.14611605 * $temp*$hum + -0.01230809 * pow($temp, 2) + -0.01642482 * pow($hum, 2) + 0.00221173 * pow($temp, 2) * $hum + 0.00072546 * $temp * pow($hum, 2) + -0.00000358 * pow($temp, 2) * pow($hum, 2);
}

//Returns CloudBase in Km
//- Input is:
//  Temperature in C
//  Humidity in %
function getCloudBase($temp, $hum) {
  $dew = getDewpoint($temp, $hum);
  $dewpointF = (9/5 * $dew)+32; //Convert C to F
  $tempF = (9/5 * $temp)+32; //Convert C to F
  $cloudbaseFT = ($tempF - $dewpointF) / 4.4 * 1000; //Cloudbase in Feet

  return $cloudbaseFT * 0.3048; //Convert cloudbase to meter
}

//Returns WindChill as Temperature C
//- Input is:
//  Temperature in C
//  Latest Wind Speed in m/s
function getWindChill($tempC, $windMs){
  // $tempF = (9/5 * $tempC)+32; //Convert C to F
  // $windMph = ($windMs / 0.44704); //Convert m/s to Mph
  // $windChillF = 35.74 + (0.6215 * $tempF) - (35.75 * pow($windMph,0.16)) + (0.4275 * $tempF * pow($windMph,0.16));
//  return ($windChillF - 32) * 5/9; //Convert Wind Chill in F to C

  if ($windMs < 1.3) {
    return $tempC;
  } else {
    $windKMH = $windMs / 0.27777777777778; // Convert m/s to km/h
    return ROUND(13.12 + (0.6215 * $tempC) - (11.37 * ($windKMH ** 0.16)) + (0.3965 * $tempC * ($windKMH ** 0.16)),2);
  }
}

//Returns Humidity Index as Temperature C
//- Input is:
//  Temperature in C
//  Humidity in %
function getHumIndex($tempC, $hum){
  $tempF = (9/5 * $tempC)+32; //Convert C to F
  $humIndexF = $tempF - (0.55 - (0.55 * $hum/100)) * ($tempF - 58);

  return ($humIndexF - 32) * 5/9; //Convert Hum Index in F to C
}

//Returns the Feels Like temperature
function getFeelsLike($temp, $wchill, $heatindex) {
  if ($temp < 10) {
    $flike = $wchill;
  } elseif ($temp > 26.67) {
    $flike = $heatindex;
  } else {
    $flike = $temp;
  }

  return $flike;
}

//Returns Apparent Temperature C
//- Input is:
//  Temperature in C
//  Average Wind Speed in m/s
//  Humidity
function getApparentTemp($tempC, $windMs, $hum){
  $vapour = $hum / 100*6.105*exp(17.27*$tempC/(237.7+$tempC));

  return $tempC + 0.33*$vapour - 0.7*$windMs - 4;
}

//Returns a text describing the sensor status supplied as parameter 1
function getSensorStatus($sensorstatus) {
  $status['0']    = "Sensors OK";
  $status['1']    = "lightning failed";
  $status['2']    = "lightning noise";
  $status['4']    = "lightning disturber";
  $status['8']    = "pressure failed";
  $status['10']   = "temperature failed";
  $status['20']   = "rh failed";
  $status['40']   = "wind failed";
  $status['80']   = "precip failed";
  $status['100']  = "light/uv failed";

  return $status[$sensorstatus];
}

//Returns Sunrise Time
function getSunrise($dt,$lat,$lon,$gmt) {
  $thisDate = new DateTime($dt);
  $sunrise_time = date_sunrise($thisDate->format('U'),SUNFUNCS_RET_STRING,$lat,$lon,90.583333,$gmt);

  return date_format($thisDate, "Y-m-d")." ".$sunrise_time;
}

//Returns Sunset Time
function getSunset($dt,$lat,$lon,$gmt) {
  $thisDate = new DateTime($dt);
  $sunset_time = date_sunset($thisDate->format('U'),SUNFUNCS_RET_STRING,$lat,$lon,90.583333,$gmt);

  return date_format($thisDate, "Y-m-d")." ".$sunset_time;
}
?>

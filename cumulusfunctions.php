<?php
// WeatherFlowDB is a Program to capture data using UDP from a WeatherFlow Weather Station
// All data is stored in a MYSQL database and can be retrieved from there
// Author: Bjarne Riis
// Last Updated: 28 jul 2018
// Filename: cumulusfunctions.php
// This Script contains functions that makes the transfer or Co-Existance from/with the Cumulus Software easier.

function createRealtimeFile($realtimeFile) {

  $rd = json_decode(getRealtimeData());

  //Build the output
  $datestring = date_format(date_create($rd->datestamp),'d-m-y');
  $timestring = date_format(date_create($rd->datestamp),'H:i:s');

  $rtString = $datestring." ".$timestring." ".$rd->temp." ".$rd->hum." ".$rd->dew." ".$rd->wspeed." ".$rd->wlatest." ".$rd->bearing." ".$rd->rrate." ";
  $rtString .= $rd->rfall." ".$rd->press." ".$rd->currentwdir." ".$rd->beaufortnumber." ".$rd->windunit." ".$rd->tempunit." ".$rd->pressunit." ".$rd->rainunit." ";
  $rtString .= $rd->windrun." ".$rd->presstrendval." ".$rd->rmonth." ".$rd->ryear." ".$rd->rfallY." ".$rd->intemp." ".$rd->inhum." ".$rd->wchill." ";
  $rtString .= $rd->temptrend." ".$rd->tempTH." ".$rd->TtempTH." ".$rd->tempTL." ".$rd->TtempTL." ".$rd->windTM." ".$rd->TwindTM." ".$rd->wgustTM." ".$rd->TwgustTM." ";
  $rtString .= $rd->pressTH." ".$rd->TpressTH." ".$rd->pressTL." ".$rd->TpressTL." ".$rd->version." ".$rd->build." ".$rd->wgust." ".$rd->heatindex." ".$rd->humidex." ";
  $rtString .= $rd->uv." ".$rd->ET." ".$rd->SolarRad." ".$rd->avgbearing." ".$rd->rhour." ".$rd->forecastnumber." ".$rd->isdaylight." ".$rd->SensorContactLost." ";
  $rtString .= $rd->wdir." ".$rd->cloudbasevalue." ".$rd->cloudbaseunit." ".$rd->apptemp." ".$rd->SunshineHours." ".$rd->CurrentSolarMax." ".$rd->IsSunny;
  //Save file to designated location
  $fo=fopen($realtimeFile,"w");
  $fr=fwrite($fo,$rtString);

  return $fr;

}

// Retrieve data from realtime table and return JSON string
function getRealtimeData() {
  include('settings.php');

  // Open Connetion to Database
  $conn = mysqli_connect($dbserver,$dbuser,$dbpass,$dbschema);
  if (mysqli_connect_errno()) {
    error_log("Database connection failed: " . mysqli_connect_error());
    // Without being able to make a connection to the Database we cannot continue, so terminate program
    die("Database connection failed: " . mysqli_connect_error());
  }

  $result = mysqli_query($conn, "SELECT * FROM weatherflowdb.realtime WHERE idx=1")
     or die ("Could not get data from realtime table: ".mysqli_error($conn));

   //fetch all data from json table in associative array format and store in $result variable
   $array=$result->fetch_assoc();

   // Close Database connection
   mysqli_close($conn);

   //Now encode PHP array in JSON string and return
   return json_encode($array,true);
}
?>

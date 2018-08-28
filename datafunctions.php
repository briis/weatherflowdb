<?php
// WeatherFlowDB is a Program to capture data using UDP from a WeatherFlow Weather Station
// All data is stored in a MYSQL database and can be retrieved from there

// Author: Bjarne Riis
// Last Updated: 28 jul 2018
// Filename: weatherfunctions.php
// - This file contains different functions used throughout the program

function updateDeviceID($stationID) {
  //Function to retrieve Device ID's and Serial Numbers from WeatherFLOW REST API
  //and store them in the settings file

  try {
    $URL = "https://swd.weatherflow.com/swd/rest/stations/".$stationID."?api_key=20c70eae-e62f-4d3b-b3a4-8586e90f3ac8";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $URL);
    $Response = curl_exec($ch);
    curl_close($ch);

    if (strlen($Response) != 0) {
      $rawdata = json_decode($Response);
      $deviceArray = $rawdata->stations[0]->devices;
      $writeString = "\n//Device ID's and Serial Numbers\n";

      foreach($deviceArray as $i => $row) {
        $device_id = $row->device_id;
        $serial_number = $row->serial_number;
        $device_type= $row->device_type;
        $firmware_revision = $row->firmware_revision;

        $writeString .= "$".$device_type."_Serial_Number = ".$serial_number.";\n";
        $writeString .= "$".$device_type."_DeviceID = ".$device_id.";\n \n";
      }
      $writeString .= "?> \n";

      //Write values to end of the settings.php file
      $fh = fopen('settings.php', 'r+');
      fseek($fh,0, SEEK_END);
      fseek($fh,-4,SEEK_CUR);
      fwrite($fh, $writeString);
      fclose($fh);

    } else {
      error_log("No data received from REST API Interface");
    }

  } catch (\Exception $e) {
    error_log($e->getMessage());
  }

}


?>

<?php
// WeatherFlowDB is a Program to capture data using UDP from a WeatherFlow Weather Station
// All data is stored in a MYSQL database and can be retrieved from there
// Make sure you have created the database before executing this script by running the following command:
// mysql -u root -p > createdatabase.sql
// Author: Bjarne Riis
// Last Updated: 24 jul 2018
// Filename: load_history_data.php
// - This part of the program should only run once, and gets all historic data from WeatherFlow
//   and stores them in the database.

// Include files
include('history_settings.php');
include('../settings.php');
include('../weatherfunctions.php');

// Setup Error Logging
$errorFile = $logdirectory.'weatherflowdb-loadhistory-errors.log';
error_reporting(E_ALL);
ini_set('log_errors','1');
ini_set('display_errors','0');
ini_set('error_log', $errorFile);

// Create empty error log file
if (file_exists($errorFile)) {unlink($errorFile);}

// Open Connetion to Database
$conn = mysqli_connect($dbserver,$dbuser,$dbpass,$dbschema);
if (mysqli_connect_errno()) {
  error_log("Database connection failed: " . mysqli_connect_error());
  // Without being able to make a connection to the Database we cannot continue, so terminate program
  die("Database connection failed: " . mysqli_connect_error());
}

// We have a socket and connection to the Database, so run the Main Loop to catch and store data
try {
  //Disable Triggers on relevante Tables
  if ($conn->query("SET @TRIGGER_ENABLE = FALSE;") != TRUE) {
    error_log('Could not disable Trigger: '.$conn->error);
  }

  $WindSpeed = 0;
  $temperature = 0;
  $humidity = 0;
  $wind_avg = 0;
  $WindDirection = 0;

  echo "Archieving AIR Data... \n";
  //Loop Throught all AIR Records
  $DataFound = True;
  $DayOffset = 0;
  $LogDate = new DateTime();
  $LogDate->setTimeZone(new DateTimeZone($TZ));
  $LogDate->sub(new DateInterval('P'.$DayOffset.'D'));

  while($DataFound) {
    $URL = "https://swd.weatherflow.com/swd/rest/observations/device/".$airdevice."?day_offset=".$DayOffset."&api_key=20c70eae-e62f-4d3b-b3a4-8586e90f3ac8";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $URL);
    $Response = curl_exec($ch);
    curl_close($ch);

    echo "Processing ".$LogDate->format('d-m-Y')."... \n";

    //If we got data, then start processing it
    if (strlen($Response) != 0) {
      $rawdata = json_decode($Response);
      if (!isset($rawdata->obs)) {
        //No More data to process
        $DataFound = False;
        break;
      }

      $obdataarray    = $rawdata->obs;

      foreach($obdataarray as $i => $row) {
        $datestamp      = convertEpoch($row[0], $TZ);
        $temperature    = $row[2];
        $dewpoint       = getDewpoint($row[2],$row[3]);
        $pressure       = $row[1];
        $humidity       = $row[3];
        $barometer      = getBarometricPressure($HeightAboveSea, $row[1]);
        $heatindex      = getHeatIndex($row[2],$row[3]);
        $humindex       = getHumIndex($temperature, $humidity);
        $windchill      = 0; // Will be updated later in the flow getWindChill($temperature, $WindSpeed);
        $feelslike      = 0; // Will be updated later in the flow getFeelsLike($temperature, $windchill, $heatindex);
        $apptemp        = 0; // Will be updated later in the flow getApparentTemp($temperature, $wind_avg, $humidity);
        $cloudbase      = getCloudBase($row[2],$row[3]);
        if (!isset($row[6])) {$batteryair = 0;} else {$batteryair = $row[6];}

        $SQL = "INSERT IGNORE INTO weatherflowdb.air_observations (datestamp, serial_number, hub_sn, temperature, dewpoint, pressure, barometer, humidity, heatindex, humindex, windchill, feelslike, apptemp, cloudbase, battery) VALUES ";
        $SQL .= "('".$datestamp."','".$airserial_number."','".$hub_sn."',".$temperature.",".$dewpoint.",".$pressure.",".$barometer.",".$humidity.",".$heatindex.",".$humindex.",".$windchill.",".$feelslike.",".$apptemp.",".$cloudbase.",".$batteryair.");";

        //Insert the Record in to the Database
        if ($conn->query($SQL) != TRUE) {
          error_log('Database Insert failed on Air Obs Table: '.$conn->error."\n ".$SQL);
        }
      }
    }
    $DayOffset++;
    $LogDate = new DateTime();
    $LogDate->setTimeZone(new DateTimeZone($TZ));
    $LogDate->sub(new DateInterval('P'.$DayOffset.'D'));
  }
  echo "AIR Data Load completed \n";

//********** SKY DATA ****************

  echo "\n \nArchieving SKY Data... \n";

    //Loop Throught all SKY Records
    $DataFound = True;
    $DayOffset = 0;
    $LogDate = new DateTime();
    $LogDate->setTimeZone(new DateTimeZone($TZ));
    $LogDate->sub(new DateInterval('P'.$DayOffset.'D'));

    while($DataFound) {
      $URL = "https://swd.weatherflow.com/swd/rest/observations/device/".$skydevice."?day_offset=".$DayOffset."&api_key=20c70eae-e62f-4d3b-b3a4-8586e90f3ac8";

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_URL, $URL);
      $Response = curl_exec($ch);
      curl_close($ch);

      echo "Processing ".$LogDate->format('d-m-Y')."... \n";

      //If we got data, then start processing it
      if (strlen($Response) != 0) {
        $rawdata = json_decode($Response);
        if (!isset($rawdata->obs)) {
          //No More data to process
          $DataFound = False;
          break;
        }

        $obdataarray    = $rawdata->obs;

        foreach($obdataarray as $i => $row) {
          $datestamp      = convertEpoch($row[0], $TZ);
          $illuminance    = $row[1];
          $uv             = $row[2];
          $rain_minute    = $row[3];
          $rain_rate      = $rain_minute * 60;
          $wind_lull      = $row[4];
          $wind_avg       = $row[5];
          $wind_gust      = $row[6];
          if (!isset($row[7])) {$wind_dir_avg = 0;} else {$wind_dir_avg = $row[7];};
          $wind_dir_sym   = getWindDirString($wind_dir_avg);
          $batterysky     = $row[8];
          $solarrad       = $row[10];
          $precip_type    = 0; // Not Available in History Load $row[12];
          $beaufort       = getBeaufortValue($wind_avg);

          $SQL = "INSERT IGNORE INTO weatherflowdb.sky_observations (datestamp, serial_number, hub_sn, illuminance, uv, rain_minute, rain_rate, wind_lull, wind_avg, wind_gust, wind_dir_avg, wind_dir_sym, solarrad, precip_type, beaufort, battery) VALUES ";
          $SQL .= "('".$datestamp."','".$skyserial_number."','".$hub_sn."',".$illuminance.",".$uv.",".$rain_minute.",".$rain_rate.",".$wind_lull.",".$wind_avg.",".$wind_gust.",".$wind_dir_avg.",'".$wind_dir_sym."',";
          $SQL .= $solarrad.",".$precip_type.",".$beaufort.",".$batterysky.");";

          //Insert the Record in to the Database
          if ($conn->query($SQL) != TRUE) {
            error_log('Database Insert failed on Sky Obs Table: '.$conn->error."\n ".$SQL);
          }
        }
      }
      $DayOffset++;
      $LogDate = new DateTime();
      $LogDate->setTimeZone(new DateTimeZone($TZ));
      $LogDate->sub(new DateInterval('P'.$DayOffset.'D'));
      //if ($DayOffset > 5) {$DataFound=False;}
    }
    echo "SKY Data Load completed \n";

  //********** SKY DATA ****************

    echo "\n \nUpdating Values... \n";
    $SQL = "CALL updateHistoryData();";

    //Update WindChill, Apparent Tempteratur and Feel Like
    if ($conn->query($SQL) != TRUE) {
      error_log('Excution of updateHistoryData failed: '.$conn->error);
    }
    echo "Value Update completed \n \n *** Process Completed *** \n";

    // Close Database connection
    mysqli_close($conn);

} catch (\Exception $e) {
  error_log($e->getMessage());
}

?>

<?php
// WeatherFlowDB is a Program to capture data using UDP from a WeatherFlow Weather Station
// All data is stored in a MYSQL database and can be retrieved from there
// Author: Bjarne Riis
// Last Updated: 19 Sep 2018
// Filename: datalogger.php
// - This part of the program runs as an infinitive loop and listens for data from the Weather Station
//   and stores them in the database.

// Include files
include('settings.php');
include('weatherfunctions.php');
include('cumulusfunctions.php');

$program_version = "1.0";
$program_build = "013";

// Setup Error Logging
$errorFile = $logdirectory.'weatherflowdb-datalogger-errors.log';
error_reporting(E_ALL);
ini_set('log_errors','1');
ini_set('display_errors','0');
ini_set('error_log', $errorFile);

// ************ Start Main Program ************

// Create empty error log file
if (file_exists($errorFile)) {unlink($errorFile);}

//Create a UDP socket
if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    error_log("Couldn't create socket: [$errorcode] $errormsg");
    // Without being able to create a socket it makes no sense to continue
    die("Couldn't create socket: [$errorcode] $errormsg \n");
}

// Bind the source address
if( !socket_bind($sock, $socketIP , $socketPort) )
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    error_log("Could not bind socket : [$errorcode] $errormsg");
    //If we cannot bind to the Weather Station, stop execution
    die("Could not bind socket : [$errorcode] $errormsg \n");
}

// Open Connetion to Database
$conn = mysqli_connect($dbserver,$dbuser,$dbpass,$dbschema);
if (mysqli_connect_errno()) {
  error_log("Database connection failed: " . mysqli_connect_error());
  // Without being able to make a connection to the Database we cannot continue, so terminate program
  die("Database connection failed: " . mysqli_connect_error());
}

// We have a socket and connection to the Database, so run the Main Loop to catch and store data
try {
    $WindSpeed = 0;
    $temperature = 0;
    $humidity = 0;
    $wind_avg = 0;
    $WindDirection = 0;

    //Update the Program Version on Startup
    $SQL = "CALL updateversionProcess('".$program_version."','".$program_build."');";
    if ($conn->query($SQL) != TRUE) {
      error_log('Update of version information failed: '.$conn->error);
    }

    while(1) {
      // Receive data from UDP
      $r = socket_recvfrom($sock, $buf, 512, 0, $remote_ip, $remote_port);
      //If we got data, then start processing it
      if (strlen($buf) != 0) {
        $rawdata = json_decode($buf);
        //Check which device is sending data
        $type = $rawdata->type;

        //Do different things based device type
        switch ($type) {
          case 'rapid_wind':
            $serial_number       = $rawdata->serial_number;
            $hub_sn              = $rawdata->hub_sn;

            $obdataarray         = $rawdata->ob;
            $datestamp           = convertEpoch($obdataarray[0], $TZ);
            $WindSpeedRapid      = $obdataarray[1];
            if ($WindSpeedRapid > 0) {$WindDirection = $obdataarray[2];}
            $wDirSymbol          = getWindDirString($WindDirection);

            $SQL = "INSERT INTO weatherflowdb.rapid_wind (datestamp, serial_number, hub_sn, wind_speed, wind_direction, wind_direction_symbol) VALUES ";
            $SQL = $SQL."('".$datestamp."','".$serial_number."','".$hub_sn."',".$WindSpeedRapid.",".$WindDirection.",'".$wDirSymbol."');";

            //Insert the Record in to the Database
            if ($conn->query($SQL) != TRUE) {
              error_log('Database Insert failed on Rapid Wind Table: '.$conn->error."\n ".$SQL);
            }
            break;
          case 'obs_air':
            $serial_number      = $rawdata->serial_number;
            $hub_sn             = $rawdata->hub_sn;
            $firmware_revision  = $rawdata->firmware_revision;

            $obdataarray    = $rawdata->obs;
            $datestamp      = convertEpoch($obdataarray[0][0], $TZ);
            $temperature    = $obdataarray[0][2];
            $dewpoint       = getDewpoint($obdataarray[0][2],$obdataarray[0][3]);
            $pressure       = $obdataarray[0][1];
            $humidity       = $obdataarray[0][3];
            $barometer      = getBarometricPressure($HeightAboveSea, $obdataarray[0][1]);
            $heatindex      = getHeatIndex($obdataarray[0][2],$obdataarray[0][3]);
            $humindex       = getHumIndex($temperature, $humidity);
            $windchill      = getWindChill($temperature, $WindSpeed);
            $feelslike      = getFeelsLike($temperature, $windchill, $heatindex);
            $apptemp        = getApparentTemp($temperature, $wind_avg, $humidity);
            $cloudbase      = getCloudBase($obdataarray[0][2],$obdataarray[0][3]);
            $batteryair     = $obdataarray[0][6];

            $SQL = "INSERT INTO weatherflowdb.air_observations (datestamp, serial_number, hub_sn, temperature, dewpoint, pressure, barometer, humidity, heatindex, humindex, windchill, feelslike, apptemp, cloudbase, battery, firmware) VALUES ";
            $SQL .= "('".$datestamp."','".$serial_number."','".$hub_sn."',".$temperature.",".$dewpoint.",".$pressure.",".$barometer.",".$humidity.",".$heatindex.",".$humindex.",".$windchill.",".$feelslike.",".$apptemp;
            $SQL .= ",".$cloudbase.",".$batteryair.",".$firmware_revision.");";

            //Insert the Record in to the Database
            if ($conn->query($SQL) != TRUE) {
              error_log('Database Insert failed on Air Obs Table: '.$conn->error."\n ".$SQL);
            }
            break;
          case 'obs_sky':
            $serial_number      = $rawdata->serial_number;
            $hub_sn             = $rawdata->hub_sn;
            $firmware_revision  = $rawdata->firmware_revision;

            $obdataarray    = $rawdata->obs;
            $datestamp      = convertEpoch($obdataarray[0][0], $TZ);
            $illuminance    = $obdataarray[0][1];
            $uv             = $obdataarray[0][2];
            $rain_minute    = $obdataarray[0][3];
            $rain_rate      = $rain_minute * 60/$obdataarray[0][9];
            $wind_lull      = $obdataarray[0][4];
            $wind_avg       = $obdataarray[0][5];
            $wind_gust      = $obdataarray[0][6];
            if (!isset($obdataarray[0][7])) {$wind_dir_avg = $WindDirection;} else {$wind_dir_avg = $obdataarray[0][7];};
            $wind_dir_sym   = getWindDirString($wind_dir_avg);
            $batterysky     = $obdataarray[0][8];
            $solarrad       = $obdataarray[0][10];
            $precip_type    = $obdataarray[0][12];
            $beaufort       = getBeaufortValue($wind_avg);

            $SQL = "INSERT INTO weatherflowdb.sky_observations (datestamp, serial_number, hub_sn, illuminance, uv, rain_minute, rain_rate, wind_lull, wind_avg, wind_gust, wind_dir_avg, wind_dir_sym, solarrad, precip_type, beaufort, battery, firmware) VALUES ";
            $SQL .= "('".$datestamp."','".$serial_number."','".$hub_sn."',".$illuminance.",".$uv.",".$rain_minute.",".$rain_rate.",".$wind_lull.",".$wind_avg.",".$wind_gust.",".$wind_dir_avg.",'".$wind_dir_sym."',";
            $SQL .= $solarrad.",".$precip_type.",".$beaufort.",".$batterysky.",".$firmware_revision.");";

            //Insert the Record in to the Database
            if ($conn->query($SQL) != TRUE) {
              error_log('Database Insert failed on Sky Obs Table: '.$conn->error."\n ".$SQL);
            }
            break;
          case 'hub_status':
            $serial_number      = $rawdata->serial_number;
            $firmware_revision  = $rawdata->firmware_revision;
            $uptime             = $rawdata->uptime;
            $uptimeText         = secondsToTime($uptime);
            $rssi               = $rawdata->rssi;
            $logdate            = convertEpochDate($rawdata->timestamp, $TZ);
            $datestamp          = convertEpoch($rawdata->timestamp, $TZ);

            $SQL = "INSERT INTO weatherflowdb.hub__observations (LogDate, datestamp, serial_number, firmware, uptime, uptimeText, rssi) VALUES ";
            $SQL = $SQL."('".DATE($logdate)."','".$datestamp."','".$serial_number."',".$firmware_revision.",".$uptime.",'".$uptimeText."',".$rssi.") ";
            $SQL = $SQL."ON DUPLICATE KEY UPDATE datestamp = '".$datestamp."', serial_number = '".$serial_number."', firmware = ".$firmware_revision.", uptime = ".$uptime.", uptimeText = '".$uptimeText."', rssi = ".$rssi.";";

            //Insert the Record in to the Database
            if ($conn->query($SQL) != TRUE) {
              error_log('Database Insert failed on HUB Status Table: '.$conn->error."\n ".$SQL);
            }
            break;
          case 'evt_strike':
            $serial_number  = $rawdata->serial_number;
            $hub_sn         = $rawdata->hub_sn;

            $obdataarray = $rawdata->evt;
            $datestamp      = convertEpoch($obdataarray[0], $TZ);
            $distance       = $obdataarray[1];
            $energy         = $obdataarray[2];

            $SQL = "INSERT INTO weatherflowdb.strike_events (datestamp, serial_number, hub_sn, distance, energy) VALUES ";
            $SQL = $SQL."('".$datestamp."','".$serial_number."','".$hub_sn."',".$distance.",".$energy.");";

            //Insert the Record in to the Database
            if ($conn->query($SQL) != TRUE) {
              error_log('Database Insert failed on Strike Events Table: '.$conn->error."\n ".$SQL);
            }
            break;
          case 'device_status':
            $serial_number      = $rawdata->serial_number;
            $hub_sn             = $rawdata->hub_sn;
            $datestamp          = convertEpoch($rawdata->timestamp, $TZ);
            $uptime             = $rawdata->uptime;
            $voltage            = $rawdata->voltage;
            $firmware_revision  = $rawdata->firmware_revision;
            $sensor_status      = $rawdata->sensor_status;
            $statusText         = getSensorStatus($sensor_status);

            $SQL = "INSERT INTO weatherflowdb.device_status (datestamp, serial_number, hub_sn, uptime, voltage, firmware_revision, sensor_status, statustext) VALUES ";
            $SQL = $SQL."('".$datestamp."','".$serial_number."','".$hub_sn."',".$uptime.",".$voltage.",".$firmware_revision.",".$sensor_status.",'".$statusText."');";

            //Insert the Record in to the Database
            if ($conn->query($SQL) != TRUE) {
              error_log('Database Insert failed on Device Status Table: '.$conn->error."\n ".$SQL);
            }
            break;
        }
      }

      //If realtime.txt file needs to be created, do it here
      if ($createRealtimeFile) {
        $realtimeResult = createRealtimeFile($realtimeFilePath);
        if ($realtimeResult < 1) {
          error_log("Could not write realtime.txt file: [$errorcode] $errormsg");
        }
      }

    }

} catch (\Exception $e) {
  error_log($e->getMessage());
}

?>

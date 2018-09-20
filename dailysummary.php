<?php
// WeatherFlowDB is a Program to capture data using UDP from a WeatherFlow Weather Station
// All data is stored in a MYSQL database and can be retrieved from there
// Make sure you have created the database before executing this script by running the following command:
// mysql -u root -p > createdatabase.sql
// Author: Bjarne Riis
// Last Updated: 12 Aug 2018
// Filename: dailysummary.php
// - This part of the program should run minimum every 10 minutes.
//   It collects the daily High and Low values and loads forecasts in to the database

// Include the settings file
include('settings.php');
include('weatherfunctions.php');
include('forecast.php');
include('moonphase.php');
date_default_timezone_set($TZ);

// Setup Error Logging
$errorFile = $logdirectory.'weatherflowdb-daily-errors.log';
error_reporting(E_ALL);
ini_set('log_errors','1');
ini_set('display_errors','1');
ini_set('error_log', $errorFile);

// ************ Start Main Program ************

// Create empty error log file
if (file_exists($errorFile)) {unlink($errorFile);}

// Open Connetion to Database
$conn = mysqli_connect($dbserver,$dbuser,$dbpass,$dbschema);
if (mysqli_connect_errno()) {
  error_log("Database connection failed: " . mysqli_connect_error());
  // Without being able to make a connection to the Database we cannot continue, so terminate program
  die("Database connection failed: " . mysqli_connect_error());
}

try {
    $conn->set_charset("utf8");
    if ($LoadYRForecast) {
      // Load the Daily Forecast from YR.NO
      $rawdata = json_decode(getYRDailyForecast($forecasturl));
      foreach($rawdata as $i => $row) {
        $SQL = "INSERT INTO weatherflowdb.forecastYR_daily (datestamp, period, timefrom, timeto, description, imagename, temperature, precipitation, windSpeed, windDescription, windDirection, avgbearing, pressure, copyrighttext, copyrighturl) VALUES ";
        $SQL .= "('".$row->datestamp."', ".$row->period.", '".$row->timefrom."', '".$row->timeto."', '".$row->description."', '".$row->imagename."', ".$row->temperature.", ".$row->precipitation.",".$row->windSpeed.", '".$row->windDescription."', '".$row->windDirection."'";
        $SQL .= ", ".$row->avgbearing.", ".$row->pressure.", '".$row->copyrighttext."', '".$row->copyrighturl."') ";
        $SQL .= "ON DUPLICATE KEY UPDATE timefrom = '".$row->timefrom."', timeto = '".$row->timeto."', description = '".$row->description."', imagename = '".$row->imagename."', temperature = ".$row->temperature.", precipitation = ".$row->precipitation.", windSpeed = ".$row->windSpeed;
        $SQL .= ", windDescription = '".$row->windDescription."', windDirection = '".$row->windDirection."', avgbearing = ".$row->avgbearing.", pressure = ".$row->pressure.", copyrighttext = '".$row->copyrighttext."', copyrighturl = '".$row->copyrighturl."';";

        //Insert the Record in to the Database
        if ($conn->query($SQL) != TRUE) {
          error_log('Database Insert failed on forecast_daily Table: '.$conn->error."\n ".$SQL);
        }
      }

      // Load the Hourly Forecast from YR.NO
      $rawdata = json_decode(getYRHourlyForecast($forecasturl));
      foreach($rawdata as $i => $row) {
        $SQL = "INSERT INTO weatherflowdb.forecastYR_hourly (datestamp, description, imagename, temperature, precipitation, windSpeed, windDescription, windDirection, avgbearing, pressure, copyrighttext, copyrighturl) VALUES ";
        $SQL .= "('".$row->datestamp."', '".$row->description."', '".$row->imagename."', ".$row->temperature.", ".$row->precipitation.",".$row->windSpeed.", '".$row->windDescription."', '".$row->windDirection."', ".$row->avgbearing.", ".$row->pressure.", '".$row->copyrighttext."', '".$row->copyrighturl."') ";
        $SQL .= "ON DUPLICATE KEY UPDATE description = '".$row->description."', imagename = '".$row->imagename."', temperature = ".$row->temperature.", precipitation = ".$row->precipitation.", windSpeed = ".$row->windSpeed.", windDescription = '".$row->windDescription."'";
        $SQL .= ", windDirection = '".$row->windDirection."', avgbearing = ".$row->avgbearing.", pressure = ".$row->pressure.", copyrighttext = '".$row->copyrighttext."', copyrighturl = '".$row->copyrighturl."';";

        //Insert the Record in to the Database
        if ($conn->query($SQL) != TRUE) {
          error_log('Database Insert failed on forecast_hourly Table: '.$conn->error."\n ".$SQL);
        }
      }
    }

    if ($LoadDSForecast) {
      // Load the Daily Forecast from DarkSky
      $rawdata = json_decode(getDarkSkyDailyForecast($DSAPIKey, $latitude, $longitude, $DSLanguage, $DSUnit, $TZ));
      foreach($rawdata as $i => $row) {
        $SQL = "INSERT INTO weatherflowdb.forecastDS_daily (datestamp, description, imagename, tempMax, tempMin, precipitation, precipProbability, windSpeed, windDirection, avgbearing, pressure, uvindex, copyrighttext, copyrighturl) VALUES ";
        $SQL .= "('".$row->datestamp."', '".$row->description."', '".$row->imagename."', ".$row->tempmax.", ".$row->tempmin.", ".$row->precipitation.", ".$row->precipProbability.", ".$row->windSpeed.", '".$row->windDirection."'";
        $SQL .= ", ".$row->avgbearing.", ".$row->pressure.", ".$row->uvindex.", '".$row->copyrighttext."', '".$row->copyrighturl."') ";
        $SQL .= "ON DUPLICATE KEY UPDATE description = '".$row->description."', imagename = '".$row->imagename."', tempMax = ".$row->tempmax.", tempMin = ".$row->tempmin.", precipitation = ".$row->precipitation.", precipProbability = ".$row->precipProbability.", windSpeed = ".$row->windSpeed;
        $SQL .= ", windDirection = '".$row->windDirection."', avgbearing = ".$row->avgbearing.", pressure = ".$row->pressure.", uvindex = ".$row->uvindex.", copyrighttext = '".$row->copyrighttext."', copyrighturl = '".$row->copyrighturl."';";

        //Insert the Record in to the Database
        if ($conn->query($SQL) != TRUE) {
          error_log('Database Insert failed on forecastDS_daily Table: '.$conn->error."\n ".$SQL);
        }
      }

      // Load the Hourly Forecast from DarkSky
      $rawdata = json_decode(getDarkSkyHourlyForecast($DSAPIKey, $latitude, $longitude, $DSLanguage, $DSUnit, $TZ));
      foreach($rawdata as $i => $row) {
        $SQL = "INSERT INTO weatherflowdb.forecastDS_hourly (datestamp, description, imagename, temperature, precipitation, precipProbability, windSpeed, windDirection, avgbearing, pressure, uvindex, copyrighttext, copyrighturl) VALUES ";
        $SQL .= "('".$row->datestamp."', '".$row->description."', '".$row->imagename."', ".$row->temperature.", ".$row->precipitation.", ".$row->precipProbability.", ".$row->windSpeed.", '".$row->windDirection."'";
        $SQL .= ", ".$row->avgbearing.", ".$row->pressure.", ".$row->uvindex.", '".$row->copyrighttext."', '".$row->copyrighturl."') ";
        $SQL .= "ON DUPLICATE KEY UPDATE description = '".$row->description."', imagename = '".$row->imagename."', temperature = ".$row->temperature.", precipitation = ".$row->precipitation.", precipProbability = ".$row->precipProbability.", windSpeed = ".$row->windSpeed;
        $SQL .= ", windDirection = '".$row->windDirection."', avgbearing = ".$row->avgbearing.", pressure = ".$row->pressure.", uvindex = ".$row->uvindex.", copyrighttext = '".$row->copyrighttext."', copyrighturl = '".$row->copyrighturl."';";

        //Insert the Record in to the Database
        if ($conn->query($SQL) != TRUE) {
          error_log('Database Insert failed on forecastDS_hourly Table: '.$conn->error."\n ".$SQL);
        }
      }

    }

    if ($LoadWUForecast) {
      // Load the Daily Forecast from Weather Underground
      $rawdata = json_decode(getWUDailyForecast($WUAPIKey, $latitude, $longitude, $WULanguage, $TZ));

      foreach($rawdata as $i => $row) {
        $ins1 = ", updatetime";
        $ins2 = "', '".$row->updatetime;
        $upd = "', updatetime = '".$row->updatetime;

        $SQL = "INSERT INTO weatherflowdb.forecastWU_daily (datestamp, description, imagename, tempMax, tempMin, precipitation, precipProbability, windSpeed, windDirection, avgbearing, pressure, uvindex, copyrighttext, copyrighturl".$ins1.") VALUES ";
        $SQL .= "('".$row->datestamp."', '".$row->description."', '".$row->imagename."', ".$row->tempmax.", ".$row->tempmin.", ".$row->precipitation.", ".$row->precipProbability.", ".$row->windSpeed.", '".$row->windDirection."'";
        $SQL .= ", ".$row->avgbearing.", ".$row->pressure.", ".$row->uvindex.", '".$row->copyrighttext."', '".$row->copyrighturl.$ins2."') ";
        $SQL .= "ON DUPLICATE KEY UPDATE description = '".$row->description."', imagename = '".$row->imagename."', tempMax = ".$row->tempmax.", tempMin = ".$row->tempmin.", precipitation = ".$row->precipitation.", precipProbability = ".$row->precipProbability.", windSpeed = ".$row->windSpeed;
        $SQL .= ", windDirection = '".$row->windDirection."', avgbearing = ".$row->avgbearing.", pressure = ".$row->pressure.", uvindex = ".$row->uvindex.", copyrighttext = '".$row->copyrighttext."', copyrighturl = '".$row->copyrighturl.$upd."';";

        //Insert the Record in to the Database
        if ($conn->query($SQL) != TRUE) {
          error_log('Database Insert failed on forecastWU_daily Table: '.$conn->error."\n ".$SQL);
        }
      }

      // Load the Hourly Forecast from Weather Underground
      $rawdata = json_decode(getWUHourlyForecast($WUAPIKey, $latitude, $longitude, $WULanguage, $TZ));
      foreach($rawdata as $i => $row) {
        $ins1 = ", updatetime";
        $ins2 = "', '".$row->updatetime;
        $upd = "', updatetime = '".$row->updatetime;

        $SQL = "INSERT INTO weatherflowdb.forecastWU_hourly (datestamp, description, imagename, temperature, precipitation, precipProbability, windSpeed, windDirection, avgbearing, pressure, uvindex, copyrighttext, copyrighturl".$ins1.") VALUES ";
        $SQL .= "('".$row->datestamp."', '".$row->description."', '".$row->imagename."', ".$row->temperature.", ".$row->precipitation.", ".$row->precipProbability.", ".$row->windSpeed.", '".$row->windDirection."'";
        $SQL .= ", ".$row->avgbearing.", ".$row->pressure.", ".$row->uvindex.", '".$row->copyrighttext."', '".$row->copyrighturl.$ins2."') ";
        $SQL .= "ON DUPLICATE KEY UPDATE description = '".$row->description."', imagename = '".$row->imagename."', temperature = ".$row->temperature.", precipitation = ".$row->precipitation.", precipProbability = ".$row->precipProbability.", windSpeed = ".$row->windSpeed;
        $SQL .= ", windDirection = '".$row->windDirection."', avgbearing = ".$row->avgbearing.", pressure = ".$row->pressure.", uvindex = ".$row->uvindex.", copyrighttext = '".$row->copyrighttext."', copyrighturl = '".$row->copyrighturl.$upd."';";

        //Insert the Record in to the Database
        if ($conn->query($SQL) != TRUE) {
          error_log('Database Insert failed on forecastWU_hourly Table: '.$conn->error."\n ".$SQL);
        }
      }

    }

    // Execute the Stored Procedure on the Database Server
    $sunrise        = getSunrise(date_format(new DateTime(),'Y-m-d'),$latitude,$longitude,$UTC);
    $sunriseTom     = getSunrise(date_format(new DateTime('tomorrow'),'Y-m-d'),$latitude,$longitude,$UTC);
    $sunset         = getSunset(date_format(new DateTime(),'Y-m-d'),$latitude,$longitude,$UTC);
    $SQL = "CALL dailySummaryProcess('".$sunrise."', '".$sunset."', '".$sunriseTom."'); ";

    //Insert the Record in to the Database
    if ($conn->query($SQL) != TRUE) {
      error_log('Excution of dailySummaryProcess failed: '.$conn->error);
    }

    // Calculate Moon Information and insert in the database.
    $moon = new Solaris\MoonPhase();
    $age = $moon->age();
    $illumination = (number_format( $moon->illumination(), 2 )*100);
    $stage = $moon->phase() < 0.5 ? 'waxing' : 'waning';
    $phasename = $moon->phase_name();
    $phase = round($moon->phase(),2);
    $now =time();
    if ($now < $moon->full_moon()) {
      $next_full_moon = date('Y-m-j', $moon->full_moon());
    } else {
      $next_full_moon = date('Y-m-j', $moon->next_full_moon());
    }
    $next_new_moon = gmdate( 'Y-m-j', $moon->next_new_moon() );

    $SQL = "UPDATE weatherflowdb.daily_summary SET moon_age = ".$age.", moon_illumination = ".$illumination.", moon_stage = '".$stage."', moon_phasename = '".$phasename."', ";
    $SQL .= "moon_phase = ".$phase.", moon_fullmoon = '".$next_full_moon."', moon_newmoon = '".$next_new_moon."' ";
    $SQL .= "WHERE LogDate = '".date_format(new DateTime(),"Y-m-d")."';";

    //Update the Record in the Database
    if ($conn->query($SQL) != TRUE) {
      error_log('Could not update Moon Information: '.$conn->error);
    }

} catch (\Exception $e) {
  error_log($e->getMessage());
}

// Close Database connection
mysqli_close($conn);
?>

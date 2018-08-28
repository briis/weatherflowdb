<?php
//Create Data Directory if it does not exist
if (!file_exists('data')) {
    mkdir('data', 0777, true);
}

function getYRDailyForecast($forecasturl) {
  // This function gets forecast pr. day from the selected YR.NO location, and returns them in an Array
  $forecast_url = $forecasturl."/forecast.xml";
  // Check if we have a cached Forecast File - If not, download it from yr.no
  $downloadfile = True;
  $forecast_file = "data/dailyforecast.xml";
  if (file_exists($forecast_file)) {
    //We already have a cached file, now check if it is still valid
    $xml = simplexml_load_file($forecast_file); // where $xml_string is the XML data you'd like to use (a well-formatted XML string). If retrieving from an external source, you can use file_get_contents to retrieve the data and populate this variable.
    $nextupdate=date_create($xml->meta->nextupdate);
    $currentTime = new DateTime();
    if ($currentTime > $nextupdate) {$downloadfile = True;} else {$downloadfile = False;}
  }
  if ($downloadfile) {
    file_put_contents($forecast_file, fopen($forecast_url, 'r'));
  }

  // Now we have a valid forecast file. Start processing it
  $xml = simplexml_load_file($forecast_file);

  // Get the Copyright Text
  $copyrighttext = ''.$xml->credit->link['text'].'';
  $copyrighturl = ''.$xml->credit->link['url'].'';

  //Initialize Variables
  $forecastarray = array();

  //Loop through the XML file and retrieve data
  foreach($xml->forecast->tabular->time as $fcst) {
    $datestamp = date_format(date_create($fcst['from']),'Y-m-d');
    $timefrom = date_format(date_create($fcst['from']),'H:i');
    $timeto = date_format(date_create($fcst['to']),'H:i');
    $period = round($fcst['period']);
    $imagename = ''.$fcst->symbol['var'].'';
    $description = ''.$fcst->symbol['name'].'';
    $temperature = round($fcst->temperature['value']);
    $precipitation = floatval($fcst->precipitation['value']);
    $windSpeed = round($fcst->windSpeed['mps']);
    $windDescription = ''.$fcst->windSpeed['name'].'';
    $windDirection = ''.$fcst->windDirection['code'].'';
    $avgbearing = round($fcst->windDirection['deg']);
    $pressure = floatval($fcst->pressure['value']);
    // Store data in Array
    $data=array("datestamp"=>$datestamp,"period"=>$period,"timefrom"=>$timefrom,"timeto"=>$timeto,"description"=>$description,"imagename"=>$imagename, "temperature"=>$temperature,"precipitation"=>$precipitation,"windSpeed"=>$windSpeed,"windDescription"=>$windDescription,"windDirection"=>$windDirection,"avgbearing"=>$avgbearing,"pressure"=>$pressure,"copyrighttext"=>$copyrighttext,"copyrighturl"=>$copyrighturl);
    $forecastarray[]=$data;
  }

  return json_encode($forecastarray);

}


function getYRHourlyForecast($forecasturl) {
  // This function gets forecast pr. hour from the selected YR.NO location, and returns them in an Array
  $forecast_url = $forecasturl."/forecast_hour_by_hour.xml";
  // Check if we have a cached Forecast File - If not, download it from yr.no
  $downloadfile = True;
  $forecast_file = "data/hourlyforecast.xml";
  if (file_exists($forecast_file)) {
    //We already have a cached file, now check if it is still valid
    $xml = simplexml_load_file($forecast_file); // where $xml_string is the XML data you'd like to use (a well-formatted XML string). If retrieving from an external source, you can use file_get_contents to retrieve the data and populate this variable.
    $nextupdate=date_create($xml->meta->nextupdate);
    $currentTime = new DateTime();
    if ($currentTime > $nextupdate) {$downloadfile = True;} else {$downloadfile = False;}
  }
  if ($downloadfile) {
    file_put_contents($forecast_file, fopen($forecast_url, 'r'));
  }

  // Now we have a valid forecast file. Start processing it
  $xml = simplexml_load_file($forecast_file);

  // Get the Copyright Text
  $copyrighttext = ''.$xml->credit->link['text'].'';
  $copyrighturl = ''.$xml->credit->link['url'].'';

  //Create empty Array to hold data
  $forecastarray = array();

  //Loop through the XML and select all data in the future
  $currentTime = new DateTime();
  foreach($xml->forecast->tabular->time as $fcst) {
    $hour = date_create($fcst['from']);
    if ($currentTime < $hour) {
      $datestamp = date_format(date_create($fcst['from']),'Y-m-d H:i');
      $description = ''.$fcst->symbol['name'].'';
      $imagename = ''.$fcst->symbol['var'].'';
      $name = $fcst->symbol['name'];
      $temperature = ''.$fcst->temperature['value'].'';
      $precipitation = floatval($fcst->precipitation['value']);
      $windSpeed = round($fcst->windSpeed['mps']);
      $windDescription = ''.$fcst->windSpeed['name'].'';
      $windDirection = ''.$fcst->windDirection['code'].'';
      $avgbearing = round($fcst->windDirection['deg']);
      $pressure = floatval($fcst->pressure['value']);

      $data=array("datestamp"=>$datestamp,"description"=>$description,"imagename"=>$imagename, "temperature"=>$temperature,"precipitation"=>$precipitation,"windSpeed"=>$windSpeed,"windDescription"=>$windDescription,"windDirection"=>$windDirection,"avgbearing"=>$avgbearing,"pressure"=>$pressure,"copyrighttext"=>$copyrighttext,"copyrighturl"=>$copyrighturl);
      $forecastarray[]=$data;
    }
  }

  return json_encode($forecastarray);

}

function getDarkSkyDailyForecast($apiKey, $lat, $lon, $language, $unit, $TZ) {
  $forecast_url = "https://api.darksky.net/forecast/".$apiKey."/".$lat.",".$lon."?exclude=minutely&lang=".$language."&units=".$unit;
  $downloadfile = True;
  $forecast_file = "data/dsforecast.json";

  if (file_exists($forecast_file)) {
    $lastmodified = date("Y-m-d H:i:s",filectime($forecast_file));
    if (strtotime($lastmodified) < time() - (60*60*2)) {$downloadfile = True;} else {$downloadfile = False;}
  }

  if ($downloadfile) {
    file_put_contents($forecast_file, fopen($forecast_url, 'r'));
  }

  //We now have the latest forecast as a file, load it.
  $json = file_get_contents($forecast_file);
  $rawdata = json_decode($json, true);

  //Create empty Array to hold data
  $forecastarray = array();

  // Create the Day-by-Day forecast and Push to New JSON Array
  $fcstarray = $rawdata['daily']['data'];
  foreach($fcstarray as $i => $row) {
    $datestamp = convertEpochDate($row['time'],$TZ);
    $summary = $row['summary'];
    $icon = $row['icon'];
    $tempmax = $row['temperatureMax'];
    $tempmin = $row['temperatureMin'];
    $precipitation = $row['precipIntensity'];
    $precipProbability = $row['precipProbability']*100;
    $windSpeed = $row['windSpeed'];
    $avgbearing = $row['windBearing'];
    $windDirection = getWindDirString($row['windBearing']);
    $pressure = $row['pressure'];
    $uvindex = $row['uvIndex'];

    $copyrighttext = "Dark Sky Company";
    $copyrighturl = "https://darksky.net/forecast/".$lat.",".$lon."/si12/en";

    $data=array("datestamp"=>$datestamp,"description"=>$summary,"imagename"=>$icon, "tempmax"=>$tempmax, "tempmin"=>$tempmin,"precipitation"=>$precipitation,"precipProbability"=>$precipProbability,"windSpeed"=>$windSpeed,"windDirection"=>$windDirection,"avgbearing"=>$avgbearing,"pressure"=>$pressure,"uvindex"=>$uvindex,"copyrighttext"=>$copyrighttext,"copyrighturl"=>$copyrighturl);
    $forecastarray[]=$data;

    }

  return json_encode($forecastarray);
  }


function getDarkSkyHourlyForecast($apiKey, $lat, $lon, $language, $unit, $TZ) {
  $forecast_url = "https://api.darksky.net/forecast/".$apiKey."/".$lat.",".$lon."?exclude=currently,minutely&lang=".$language."&units=".$unit;
  $downloadfile = True;
  $forecast_file = "data/dsforecast.json";

  if (file_exists($forecast_file)) {
    $lastmodified = date("Y-m-d H:i:s",filectime($forecast_file));
    if (strtotime($lastmodified) < time() - (60*60*2)) {$downloadfile = True;} else {$downloadfile = False;}
  }

    if ($downloadfile) {
      file_put_contents($forecast_file, fopen($forecast_url, 'r'));
    }

  //We now have the latest forecast as a file, load it.
  $json = file_get_contents($forecast_file);
  $rawdata = json_decode($json, true);

  //Create empty Array to hold data
  $forecastarray = array();

  // Create the Hourly forecast and Push to New JSON Array
  $fcstarray = $rawdata['hourly']['data'];
  foreach($fcstarray as $i => $row) {
    $datestamp = convertEpoch($row['time'],$TZ);
    $summary = $row['summary'];
    $icon = $row['icon'];
    $temperature = $row['temperature'];
    $precipitation = $row['precipIntensity'];
    $precipProbability = $row['precipProbability']*100;
    $windSpeed = $row['windSpeed'];
    $avgbearing = $row['windBearing'];
    $windDirection = getWindDirString($row['windBearing']);
    $pressure = $row['pressure'];
    $uvindex = $row['uvIndex'];

    $copyrighttext = "Dark Sky Company";
    $copyrighturl = "https://darksky.net/forecast/".$lat.",".$lon."/si12/en";

    $data=array("datestamp"=>$datestamp,"description"=>$summary,"imagename"=>$icon, "temperature"=>$temperature,"precipitation"=>$precipitation,"precipProbability"=>$precipProbability,"windSpeed"=>$windSpeed,"windDirection"=>$windDirection,"avgbearing"=>$avgbearing,"pressure"=>$pressure,"uvindex"=>$uvindex,"copyrighttext"=>$copyrighttext,"copyrighturl"=>$copyrighturl);
    $forecastarray[]=$data;

    }

  return json_encode($forecastarray);
  }


  function getWUDailyForecast($apiKey, $lat, $lon, $language, $TZ) {

    $forecast_url = "http://api.wunderground.com/api/".$apiKey."/forecast10day/lang:".$language."/q/".$lat.",".$lon.".json";
    $downloadfile = True;
    $forecast_file = "data/wuforecastday.json";

    if (file_exists($forecast_file)) {
      $lastmodified = date("Y-m-d H:i:s",filectime($forecast_file));
      if (strtotime($lastmodified) < time() - (60*60*2)) {$downloadfile = True;} else {$downloadfile = False;}
    }

    if ($downloadfile) {
      file_put_contents($forecast_file, fopen($forecast_url, 'r'));
    }

    //We now have the latest forecast as a file, load it.
    $json = file_get_contents($forecast_file);
    $rawdata = json_decode($json, true);

    //Create empty Array to hold data
    $forecastarray = array();

    // Create the Day-by-Day forecast and Push to New JSON Array
    $fcstarray = $rawdata['forecast']['simpleforecast']['forecastday'];
    foreach($fcstarray as $i => $row) {
      $datestamp = convertEpochDate($row['date']['epoch'],$TZ);
      $summary = $row['conditions'];
      $icon = $row['icon'];
      $tempmax = $row['high']['celsius'];
      $tempmin = $row['low']['celsius'];
      $precipitation = $row['qpf_allday']['mm'];
      $precipProbability = $row['pop'];
      $windSpeed = getMS($row['avewind']['kph']);
      $avgbearing = $row['avewind']['degrees'];
      $windDirection = getWindDirString($avgbearing);
      $pressure = 0;
      $uvindex = 0;

      $copyrighttext = "Weather Underground";
      $copyrighturl = "https://www.wunderground.com/personal-weather-station/dashboard?ID=I84SOHOL2";

      $data=array("datestamp"=>$datestamp,"description"=>$summary,"imagename"=>$icon, "tempmax"=>$tempmax, "tempmin"=>$tempmin,"precipitation"=>$precipitation,"precipProbability"=>$precipProbability,"windSpeed"=>$windSpeed,"windDirection"=>$windDirection,"avgbearing"=>$avgbearing,"pressure"=>$pressure,"uvindex"=>$uvindex,"copyrighttext"=>$copyrighttext,"copyrighturl"=>$copyrighturl);
      $forecastarray[]=$data;

      }

    return json_encode($forecastarray);

    }

    function getWUHourlyForecast($apiKey, $lat, $lon, $language, $TZ) {

      $forecast_url = "http://api.wunderground.com/api/".$apiKey."/hourly/lang:".$language."/q/".$lat.",".$lon.".json";
      $downloadfile = True;
      $forecast_file = "data/wuforecasthour.json";

      if (file_exists($forecast_file)) {
        $lastmodified = date("Y-m-d H:i:s",filectime($forecast_file));
        if (strtotime($lastmodified) < time() - (60*60*2)) {$downloadfile = True;} else {$downloadfile = False;}
      }

      if ($downloadfile) {
        file_put_contents($forecast_file, fopen($forecast_url, 'r'));
      }

      //We now have the latest forecast as a file, load it.
      $json = file_get_contents($forecast_file);
      $rawdata = json_decode($json, true);

      //Create empty Array to hold data
      $forecastarray = array();

      // Create the Hourly forecast and Push to New JSON Array
      $fcstarray = $rawdata['hourly_forecast'];
      foreach($fcstarray as $i => $row) {
        $datestamp = convertEpoch($row['FCTTIME']['epoch'],$TZ);
        $summary = $row['condition'];
        $icon = $row['icon'];
        $temperature = $row['temp']['metric'];
        $precipitation = $row['qpf']['metric'];
        $precipProbability = $row['pop'];
        $windSpeed = getMS($row['wspd']['metric']);
        $avgbearing = $row['wdir']['degrees'];
        $windDirection = getWindDirString($avgbearing);
        $pressure = $row['mslp']['metric'];
        $uvindex = $row['uvi'];

        $copyrighttext = "Weather Underground";
        $copyrighturl = "https://www.wunderground.com/personal-weather-station/dashboard?ID=I84SOHOL2";

        $data=array("datestamp"=>$datestamp,"description"=>$summary,"imagename"=>$icon, "temperature"=>$temperature,"precipitation"=>$precipitation,"precipProbability"=>$precipProbability,"windSpeed"=>$windSpeed,"windDirection"=>$windDirection,"avgbearing"=>$avgbearing,"pressure"=>$pressure,"uvindex"=>$uvindex,"copyrighttext"=>$copyrighttext,"copyrighturl"=>$copyrighturl);
        $forecastarray[]=$data;

        }

        return json_encode($forecastarray);

      }

 ?>

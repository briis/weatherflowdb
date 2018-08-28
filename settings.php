
<?php
// WeatherFlowDB is a Program to capture data using UDP from a WeatherFlow Weather Station
// All data is stored in a MYSQL database and can be retrieved from there
// Author: Bjarne Riis
// Last Updated: 19 aug 2018
// Filename: settings.php
// - This file is used to store common settings for all parts of the program

// Log Directory - This is the directory where the Log files will be Stored
// Note: Log files are only created if there has been an error
// Remember the / at the end
$logdirectory = "/Users/bjarne/documents/weatherflowdb/";

// Set Station Parameters
$TZ = "Europe/Copenhagen";  //Timezone for Station
$HeightAboveSea = 34.4;     //Station Height above Sealevel
$latitude = 55.973117;      //Station Latitude
$longitude = 12.469451;     //Station Longitude
$UTC = 2;                   //Specifies the difference between GMT and local time in hours
$stationID = 2777;          //The ID your WeatherFlow Station has been assigned

//YR.NO Forecast Link
$LoadYRForecast = True;    //True if you want to load forecast from YR.no else False
$forecasturl = "https://www.yr.no/place/Denmark/Capital/Fredensborg"; //URL to pull forecast from

//DarkSky Forecast
$LoadDSForecast = False;    //True if you want to load forecast from DarkSky else False
$DSAPIKey = "[Insert API KEY]"; //API Key for DarkSky - Needs to be filled if above is True
$DSLanguage = "da";  //DarkSky Language
$DSUnit = "si"; //DarkSky Units

//Weather Underground Forecast
$LoadWUForecast = False;     //True if you want to load forecast from Wather Underground else False
$WUAPIKey = "[Insert API KEY]"; //WU API Key - Needs to be filled if above is True
$WULanguage = "DK"; //Weather Underground Units

// Cumulus Settings
$createRealtimeFile = True; //True if you want the program to create realtime.txt file
$realtimeFilePath = "/tmp/realtime.txt"; //Location where to store realtime.txt

// Define Database Connection
$dbserver = "localhost";
$dbuser = "weatherflowuser";
$dbpass = "weatherflow";
$dbschema = "weatherflowdb";

// Define UDP Socket for WeatherFlow
$socketIP = "0.0.0.0";
$socketPort = "50222";

?>

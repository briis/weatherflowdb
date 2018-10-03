# WeatherFlowDB
This set of PHP Scripts reads UDP data from a WeatherFlow Weather System and stores them in realtime in a MYSQL Database.

<strong>Version</strong>: 1.0-015

The Company WeatherFlow (https://weatherflow.com) has created an excellent and low cost Weather Station that can broadcast data in realtime over UDP. The aim of this program is to capture these data, make some Weather related calculations, and then store that data in realtime in a MYSQL database.

As I have been using CumulusMX software from Sandaysoft (https://sandaysoft.com) for many years, a lot of the display option I use is reliant on the realtime.txt format that Cumulus supplies. So this system also creates a table called realtime, that more or less matches the realtime.txt format from Cumulus. The table is updated every 3-5 seconds, depending on how often we see Rapid Wind data.

Here is a link to my Weather site, that uses the data produced by WeatherFlowDB: https://vejr.briis.com

The system broadcast the data in the following intervals:
- Rapid Wind data: every 5 seconds (Wind Speed and Wind Direction)
- AIR data: Every 1 minute (Temperature, Pressure and Humidity)
- SKY data: Every 1 minute (Rain, UV, Solar radiation and Wind Data)

<h4>Prerequisite</h4>
<ul>
  <li>A WeatherFlow Weather Station</li>
  <li>PHP installed on the machine running the scripts. It is developed on a machine running PHP V7.2, but should work with earlier releases.</li>
  <li>MYSQL installed on a machine. Can be a different machine than the one running the scripts. Requires minimum V5.5 of MYSQL as the database uses Triggers.</li>
</ul>

<h4>Explanations of the different files</h4>
<ul>
  <li><strong>datalogger.php</strong> This is the main script and uns in an infinitive loop, and listens for the UDP data. Once data are received, it then does some Weather releated calculations, like Dew Point, Wind Chill etc, and the data are store in the database.</li>
  <li><strong>weatherfunctions.php</strong> This script contains all the different weather functions and calculations. Please note that my system runs in scandinavia, so all functions expect temperature in Celcius, Wind in m/s (meters pr. second) and pressure in hPa. If you use other units, the functions in here need to be adjusted.</li>
  <li><strong>settings.php</strong> Unless you need to adjust the Weather Calculation functions, this is the only file you need to edit. This contains different settings used throughout the system.</li>
  <li><strong>dailysummary.php</strong> This script should run every 5 minutes (or more often if you wish) and will update the daily Highs and Lows in the database.</li>
  <li><strong>maintenance.php</strong> This script should run once a day, prefereable right after midnight, and will at current, clean-up the rapid wind table for data older than a day, so that this table is not growing too big.</li>
  <li><strong>forecast.php</strong> Includes functions to load Daily and Hourly forecast from one or all of the three forecast providers described below. The functions are called from the dailysummary.php file.</li>
  <li><strong>cumulusfunctions.php</strong> This file contains functions that are used to simulate some of the functions found in Sandaysoft Cumulus and CumulusMX program.</li>
  <li><strong>moonphase.php</strong> Script by Samir Shah, that calculates different moon related values.</li>
  <li><strong>install directory</strong> This directory contains files that are explained in the Installation section. Once the initial installation is done, these are not needed anymore.</li>
  <li><strong>history directory</strong> In here you will the files necessary to load all historic data from the WeatherFlow REST API.</li>
</ul>

<h4>Installation</h4>
This procedure assumes you are on a Linux based system, but I see no reason why it should not run on any system that supports PHP and MYSQL. It will just be the steps 4-6 than need to be done differently.
<ol type="1">
  <li>Start by copying all the files to a directory on your machine. Ensure you keep the directory structure.</li>
  <li>Edit the <strong>settings.php</strong> script and make sure that the <i>$logdirectory</i> points to a directory that the user running the scripts has write access to. This is where the error logs are written, if you get any. Also change the <i>$TZ</i> to your local Timezone and finaly the <i>$HeightAboveSea</i> variable to the height (in meters) your weather station is located.<br><i>$dbserver</i> should point to the IP Address of the MYSQL Server. If it is not on your localhost, then make sure it has network access enabled. (bind address 0.0.0.0). The remaining database parameters can be left unchanged, unless you also change them in the createdatabase.sql script.</li>
  <li>Now we need to create the database. Go to the main directory where you placed the files and type:<br>
  <i>mysql -u root -p < install/createdatabase.sql</i><br>
    Enter your root password, and if all goes well, you should, after a few seconds, have the MYSQL database setup.</li>
  <li>Next step is to setup the datalogger.php file to run whenever the system reboots and run in the background. For a Linux system I have included the file <i>weatherflowdb.service</i> in the install directory. Do the following to install the file and start the service:<br><ul>
    <li>Edit the file and change the parameter <i>ExecStart</i> so that it points to the correct directory where the datalogger.php file is located.</li>
    <li>Copy the file to the system directory: <br>sudo cp install/weatherflowdb.service /etc/systemd/system/weatherflowdb.service</li>
    <li>Enable the Service to run: sudo systemctl enable weatherflowdb.service</li>
    <li>Start the service right away: sudo systemctl start weatherflowdb.service</li></ul></li>
  <li>Now setup the dailysummary.php to run every 5 minutes, by doing the following:<br>
    <ul><li>On the command line type: <i>crontab -e</i></li>
      <li>Go the bottom of the file and type:<br>*/5 * * * * /usr/bin/php /[YOUR_WEATHERFLOWDB_DIRECTORY]/dailysummary.php</li>
      <li>Save the file and you are all set.</li></ul></li>
  <li>Finally setup the maintenance.php file to run 10 minutes past midnight. Repeat the <i>crontab</i> steps above, this time just type:<br>10 0 * * * /usr/bin/php /[YOUR_WEATHERFLOWDB_DIRECTORY]/maintenance.php</li>
</ol>
All 3 scripts should now be active and you should start seing data coming in to the MYSQL database.

<h4>Loading Historic Data</h4>
<ol type="1">
  <li>Edit the file <i>history_settings.php</i> in the history directory, and enter your own numbers for the devices in each field. If you don't know what they are you can paste the following into a web browser, replacing [STATIONID] with the ID of your own Station.<br>
  https://swd.weatherflow.com/swd/rest/stations/[STATIONID]?api_key=20c70eae-e62f-4d3b-b3a4-8586e90f3ac8</li>
  <li>When history_settings.php has been updated, open a Terminal Window, go to the history directory and execute the following command:<br>
  <i>php load_history_data.php</i><br>
  Please note, this will take a significant amount of time, if you have a lot of data, so start this from a Computer that does not go in to sleep mode. You will notified during the program about progress.
</ol>
When program is finished, you should see all you historic data for the AIR and SKY devices.

<h4>Forecast</h4>
The system supports loading Forecast data from 3 different sources:
<ol type="1">
  <li>yr.no - The Norwegian Meteorological Institute - Free data, no registration required</li>
  <li>DarkSky - DarkSky Corporation - Free data up to a certain number of requests pr. day. Requires registration and an API key</li>
  <li>Weather Underground - Free data up to a certain number of requests pr. day. Requires registration and an API key</li>
 </ol>
  
If you set the variable <i>$Load[XX]Forecast</i> to True in settings.php, then the system will load forecast data from the specified provider and store them in the database. Each provider has its own tables.<br>
In order to find the correct link to YR.NO for your own location, go to https://www.yr.no and search for your location. Once you have the right url, copy that in to the <i>$forecasturl</i> variable. Remember NO slash (/) at the end. That is all there is to it.<br>
In order not to overload the forecast providers, the program will cache the file it downloads, and only load a new, when it has expired. That will be approximately 4-12 times a day.

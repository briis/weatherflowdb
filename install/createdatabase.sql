/* Create the Database */
DROP DATABASE IF EXISTS weatherflowdb;
CREATE DATABASE weatherflowdb DEFAULT CHARACTER SET utf8  DEFAULT COLLATE utf8_general_ci;

/* Create User and Grant Privileges to Login and Store data
GRANT USAGE ON *.* TO 'weatherflowuser'@'%';
DROP USER 'weatherflowuser'@'%';
*/
CREATE USER 'weatherflowuser'@'%' IDENTIFIED BY 'weatherflow';
GRANT ALL PRIVILEGES ON weatherflowdb.* TO 'weatherflowuser'@'%';
FLUSH PRIVILEGES;

/* Create table rapid_wind - holds the 3 second wind values */
DROP TABLE IF EXISTS weatherflowdb.rapid_wind;
CREATE TABLE weatherflowdb.rapid_wind (
  datestamp DATETIME NOT NULL,
  serial_number VARCHAR(15) NULL,
  hub_sn VARCHAR(15) NULL,
  wind_speed DECIMAL(4,1) NULL,
  wind_direction INT NULL,
  wind_direction_symbol VARCHAR(3) NULL,
  PRIMARY KEY (datestamp));

/* Create table air_observations - holds data from the AIR Device */
DROP TABLE IF EXISTS weatherflowdb.air_observations;
CREATE TABLE weatherflowdb.air_observations (
  datestamp DATETIME NOT NULL,
  serial_number VARCHAR(15) NULL,
  hub_sn VARCHAR(15) NULL,
  temperature DECIMAL(4,1) NULL,
  dewpoint DECIMAL(4,1) NULL,
  pressure DECIMAL(6,1) NULL,
  barometer DECIMAL(6,1) NULL,
  humidity INT NULL,
  heatindex DECIMAL(4,1) NULL,
  humindex DECIMAL(4,1) NULL,
  windchill DECIMAL(4,1) NULL,
  feelslike DECIMAL(4,1) NULL,
  apptemp DECIMAL(4,1) NULL,
  cloudbase DECIMAL(6,1) NULL,
  battery DECIMAL(4,2) NULL,
  firmware INT(11) NULL,
  PRIMARY KEY (datestamp));

/* Create table sky_observations - holds data from the SKY Device */
DROP TABLE IF EXISTS weatherflowdb.sky_observations;
CREATE TABLE weatherflowdb.sky_observations (
  datestamp DATETIME NOT NULL,
  serial_number VARCHAR(15) NULL,
  hub_sn VARCHAR(15) NULL,
  illuminance INT NULL,
  uv DECIMAL(3,1) NULL,
  rain_minute FLOAT NULL,
  rain_rate FLOAT NULL,
  wind_lull DECIMAL(4,1) NULL,
  wind_avg DECIMAL(4,1) NULL,
  wind_gust DECIMAL(4,1) NULL,
  wind_dir_avg INT NULL,
  wind_dir_sym VARCHAR(3),
  solarrad INT NULL,
  precip_type INT NULL,
  beaufort INT NULL,
  battery DECIMAL(4,2) NULL,
  firmware INT(11) NULL,
  PRIMARY KEY (datestamp));

/* Create table daily_summary - holds data summary data per day
   File is updated every 10 minutes */
DROP TABLE IF EXISTS weatherflowdb.daily_summary;
CREATE TABLE weatherflowdb.daily_summary (
  LogDate DATE NOT NULL,
  datestamp DATETIME NOT NULL,
  tempTH DECIMAL(4,1) NULL,
  TtempTH VARCHAR(5) NULL,
  tempTL DECIMAL(4,1) NULL,
  TtempTL VARCHAR(5) NULL,
  windTM DECIMAL(4,1) NULL,
  TwindTM VARCHAR(5) NULL,
  wgustTM DECIMAL(4,1) NULL,
  TwgustTM VARCHAR(5) NULL,
  pressTH DECIMAL(6,1) NULL,
  TpressTH VARCHAR(5) NULL,
  pressTL DECIMAL(6,1) NULL,
  TpressTL VARCHAR(5) NULL,
  uvTM DECIMAL(4,1) NULL,
  TuvTM VARCHAR(5) NULL,
  solarTM DECIMAL(4,1) NULL,
  TsolarTM VARCHAR(5) NULL,
  strike3hour INT NULL,
  sunrise DATETIME NULL,
  sunriseTom DATETIME NULL,
  sunset DATETIME NULL,
  moon_age FLOAT NULL,
  moon_illumination INT NULL,
  moon_stage VARCHAR(45) NULL,
  moon_phase DECIMAL(4,2) NULL,
  moon_phasename VARCHAR(45) NULL,
  moon_fullmoon DATE NULL,
  moon_newmoon DATE NULL,
  PRIMARY KEY (LogDate));

/* Create table hub__observations - holds status about the WeatherFlow HUB Device
   File is updated every 10 minutes */
DROP TABLE IF EXISTS weatherflowdb.hub__observations;
CREATE TABLE weatherflowdb.hub__observations (
  LogDate DATE NOT NULL,
  datestamp DATETIME NOT NULL,
  serial_number VARCHAR(15) NULL,
  firmware INT NULL,
  rssi INT NULL,
  uptime INT NULL,
  uptimeText VARCHAR(50) NULL,
  PRIMARY KEY (LogDate));

/* Create table strike_events - logs every time a Lightning Strike occurs */
DROP TABLE IF EXISTS weatherflowdb.strike_events;
CREATE TABLE weatherflowdb.strike_events (
  datestamp DATETIME NOT NULL,
  serial_number VARCHAR(15) NULL,
  hub_sn VARCHAR(15) NULL,
  distance INT NULL,
  energy INT NULL,
  PRIMARY KEY (datestamp));

/* Create table device_status - logs status fromt the attached devices */
DROP TABLE IF EXISTS weatherflowdb.device_status;
CREATE TABLE weatherflowdb.device_status (
  datestamp DATETIME NOT NULL,
  serial_number VARCHAR(15) NOT NULL,
  hub_sn VARCHAR(15) NULL,
  uptime INT NULL,
  voltage DECIMAL(4,2) NULL,
  firmware_revision INT NULL,
  sensor_status INT NULL,
  statustext VARCHAR(50) NULL,
  PRIMARY KEY (datestamp, serial_number));

/* Create table forecast_daily - collects the day-by-day forecast data from YR.NO */
DROP TABLE IF EXISTS weatherflowdb.forecastYR_daily;
CREATE TABLE weatherflowdb.forecast_daily (
  datestamp date NOT NULL,
  period int(11) NOT NULL,
  timefrom varchar(5) DEFAULT NULL,
  timeto varchar(5) DEFAULT NULL,
  description varchar(20) DEFAULT NULL,
  imagename varchar(6) DEFAULT NULL,
  temperature int(11) DEFAULT NULL,
  precipitation decimal(4,1) DEFAULT NULL,
  windSpeed decimal(4,1) DEFAULT NULL,
  windDescription varchar(45) DEFAULT NULL,
  windDirection varchar(3) DEFAULT NULL,
  avgbearing int(11) DEFAULT NULL,
  pressure decimal(6,1) DEFAULT NULL,
  copyrighttext varchar(100) DEFAULT NULL,
  copyrighturl varchar(100) DEFAULT NULL,
  PRIMARY KEY (datestamp, period));

/* Create table forecast_hourly - collects the hourly forecast data from YR.NO */
DROP TABLE IF EXISTS weatherflowdb.forecastYR_hourly;
CREATE TABLE weatherflowdb.forecast_hourly (
  datestamp datetime NOT NULL,
  description varchar(20) DEFAULT NULL,
  imagename varchar(6) DEFAULT NULL,
  temperature int(11) DEFAULT NULL,
  precipitation decimal(4,1) DEFAULT NULL,
  windSpeed decimal(4,1) DEFAULT NULL,
  windDescription varchar(45) DEFAULT NULL,
  windDirection varchar(3) DEFAULT NULL,
  avgbearing int(11) DEFAULT NULL,
  pressure decimal(6,1) DEFAULT NULL,
  copyrighttext varchar(100) DEFAULT NULL,
  copyrighturl varchar(100) DEFAULT NULL,
  PRIMARY KEY (datestamp));

/* Create table forecastDS_daily - Holds the daily Dark Sky Daily forecast */
DROP TABLE IF EXISTS weatherflowdb.forecastDS_daily;
CREATE TABLE weatherflowdb.forecastDS_daily (
	datestamp DATE NOT NULL,
	description varchar(45) DEFAULT NULL,
	imagename varchar(45) DEFAULT NULL,
	tempMax decimal(4,1) DEFAULT NULL,
	tempMin decimal(4,1) DEFAULT NULL,
	precipitation decimal(4,1) DEFAULT NULL,
  precipProbability INT(11) DEFAULT NULL,
	windSpeed decimal(4,1) DEFAULT NULL,
	windDirection varchar(3) DEFAULT NULL,
	avgbearing int(11) DEFAULT NULL,
	pressure decimal(6,1) DEFAULT NULL,
  uvindex int(11) DEFAULT NULL,
	copyrighttext varchar(100) DEFAULT "",
	copyrighturl varchar(100) DEFAULT "",
  PRIMARY KEY (datestamp));

/* Create table forecastDS_daily - Holds the daily Dark Sky Hourly forecast */
DROP TABLE IF EXISTS weatherflowdb.forecastDS_hourly;
CREATE TABLE weatherflowdb.forecastDS_hourly (
	datestamp DATETIME NOT NULL,
	description varchar(45) DEFAULT NULL,
	imagename varchar(45) DEFAULT NULL,
	temperature decimal(4,1) DEFAULT NULL,
	precipitation decimal(4,1) DEFAULT NULL,
	precipProbability INT(11) DEFAULT NULL,
	windSpeed decimal(4,1) DEFAULT NULL,
	windDirection varchar(3) DEFAULT NULL,
	avgbearing int(11) DEFAULT NULL,
	pressure decimal(6,1) DEFAULT NULL,
  uvindex int(11) DEFAULT NULL,
	copyrighttext varchar(100) DEFAULT "",
	copyrighturl varchar(100) DEFAULT "",
  PRIMARY KEY (datestamp));

/* Create table forecastWU_daily - Holds the daily Weather Underground Daily forecast */
DROP TABLE IF EXISTS weatherflowdb.forecastWU_daily;
CREATE TABLE weatherflowdb.forecastWU_daily (
	datestamp DATE NOT NULL,
	description varchar(45) DEFAULT NULL,
	imagename varchar(45) DEFAULT NULL,
	tempMax decimal(4,1) DEFAULT NULL,
	tempMin decimal(4,1) DEFAULT NULL,
	precipitation decimal(4,1) DEFAULT NULL,
  precipProbability INT(11) DEFAULT NULL,
	windSpeed decimal(4,1) DEFAULT NULL,
	windDirection varchar(3) DEFAULT NULL,
	avgbearing int(11) DEFAULT NULL,
	pressure decimal(6,1) DEFAULT NULL,
  uvindex int(11) DEFAULT NULL,
	copyrighttext varchar(100) DEFAULT "",
	copyrighturl varchar(100) DEFAULT "",
  PRIMARY KEY (datestamp));

/* Create table forecastWU_hourly - Holds the daily Weather Underground Hourly forecast */
DROP TABLE IF EXISTS weatherflowdb.forecastWU_hourly;
CREATE TABLE weatherflowdb.forecastWU_hourly (
	datestamp DATETIME NOT NULL,
	description varchar(45) DEFAULT NULL,
	imagename varchar(45) DEFAULT NULL,
	temperature decimal(4,1) DEFAULT NULL,
	precipitation decimal(4,1) DEFAULT NULL,
	precipProbability INT(11) DEFAULT NULL,
	windSpeed decimal(4,1) DEFAULT NULL,
	windDirection varchar(3) DEFAULT NULL,
	avgbearing int(11) DEFAULT NULL,
	pressure decimal(6,1) DEFAULT NULL,
  uvindex int(11) DEFAULT NULL,
	copyrighttext varchar(100) DEFAULT "",
	copyrighturl varchar(100) DEFAULT "",
  PRIMARY KEY (datestamp));

/* Create table realtime - holds the latest record from the other tables,
   and is updated by triggers on these 3 tables  */
DROP TABLE IF EXISTS weatherflowdb.realtime;
CREATE TABLE weatherflowdb.realtime (
  idx INT NOT NULL,
  datestamp DATETIME NULL,
  temp DECIMAL(4,1) NULL,
  hum INT NULL,
  dew DECIMAL(4,1) NULL,
  wspeed DECIMAL(4,1) NULL,
  wlatest DECIMAL(4,1) NULL,
  bearing INT NULL,
  rrate DECIMAL(5,2) NULL,
  rfall DECIMAL(4,1) NULL,
  press DECIMAL(6,1) NULL,
  currentwdir VARCHAR(3) NULL,
  beaufortnumber INT NULL,
  windunit VARCHAR(3) NULL,
  tempunit VARCHAR(3) NULL,
  pressunit VARCHAR(3) NULL,
  rainunit VARCHAR(3) NULL,
  windrun DECIMAL(4,1) NULL,
  presstrendval DECIMAL(4,1) NULL,
  rmonth DECIMAL(4,1) NULL,
  ryear DECIMAL(4,1) NULL,
  rfallY DECIMAL(4,1) NULL,
  intemp DECIMAL(4,1) NULL DEFAULT 0,
  inhum INT NULL DEFAULT 0,
  wchill DECIMAL(4,1) NULL,
  temptrend DECIMAL(4,1) NULL,
  tempTH DECIMAL(4,1) NULL,
  TtempTH VARCHAR(5) NULL,
  tempTL DECIMAL(4,1) NULL,
  TtempTL VARCHAR(5) NULL,
  windTM DECIMAL(4,1) NULL,
  TwindTM VARCHAR(5) NULL,
  wgustTM DECIMAL(4,1) NULL,
  TwgustTM VARCHAR(5) NULL,
  pressTH DECIMAL(6,1) NULL,
  TpressTH VARCHAR(5) NULL,
  pressTL DECIMAL(6,1) NULL,
  TpressTL VARCHAR(5) NULL,
  version VARCHAR(5) NULL,
  build VARCHAR(5) NULL,
  wgust DECIMAL(4,1) NULL,
  heatindex DECIMAL(4,1) NULL,
  humidex DECIMAL(4,1) NULL DEFAULT 0,
  uv DECIMAL(3,1) NULL,
  ET DECIMAL(4,1) NULL DEFAULT 0 COMMENT 'Don’t know how to calculate this yet',
  SolarRad INT NULL,
  avgbearing INT NULL,
  rhour DECIMAL(4,1) NULL,
  forecastnumber INT NULL DEFAULT 0,
  isdaylight TINYINT NULL DEFAULT 0,
  SensorContactLost TINYINT NULL DEFAULT 0,
  wdir VARCHAR(3) NULL,
  cloudbasevalue DECIMAL(6,1) NULL,
  cloudbaseunit VARCHAR(3) NULL,
  apptemp DECIMAL(4,1) NULL,
  SunshineHours DECIMAL(4,1) NULL DEFAULT 0,
  CurrentSolarMax DECIMAL(6,1) NULL DEFAULT 0 COMMENT 'Don’t know how to calculate this yet',
  IsSunny TINYINT NULL DEFAULT 0,
  feelslike DECIMAL(4,1) NULL,
  lux INT NULL,
  PRIMARY KEY (idx));

  /* Insert 1 record in to the realtime table */
  INSERT INTO weatherflowdb.realtime (idx, windunit, tempunit, pressunit, rainunit, rfallY, version, build, humidex, cloudbaseunit) VALUES (1,"m/s","C","hPa","mm",0,"0.0.2","Beta", 0,"m");


  /* Create Triggers for Realtime table - AFTER INSERT */
  /*****************************************************/

  /* rapid_wind Trigger */
  DROP TRIGGER IF EXISTS `weatherflowdb`.`rapid_wind_AFTER_INSERT`;

  DELIMITER $$
  USE `weatherflowdb`$$
  CREATE DEFINER = CURRENT_USER TRIGGER `weatherflowdb`.`rapid_wind_AFTER_INSERT` AFTER INSERT ON `rapid_wind` FOR EACH ROW
  BEGIN
    UPDATE `weatherflowdb`.`realtime`
  	SET
      `datestamp` = NEW.datestamp,
      `wlatest` = NEW.wind_speed,
      `bearing` = NEW.wind_direction,
      `currentwdir` = NEW.wind_direction_symbol

  	WHERE `idx` = 1;
  END$$
  DELIMITER ;

  /* air_observations Trigger */
  DROP TRIGGER IF EXISTS `weatherflowdb`.`air_observations_AFTER_INSERT`;

  DELIMITER $$
  USE `weatherflowdb`$$
  CREATE DEFINER=`root`@`localhost` TRIGGER `weatherflowdb`.`air_observations_AFTER_INSERT` AFTER INSERT ON `air_observations` FOR EACH ROW
  thisTrigger: BEGIN

  	IF (@TRIGGER_ENABLE = FALSE)
  	THEN
          LEAVE thisTrigger;
  	END IF;

      SET @oldTempValue = (SELECT temperature FROM air_observations WHERE DATE_FORMAT(air_observations.datestamp, '%Y-%m-%d %H:%i') >= DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i'), INTERVAL 1 HOUR) LIMIT 1);
      SET @newTempValue = (SELECT temperature FROM air_observations ORDER BY datestamp DESC LIMIT 1);
      SET @temptrend = @newTempValue - @oldTempValue;

      SET @oldPressValue = (SELECT pressure FROM air_observations WHERE DATE_FORMAT(air_observations.datestamp, '%Y-%m-%d %H:%i') >= DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i'), INTERVAL 3 HOUR) LIMIT 1);
      SET @newPressValue = (SELECT pressure FROM air_observations ORDER BY datestamp DESC LIMIT 1);
      SET @presstrendval = @newPressValue - @oldPressValue;

      UPDATE `weatherflowdb`.`realtime`
    	SET
      `datestamp` = NEW.datestamp,
      `temp` = NEW.temperature,
      `dew` = NEW.dewpoint,
      `press` = NEW.barometer,
      `hum` = NEW.humidity,
      `heatindex` = NEW.heatindex,
      `wchill` = NEW.windchill,
      `feelslike` = NEW.feelslike,
      `apptemp` = NEW.apptemp,
      `cloudbasevalue` = NEW.cloudbase,
      `temptrend` = @temptrend,
      `presstrendval` = @presstrendval
    	WHERE `idx` = 1;
    END$$
  DELIMITER ;

  /* sky_observations Trigger */
  DROP TRIGGER IF EXISTS `weatherflowdb`.`sky_observations_AFTER_INSERT`;

  DELIMITER $$
  USE `weatherflowdb`$$
  CREATE DEFINER=`root`@`localhost` TRIGGER `weatherflowdb`.`sky_observations_AFTER_INSERT` AFTER INSERT ON `sky_observations` FOR EACH ROW
    thisTrigger: BEGIN

  	IF (@TRIGGER_ENABLE = FALSE)
  	THEN
          LEAVE thisTrigger;
  	END IF;

      SET @recordcount = (SELECT COUNT(datestamp) AS records FROM sky_observations WHERE DATE(datestamp) = CURDATE());
      SET @windrunkm = (SELECT SUM(IFNULL(wind_avg,0)) AS records FROM sky_observations WHERE DATE(datestamp) = CURDATE());
      SET @windrun = ((@windrunkm*3.6)/@recordcount)*@recordcount/60;

      UPDATE `weatherflowdb`.`realtime`
      SET
      `datestamp` = NEW.datestamp,
      `lux` = NEW.illuminance,
      `uv` = NEW.uv,
      `rrate` = NEW.rain_rate,
      `rfall` = (SELECT SUM(sky_observations.rain_minute) FROM sky_observations WHERE DATE(datestamp) = DATE(NOW())),
      `wspeed` = NEW.wind_avg,
      `wgust` = NEW.wind_gust,
      `avgbearing` = NEW.wind_dir_avg,
      `wdir` = NEW.wind_dir_sym,
      `SolarRad` = NEW.solarrad,
      `beaufortnumber` = NEW.beaufort,
      `windrun` = @windrun,
      `ryear` = (SELECT SUM(sky_observations.rain_minute) FROM sky_observations WHERE DATE_FORMAT(datestamp,'%Y') = date_format(current_date(),'%Y')),
      `rfallY` = (SELECT SUM(sky_observations.rain_minute) FROM sky_observations WHERE DATE(datestamp) = date(DATE_SUB(NOW(), INTERVAL 1 DAY))),
      `rhour` = (SELECT SUM(sky_observations.rain_minute) FROM sky_observations WHERE DATE_FORMAT(datestamp, '%Y-%m-%d %H:%i') >= DATE_FORMAT(DATE_SUB(NOW(),INTERVAL 1 HOUR),'%Y-%m-%d %H:%i')),
      `rmonth` = (SELECT SUM(sky_observations.rain_minute) FROM sky_observations WHERE DATE_FORMAT(datestamp,'%Y-%m') = date_format(current_date(),'%Y-%m')),
      `SunshineHours` = (SELECT count(illuminance)/60 FROM sky_observations WHERE illuminance >32000 AND DATE(datestamp) = CURDATE()),
      `IsSunny` = (CASE WHEN (NEW.illuminance > 32000) THEN 1 ELSE 0 END)
      WHERE `idx` = 1;


    END$$
  DELIMITER ;

/* forecastWU_hourly Trigger - update UV in daily table */
DROP TRIGGER IF EXISTS `weatherflowdb`.`forecastWU_hourly_AFTER_UPDATE`;

DELIMITER $$
USE `weatherflowdb`$$
CREATE DEFINER=`root`@`localhost` TRIGGER `weatherflowdb`.`forecastWU_hourly_AFTER_UPDATE` AFTER UPDATE ON `forecastWU_hourly` FOR EACH ROW
BEGIN
    UPDATE `weatherflowdb`.`forecastWU_daily`
  	SET
    `uvindex` = (SELECT max(uvindex) FROM weatherflowdb.forecastWU_hourly WHERE DATE(datestamp) = DATE(NEW.datestamp))
  	WHERE `datestamp` = DATE(NEW.datestamp);
END$$
DELIMITER ;

/* daily summary stored procedure */
USE `weatherflowdb`;
DROP procedure IF EXISTS `dailySummaryProcess`;

DELIMITER $$
USE `weatherflowdb`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `dailySummaryProcess`(sunrisetime DATETIME, sunsettime DATETIME, sunrisetimeTom DATETIME)
BEGIN

	SET @tempTH = (SELECT MAX(`weatherflowdb`.`air_observations`.`temperature`) FROM `weatherflowdb`.`air_observations` WHERE DATE(datestamp) = DATE(NOW()));
    SET @tempTL = (SELECT MIN(`weatherflowdb`.`air_observations`.`temperature`) FROM `weatherflowdb`.`air_observations` WHERE DATE(datestamp) = DATE(NOW()));
    SET @TtempTH = (SELECT DATE_FORMAT(`weatherflowdb`.`air_observations`.`datestamp`, '%H:%i') FROM `weatherflowdb`.`air_observations` WHERE (`weatherflowdb`.`air_observations`.`temperature` = (SELECT MAX(`weatherflowdb`.`air_observations`.`temperature`) FROM `weatherflowdb`.`air_observations` WHERE DATE(datestamp) = DATE(NOW()))) AND DATE(datestamp) = DATE(NOW()) ORDER BY `weatherflowdb`.`air_observations`.`datestamp` DESC LIMIT 1);
    SET @TtempTL = (SELECT DATE_FORMAT(`weatherflowdb`.`air_observations`.`datestamp`, '%H:%i') FROM `weatherflowdb`.`air_observations` WHERE (`weatherflowdb`.`air_observations`.`temperature` = (SELECT MIN(`weatherflowdb`.`air_observations`.`temperature`) FROM `weatherflowdb`.`air_observations` WHERE DATE(datestamp) = DATE(NOW()))) AND DATE(datestamp) = DATE(NOW()) ORDER BY `weatherflowdb`.`air_observations`.`datestamp` ASC LIMIT 1);
	SET @pressTH = (SELECT MAX(`weatherflowdb`.`air_observations`.`barometer`) FROM `weatherflowdb`.`air_observations` WHERE DATE(datestamp) = DATE(NOW()));
    SET @pressTL = (SELECT MIN(`weatherflowdb`.`air_observations`.`barometer`) FROM `weatherflowdb`.`air_observations` WHERE DATE(datestamp) = DATE(NOW()));
    SET @TpressTH = (SELECT DATE_FORMAT(`weatherflowdb`.`air_observations`.`datestamp`, '%H:%i') FROM `weatherflowdb`.`air_observations` WHERE (`weatherflowdb`.`air_observations`.`barometer` = (SELECT MAX(`weatherflowdb`.`air_observations`.`barometer`) FROM `weatherflowdb`.`air_observations` WHERE DATE(datestamp) = DATE(NOW()))) AND DATE(datestamp) = DATE(NOW()) ORDER BY `weatherflowdb`.`air_observations`.`datestamp` DESC LIMIT 1);
    SET @TpressTL = (SELECT DATE_FORMAT(`weatherflowdb`.`air_observations`.`datestamp`, '%H:%i') FROM `weatherflowdb`.`air_observations` WHERE (`weatherflowdb`.`air_observations`.`barometer` = (SELECT MIN(`weatherflowdb`.`air_observations`.`barometer`) FROM `weatherflowdb`.`air_observations` WHERE DATE(datestamp) = DATE(NOW()))) AND DATE(datestamp) = DATE(NOW()) ORDER BY `weatherflowdb`.`air_observations`.`datestamp` ASC LIMIT 1);
	SET @windTM = (SELECT MAX(`weatherflowdb`.`sky_observations`.`wind_avg`) FROM `weatherflowdb`.`sky_observations` WHERE DATE(datestamp) = DATE(NOW()));
    SET @TwindTM = (SELECT DATE_FORMAT(`weatherflowdb`.`sky_observations`.`datestamp`, '%H:%i') FROM `weatherflowdb`.`sky_observations` WHERE (`weatherflowdb`.`sky_observations`.`wind_avg` = (SELECT MAX(`weatherflowdb`.`sky_observations`.`wind_avg`) FROM `weatherflowdb`.`sky_observations` WHERE DATE(datestamp) = DATE(NOW()))) AND DATE(datestamp) = DATE(NOW()) ORDER BY `weatherflowdb`.`sky_observations`.`datestamp` DESC LIMIT 1);
	SET @wgustTM = (SELECT MAX(`weatherflowdb`.`sky_observations`.`wind_gust`) FROM `weatherflowdb`.`sky_observations` WHERE DATE(datestamp) = DATE(NOW()));
    SET @TwgustTM = (SELECT DATE_FORMAT(`weatherflowdb`.`sky_observations`.`datestamp`, '%H:%i') FROM `weatherflowdb`.`sky_observations` WHERE (`weatherflowdb`.`sky_observations`.`wind_gust` = (SELECT MAX(`weatherflowdb`.`sky_observations`.`wind_gust`) FROM `weatherflowdb`.`sky_observations` WHERE DATE(datestamp) = DATE(NOW()))) AND DATE(datestamp) = DATE(NOW()) ORDER BY `weatherflowdb`.`sky_observations`.`datestamp` DESC LIMIT 1);
	SET @Strike3Hour = (SELECT COUNT(datestamp) FROM strike_events WHERE DATE_FORMAT(strike_events.datestamp, '%Y-%m-%d %H:%i') >= DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i'), INTERVAL 3 HOUR) LIMIT 1);
  SET @uvTM = (SELECT MAX(`weatherflowdb`.`sky_observations`.`uv`) FROM `weatherflowdb`.`sky_observations` WHERE DATE(datestamp) = DATE(NOW()));
    SET @TuvTM = (SELECT DATE_FORMAT(`weatherflowdb`.`sky_observations`.`datestamp`, '%H:%i') FROM `weatherflowdb`.`sky_observations` WHERE (`weatherflowdb`.`sky_observations`.`uv` = (SELECT MAX(`weatherflowdb`.`sky_observations`.`uv`) FROM `weatherflowdb`.`sky_observations` WHERE DATE(datestamp) = DATE(NOW()))) AND DATE(datestamp) = DATE(NOW()) ORDER BY `weatherflowdb`.`sky_observations`.`datestamp` DESC LIMIT 1);
  SET @solarradTM = (SELECT MAX(`weatherflowdb`.`sky_observations`.`solarrad`) FROM `weatherflowdb`.`sky_observations` WHERE DATE(datestamp) = DATE(NOW()));
    SET @TsolarradTM = (SELECT DATE_FORMAT(`weatherflowdb`.`sky_observations`.`datestamp`, '%H:%i') FROM `weatherflowdb`.`sky_observations` WHERE (`weatherflowdb`.`sky_observations`.`solarrad` = (SELECT MAX(`weatherflowdb`.`sky_observations`.`solarrad`) FROM `weatherflowdb`.`sky_observations` WHERE DATE(datestamp) = DATE(NOW()))) AND DATE(datestamp) = DATE(NOW()) ORDER BY `weatherflowdb`.`sky_observations`.`datestamp` DESC LIMIT 1);


	INSERT INTO `weatherflowdb`.`daily_summary` (LogDate, datestamp, tempTH, TtempTH, tempTL, TtempTL, windTM, TwindTM, wgustTM, TwgustTM, pressTH, TpressTH, pressTL, TpressTL, uvTM, TuvTM, solarTM, TsolarTM, strike3hour, sunrise, sunset, sunriseTom) VALUES
    (DATE(NOW()), NOW(), @tempTH, @TtempTH, @tempTL, @TtempTL, @windTM, @TwindTM, @wgustTM, @TwgustTM, @pressTH, @TpressTH, @pressTL, @TpressTL, @uvTM, @TuvTM, @solarTM, @TsolarTM, @Strike3Hour, sunrisetime, sunsettime, sunrisetimeTom)
    ON DUPLICATE KEY UPDATE
    datestamp = NOW(), tempTH = @tempTH, TtempTH = @TtempTH, tempTL = @tempTL, TtempTL = @TtempTL, pressTH = @pressTH, TpressTH = @TpressTH, pressTL = @pressTL, TpressTL = @TpressTL, windTM = @windTM, TwindTM = @TwindTM, wgustTM = @wgustTM, TwgustTM = @TwgustTM, uvTM = @uvTM, TuvTM = @TuvTM, solarTM = @solarradTM, TsolarTM = @TsolarradTM, strike3hour = @Strike3Hour, sunrise = sunrisetime, sunset = sunsettime, sunriseTom = sunrisetimeTom;


	UPDATE `weatherflowdb`.`realtime`
		SET
        datestamp = NOW(),
        tempTH = @tempTH,
        TtempTH = @TtempTH,
        tempTL = @tempTL,
        TtempTL = @TtempTL,
        pressTH = @pressTH,
        TpressTH = @TpressTH,
        pressTL = @pressTL,
        TpressTL = @TpressTL,
        windTM = @windTM,
        TwindTM = @TwindTM,
        wgustTM = @wgustTM,
        TwgustTM = @TwgustTM,
		isdaylight = (CASE WHEN (sunrisetime < NOW() AND sunsettime > NOW()) THEN 1 ELSE 0 END)
WHERE `idx` = 1;


END$$

DELIMITER ;

/* Views - rain_pr_hour_view */
CREATE VIEW `weatherflowdb`.`rain_pr_hour_view` AS
select
`weatherflowdb`.`sky_observations`.`datestamp` AS `datestamp`,
cast(`weatherflowdb`.`sky_observations`.`datestamp` as date) AS `day_stamp`,
hour(`weatherflowdb`.`sky_observations`.`datestamp`) AS `hour_stamp`,
sum(`weatherflowdb`.`sky_observations`.`rain_minute`) AS `rain_total`,
max(`weatherflowdb`.`sky_observations`.`rain_rate`) AS `rain_rate`
from `weatherflowdb`.`sky_observations`
group by `day_stamp`,`hour_stamp`
order by `day_stamp`,`hour_stamp`;

/* Views - rain_pr_day_view */
CREATE  VIEW `weatherflowdb`.`rain_pr_day_view` AS
select
`t1`.`datestamp` AS `datestamp`,
`t1`.`day_stamp` AS `day_stamp`,
`t1`.`hour_stamp` AS `hour_stamp`,
`t1`.`rain_total` AS `rain_total`,
`t1`.`rain_rate` AS `rain_rate`,
sum(`t2`.`rain_total`) AS `rain_accum`
from (`weatherflowdb`.`rain_pr_hour_view` `t1`
  join `weatherflowdb`.`rain_pr_hour_view` `t2`
  on(((`t2`.`day_stamp` = `t1`.`day_stamp`) and (`t2`.`hour_stamp` <= `t1`.`hour_stamp`))))
  group by `t1`.`day_stamp`,`t1`.`hour_stamp`
  order by `t1`.`day_stamp`,`t1`.`hour_stamp`;

  /* Views - strikes_pr_day */
 USE `weatherflowdb`;
 CREATE  OR REPLACE VIEW `strikes_pr_day` AS
 SELECT
 	CAST(datestamp
             AS DATE) AS day_stamp,
 	COUNT(datestamp) AS strikes
 FROM weatherflowdb.strike_events
 GROUP BY day_stamp
 ORDER BY day_stamp;

/* maintenanceProcess stored procedure */
USE `weatherflowdb`;
DROP procedure IF EXISTS `maintenanceProcess`;

DELIMITER $$
USE `weatherflowdb`$$
CREATE PROCEDURE `maintenanceProcess` ()
BEGIN

	/* This procedure performs daily maintenance tasks */
	/************************************************************/

	/* Delete records from the rapid_wind table that are more than 2 days old */
    DELETE FROM rapid_wind WHERE DATE(datestamp) = date(DATE_SUB(NOW(), INTERVAL 2 DAY));

END$$

DELIMITER ;

USE `weatherflowdb`;
DROP procedure IF EXISTS `updateversionProcess`;

DELIMITER $$
USE `weatherflowdb`$$
CREATE PROCEDURE `updateversionProcess` (newversion VARCHAR(5), newbuild VARCHAR(5))
BEGIN
	UPDATE realtime SET version = newversion, build = newbuild WHERE idx = 1;
END$$

DELIMITER ;

/* Function to Calculate WindChill - Only used by the load_history program */
/************************************************************/
USE `weatherflowdb`;
DROP function IF EXISTS `getWindChill`;

DELIMITER $$
USE `weatherflowdb`$$
CREATE FUNCTION `getWindChill`(tempC DECIMAL(4,1), windMs DECIMAL(4,1)) RETURNS decimal(4,1) DETERMINISTIC
BEGIN
  SET @tempF = (9/5 * tempC)+32;
  SET @windMph = (windMs / 0.44704);
  SET @windChillF = 35.74 + (0.6215 * @tempF) - (35.75 * pow(@windMph,0.16)) + (0.4275 * @tempF * pow(@windMph,0.16));

  RETURN (@windChillF - 32) * 5/9;

END$$

DELIMITER ;

/* Function to Calculate Feels Like Temperature - Only used by the load_history program */
/************************************************************/
USE `weatherflowdb`;
DROP function IF EXISTS `getFeelsLike`;

DELIMITER $$
USE `weatherflowdb`$$
CREATE FUNCTION `getFeelsLike`(tempC DECIMAL(4,1), wchillC DECIMAL(4,1), heatIndexC DECIMAL(4,1)) RETURNS decimal(4,1) DETERMINISTIC
BEGIN

	CASE
		WHEN tempC < 10 THEN RETURN wchillC;
        WHEN tempC > 26.66 THEN RETURN heatIndexC;
        ELSE RETURN tempC;
	END CASE;

END$$

DELIMITER ;

/* Function to Calculate Apparent Temperature - Only used by the load_history program */
/************************************************************/
USE `weatherflowdb`;
DROP function IF EXISTS `getApparentTemp`;

DELIMITER $$
USE `weatherflowdb`$$
CREATE FUNCTION `getApparentTemp`(tempC DECIMAL(4,1), windMs DECIMAL(4,1), hum INT) RETURNS decimal(4,1) DETERMINISTIC
BEGIN

  SET @vapour = hum / 100*6.105*exp(17.27*tempC/(237.7+tempC));
  RETURN tempC + 0.33*@vapour - 0.7*windMs - 4;

END$$

DELIMITER ;

/* Procedure to Update Values after History Load - Only used by the load_history program */
/************************************************************/
USE `weatherflowdb`;
DROP procedure IF EXISTS `updateHistoryData`;

DELIMITER $$
USE `weatherflowdb`$$
CREATE PROCEDURE `updateHistoryData`()
BEGIN
	/* Start by updating Wind Chill and Apparent Tempterature */
	UPDATE air_observations  INNER JOIN sky_observations
		ON air_observations.datestamp = sky_observations.datestamp
	SET air_observations.windchill = getWindChill(air_observations.temperature, sky_observations.wind_gust),
			air_observations.apptemp = getApparentTemp(air_observations.temperature, sky_observations.wind_avg, air_observations.humidity);

	/* Now that we have Wind Chill - Update Feels Like*/
	UPDATE air_observations
	SET feelslike = getFeelsLike(temperature, windchill,heatindex);
END$$

DELIMITER ;

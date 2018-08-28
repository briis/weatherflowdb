<?php
// WeatherFlowDB is a Program to capture data using UDP from a WeatherFlow Weather Station
// All data is stored in a MYSQL database and can be retrieved from there
// Make sure you have created the database before executing this script by running the following command:
// mysql -u root -p > createdatabase.sql
// Author: Bjarne Riis
// Last Updated: 19 jul 2018
// Filename: dailysummary.php
// - This part of the program must run 1 time a day. Just after midnight would be a good time
//   It cleans up the database and removes records no longer used, to prevent the database to grow too big

// Include the settings file
include('settings.php');

// Setup Error Logging
$errorFile = $logdirectory.'weatherflowdb-maintenance-errors.log';
error_reporting(E_ALL);
ini_set('log_errors','1');
ini_set('display_errors','0');
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

// Execute the Stored Procedure on the Database Server
try {
    $SQL = "CALL maintenanceProcess(); ";

    //Insert the Record in to the Database
    if ($conn->query($SQL) != TRUE) {
      error_log('Excution of maintenanceProcess failed: '.$conn->error);
    }

} catch (\Exception $e) {
  error_log($e->getMessage());
}

// Close Database connection
mysqli_close($conn);
?>

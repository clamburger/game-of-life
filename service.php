<?php
require_once("files.php");
chdir(dirname(__FILE__));

date_default_timezone_set("Australia/Brisbane");

// Load the config file
$config = json_decode(file_get_contents($configFile), true);
extract($config);

echo "\n";
echo "=======================\n";
echo "=  THE GAME OF LIFE   =\n";
echo "=   Updater Service   =\n";
echo "=======================\n";
echo "\n";
echo "Update interval is every $updateInterval seconds.\n"; 
echo "Current time: ".date("H:i:s")."\n";

while (true) { 
  // This handy line of code gets the next update by using mathemagics
  // If the current time is already on an interval, it will give the next interval instead
  $nextUpdate = ceil((time()+1)/$updateInterval) * $updateInterval;

  echo "Next update: ".date("H:i:s", $nextUpdate)."\n";

  time_sleep_until($nextUpdate);
  
  // Load property data
  $properties = json_decode(file_get_contents($propertyFile));
  
  echo "\n";
  echo "Updating properties...\n";
  foreach ($properties as &$property) {
    if ($property->Destroyed) {
      continue;
    }
    $oldValue = $property->Value;
    $modifier = rand($minChange, $maxChange);
    $newValue = round($oldValue * ($modifier / 100), -3);
    echo sprintf("  %s %+d%% = $%d\n", $property->Name, $modifier - 100, $newValue);
    $property->Value = $newValue;
  }

  file_put_contents($propertyFile, json_encode($properties, JSON_PRETTY_PRINT));
  
  $eventLog = json_decode(file_get_contents($eventLogFile), true);
  $eventLog[] = ["Action" => "update", "Message" => "Property prices have changed.", "Time" => time()];
  file_put_contents($eventLogFile, json_encode($eventLog, JSON_PRETTY_PRINT));
}

?> 
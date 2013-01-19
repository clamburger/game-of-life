<?php
require_once("files.php");
$config = json_decode(file_get_contents($configFile));
$properties = json_decode(file_get_contents($propertyFile));
$users = json_decode(file_get_contents($userFile), true);
$destroyed = json_decode(file_get_contents($destroyedFile), true);

function error($msg) {
  echo json_encode(array("result" => "error", "error" => $msg));
  exit;
}

function valueSort($a, $b) {
  if ($a->Value == $b->Value) {
    return 0;
  }
  return $a->Value < $b->Value ? -1 : 1;
}

function overviewSort($a, $b) {
  if ($a->Destroyed && !$b->Destroyed) {
    return 1;
  } else if (!$a->Destroyed && $b->Destroyed) {
    return -1;
  }
  
  if ($a->Owner && !$b->Owner) {
    return -1;
  } else if (!$a->Owner && $b->Owner) {
    return 1;
  }
  
  if ($a->Location != $b->Location) {
    return strcmp($a->Location, $b->Location);
  }
  
  if ($a->Value == $b->Value) {
    return 0;
  }
  return $a->Value > $b->Value ? -1 : 1;
}

if (!isset($_GET['action'])) {
  error("No action specified.");
}

$action = $_GET['action'];
if ($action == "page-load") {
  $userList = $users;
  sort($userList);
  $locations = [];
  foreach ($properties as $property) {
    $locations[] = $property->Location;
  }
  $locations = array_unique($locations);
  sort($locations);
  echo json_encode(array("result" => "success", "config" => $config, "users" => $userList, "locations" => $locations));
} else if ($action == "load-lottery") {
  $lottery = json_decode(file_get_contents($lotteryFile), true);
  $userList = $users;
  sort($userList);
  echo json_encode(array("result" => "success", "users" => $userList, "lottery" => $lottery));
} else if ($action == "get-config") {
  echo json_encode(array("result" => "success", "config" => $config));
} else if ($action == "view") {
  echo json_encode(array("result" => "success", "properties" => $properties));
} else if ($action == "view-overview") {
  $properties = (array)$properties;
  uasort($properties, "overviewSort");
  echo json_encode(array("result" => "success", "properties" => $properties));
} else if ($action == "view-available") {
  $available = [];
  foreach ($properties as $ID => $property) {
    if (!$property->Owner && !$property->Destroyed) {
      $available[$ID] = $property;
    }
  }
  uasort($available, "valueSort");
  echo json_encode(array("result" => "success", "properties" => $available));
} else if ($action == "view-owned") {
  $owned = [];
  $skipped = false;
  foreach ($properties as $ID => $property) {
    if ($property->Owner === $_GET['owner']) {
      if (isset($_GET['insurable']) && $property->Destroyed) {
        $skipped = true;
        continue;
      }
      $owned[$ID] = $property;
    }
  }
  uasort($owned, "valueSort");
  echo json_encode(array("result" => "success", "properties" => $owned, "skipped" => $skipped));
} else if ($action == "buy") {
  $ID = $_POST['id'];
  $buyer = $_POST['user'];
  if (!isset($properties->$ID)) {
    error("Property $ID doesn't exist!");
  } else if (!in_array($buyer, $users)) {
    error("Couldn't find $buyer's account.");
  }/* else if ($_POST['value'] > $users[$buyer]) {
    error("$buyer can't afford that (balance: $".number_format($users[$buyer]).")");
  }*/
  $prop =& $properties->$ID;
  if ($prop->Owner) {
    error("Property is already owned by {$prop->Owner}.");
  } else if ($prop->Destroyed) {
    error("Property has been destroyed by {$prop->Destroyed}.");
  }
  $prop->Owner = $buyer;
  $prop->Value = $_POST['value'];
  //$users[$buyer] -= $_POST['value'];
  updatePropertyFile();
  //updateUserFile();
  event("buy", "<strong>$buyer</strong> bought <strong>{$prop->Name}</strong> for <strong>$".number_format($_POST['value'])."</strong>");
  echo json_encode(array("result" => "success"));
  
} else if ($action == "sell") {
  $ID = $_POST['id'];
  $seller = $_POST['user'];
  if (!isset($properties->$ID)) {
    error("Property $ID doesn't exist!");
  } else if (!in_array($seller, $users)) {
    error("Couldn't find $seller's account.");
  }
  $prop =& $properties->$ID;
  if ($prop->Owner != $_POST['user']) {
    error("{$_POST['user']} doesn't own that property.");
  } else if ($prop->Destroyed && !in_array($prop->Destroyed, $prop->Insurance)) {
    error("Property is destroyed and doesn't have {$prop->Destroyed} insurance.");
  }
  $prop->Owner = false;
  $prop->Insurance = [];
  //$users[$seller] += $_POST['value'];
  updatePropertyFile();
  //updateUserFile();
  if ($prop->Destroyed) {
    event("insurance-claim", "<strong>{$_POST['user']}</strong> claimed <strong>$".number_format($_POST['value'])."</strong> on insurance from the destruction of <strong>{$prop->Name}</strong>.");
  } else {
    event("sell", "<strong>{$_POST['user']}</strong> sold <strong>{$prop->Name}</strong> for <strong>$".number_format($_POST['value'])."</strong>");
  }
  echo json_encode(array("result" => "success"));
} else if ($action == "disaster") {
  $location = $_POST['location'];
  $disaster = $_POST['disaster'];
  if (isset($destroyed[$location])) {
    error("$location has already been destroyed by {$destroyed[$location]}.");
  }
  $count = 0;
  foreach ($properties as &$property) {
    if ($property->Location === $location && !$property->Destroyed) {
      $property->Destroyed = $disaster;
      $count++  ;
    }
  }
  unset($property);
  updatePropertyFile();
  $destroyed[$location] = $disaster;
  file_put_contents($destroyedFile, json_encode($destroyed));
  
  $msg = "<strong>It's a disaster!</strong> ";
  if ($disaster == "fire") {
    $msg .= "Vicious fires sweep across <strong>$location</strong>, ";
  } else if ($disaster == "earthquake") {
    $msg .= "<strong>$location</strong> has been struck by an earthquake, ";
  } else if ($disaster == "tornado") {
    $msg .= "An F5-rated tornado has passed directly over <strong>$location</strong>, ";
  } else if ($disaster == "Godzilla") {
    $msg .= "Godzilla has awoken in <strong>$location</strong>, causing choas and ";
  }
  $msg .= "destroying all <strong>$count properties</strong> located there.";
  event("disaster", $msg);
  echo json_encode(array("result" => "success"));
} else if ($action == "insurance") {
  $ID = $_POST['id'];
  $types = $_POST['types'];
  $buyer = $_POST['user'];
  if (!isset($properties->$ID)) {
    error("Property $ID doesn't exist!");
  } else if (!in_array($buyer, $users)) {
    error("Couldn't find $buyer's account.");
  }/* else if ($_POST['cost'] > $users[$buyer]) {
    error("$buyer can't afford that (balance: $".number_format($users[$buyer]).")");
  }*/
  $prop =& $properties->$ID;
  if ($prop->Owner != $buyer) {
    error("$buyer doesn't own that property.");
  } else if ($prop->Destroyed) {
    error("Can't insure destroyed property.");
  } else {
    foreach ($types as $type) {
      if (in_array($type, $prop->Insurance)) {
        error("{$prop->Name} already has ".$type." insurance.");
      }
    }
  }
  
  foreach ($types as $type) {
    $prop->Insurance[] = $type;
  }
  //$users[$buyer] -= $_POST['cost'];
  updatePropertyFile();
  //updateUserFile();
  event("insurance", "<strong>{$_POST['user']}</strong> purchased insurance for <strong>{$prop->Name}</strong> for <strong>$".number_format($_POST['cost']).".</strong>");
  echo json_encode(array("result" => "success"));
} else if ($action == "view-log") {
  $eventLog = json_decode(file_get_contents($eventLogFile), true);
  $eventLog = array_reverse($eventLog);
  echo json_encode(array("result" => "success", "log" => $eventLog));
} else if ($action == "lottery-reset") {
  file_put_contents($lotteryFile, "{}");
  echo json_encode(array("result" => "success"));
} else if ($action == "lottery-add") {
  $lottery = json_decode(file_get_contents($lotteryFile), true);
  if (!isset($lottery[$_POST['user']])) {
    $lottery[$_POST['user']] = 0;
  }
  $lottery[$_POST['user']] += $_POST['tickets'];
  file_put_contents($lotteryFile, json_encode($lottery, JSON_PRETTY_PRINT));
  echo json_encode(array("result" => "success"));
} else {
  error("Invalid action specified.");
}

function updatePropertyFile() {
  global $propertyFile, $properties;
  file_put_contents($propertyFile, json_encode($properties, JSON_PRETTY_PRINT));
}

function updateUserFile() {
  global $userFile, $users;
  file_put_contents($userFile, json_encode($users, JSON_PRETTY_PRINT));
}

function event($action, $msg) {
  global $eventLogFile;
  
  $eventLog = json_decode(file_get_contents($eventLogFile), true);
  $eventLog[] = ["Action" => $action, "Message" => $msg, "Time" => time()];
  file_put_contents($eventLogFile, json_encode($eventLog, JSON_PRETTY_PRINT));
}
?>
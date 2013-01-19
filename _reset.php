<?php
chdir(dirname(__FILE__));

$array = [
[ 375000, "Brisbane City", "5 Smith Road"],
[ 259000, "Acacia Ridge", "604 Beatty Road"],
[ 279000, "Acacia Ridge", "10 Harden St"],
[ 370000, "Albany Creek", "22 Wilson Ave"],
[ 455000, "Albany Creek", "2 Yellowjack Cres"],
[ 839000, "Albion", "5 Wakefield St"],
[ 444000, "Albion", "10 Bale St"],
[ 409000, "Alderley", "80 Alderley Ave"],
[ 460000, "Alderley", "41 Marne St"],
[ 637000, "Alexandra Hills", "64 Hanover Dr"],
[ 399000, "Alexandra Hills", "3 Wetherl Pl"],
[2500000, "Ascot", "76 Towers St"],
[3750000, "Ascot", "4 Yabba St"],
[1490000, "Ascot", "116 Kitchener Rd"],
[6000000, "Ascot", "1 Palm Ave"],
[ 525000, "Ashgrove", "658 Waterworks Rd"],
[1550000, "Ashgrove", "2 Vaux St"],
[ 669000, "Aspley", "63 Petrie Cres"],
[ 799000, "Aspley", "39 Alexis St"],
[ 639000, "Aspley", "24 Cara St"],
[ 595000, "Aspley", "4 Mountview St"],
[ 495000, "Aspley", "531 Robinson Road"],
[ 329000, "Beenleigh", "7 Bullet Ct"],
[ 279000, "Bellara", "51 Bellara St"],
[ 289000, "Bethania", "13 Jessie Cres"],
[ 890000, "Blacksoil", "32 Eleazor Dr"],
[ 308000, "Boronia Heights", "59 Flinders Cres"],
[ 319000, "Boronia Heights", "12 Parkloads Dr"],
[ 299000, "Boronia Heights", "1 Michelle Ct"],
[ 311000, "Boronia Heights", "3 Flinders Cres"],
[ 740000, "Bridgeman Downs", "7 Habitat Pl"],
[ 625000, "Bridgeman Downs", "140 Voyager Cct"],
[ 969000, "Bridgeman Downs", "12 Silverbirch Pl"],
[ 499000, "Bridgeman Downs", "15 Fairhaven St"],
[ 570000, "Bridgeman Downs", "39 Kurrajong Pl"],
[1495000, "Bridgeman Downs", "10 Stuart Cl"],
[2690000, "Bridgeman Downs", "23 Apualia Pl"],
[ 330000, "Caboolture", "10 Sunnymeade Dr"],
[ 685000, "Caboolture", "6 Glady Ave"],
[ 349000, "Caboolture", "9 Williams Rd"],
[ 559000, "Calamvale", "11 Begonia Pl"],
[ 569000, "Calamvale", "11 Chateau St"],
[1288000, "Calamvale", "20 Bottletree Pl"],
[ 339000, "Coomera", "17 Talpha St"],
[ 479000, "Coomera", "24 Esplanade"],
[ 349000, "Coomera", "2 Heron Cl"],
[ 275000, "Coomera", "50 Beattie Rd"],
[ 790000, "Coomera", "22 Marriott St"],
[  55000, "Kingston", "482 Kingston Rd"],
[ 125000, "Russell Island", "21 Vista St"],
[  99000, "Russell Island", "10 Saturn St"],
];

$all = [];

foreach ($array as $house) {
  $id = strtolower(str_replace(" ", "-", preg_replace("/[^A-Za-z0-9 ]/", '', $house[2])));
  $all[$id] = [
    "InitialValue" => $house[0],
    "Value" => $house[0],
    "Location" => $house[1],
    "Name" => $house[2],
    "Owner" => false,
    "Destroyed" => false,
    "Insurance" => []
  ];
}

if (!is_dir("data")) {
  if (!mkdir("data", 0777)) {
    echo "<span style='color: red;'>Error: could not create <code>data</code> directory. Try creating it manually.</span><br>";
  }
}

$toWrite = [
 "properties" => json_encode($all, JSON_PRETTY_PRINT),
 "log" => "[]",
 "destroyed" => "{}",
 "lottery" => "{}"
];

$defaultConfig = [
  "updateInterval" => 60,
  "minChange" => 95,
  "maxChange" => 110
];

if (!file_exists("data/config.json")) {
  $toWrite["config"] = json_encode($defaultConfig, JSON_PRETTY_PRINT);
}

if (!file_exists("data/users.json")) {
  $toWrite["users"] = "[]";
}

foreach ($toWrite as $filename => $contents) {
  if (!file_put_contents("data/$filename.json", $contents)) {
    echo "<span style='color: red;'>Error: could not write to data/$filename.json. Ensure that the data directory exists and is world-writable.</span><br>";
  }
}

echo "World reset.";
?>
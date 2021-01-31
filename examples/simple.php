<?php
require_once "../vendor/autoload.php";

use Iggi\AadeAfm;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$username = $_ENV["AADE_USERNAME"]; // username can be obtained from https://www.aade.gr/epiheiriseis/forologikes-ypiresies/mitroo/anazitisi-basikon-stoiheion-mitrooy-epiheiriseon
$password = $_ENV["AADE_PASSWORD"]; // password
$authorisedCallerAfm = $_ENV["AADE_AUTH"]; // optional if same with the caller's afm but if present must be authorised
$afm = isset($argv[1]) ? $argv[1] : $_ENV["AFM"]; // AFM to search

$api = new AadeAfm($username, $password, $authorisedCallerAfm);

// You may check the API version
// $data = $api->version();

// You may check the validity of the AFM (boolean)
// $valid = $api->validate($afm);

// You may retrieve the AFM information
$data = $api->info($afm);
if (empty($data["success"])) {
    echo "<p style='color: red'>" . $data["reason"] . "</p>";
    exit(1);
}
if (isset($argv[1])) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit(0);
}
$business = $data["business"];
echo "Ονομασία: " . $business["onomasia"];
echo "<br/>";
echo "Δραστηριότητα: " . $business["drastiriotita"];
echo "<br/>";
echo "Κ.Α.Δ.: " . $business["kad"];
echo "<br/>";
if (!empty($business["commerTitle"])) { // Συνήθως άδειο
    echo "Διακριτικός Τίτλος: " . $business["commerTitle"];
    echo "<br/>";
}
echo "Α.Φ.Μ.: " . $business["afm"];
echo "<br/>";
echo "Δ.Ο.Υ.: " . $business["doyDescr"]. " (κωδ: ".$business["doy"].")";
echo "<br/>";
echo "Τύπος: " . $business["firmFlagDescr"];
if (!empty($business["legalStatusDescr"])) {
    echo " (".$business["legalStatusDescr"].")"; // "AE" ή κενό
}
echo "<br/>";
echo "Φυσικό Πρόσωπο: " . $business["INiFlagDescr"]; // "ΦΠ"  ή "ΜΗ ΦΠ"
echo "<br/>";
echo "Έδρα: " . $business["postalAddress"] . " " . $business["postalAddressNo"] . ", " . $business["postalAreaDescription"];
echo "<br/>";
echo "TK: " . $business["postalZipCode"];
echo "<br/>";
echo "Έναρξη: " . substr($business["registDate"], 0, 10); // Μορφή "950-01-01T00:00:00.000+02:00"
echo "<br/>";
echo "Ενεργός: " . $business["deactivationFlagDescr"];
echo "<br/>";
if (!empty($business["stopDate"])) {
    echo "Διακοπή: " . substr($business["stopDate"], 0, 10); // πχ 2000-01-01T00:00:00.000+02:00
    echo "<br/>";
}
echo "<br/>";
echo "Δραστηριότητες: <br/>";
foreach($business["drastiriotites"] as $d) {
    echo "- ";
    echo "(" . $d["kad"] . " - " . $d["typeName"] . ") ";
    echo $d["drastiriotita"];
    echo "<br/>";
}

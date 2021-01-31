<?php
require_once "../vendor/autoload.php";

use Iggi\AadeAfm;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$username = $_ENV["AADE_USERNAME"]; // username can be obtained from https://www.aade.gr/epiheiriseis/forologikes-ypiresies/mitroo/anazitisi-basikon-stoiheion-mitrooy-epiheiriseon
$password = $_ENV["AADE_PASSWORD"]; // password
$authorisedCallerAfm = $_ENV["AADE_AUTH"]; // optional if same with the caller's afm but if present must be authorised

$crawler = new AadeAfm($username, $password, $authorisedCallerAfm);
//$data = $crawler->version();

$afm = $_ENV["AFM"]; // AFM to search
//$valid = $crawler->validate($afm);
$data = $crawler->info($afm);
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
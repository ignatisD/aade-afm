<?php
require_once "../vendor/autoload.php";

use Iggi\AadeAfm;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$username = $_ENV["USERNAME"]; // username can be obtained from https://www.aade.gr/epiheiriseis/forologikes-ypiresies/mitroo/anazitisi-basikon-stoiheion-mitrooy-epiheiriseon
$password = $_ENV["PASSWORD"]; // password
$authorisedCallerAfm = $_ENV["AUTH"]; // optional if same with the caller's afm but if present must be authorised

$crawler = new AadeAfm($username, $password, $authorisedCallerAfm);
//$data = $crawler->version();

$afm = $_ENV["AFM"]; // AFM to search
//$valid = $crawler->validate($afm);
$data = $crawler->info($afm);
AadeAfm::dd($data);
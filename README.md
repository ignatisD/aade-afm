# AadeAfm - Info about a registered business Tax ID (AFM)


## Installation

Install the latest version with

```bash
$ composer require iggi/aade-afm
```
## Instructions on how to obtain a username and password

Your regular Taxisnet username - password will not work.
You should follow the instructions at:

https://www.aade.gr/epiheiriseis/forologikes-ypiresies/mitroo/anazitisi-basikon-stoiheion-mitrooy-epiheiriseon

## Basic Usage

```php
<?php
require_once "vendor/autoload.php";

use Iggi\AadeAfm;
use Dotenv\Dotenv;
// Dotenv is not actually a prerequisite, but is recommended
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$username = $_ENV["AADE_USERNAME"]; // username can be obtained from https://www.aade.gr/epiheiriseis/forologikes-ypiresies/mitroo/anazitisi-basikon-stoiheion-mitrooy-epiheiriseon
$password = $_ENV["AADE_PASSWORD"]; // password
$authorisedCallerAfm = $_ENV["AADE_AUTH"]; // optional if same with the caller's afm but if present must be authorised
$afm = $_ENV["AFM"]; // AFM to search

$crawler = new AadeAfm($username, $password, $authorisedCallerAfm);
// You may check the API version
// $data = $crawler->version();

// You may check the validity of the AFM (boolean)
// $valid = $crawler->validate($afm);

// You may retrieve the AFM information
$data = $crawler->info($afm);
echo json_encode($data, JSON_PRETTY_PRINT);
/*
 * Success
{
    "success": true,
    "business": {
        "afm": "XXXXXXX",
        "stopDate": "XXXXXXX",
        "postalAddressNo": "XXXXXXX",
        "doyDescr": "XXXXXXX",
        "doy": "XXXXXXX",
        "onomasia": "XXXXXXX",
        "legalStatusDescr": "XXXXXXX",
        "registDate": "XXXXXXX",
        "deactivationFlag": "XXXXXXX",
        "deactivationFlagDescr": "XXXXXXX",
        "postalAddress": "XXXXXXX",
        "firmFlagDescr": "XXXXXXX",
        "commerTitle": null,
        "postalAreaDescription": "XXXXXXX",
        "INiFlagDescr": ""XXXXXXX",
        "postalZipCode": "XXXXXXX",
    }
}
 * Error
{
    "success": false,
    "reason": "O Α.Φ.Μ. για τον οποίο ζητούνται πληροφορίες δεν ανήκει και δεν ανήκε ποτέ σε νομικό πρόσωπο, νομική οντότητα, ή φυσικό πρόσωπο με εισόδημα από επιχειρηματική δραστηριότητα.",
    "isNotBusiness": true
}

*/

```

### Author

Ignatios Drakoulas - <ignatisnb@gmail.com> - <https://twitter.com/ignatisd><br />

### License

Crawler is licensed under the MIT License - see the [LICENSE](LICENSE) file for details

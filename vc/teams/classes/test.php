<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Display information about all the mod_hybridteaching modules in the requested course. *
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../vendor/autoload.php'; 

// Include the Microsoft Graph classes  .
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

// Data from Azure Active Directory.
$tenantid = "1b2b6af1-b62d-452c-94b5-3349bede3179";
$clientid = "f2586839-35c4-4dca-bd1f-c2e5ccc528de";
$clientsecret = "vHS8Q~wJccKtG92-mIDUOwLb3JZ.CZIO2dby0a~P";

$guzzle = new \GuzzleHttp\Client();

$url = 'https://login.microsoftonline.com/' . $tenantid . '/oauth2/v2.0/autorize';

$token = json_decode($guzzle->post($url, [
    'form_params' => [
        'client_id' => $clientid,
        'client_secret' => $clientsecret,
        'response_type' => 'code',
        'approval_prompt' => 'auto',
        'redirect_uri' => 'https://marian.moodle41.com/mod/hybridteaching/vc/teams/classes/test1.php',
        // 'response_mode' => 'query',
        // 'scope' => 'offline_access user.read mail.read', //'https://graph.microsoft.com/.default',
        'scope' => 'User.Read',
        'grant_type' => 'client_credentials',
    ],
])->getBody()->getContents());
$accesstoken = $token->access_token;
var_dump($token);
echo "<br><br>";

/*
$url = 'https://login.microsoftonline.com/' . $tenantid . '/oauth2/v2.0/token';
$token = json_decode($guzzle->post($url, [
    'form_params' => [
        'client_id' => $clientid,
        'client_secret' => $clientsecret,
        'scope' => 'https://graph.microsoft.com/.default',
        'grant_type' => 'client_credentials',
    ],
])->getBody()->getContents());
$accesstoken = $token->access_token;
var_dump($token);
echo "<br><br>";
*/

// This works! The Access-Token is echoed.
echo "Accesstoken:".$accesstoken;
echo "<br><br>";

// But from here on, i get no output.
$graph = new Graph();
$graph->setAccessToken($accesstoken);

// $user = $graph->createRequest("GET", "/users/{user id}")
/*
$user = $graph->createRequest("GET", "/me")
    ->setReturnType(Model\User::class)
    ->execute();
*/

// $user = $graph->createRequest("GET", "/users/9bbdddb8-bf16-4bdd-9338-ab167b27691f")  

// Get users list.
// $select='$select=displayName,id,mail';
// $user = $graph->createCollectionRequest("GET", "/users/$select")
$user = $graph->createRequest("get", "/me")
    ->addHeaders(["Content-Type" => "application/json"])
    ->setReturnType(Model\User::class)
    ->setTimeout("1000")
    ->execute();

var_dump($user);

echo "<br><br>Hello, my name is {$user->getGivenName()}.";

// MEETING.

$data = [
    'subject' => 'Test Meeting',
    'startDateTime' => '2023-08-29T06:30:00.00+01:00',
    'endDateTime' => '2023-08-29T07:30:00.00+01:00',
];
/*
$onlinemeet = new \stdClass();
$onlinemeet->startDateTime = "2020-09-02T14:30:34.2444915";
$onlinemeet->endDateTime = "2020-09-02T15:30:34.2444915";
$onlinemeet->subject = "Test Meeting";
$jso = json_encode($onlinemeet);
var_dump($user);
*/

$graphresponse = $graph
    ->createRequest("POST", "/me/onlineMeetings")
    ->attachBody($data)
    ->setReturnType(Model\OnlineMeeting::class)
    ->execute();

var_dump($graphresponse);

/*
Create an online meeting on behalf of a user (`POST /beta/users/{userId}/onlineMeetings/)
Update an online meeting on behalf of a user (`PATCH /beta/users/{userId}/onlineMeetings/{id})
Delete an online meeting on behalf of a user (`DELETE /beta/users/{userId}/onlineMeetings/{id})
*/

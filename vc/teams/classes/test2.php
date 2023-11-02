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

require_once(__DIR__ . '/../vendor/autoload.php');

// Include the Microsoft Graph classes.
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

// Data from Azure Active Diretory.
$tenantid = "xxxxxxxx-xxxx-xxxx-xxxx-3349bede3179";
$clientid = "xxxxxxxx-xxxx-xxxx-xxxx-c2e5ccc528de";
$clientsecret = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxx";

echo "<pre>";
var_dump($_GET);  // Debug print.
echo "</pre>";

if (!isset($_GET["code"]) && !isset($_GET["error"])) {
    $url = 'https://login.microsoftonline.com/' . $tenantid . '/oauth2/v2.0/authorize';
    $params = [
        'client_id' => $clientid,
        'scope' => 'openid offline_access user.read OnlineMeetings.ReadWrite Calendars.ReadWrite',
        // 'https://graph.microsoft.com/.default',
        // 'scope' => 'User.Read',
        'response_type' => 'code',
        'approval_prompt' => 'auto',
        // 'client_secret' => $clientsecret,
        'redirect_uri' => 'https://marian.moodle41.com/mod/hybridteaching/vc/teams/classes/test2.php',
        // 'response_mode' => 'query',
        // 'grant_type' => 'client_credentials'
    ];
    $authredirecturl = $url. '?' . http_build_query($params);
    header('Location: ' . $authredirecturl);
} else if (isset($_GET["error"])) {
    echo "Error handler activated:\n\n";
    var_dump($_GET);  // Debug print.
} else if (isset($_GET["code"])) {
    // Obtener tokens y finalizar la parte de autenticación.

    $guzzle = new \GuzzleHttp\Client();

    $url = 'https://login.microsoftonline.com/' . $tenantid . '/oauth2/v2.0/token';

    $token = json_decode($guzzle->post($url, [
        'form_params' => [
            'client_id' => $clientid,
            'client_secret' => $clientsecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'https://marian.moodle41.com/mod/hybridteaching/vc/teams/classes/test2.php',
            'code' => $_GET["code"],
            'scope' => 'openid offline_access user.read OnlineMeetings.ReadWrite Calendars.ReadWrite',
            // 'response_type' => 'code',
            // 'approval_prompt' => 'auto',
            // 'response_mode' => 'query',
            // 'scope' => 'offline_access user.read mail.read', //'https://graph.microsoft.com/.default',
            // 'scope' => 'User.Read',
        ],
    ])->getBody()->getContents());
    $accesstoken = $token->access_token;
    echo "<br><br>Accesstoken:<br>";
    echo "<pre>";
    var_dump($token);
    echo "</pre><br><br>";

    // Refresh authorization token.

    $guzzle = new \GuzzleHttp\Client();

    $url = 'https://login.microsoftonline.com/' . $tenantid . '/oauth2/v2.0/token';

    $tokenderefresco = json_decode($guzzle->post($url, [
        'form_params' => [
            'client_id' => $clientid,
            'grant_type' => 'refresh_token',
            'client_secret' => $clientsecret,
            'refresh_token' => $token->refresh_token,
            // 'redirect_uri' => 'https://marian.moodle41.com/mod/hybridteaching/vc/teams/classes/test2.php',
        ],
    ])->getBody()->getContents());

    echo "<br><br>Accesstoken REFRESCADO:<br>";
    echo "<pre>";
    var_dump($tokenderefresco);
    echo "</pre>";
    echo "<br><br>";

    // Api access: see user.
    $graph = new Graph();
    $graph->setAccessToken($tokenderefresco->access_token);
    $user = $graph->createRequest("get", "/users/8e23bbe5-961c-472c-a092-983481f8d792")
        ->addHeaders(["Content-Type" => "application/json"])
        ->setReturnType(Model\User::class)
        ->setTimeout("1000")
        ->execute();

    echo "<pre>";
    var_dump($user);
    echo "</pre>";

    echo "<br><br>Hello, my name is {$user->getGivenName()}.";

    // Api access: create meeting.
    $data = [
        'subject' => 'Test Meeting',
        'startDateTime' => '2023-08-29T19:30:00.00+01:00',
        'endDateTime' => '2023-08-29T20:30:00.00+01:00',
        'organizerId' => '8e23bbe5-961c-472c-a092-983481f8d792',
    ];

    $graphresponse = $graph
        ->createRequest("POST", "/users/8e23bbe5-961c-472c-a092-983481f8d792/onlineMeetings")
        ->attachBody($data)
        ->setReturnType(Model\OnlineMeeting::class)
        ->execute();
    echo "<pre>";
    var_dump($graphresponse);
    echo "</pre>";

    // Create meeting in calendar.
    $startdatetime = "2023-10-26T16:00:00";
    $enddatetime = "2023-10-26T18:00:00";
    echo "<br><br>------------------------<br><br>Meeting:<br>";

    // Create an event calendar as online meeting.
    $data = [
        'subject' => "Test en calendario de Adele",
        "start" => [
            "dateTime" => $startdatetime, // "2023-10-24T16:00:00",
            "timeZone" => "Europe/Paris",
        ],
        "end" => [
            "dateTime" => $enddatetime, // "2023-10-24T18:00:00",
            "timeZone" => "Europe/Paris",
        ],
        "location" => [
            "displayName" => "Test en calendario de Adele",
        ],
        "allowNewTimeProposals" => false,
        "isOnlineMeeting" => true,
        "onlineMeetingProvider" => "teamsForBusiness",
        'hideAttendees' => true, // Each attendee only sees themselves in the meeting request and meeting Tracking list.
        'isOrganizer' => true,
    ];

    // Create event in calendar.
    try {
        $graphresponse = $graph
            ->createRequest("POST", "/me/events")
            ->attachBody($data)
            ->setReturnType(Model\OnlineMeeting::class)
            ->execute();

        $result = json_decode(json_encode($graphresponse), true);
        echo "<pre>";
        var_dump($result);
        echo "</pre>";
    } catch (\Exception $e) {
        echo "<pre>";
        var_dump($e);
        echo "</pre>";
    }
}

/*
function refreshtokenauth($refreshtoken){

    // Data from Azure Active Diretory.
    $tenantId="1b2b6af1-b62d-452c-94b5-3349bede3179";
    $clientid="f2586839-35c4-4dca-bd1f-c2e5ccc528de";
    $clientsecret="vHS8Q~wJccKtG92-mIDUOwLb3JZ.CZIO2dby0a~P";

    $guzzle = new \GuzzleHttp\Client();

    $url = 'https://login.microsoftonline.com/' . $tenantid . '/oauth2/v2.0/token';

    $tokenderefresco = json_decode($guzzle->post($url, [
    'form_params' => [
        'client_id' => $clientid,
        'grant_type' => 'refresh_token',
        'client_secret' => $clientsecret,
        'refresh_token' => $refreshtoken,
    ],
])->getBody()->getContents());

echo "<br><br>Accesstoken REFRESCADO:<br>";
echo "<pre>";
var_dump($tokenderefresco);
echo "</pre>";
echo "<br><br>";

}
*/

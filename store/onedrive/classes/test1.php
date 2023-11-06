<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../vendor/autoload.php');

// Include the Microsoft Graph classes.
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

// Data from Azure Active Diretory.
$tenantid = "1b2b6af1-b62d-452c-94b5-3349bede3179";
$clientid = "f2586839-35c4-4dca-bd1f-c2e5ccc528de";
$clientsecret = "vHS8Q~wJccKtG92-mIDUOwLb3JZ.CZIO2dby0a~P";

/*echo "<pre>";
var_dump($_GET);  //Debug print.
echo "</pre>";*/

if (!isset($_GET["code"]) && !isset($_GET["error"]) ) {

    $url = 'https://login.microsoftonline.com/' . $tenantid . '/oauth2/v2.0/authorize';
    $params = [
        'client_id' => $clientid,
        'scope' => 'offline_access user.read openid',
        // https://nhb7.sharepoint.com/Sites.FullControl.All',
        // api://f2586839-35c4-4dca-bd1f-c2e5ccc528de/Sites.FullControl.All',
            // https://nhb7.sharepoint.com/Sites.FullControl.All', //'https://graph.microsoft.com/.default',
        // 'scope' => 'User.Read',
        'response_type' => 'code',
        'approval_prompt' => 'auto',
        // 'client_secret' => $clientsecret,
        'redirect_uri' => 'https://marian.moodle41.com/mod/hybridteaching/store/onedrive/classes/test1.php',
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
            'redirect_uri' => 'https://marian.moodle41.com/mod/hybridteaching/store/onedrive/classes/test1.php',
            'code' => $_GET["code"],
            'scope' => 'offline_access user.read', // https://nhb7.sharepoint.com/Sites.FullControl.All'.
            // https://nhb7.sharepoint.com/Sites.FullControl.All',
            // api://f2586839-35c4-4dca-bd1f-c2e5ccc528de/Sites.FullControl.All',
            // https://nhb7.sharepoint.com/Sites.FullControl.All',
            // 'response_type'=>'code',
            // 'approval_prompt' => 'auto',
            // 'response_mode' => 'query',
            // 'scope' => 'offline_access user.read mail.read', //'https://graph.microsoft.com/.default',
            // 'scope' => 'User.Read',

        ],
    ])->getBody()->getContents());
    /*$accessToken = $token->access_token;
    echo "<br><br>Accesstoken:<br>";
    echo "<pre>";
    var_dump($token);
    echo "</pre>";
    echo "<br><br>";
    */

    // REFRESCAR TOKEN DE AUTORIZACIÓN.

    $guzzle = new \GuzzleHttp\Client();

    $url = 'https://login.microsoftonline.com/' . $tenantid . '/oauth2/v2.0/token';
    echo "<br><br>Accesstoken para onedrive:<br>";
    $tokenderefresco = json_decode($guzzle->post($url, [
        'form_params' => [
            'client_id' => $clientid,
            'client_secret' => $clientsecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $token->refresh_token,
            'scope' => 'offline_access user.read https://nhb7.sharepoint.com/AllSites.FullControl',
            // Sites.FullControl.All',
            'redirect_uri' => 'https://marian.moodle41.com/mod/hybridteaching/store/onedrive/classes/test2.php',


        ],
    ])->getBody()->getContents());
    // $accessToken = $token->refresh_token;
    /*echo "<br><br>Accesstoken REFRESCADO:<br>";
    echo "<pre>";
    var_dump($tokenderefresco);
    echo "</pre>";
    echo "<br><br>";*/


    // HACER ACCESOS A LA API: VER MI USUARIO:.
    $graph = new Graph();
    $graph->setAccessToken($tokenderefresco->access_token);
    $user = $graph->createRequest("get", "/me")
        ->addHeaders(["Content-Type" => "application/json"])
        ->setReturnType(Model\User::class)
        ->setTimeout("1000")
        ->execute();

    echo "<br><br>Hello, my name is {$user->getGivenName()}.";



    // HACER ACCESOS A LA API: CREAR MEETING:.
    /*$data = [
        'subject' => 'Test Meeting',
        'startDateTime' => '2023-08-29T19:30:00.00+01:00',
        'endDateTime' => '2023-08-29T20:30:00.00+01:00',
    ];

    $graphresponse = $graph
            ->createRequest("POST", "/me/onlineMeetings")
            ->attachBody($data)
            ->setReturnType(Model\OnlineMeeting::class)
            ->execute();*/




    // ACCEDER A ONEDRIVE.
            $graphresponse = $graph
            // ->setApiVersion("beta")
            // ->createRequest("GET", "/me")
            // ->createRequest("GET", "/sites/root")
                ->createRequest("GET", "/me/Drive")
            // ->createRequest("GET", "/me/drive/root/search(q='{s1}')")

            // ->createRequest("GET", "/sites/ca50e87a-5e07-4a3d-b095-e1948d99cc58/drive")


            // ->createRequest("GET", "/users/$organizer/onlineMeetings/$meetingid/recordings/")
            // ->attachBody($data)
                ->setReturnType(Model\Drive::class)

                ->execute();

    echo "<pre>";
    // var_dump($graphresponse);.
    $result = json_decode(json_encode($graphresponse), true);
    var_dump($result);
    echo "</pre>";
    echo "<br><br>ID:";
    var_dump($result['id']);

    echo "<br><br>ENCONTRADO:";
    $data = [
        'name' => 'nombre1',
    ];
        $graphresponse = $graph
            ->createRequest("POST", "/me/drive/root/children")
            ->attachBody($data);
            // ->createRequest("GET", "/me/drive/search(q='test')");

            // ->setReturnType(Model\DriveItem::class);
    echo "<pre>";
    var_dump($graphresponse);
    $result = json_decode(json_encode($graphresponse), true);
    var_dump($result);
    echo "</pre>";

}

/*
function refreshtokenauth($refreshtoken){

    // Data from Azure Active Diretory
    $tenantid="1b2b6af1-b62d-452c-94b5-3349bede3179";
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

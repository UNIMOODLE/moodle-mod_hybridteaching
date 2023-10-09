<?php 

require_once __DIR__ . '/../vendor/autoload.php'; 
  
// Include the Microsoft Graph classes  
use Microsoft\Graph\Graph;  
use Microsoft\Graph\Model;  
  
// Data from Azure Active Diretory  
$tenantId="1b2b6af1-b62d-452c-94b5-3349bede3179";  
$clientId="f2586839-35c4-4dca-bd1f-c2e5ccc528de";  
//$clientSecret="rGu8Q~n_6q5kk_q-n5zoA53NMlmYjmy6APpDcbzN";  
$clientSecret="vHS8Q~wJccKtG92-mIDUOwLb3JZ.CZIO2dby0a~P";


$guzzle = new \GuzzleHttp\Client();

$url = 'https://login.microsoftonline.com/' . $tenantId . '/oauth2/v2.0/autorize';

$token = json_decode($guzzle->post($url, [
    'form_params' => [
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'response_type'=>'code',
        'approval_prompt' => 'auto',
        'redirect_uri' => 'https://marian.moodle41.com/mod/hybridteaching/vc/teams/classes/test1.php',
        //'response_mode' => 'query',
        //'scope' => 'offline_access user.read mail.read', //'https://graph.microsoft.com/.default',
        'scope' => 'User.Read',
        'grant_type' => 'client_credentials',
    ],
])->getBody()->getContents());
$accessToken = $token->access_token;
var_dump($token);
echo "<br><br>";

/*
$url = 'https://login.microsoftonline.com/' . $tenantId . '/oauth2/v2.0/token';
$token = json_decode($guzzle->post($url, [
    'form_params' => [
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'scope' => 'https://graph.microsoft.com/.default',
        'grant_type' => 'client_credentials',
    ],
])->getBody()->getContents());
$accessToken = $token->access_token;
var_dump($token);
echo "<br><br>";
*/


  
// This works! The Access-Token is echoed  
echo "AccessToken:".$accessToken;  
echo "<br><br>";
  
// But from here on, i get no output  
$graph = new Graph();  
$graph->setAccessToken($accessToken);  
  
//$user = $graph->createRequest("GET", "/users/{user id}")  
/*$user = $graph->createRequest("GET", "/me")
->setReturnType(Model\User::class)  
->execute();  
*/
$user = $graph->createRequest("get", "/me")
//$user = $graph->createRequest("GET", "/users/9bbdddb8-bf16-4bdd-9338-ab167b27691f")  

//obtener lista de usuarios
//$select='$select=displayName,id,mail';
//$user = $graph->createCollectionRequest("GET", "/users/$select")  

->addHeaders(array("Content-Type" => "application/json"))
->setReturnType(Model\User::class)
->setTimeout("1000")
->execute();
  
print_r($user);  
  
echo "<br><br>Hello, my name is {$user->getGivenName()}.";  


//MEETING -------------------


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
$user = $graph->createRequest("POST", "/users/9bbdddb8-bf16-4bdd-9338-ab167b27691f/onlineMeetings")->addHeaders(array("Content-Type" => "application/json"))->attachBody($jso)->setReturnType(User::class) ->execute();
var_dump($user);
*/
echo "<br>sdssssssssssssssssss<br>";
$graphresponse = $graph//->setApiVersion("beta")
            ->createRequest("POST", "/me/onlineMeetings")
			//->createRequest("POST", "/users/9bbdddb8-bf16-4bdd-9338-ab167b27691f/onlineMeetings")
			->attachBody($data)
			->setReturnType(Model\OnlineMeeting::class)
			->execute();

var_dump($graphresponse);    

//  /beta/users/userid/onlineMeetings

/*Create an online meeting on behalf of a user (`POST /beta/users/{userId}/onlineMeetings/)
Update an online meeting on behalf of a user (`PATCH /beta/users/{userId}/onlineMeetings/{id})
Delete an online meeting on behalf of a user (`DELETE /beta/users/{userId}/onlineMeetings/{id})
*/
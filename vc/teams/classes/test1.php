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

// This login script is based on Sami Sipponen's Simple Azure Oauth2 Example with PHP:
// https://www.sipponen.com/archives/4024 .

session_start();  // Since you likely need to maintain the user session, let's start it an utilize it's ID later.
error_reporting(-1);  // Remove from production version.
ini_set("display_errors", "on");  // Remove from production version.

// Configuration, needs to match with Azure app registration.
$clientid = "f2586839-35c4-4dca-bd1f-c2e5ccc528de";  // Application (client) ID.
$adtenant = "1b2b6af1-b62d-452c-94b5-3349bede3179";  // Azure Active Directory Tenant ID.
$clientsecret = "vHS8Q~wJccKtG92-mIDUOwLb3JZ.CZIO2dby0a~P";  // Client Secret.
$redirecturi = "https://marian.moodle41.com/mod/hybridteaching/vc/teams/classes/test1.php";
$erroremail = "adminisyc@nhb7.onmicrosoft.com";

function errorhandler($input, $email) {
    $output = "PHP Session ID:    " . session_id() . PHP_EOL;
    $output .= "Client IP Address: " . getenv("REMOTE_ADDR") . PHP_EOL;
    $output .= "Client Browser:    " . $_SERVER["HTTP_USER_AGENT"] . PHP_EOL;
    $output .= PHP_EOL;
    ob_start();  // Start capturing the output buffer.
    var_dump($input);  // This is not for debug print, this is to collect the data for the email.
    $output .= ob_get_contents();  // Storing the output buffer content to $output.
    ob_end_clean();  // While testing, you probably want to comment the next row out.
    mb_send_mail($email, "Your Azure AD Oauth2 script faced an error!", $output, "X-Priority: 1\nContent-Transfer-Encoding: 8bit\nX-Mailer: PHP/" . phpversion());
    exit;
}

if (isset($_GET["code"])) {
    echo "<pre>";  // This is just for easier and better looking var_dumps for debug purposes.
}

if (!isset($_GET["code"]) && !isset($_GET["error"])) {  // Real authentication part begins.
    // First stage of the authentication process; This is just a simple redirect (first load of this page).
    $url = "https://login.microsoftonline.com/" . $adtenant . "/oauth2/v2.0/authorize?";
    $url .= "state=" . session_id();  // This at least semi-random string is likely good enough as state identifier.
    // This scope seems to be enough, but you can try "&scope=profile+openid+email+offline_access+User.Read" if you like.
    $url .= "&scope=User.Read";
    $url .= "&response_type=code";
    $url .= "&approval_prompt=auto";
    $url .= "&client_id=" . $clientid;
    $url .= "&redirect_uri=" . urlencode($redirecturi);
    header("Location: " . $url);  // So off you go my dear browser and welcome back for round two after some redirects at Azure end.
} else if (isset($_GET["error"])) {  // Second load of this page begins, but hopefully we end up to the next elseif section...
    echo "Error handler activated:\n\n";
    var_dump($_GET);  // Debug print.
    errorhandler([
        "Description" => "Error received at the beginning of second stage.",
        "\$_GET[]" => $_GET,
        "\$_SESSION[]" => $_SESSION,
      ], $erroremail);
} else if (strcmp(session_id(), $_GET["state"]) == 0) {  // Checking that the session_id matches to the state for security reasons.
    echo "Stage2:\n\n";
    // And now the browser has returned from its various redirects at Azure side and carrying some gifts inside $_GET .
    var_dump($_GET);  // Debug print.

    // Verifying the received tokens with Azure and finalizing the authentication part.
    $content = "grant_type=authorization_code";
    $content .= "&client_id=" . $clientid;
    $content .= "&redirect_uri=" . urlencode($redirecturi);
    $content .= "&code=" . $_GET["code"];
    $content .= "&client_secret=" . urlencode($clientsecret);
    $options = [
      "http" => [  // Use "http" even if you send the request with https.
        "method"  => "POST",
        "header"  => "Content-Type: application/x-www-form-urlencoded\r\n" .
          "Content-Lengh: " . strlen($content) . "\r\n",
        "content" => $content,
      ],
    ];
    $context  = stream_context_create($options);
    $json = file_get_contents("https://login.microsoftonline.com/" . $adtenant . "/oauth2/v2.0/token", false, $context);
    if ($json === false) {
        errorhandler([
            "Description" => "Error received during Bearer token fetch.",
            "PHP_Error" => error_get_last(),
            "\$_GET[]" => $_GET,
            "HTTP_msg" => $options,
        ], $erroremail);
    }
    $authdata = json_decode($json, true);
    if (isset($authdata["error"])) {
        errorhandler( [
            "Description" => "Bearer token fetch contained an error.",
            "\$authdata[]" => $authdata,
            "\$_GET[]" => $_GET,
            "HTTP_msg" => $options,
        ], $erroremail);
    }

    var_dump($authdata);  // Debug print.

    // Fetching the basic user information that is likely needed by your application.
    $options = [
      "http" => [  // Use "http" even if you send the request with https.
        "method" => "GET",
        "header" => "Accept: application/json\r\n" .
          "Authorization: Bearer " . $authdata["access_token"] . "\r\n",
      ],
    ];
    $context = stream_context_create($options);
    $json = file_get_contents("https://graph.microsoft.com/v1.0/me", false, $context);
    if ($json === false) {
        errorhandler( [
            "Description" => "Error received during user data fetch.",
            "PHP_Error" => error_get_last(),
            "\$_GET[]" => $_GET,
            "HTTP_msg" => $options,
        ], $erroremail);
    }
    $userdata = json_decode($json, true);  // This should now contain your logged on user information.
    if (isset($userdata["error"])) {
        errorhandler( [
            "Description" => "User data fetch contained an error.",
            "\$userdata[]" => $userdata,
            "\$authdata[]" => $authdata,
            "\$_GET[]" => $_GET,
            "HTTP_msg" => $options,
        ], $erroremail);
    }

    var_dump($userdata);  // Debug print.
} else {
      // If we end up here, something has obviously gone wrong...
      // Likely a hacking attempt since sent and returned state aren't matching and no $_GET["error"] received.
      echo "Hey, please don't try to hack us!\n\n";
      echo "PHP Session ID used as state: " . session_id() . "\n";
      var_dump($_GET);  // But this being a test script having the var_dumps might be useful.
      errorhandler([
          "Description" => "Likely a hacking attempt, due state mismatch.",
          "\$_GET[]" => $_GET,
          "\$_SESSION[]" => $_SESSION,
      ], $erroremail);
}
echo "\n<a href=\"" . $redirecturi . "\">Click here to redo the authentication</a>";  // Only to ease up your tests.

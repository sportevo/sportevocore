<?php 
//Facebook Connect
$fbconfig['appid' ]  = "172725479422689"; //your application id
$fbconfig['api'   ]  = "8a8b4e2194c761a24503b503ab0da74a"; //your api key
$fbconfig['secret']  = "80a5a430ddb3b7ffd5ca9e8625fd1fe4"; //your application secret

    // Create our Application instance.
    $facebook = new Facebook(array(
      'appId'  => $fbconfig['appid'],
      'secret' => $fbconfig['secret'],
      'cookie' => true,
    ));
 
    // We may or may not have this data based on a $_GET or $_COOKIE based session.
    // If we get a session here, it means we found a correctly signed session using
    // the Application Secret only Facebook and the Application know. We dont know
    // if it is still valid until we make an API call using the session. A session
    // can become invalid if it has already expired (should not be getting the
    // session back in this case) or if the user logged out of Facebook.
    $session = $facebook->getSession();
 
    $fbme = null;
    // Session based graph API call.
    if ($session) {
        $uid = $facebook->getUser();
        $fbme = $facebook->api('/me');
    }
 
?>
<?php

/*
 * We need a token to verify the subscriptions as explained in the docs.
 * http://instagram.com/developer/realtime/
 * [Quote] In order to keep someone else from creating unwanted subscriptions, we must verify that the endpoint would like this new subscription
 * This $verifytoken must match the $_verifytoken in the Instagram class set by the setVerifyToken method. 
 */

$verifytoken = '123451234';


if(isset($_GET['hub_mode']) && $_GET['hub_mode'] == 'subscribe'){
    if($_GET['hub_verify_token']==$verifytoken)
        die($_GET['hub_challenge']);   
}
echo 'Invalid valid token';
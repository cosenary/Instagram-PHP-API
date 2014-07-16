<?php

/**
 * This router file is to facilitate tests with the subscription portion of the Instagram API Class
 * You can test it like so :
 *  http://localhost/instagram/router.php
 *  http://localhost/instagram/router.php?fct=addUsers
 *  http://localhost/instagram/router.php?fct=addTag&tag=nofilter
 *  http://localhost/instagram/router.php?fct=addGeo
 *  http://localhost/instagram/router.php?fct=addLocation
 *  http://localhost/instagram/router.php?fct=del&object=tag  
 *  http://localhost/instagram/router.php?fct=del&all=1 
 * 
 *  
 */


require_once 'instagram.class.php';

$config = array('apiKey'=>'CLIENT_API_KEY',
                'apiSecret'=>'CLIENT_API_SECRET',
                 'apiCallback'=>'http://localhost/link/to/callback.php'
                );


$instagram = new Instagram($config);


/*
 * We need a token that will also be used in the callback script to verify the subscriptions as explained in the docs.
 * http://instagram.com/developer/realtime/
 * Quote : In order to keep someone else from creating unwanted subscriptions, we must verify that the endpoint would like this new subscription
 * See callback.php script. 
 */
$verifytoken = '123451234';        
$instagram->setVerifyToken($verifytoken);


if(isset($_GET['fct'])){
   
    /*
     * Will create a subscription for every authorized user through app.
     */
    if($_GET['fct']=='addUsers')
        echo json_encode( $instagram->createSubscription('user'));

    /*
     * Will create a subscription for a certain tag.
     */
    elseif($_GET['fct']=='addTag')
        echo json_encode( $instagram->createSubscription('tag',$_GET['tag']));
    
     /*
     * Will create a subscription for a certain instagram location id.
     */
    elseif($_GET['fct']=='addLocation')
        echo json_encode( $instagram->createSubscription('location','1257285'));

     /*
     * Will create a subscription for a certain geographic position using latitude, longitude and a given radius.
     */
    elseif($_GET['fct']=='addGeo')
        echo json_encode( $instagram->createSubscription('geography','',array('lat'=>35.657872,'lng'=>139.70232,'radius'=>'1000')));
     
    /*
     * Will delete a certain subscription identified by its unique identifer
     */
    elseif($_GET['fct']=='del' && isset($_GET['id']))
        echo json_encode( $instagram->deleteSubscriptionById($_GET['id']));
    
    /*
     * Will delete all subscriptions by object type (tag, user, location, geography).
     */
    elseif($_GET['fct']=='del' && isset($_GET['object']))
        echo json_encode( $instagram->deleteSubscriptions($_GET['object']));
    
    
    /*
     * Will delete all subscriptions.
     */
    elseif($_GET['fct']=='del' && isset($_GET['all']))
        echo json_encode( $instagram->deleteSubscriptions('all'));
}

/*
 * Always list active subsriptions.
 */
echo json_encode( $instagram->listSubscriptions());
?>


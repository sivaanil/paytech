<?php

require_once('TwitterAPIExchange.php');

/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
$settings = array(
    'oauth_access_token' => "2985239233-IebohhILTLn4nFlyQFPwJl7FXysJdNXU8Cl75zH",
    'oauth_access_token_secret' => "5FMPs3TgsTI2lKDqsVNPOZ8Fo9zm9XC8hg7aeocGmUk6D",
    'consumer_key' => "nnJyT2dErBJZILr2d6EfTxhG4",
    'consumer_secret' => "V2YRjm0Mm5hScGzYp54cQmW3xbMxTavQmcATXSR5Vm88XcqIo0"
);

$url = 'https://api.twitter.com/1.1/followers/list.json';
$getfield = '?username=J7mbo&skip_status=1';
$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
$twitter_data_string = $twitter->setGetfield($getfield)
             		     ->buildOauth($url, $requestMethod)
             		     ->performRequest();

$twitter_data = json_decode($twitter_data_string, true);
if(!empty($twitter_data['users'])){ 
	foreach($twitter_data['users'] as $user){
		
		$twitter_ids[] = $user['id'];
	}
}
echo "<pre>";
print_r($twitter_ids);
echo "</pre>";


?>

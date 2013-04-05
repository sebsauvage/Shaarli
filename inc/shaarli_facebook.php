<?php
/** Post a message to Facebook.
 *
 * 1/ Obtain appId and secret from https://developers.facebook.com/
 *    a/ Create a new App (Tab 'Apps' and button 'Create New App'
 *    b/ Enter only name 'shaarli-<your suffix>'  and captcha
 *    c/ Click on 'Website with Facebook Login' and add your Shaarli URL
 *    d/ Fill 'App ID' and 'App Secret' bellow
 * 2/ Enter userId (numeric or string from your account
 * 3/ Uncomment the 'die' instruction below and fill the redirect_uri with your Shaarli URL. Call Shaarli to authorize facebook.
 * 4/ Comment back the 'die' instruction
 *
 */
function shaarli_Facebook($url, $title, $description = null)
{
	require_once("inc/facebook-php-sdk/src/facebook.php");

	$userId = '<ENTER YOUR FACEBOOK ID>';
	
	$facebook = new Facebook(array(
		'appId' => '<ENTER YOUR APPID>',
		'secret' => '<ENTER YOUR SECRET>'
	));
	
	/*
	die($facebook->getLoginUrl(array(
		'redirect_uri'=>'<ENTER YOUR SHAARLI URL>',
		'scope'=>'publish_stream,manage_pages'
	)));
	*/

	if ( is_null( $description ) )
	{
		$facebook->api( '/'.$userId.'/feed',
			'post',
			array(
				'link'            =>    $url,
				'name'            =>     $title
			)
		);
	}
	else
	{
		$facebook->api( '/'.$userId.'/feed',
			'post',
			array(
				'message'         =>    $description, 
				'link'            =>    $url,
				'name'            =>     $title
			)
		);
	}
}

?>

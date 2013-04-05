<?php
/** Twitt a message to Twitter.
 *  You need to obtain "Consumer" and "Access token" keys from https://dev.twitter.com/
 *  Input: $message
 *  Output: 0 = ok.
 *          other = error message.
 */
function shaarli_Tweet($message)
{
	require 'inc/tmhOAuth/tmhOAuth.php';
	require 'inc/tmhOAuth/tmhUtilities.php';
	$tmhOAuth = new tmhOAuth(array(
	  'consumer_key'    => '<ENTER YOUR CONSUMER KEY>',
	  'consumer_secret' => '<ENTER YOUR CONSUMER SECRET>',
	  'user_token'      => '<ENTER YOUR APPLICATION TOKEN WITH READ-WRITE AUTHORIZATION>',
	  'user_secret'     => '<ENTER YOUR APPLICATION SECRET>',
	));
	$code = $tmhOAuth->request('POST', $tmhOAuth->url('1/statuses/update'), array('status' => $message));
	if ($code == 200) { return 0; } 
	else { return tmhUtilities::pr($tmhOAuth->response['response']); }
}
 
/* Example code:
$result = shaarli_Tweet("Hello from Shaarli");
if ($result==0) { echo "Ok.\n"; } else { echo "FAILED";}
*/
?>

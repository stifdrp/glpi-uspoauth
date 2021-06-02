<?php
session_start();
require_once ('config.php');
?>
<!--
	Consumer OAuth basedo no exemplo googledocs.php da biblioteca oauth-php.
	Site: http://code.google.com/p/oauth-php/
	Acessado em: 2/10/2012
-->
<html>
<head>
<script language=javascript>
	function refreshWindow(){
		window.location.assign('<?= $url_app ?>');
        self.close();
	}
</script>
</head>
<body>
<?php
/**
 * oauth-php: Example OAuth client for accessing Google Docs
 *
 * @author BBG
 *
 * 
 * The MIT License
 * 
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
include_once "library/OAuthStore.php";
include_once "library/OAuthRequester.php";

//Usuario e senha ambiente de produção
define("CONSUMER_KEY", $consumer_key);
define("CONSUMER_SECRET", $consumer_secret);

// url servidor de produção
define("OAUTH_HOST", "https://uspdigital.usp.br");

$curl_options = array();

define("REQUEST_TOKEN_URL", OAUTH_HOST . "/wsusuario/oauth/request_token");
define("AUTHORIZE_URL", OAUTH_HOST . "/wsusuario/oauth/authorize");
define("ACCESS_TOKEN_URL", OAUTH_HOST . "/wsusuario/oauth/access_token");

define('OAUTH_TMP_DIR', function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : realpath($_ENV["TMP"]));

//  Init the OAuthStore
$options = array(
	'consumer_key' => CONSUMER_KEY, 
	'consumer_secret' => CONSUMER_SECRET,
	'server_uri' => OAUTH_HOST,
	'request_token_uri' => REQUEST_TOKEN_URL,
	'authorize_uri' => AUTHORIZE_URL,
	'access_token_uri' => ACCESS_TOKEN_URL
);
// Note: do not use "Session" storage in production. Prefer a database
// storage, such as MySQL.

OAuthStore::instance("Session", $options);

try
{
	//  STEP 1:  If we do not have an OAuth token yet, go get one
	if (empty($_GET["oauth_token"]))
	{	
		// get a request token
		$tokenResultParams = OAuthRequester::requestRequestToken(CONSUMER_KEY, null, null, 'POST', null, $curl_options);
		
		$_SESSION["oauth_token"] = $tokenResultParams['token'];
		
		//  redirect to the authorization page, they will redirect back
		header("Location: " . AUTHORIZE_URL . "?oauth_token=" . $tokenResultParams['token']."&callback_id=".$callback_id);
	}
	else {
		//  STEP 2:  Get an access token
		$oauthToken = $_GET["oauth_token"];		
		$oauthVerifier = $_GET["oauth_verifier"];				
//		$tokenResultParams = $_GET;				
//		$_GET['oauth_verifier'] = $oauthVerifier;
				
		try {
		    OAuthRequester::requestAccessToken(CONSUMER_KEY, $oauthToken, 0, 'POST', $_GET, $curl_options);
		}
		catch (OAuthException2 $e)
		{
	 	    echo"<h2 style='color:red;'>Erro na solicitação, favor tentar em outro navegador</h2><br/>";
		    var_dump($e);
		    return;
		}

		$request = new OAuthRequester(OAUTH_HOST . "/wsusuario/oauth/usuariousp", 'POST');
		$result = $request->doRequest(null, $curl_options);
		if ($result['code'] == 200) {			
			require_once ('autenticacao.php');	
		}
		else {
			echo 'Error';
		}		
	}
}
catch(OAuthException2 $e) {
	echo "OAuthException:  " . $e->getMessage();
	var_dump($e);
}
?>
</body> 
</html>

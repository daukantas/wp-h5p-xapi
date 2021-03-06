<?php

/**
 * This file receives the xAPI statement as a http post.
 */

require_once __DIR__."/src/utils/Template.php";
require_once __DIR__."/src/utils/WpUtil.php";

use h5pxapi\Template;
use h5pxapi\WpUtil;

require_once WpUtil::getWpLoadPath();

$statementObject=json_decode(stripslashes($_REQUEST["statement"]),TRUE);
$content=json_encode($statementObject);//."asdf";

$url=get_option("h5pxapi_endpoint_url");
if (substr($url,-1)!="/")
	$url.="/";
$url.="statements";

$userpwd=get_option("h5pxapi_username").":".get_option("h5pxapi_password");

$headers=array(
	"Content-Type: application/json",
	"X-Experience-API-Version: 1.0.1",
);

$curl=curl_init();
curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);
curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
curl_setopt($curl,CURLOPT_USERPWD,$userpwd);
curl_setopt($curl,CURLOPT_URL,$url);
curl_setopt($curl,CURLOPT_POST,1);
curl_setopt($curl,CURLOPT_POSTFIELDS,$content);

$res=curl_exec($curl);
$decoded=json_decode($res,TRUE);
$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);

// We rely on the response to be an array with a single entry
// constituting a uuid for the inserted statement, something like
// ["70de9692-2a4e-4f66-8441-c15ef534b690"].
// Is this learninglocker specific?
if ($code!=200 || sizeof($decoded)!=1 || strlen($decoded[0])!=36) {
	$response=array(
		"ok"=>0,
		"message"=>"Unknown error",
		"code"=>$code
	);

	if ($decoded["message"])
		$response["message"]=$decoded["message"];
		
	if (is_string($res))
		$response["message"] = $res;

	echo json_encode($response);
	exit;
}

$response=array(
	"ok"=>1
);

echo json_encode($response);

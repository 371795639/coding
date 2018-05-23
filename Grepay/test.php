<?php
$params = array(
  'version'    => '1.0',
  'merchantId' => '801115853113001',
  'charset'    => 'utf-8',
  'language'   => 'en',
  'signType'   => 'MD5',
  'queryType'  => '1',
  'merchantOrderNo'=> '10003',
  //'startTime'  => '20121117020101',
  //'endTime'    => '20131117020101',
);
$key = "H1LIxLVT";
//$signMsg = "version=".$params['version']."&merchantId=".$params['merchantId'].
//    "&charset=".$params['charset']."&language=".$params['language']."&signType=".$params['signType'].
//    "&queryType=".$params['queryType']."&merchantOrderNo=".$params['merchantOrderNo']."&key=".$key;

//$signMsg = hash("SHA256","version=".$params['version']."&merchantId=".$params['merchantId'].
//    "&charset=".$params['charset']."&language=".$params['language']."&signType=".$params['signType'].
//    "&queryType=".$params['queryType']."&merchantOrderNo=".$params['merchantOrderNo']."&key=".$key);

$signMsg = md5("version=".$params['version']."&merchantId=".$params['merchantId'].
    "&charset=".$params['charset']."&language=".$params['language']."&signType=".$params['signType'].
    "&queryType=".$params['queryType']."&merchantOrderNo=".$params['merchantOrderNo']."&key=".$key);


foreach ($params as $item => $val){
    $curlPost .= "&".$item."=".$val;
}

$curlPost = $curlPost."&signMsg=".$signMsg;
//echo $curlPost;exit;
//echo $curlPost;exit();
$ch = curl_init();
$gateway_url = "https://sandbox-open.grepay.com/masapi/orderQuery.htm";
curl_setopt($ch, CURLOPT_URL, $gateway_url);
curl_setopt($ch,CURLOPT_POST,1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$curlPost);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
curl_close($ch);

echo $response;




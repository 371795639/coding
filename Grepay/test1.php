<?php

$back_data['state'] = 0;  //除非state为1，否则不予继续cascade
$back_data['isview'] = 1; //是否可以显示，默认都是可以显示的交易
$back_data['blacklist_state'] = 0; //是否加入黑名单

//street不能超过50个字符
//$cur_street = substr($order_info['bill_street'], 0, 50);
//$v_url = $order_info['order_site_domain'];
//提交的订单金额
//$v_amt = $order_info['gateway_amount'] * 100;

//basic params
$curlPost['version'] = "1.6";
$curlPost['merchantId'] = "801115853113001";
$curlPost['charset'] = "utf-8";
$curlPost['language'] = "en";
$curlPost['signType'] = "MD5";

//business params
$curlPost['merchantOrderNo'] = "10003001";
$curlPost['goodsName'] = "tech_support";
$curlPost['goodsDesc'] = "tech_support^virtual^300^test";
$curlPost['orderExchange'] = 2;
$curlPost['currencyCode'] = "USD";
$curlPost['orderAmount'] = "300";

//$curlPost['flag3D'] = "";
$curlPost['submitTime'] = date("YmdHis");
$curlPost['expiryTime'] = date("YmdHis", strtotime("+1 day"));
$curlPost['bgUrl'] = "http://www.yournotice.com";
//$curlPost['ext1'] = "";
//$curlPost['ext2'] = "";
//$curlPost['remark'] = "";

//payment params
$curlPost['payMode'] = 10;
$curlPost['orgCode'] = "VISA";
$curlPost['cardNumber'] = "4111111111111111";
//$curlPost['cardHolderFirstName'] = "";
//$curlPost['cardHolderLastName'] = "";
$curlPost['cardExpirationMonth'] = "11";
$curlPost['cardExpirationYear'] = "2019";
$curlPost['securityCode'] = "123";
$curlPost['cardHolderEmail'] = "test@hotmail.com";
$curlPost['cardHolderPhoneNumber'] = "13600000000";

//billing params
$curlPost['billName'] = "test";
$curlPost['billAddress'] = "test";
$curlPost['billPostalCode'] =  "230011";
//$curlPost['billCompany'] = "";
$curlPost['billCountry'] = "CHN";
$curlPost['billState'] = "hefei";
$curlPost['billCity'] = "anhui";
$curlPost['billEmail'] = "test2@hotmail.com";
$curlPost['billPhoneNumber'] = "13600555555";

//shipping params
$curlPost['shippingName'] = $curlPost['billName'];
$curlPost['shippingAddress'] = $curlPost['billAddress'];
$curlPost['shippingPostalCode'] = $curlPost['billPostalCode'];
//$curlPost['shippingCompany'] = "";
$curlPost['shippingCountry'] = $curlPost['billCountry'];
$curlPost['shippingState'] = $curlPost['billState'];
$curlPost['shippingCity'] = $curlPost['billCity'];
$curlPost['shippingEmail'] = $curlPost['billEmail'];
$curlPost['shippingPhoneNumber'] = $curlPost['billPhoneNumber'];

//risk control params
//‘m’+merchantId+’_’+guid
$curlPost['deviceFingerprintID'] = "m" . $curlPost['merchantId'] . "_" . create_guid();
//$curlPost['payerName'] = "";
//$curlPost['payerMobile'] = "";
//$curlPost['payerEmail'] = "";
//$curlPost['registerUserId'] = "";
$curlPost['registerUserEmail'] = $curlPost['billEmail'];
$curlPost['registerTime'] = date("YmdHis");
$curlPost['registerIp'] = "[202.96.209.16]";
$curlPost['registerTerminal'] = "00";
$curlPost['orderIp'] ="[202.96.209.16]";
$curlPost['orderTerminal'] = "00";
//$curlPost['referer'] = "";
//$curlPost['ext3'] = "";
//$curlPost['ext4'] = "";

//加密字符串
$signMsg = "version=" . $curlPost['version'] . "&merchantId=" . $curlPost['merchantId'] . "&signType=" . $curlPost['signType'] . "&merchantOrderNo=" . $curlPost['merchantOrderNo'] . "&currencyCode=" . $curlPost['currencyCode'] . "&orderAmount=" . $curlPost['orderAmount'] . "&submitTime=" . $curlPost['submitTime'] . "&cardNumber=" . $curlPost['cardNumber'] . "&cardExpirationMonth=" . $curlPost['cardExpirationMonth'] . "&cardExpirationYear=" . $curlPost['cardExpirationYear'] . "&securityCode=" . $curlPost['securityCode'] . "&key=H1LIxLVT";

//echo "<hr>";
$curlPost['signMsg'] = strtoupper(md5($signMsg));

//dump($curlPost);
//exit;

foreach ($curlPost as $item => $val){
    $curl.= "&".$item."=".$val;
}

$ch = curl_init();
//$gateway_url = "https://test-open.grepay.com/masapi/orderQuery.htm";
$gateway_url = "https://sandbox-open.grepay.com/masapi/receiveMerchantOrder.htm";
curl_setopt($ch, CURLOPT_URL, $gateway_url);
curl_setopt($ch,CURLOPT_POST,1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$curl);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
curl_close($ch);
echo $response;exit;



$reason_arr = [
];
$result = $this->apiProcess2($this->test_gateway,
    http_build_query($curlPost),
    $reason_arr,
    $count = 1,
    $source_ip = $order_info["order_source_ip"],
    $source_url = $v_url);

//dump($result);exit;
$rs_arr = json_decode($result['result'], true);
/////////////////////////////////////

switch ($rs_arr['resultCode']) {
    case "11": //declined
        $back_data['status'] = 0;
        $back_data['dc_reason'] = $rs_arr["errCode"] . "|" . $rs_arr["errMsg"];
        break;
    case "10": //success
        $back_data['status'] = 1;
        $back_data['ips_id'] = $rs_arr['masapayOrderNo'] ?: $rs_arr['GrepayOrderNo'];
        break;
    default: //pending
        $back_data['status'] = 2;
}

//dump($back_data);
//计入系统通知，可以查看通知情况
$in_title = 'Grepay直连即时通知-' . $order_info['order_id'] . '-' . $rs_arr['resultCode'];
//信用卡
$curlPost["cardNumber"] = hash("sha256", $card_info['number']);
$curlPost["securityCode"] = "***";
$in_content = dump($rs_arr, false) . '<hr>' . dump($curlPost, false) . '<hr>' . $this->gateway;
$in_oid = $order_info['order_id'];
$this->inputIPS($in_title, $in_content, $in_oid, $result['count']);
/////////////////////////////////////
return $back_data;

// uuid
function create_guid() {
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $hyphen = chr(45); // "-"
    $uuid = substr($charid, 0, 8) . $hyphen
        . substr($charid, 8, 4) . $hyphen
        . substr($charid, 12, 4) . $hyphen
        . substr($charid, 16, 4) . $hyphen
        . substr($charid, 20, 12);
    return $uuid;
}
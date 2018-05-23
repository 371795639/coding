<?php
/**
 * @describe Ipasspay payment Gateway
 * @version 1.0
 * @author Afonso
 * @email tech@ipasspay.com
 */
$config = require('../config/config.php'); // import config file
$params = array(
      'version'    => $config['version'],
      'merchantId' => $config['merchantId'],
      'charset'    => 'utf-8',
      'language'   => 'en',
      'signType'   => 'MD5', //MD5 SHA256

      // business param
      'merchantOrderNo' => isset($_POST["merchantOrderNo"]) ? $_POST["merchantOrderNo"] : "10003",
      'goodsName'       => isset($_POST["goodsName"]) ? $_POST["goodsName"] : "test",
      'goodsDesc'       => isset($_POST["goodsDesc"]) ? $_POST["goodsDesc"] : "test^test^1^3.00",
      'orderExchange'   => isset($_POST["orderExchange"]) ? $_POST["orderExchange"] : "1",
      'currencyCode'    => 'USD',
      'orderAmount'     => isset($_POST["orderAmount"]) ? $_POST["orderAmount"] : "300",
      'flag3D'          => 'N',
      'submitTime'      => date("YmdHis",time()),
      'expiryTime'      => isset($_POST["expiryTime"]) ? $_POST["expiryTime"] : "20191117020101",
      'bgUrl'           => $config['bgUrl'],
      'ext1'            => 'ext1',
      'ext2'            => 'ext2',
      'remark'          => 'remark',
      // payment param
      'payMode'         => '10',
      'orgCode'         =>  isset($_POST["orgCode"]) ? $_POST["orgCode"] : "VISA",
      'cardNumber'      =>  isset($_POST["cardNumber"]) ? $_POST["cardNumber"] : "4111111111111111",
      'cardHolderFirstName'=> isset($_POST["cardHolderFirstName"]) ? $_POST["cardHolderFirstName"] : "test",
      'cardHolderLastName' =>  isset($_POST["cardHolderLastName"]) ? $_POST["cardHolderLastName"] : "test",
      'cardExpirationMonth'=> isset($_POST["cardExpirationMonth"]) ? $_POST["cardExpirationMonth"] : "11",
      'cardExpirationYear' => isset($_POST["cardExpirationYear"]) ? $_POST["cardExpirationYear"] : "2019",
      'securityCode'       => isset($_POST["securityCode"]) ? $_POST["securityCode"] : "123",
      'cardHolderEmail'    => isset($_POST["cardHolderEmail"]) ? $_POST["cardHolderEmail"] : "test@hotmail.com",
      'cardHolderPhoneNumber'=> isset($_POST["cardHolderPhoneNumber"]) ? $_POST["cardHolderPhoneNumber"] : "13600000000",

      // bill info
      'billName'    => isset($_POST["cardHolderFirstName"])?$_POST["cardHolderFirstName"]."//".$_POST["cardHolderLastName"]:"test",
      'billAddress' => isset($_POST["billAddress"]) ? $_POST["billAddress"] : "test",
      'billPostalCode' => isset($_POST["billPostalCode"]) ? $_POST["billPostalCode"] : "230011",
      'billCompany' => isset($_POST["billCompany"]) ? $_POST["billCompany"] : "test",
      'billCountry' => isset($_POST["billCountry"]) ? $_POST["billCountry"] : "CHN",
      'billState'   => isset($_POST["billState"]) ? $_POST["billState"] : "anhui",
      'billCity'    => isset($_POST["billCity"]) ? $_POST["billCity"] : "hefei",
      'billEmail'   => isset($_POST["billEmail"]) ? $_POST["billEmail"] : "test@hotmail.com",
      'billPhoneNumber' => isset($_POST["billPhoneNumber"]) ? $_POST["billPhoneNumber"] : "13600000000",

      //shipping info
      'shippingName'    => isset($_POST["shippingName"]) ? $_POST["shippingName"] : "test1",
      'shippingAddress' => isset($_POST["shippingAddress"]) ? $_POST["shippingAddress"] : "test1",
      'shippingPostalCode'=> isset($_POST["shippingPostalCode"]) ? $_POST["shippingPostalCode"] : "230011",
      'shippingCompany'   => isset($_POST["shippingCompany"]) ? $_POST["shippingCompany"] : "test1",
      'shippingCountry'   => isset($_POST["shippingCountry"]) ? $_POST["shippingCountry"] : "test1",
      'shippingState'     => isset($_POST["shippingStat"]) ? $_POST["shippingStat"] : "hefei",
      'shippingCity'      => isset($_POST["shippingCity"]) ? $_POST["shippingCity"] : "anhui",
      'shippingEmail'     => isset($_POST["shippingEmail"]) ? $_POST["shippingEmail"] : "test@qq.com",
      'shippingPhoneNumber'=> isset($_POST["shippingPhoneNumber"]) ? $_POST["shippingPhoneNumber"] : "13600323232",

      // risk info
      'deviceFingerprintID' => create_fingerprint(),
      'payerName'           => 'test',
      'payerMobile'         => '13605555555',
      'payerEmail'          =>  'test@qq.com',
      'registerUserId'     => '2018',
      'registerUserEmail' =>   isset($_POST["registerUserEmail"]) ? $_POST["registerUserEmail"] : "test@gmail.com",
      'registerTime' => "20121117020101",
      'registerIp'   => '[202.96.209.16]',
      'registerTerminal' => '00',
      'orderIp'      =>  '[202.96.209.16]',
      'orderTerminal'=> '00',
      'referer'      => '',
      'ext3'         => 'ext3',
      'ext4'         => 'ext4',

);

//encrypted the sensitive data
//$signMsg = hash("sha256","version=".$params['version']."&merchantId=".$params['merchantId'].
//"&signType=".$params['signType']."&merchantOrderNo=".$params['merchantOrderNo']."&currencyCode=".$params['currencyCode'].
//"&orderAmount=".$params['orderAmount']."&submitTime=".$params['submitTime']."&cardNumber=".$params['cardNumber']."&cardExpirationMonth=".$params['cardExpirationMonth'].
//"&cardExpirationYear=".$params['cardExpirationYear']."&securityCode=".$params['securityCode']."&key=".$config['key']);

$signMsg = md5("version=".$params['version']."&merchantId=".$params['merchantId'].
    "&signType=".$params['signType']."&merchantOrderNo=".$params['merchantOrderNo']."&currencyCode=".$params['currencyCode'].
    "&orderAmount=".$params['orderAmount']."&submitTime=".$params['submitTime']."&cardNumber=".$params['cardNumber']."&cardExpirationMonth=".$params['cardExpirationMonth'].
    "&cardExpirationYear=".$params['cardExpirationYear']."&securityCode=".$params['securityCode']."&key=".$config['key']);

// post data string
foreach ($params as $item => $val){
    $curlPost .= "&".$item."=".$val;
}
$curlPost .= "&signMsg=".$signMsg;
//$curlPost = htmlspecialchars($curlPost);
//ECHO $curlPost;EXIT;
//$curlPost .= "version=".$params['version'];
//$curlPost .= "&merchantId=".$params['merchantId'];
//$curlPost .= "&charset=".$params['charset'];
//$curlPost .= "&language=".$params['language'];
//$curlPost .= "&signType=".$params['signType'];
//$curlPost .= "&merchantOrderNo=".$params['merchantOrderNo'];
//$curlPost .= "&goodsName=".$params['goodsName'];
//$curlPost .= "&goodsDesc=".$params['goodsDesc'];
//$curlPost .= "&orderExchange=".$params['orderExchange'];
//$curlPost .= "&currencyCode=".$params['currencyCode'];
//$curlPost .= "&orderAmount=".$params['orderAmount'];
//$curlPost .= "&submitTime=".$params['submitTime'];
//$curlPost .= "&expiryTime=".$params['expiryTime'];
//$curlPost .= "&bgUrl=".$params['bgUrl'];
//$curlPost .= "&payMode=".$params['payMode'];
//$curlPost .= "&orgCode=".$params['orgCode'];
//$curlPost .= "&cardNumber=".$params['cardNumber'];
//$curlPost .= "&cardHolderFirstName=".$params['cardHolderFirstName'];
//$curlPost .= "&cardHolderLastName=".$params['cardHolderLastName'];
//$curlPost .= "&cardExpirationMonth=".$params['cardExpirationMonth'];
//$curlPost .= "&cardExpirationYear=".$params['cardExpirationYear'];
//$curlPost .= "&securityCode=".$params['securityCode'];
//$curlPost .= "&cardHolderEmail=".$params['cardHolderEmail'];
//$curlPost .= "&cardHolderPhoneNumber=".$params['cardHolderPhoneNumber'];
//$curlPost .= "&billName=".$params['billName'];
//$curlPost .= "&billAddress=".$params['billAddress'];
//$curlPost .= "&billPostalCode=".$params['billPostalCode'];
//$curlPost .= "&billCompany=".$params['billCompany'];
//$curlPost .= "&billCountry=".$params['billCountry'];
//$curlPost .= "&billState=".$params['billState'];
//$curlPost .= "&billCity=".$params['billCity'];
//$curlPost .= "&billEmail=".$params['billEmail'];
//$curlPost .= "&billPhoneNumber=".$params['billPhoneNumber'];
//$curlPost .= "&shippingName=".$params['shippingName'];
//$curlPost .= "&shippingAddress=".$params['shippingAddress'];
//$curlPost .= "&shippingPostalCode=".$params['shippingPostalCode'];
//$curlPost .= "&shippingCompany=".$params['shippingCompany'];
//$curlPost .= "&shippingCountry=".$params['shippingCountry'];
//$curlPost .= "&shippingState=".$params['shippingState'];
//$curlPost .= "&shippingCity=".$params['shippingCity'];
//$curlPost .= "&shippingEmail=".$params['shippingEmail'];
//$curlPost .= "&shippingPhoneNumber=".$params['shippingPhoneNumber'];
//$curlPost .= "&deviceFingerprintID=".$params['deviceFingerprintID'];
//$curlPost .= "&registerUserEmail=".$params['registerUserEmail']; //®
//$curlPost .= "&registerTime=".$params['registerTime'];//®
//$curlPost .= "&registerIp=".$params['registerIp'];//®
//$curlPost .= "&registerTerminal=".$params['registerTerminal'];//®
//$curlPost .= "&orderIp=".$params['orderIp'];
//$curlPost .= "&orderTerminal=".$params['orderTerminal'];
//$curlPost .= "&signMsg=".$signMsg;
//echo $curlPost;exit;

$ch = curl_init();
//$gateway_url = "https://test-open.grepay.com/masapi/orderQuery.htm";
$gateway_url = "https://sandbox-open.grepay.com/masapi/receiveMerchantOrder.htm";
curl_setopt($ch, CURLOPT_URL, $gateway_url);
curl_setopt($ch,CURLOPT_POST,1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$curlPost);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
curl_close($ch);

//$result = json_decode($response,true);
//var_dump($result);die(); // Get the return parameter
echo $response;exit;

/*Payment Gateway(Direct)*/
function paymentDirect($curlPost){
    $ch = curl_init();
    $gateway_url = "https://www.ipasspay.biz/index.php/Gateway/securepay";
    curl_setopt($ch, CURLOPT_URL, $gateway_url);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$curlPost);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    curl_close($ch);

    //$result = json_decode($response,true);
    //var_dump($result);die(); // Get the return parameter
    echo $response;
}

/*Payment Gateway(Host)*/
function paymentHost($curlPost){
    $gateway_url = "https://www.ipasspay.biz/index.php/Gateway/paygates";
    $gatewau_host_url = $gateway_url."?".$curlPost;
    Header("HTTP/1.1 303 See Other");
    Header("Location: $gatewau_host_url");
    exit;
}


/*Creating uuid in PHP*/
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

/*Creating deviceFingerprintID in PHP*/
function create_fingerprint($merchantId) {
    $deviceFingerprintID = "m".$merchantId.create_guid();
    return $deviceFingerprintID;
}
// Execute different programs according to the specified parameters.
switch($_POST['paymentStyle'])
{
    case "direct":
        paymentDirect($curlPost);
        break;
    case "host":
        paymentHost($curlPost);
        break;
    default:
        break;
}
?>

<script src="https://h.online-metrix.net/fp/check.js?org_id=1snn5n9w&session_id=Grepay2<?php echo $params['deviceFingerprintID'] ?>></script>

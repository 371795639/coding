<?php
/**
 * Created by PhpStorm.
 * User: administrator
 * Date: 2018/5/22
 * Time: 上午9:52
 */

/**-----------------------------------begin--------------------------------------**/
$gateway_url="https://portal.duspay.com/api.htm";
$curlPost=array();
//<!--Replace of 3 very important parameters * your product API code -->

$curlPost["member"]="521";         // Merchant ID
$curlPost["bid"]="MguwLM0iJU";    // Secure API Key
$curlPost["product"]="85";        // Merchant Product Key
$curlPost["accountid"]="D2003";    // Merchant Account Id

//<!--default (fixed) value * default -->

$curlPost["cardsend"]="curl";
$curlPost["action"]="product";
$curlPost["mode"]="live";

//<!--product price and product name * by cart total amount -->

$curlPost["price"]="999.00";
$curlPost["product_name"]="I phone-7";

//<!--billing details of .* customer -->

$curlPost["ccholder"]="Mith";
$curlPost["ccholder_lname"]="San";
$curlPost["email"]="mith.san@gmail.com";
$curlPost["bill_street_1"]="25A Alpha";
$curlPost["bill_street_2"]="tagore lane";
$curlPost["bill_city"]="Jurong";
$curlPost["bill_state"]="Singapore";
$curlPost["bill_country"]="Singapore";
$curlPost["bill_zip"]="787602";
$curlPost["bill_phone"]="+65 62200944";
$curlPost["id_order"]="20170131";
$curlPost["notify_url"]="http://www.ipasspay.xyz/test1/test1.php"; //http://www.ipasspay.xyz/test1/test.php
$curlPost["client_id"]=$_SERVER['REMOTE_ADDR'];

//<!--card details of .* customer -->

$curlPost["ccno"]="4242424242424242";
$curlPost["ccvv"]="123";
$curlPost["month"]="01";
$curlPost["year"]="20";
$curlPost["notes"]="Remark for transaction";

$protocol = isset($_SERVER["HTTPS"])?'https://':'http://';
$referer=$protocol.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

$curl_cookie="";
$curl = curl_init();
curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
curl_setopt($curl, CURLOPT_URL, $gateway_url);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
curl_setopt($curl, CURLOPT_REFERER, $referer);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
curl_setopt($curl, CURLOPT_TIMEOUT, 300);
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_COOKIE,$curl_cookie);
$response = curl_exec($curl);
//echo $response;

//After posting the data you will get below response
/*{
    "transaction_id": "20171010122934",
    "status_nm": "1",
    "status": "Completed",
    "price": "999.00",
    "curr": "USD",
    "id_order": "20170131",
    "cardtype": "visa",
    "ccno": "XXXXXXXXXXXX4242",
    "reason": "success",
    "fullname": "Mith San",
    "email": "mith.san@gmail.com",
    "address": "25A Alpha,tagore lane ",
    "city": "Jurong",
    "state": "Singapore",
    "country": "Singapore",
    "phone": "+65 62200944",
    "product_name": "I phone-7",
    "amt": "999.00",
    "memail": "billing@duspay.com",
    "company": "duspay.com",
    "bussinessurl": "https://www.duspay.com",
    "contact_us_url": "https://www.duspay.com/contact-us",
    "customer_service_no": "912233445566",
    "tdate": "2017-10-11 10:30:09",

    "callbacks": "OK",
    "info": {
    "mode": "live",
        "accountid": "D2003",
        "ccholder": "Mith",
        "ccholder_lname": "San",
        "email": "mith.san@gmail.com"
        "etc...": .....
    }
}*/

// Echo for your data of response: (curl的返回response格式处理)
function jsonvaluef($theArray,$keyName,$array2=''){
    $theArray1=str_replace(array('{"','"}'),array('','","'),$theArray);
    if((!empty($theArray1))&&(strpos($theArray,$keyName)!==false)){
        if(!empty($array2)){
            $theArray1=explode($array2, $theArray1);
            $theArray1=$theArray1[1];
        }
        $keyName1=explode($keyName.'":"', $theArray1);
        if(!empty($keyName1[1])){
            $keyName2=explode('","', $keyName1[1]);
            return $keyName2[0];
        }
    }
}

//输出想要的transaction_id字段
echo jsonvaluef($response,"transaction_id");
//输出想要的price字段
echo jsonvaluef($response,"price");
//输出想要的status字段
echo jsonvaluef($response,"status");
//输出想要的reason字段
echo jsonvaluef($response,"reason");
// .... 输出想要的其他字段
/**-----------------------------------end--------------------------------------**/





/**-----------------------------------begin--------------------------------------**/
//https://yourdomain.com/notify.php 通知地址接口代码
if($_POST) {
    // post发送数组过来，
    echo $_POST['transaction_id'];
    echo $_POST['status_nm'];
    echo $_POST['id_order'];
    //....
}
/**-----------------------------------end--------------------------------------**/
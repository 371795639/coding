<?php
/**
 * @describe The query of bin
 * @version 1.0
 * @author Afonso
 * @email tech@ipasspay.com
 */
function getresult(){
$data = '400023
';
$result = preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)/" ,',' ,$data);
$arr=explode(',',$result);
foreach ($arr as $item => $value) {
    $res = json_decode(binCurl($value),true);
    $ref[$item]['bin'] = $value?$value:'null';

    $brand = str_replace(',', ' ', $res['brand']);
    $ref[$item]['brand'] = $brand?$brand:'null';

    $brandName = str_replace(',', ' ', $res['bank']['name']);
    $ref[$item]['bank_name'] = $brandName?$brandName:'null';

    //$ref[$item]['sub_brand'] = 'null';
    $ref[$item]['country_code'] = $res['country']['alpha2']?$res['country']['alpha2']:'null';

    $countryName = str_replace(',', ' ', $res['country']['name']);
    $ref[$item]['country_name'] = $countryName?$countryName:'null';

    $ref[$item]['card_type'] = $res['type']?$res['type']:'null';
    $ref[$item]['card_category'] = $res['scheme']?$res['scheme']:'null';
    $ref[$item]['latitude'] = $res['country']['latitude']?$res['country']['latitude']:'null';
    $ref[$item]['longitude'] = $res['country']['longitude']?$res['country']['longitude']:'null';
    //$ref[$item]['remark'] = 'null';
    $ref[$item]['cb_type'] = 1;// 1白名单 0黑名单

}
return $ref;
}
//bin  brand  bank_name sub_brand country_code  country_name  card_type  card_category  latitude  longitude
//status remark add_time
/**
 * 开始生成
 * 1. 首先将数组拆分成以逗号（注意需要英文）分割的字符串
 * 2. 然后加上每行的换行符号，这里建议直接使用PHP的预定义
 * 常量PHP_EOL
 * 3. 最后写入文件
 */
$csv_header = ['bin','brand','bank_name','sub_brand','country_code','country_name','card_type','card_category','latitude','longitude','remark','cb_type'];
// 打开文件资源，不存在则创建
$fp = fopen('test.csv','a');
// 处理头部标题
$header = implode(',', $csv_header) . PHP_EOL;
$csv_body = getresult();
// 处理内容
$content = '';
foreach ($csv_body as $k => $v) {
    $content .= implode(',', $v) . PHP_EOL;
}
// 拼接
$csv = $header.$content;
// 写入并关闭资源
fwrite($fp, $csv);
fclose($fp);

/* binCurl查询 */
function binCurl($curlPost){
    $ch = curl_init();
    $url = 'https://lookup.binlist.net/'."$curlPost";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//绕过ssl验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $headers = array(
        "Accept-Version: 3",
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

?>
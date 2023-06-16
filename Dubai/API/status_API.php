<?php
error_reporting(-1);
error_reporting(E_ALL);
ini_set("display_errors", 1);
// Provide working key share by CCAvenues
$working_key = 'DB52ABCF09144F1F145607BEEF3C349A';

// Provide access code Shared by CCAVENUES
$access_code = 'AVUQ03GK18BR25QURB';

// Provide URL shared by ccavenue (UAT OR Production url)
$URL = "https://login.ccavenue.ae/apis/servlet/DoWebTrans";

// Sample request string for the API call
$merchant_json_data = array(
    'reference_no' => '112040222681',
    'order_no' => ''
);

// Generate json data after call below method
$merchant_data = json_encode($merchant_json_data);
// Encrypt merchant data with working key shared by ccavenue
$encrypted_data = encrypt($merchant_data, $working_key);

//make final request string for the API call
$final_data = "request_type=JSON&access_code=" . $access_code . "&command=orderStatusTracker&version=1.1&response_type=JSON&enc_request=" . $encrypted_data;
//echo $final_data;exit;
// Initiate api call on shared url by CCAvenues
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $final_data);

// Get server response ... curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

$information = explode('&', $result);
$dataSize = sizeof($information);
$status1 = explode('=', $information[0]);
$status2 = explode('=', $information[1]);

if ($status1[1] == '1') {
    $recorddata = $status2[1];
} else {
    $status = decrypt(trim($status2[1]), $working_key);
    echo "<pre>";
    print_r($status);
    echo "</pre>";
}

function encrypt($plainText, $key) {
    $key = hextobin(md5($key));
    $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
    $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
    $encryptedText = bin2hex($openMode);
    return $encryptedText;
}

function decrypt($encryptedText, $key) {
    $key = hextobin(md5($key));
    $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
    $encryptedText = hextobin($encryptedText);
    $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
    return $decryptedText;
}

function hextobin($hexString) {
    $length = strlen($hexString);
    $binString = "";
    $count = 0;
    while ($count < $length) {
        $subString = substr($hexString, $count, 2);
        //echo $subString;exit;           
        $packedString = pack("H*", $subString);
        if ($count == 0) {
            $binString = $packedString;
        } else {
            $binString .= $packedString;
        }

        $count += 2;
    }
    return $binString;
}

?>

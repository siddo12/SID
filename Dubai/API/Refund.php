
<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus®">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <title>API</title>
  <h1>Refund</h1>
  <p>The Refund API call allows you to refund an order/transaction that has previously been executed but not
yet refunded. Funds will be refunded to the credit card or debit card or net banking account that was
originally charged.</p>
 </head>
 <body>
  <form action="<?php $_SERVER["PHP_SELF"]; ?>" name="form1" method="post"> 
 <table>
 
 <tr><td>CCAvenue Reference Number :</td> <td><input type ="text" name ="reference_no"></td></tr>
 <tr><td>Refund Amount :</td> <td><input type ="text" name ="refund_amount"></td></tr>
 <tr><td>Merchant Reference Number :</td> <td><input type ="text" name ="refund_ref_no"></td></tr>
<tr><td>Access Code : </td>  <td><input type ="text" name ="access_code"> </td> </tr>
<tr><td>Working Key : </td>   <td><input type ="text" name ="working_key"> </td> </tr>
</table>
<input type= "submit" value="Click"> </br> </br>

 <Strong >Auth Query Result : </strong> </br> </br>
 </body>
</html>

<?php

function encrypt($plainText, $key) {
    $secretKey = hex2bin(md5($key));
    $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
    $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $secretKey, OPENSSL_RAW_DATA, $initVector);
    $encryptedText = bin2hex($openMode);
    return $encryptedText;
}

function decrypt($encryptedText, $key) {
    $key = hex2bin(md5($key));
    $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
    $encryptedText = hex2bin($encryptedText);
    $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
    return $decryptedText;
}

//to enable error
error_reporting(-1);
ini_set('display_errors', 'On');

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    // $working_key = '0EFF84E6DF7FDDAC027230DD77B4E8C7'; //Shared by CCAVENUES
    // $access_code = 'AVPH03GF03BJ81HPJB';

    $access_code = $_POST ["access_code"];
    $working_key = $_POST ["working_key"]; 

    $merchant_json_data =
        array(
        // 'reference_no' => '108009648337',
        'reference_no' => $_POST ["reference_no"],
        'refund_amount' => $_POST ["refund_amount"],
        'refund_ref_no' => $_POST ["refund_ref_no"],
    );

    $merchant_data = json_encode($merchant_json_data);
    $encrypted_data = encrypt($merchant_data, $working_key);
    $final_data = "request_type=JSON&response_type=JSON&access_code=".$access_code."&command=refundOrder&version=1.1&enc_request=" . $encrypted_data;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://login.ccavenue.ae/apis/servlet/DoWebTrans");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $final_data);
    $result = curl_exec($ch);
    curl_close($ch);

    //echo $result;exit;      //uncomment to check response

    //decrypting response
    $status = '';
    $information = explode('&', $result);
    $dataSize = sizeof($information);
    for ($i = 0; $i < $dataSize; $i++) {
        $info_value = explode('=', $information[$i]);
        if ($info_value[0] == 'enc_response') {
           // echo $info_value[1];
           $status = decrypt(trim($info_value[1]), $working_key);
        }
    }
    // header('Content-Type: application/json');
    echo 'Status revert is: ' . $status;
    exit;
}

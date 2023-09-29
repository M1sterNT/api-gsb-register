<?php
class GSBClass
{
    private $aes_key = "fc1e360027628363c1178875053668750bfb6ed4cd1cabea0a407ff034105987";
    private $aes_iv = "36453633394331363442344239313938";
    private $mobile_api_gateway = "https://mymo.gsb.or.th:20443";
    private $version = "2.15.4";

    public function buildHeaders($array)
    {
        $headers = array();
        foreach ($array as $key => $value) {
            $headers[] = $key . ": " . $value;
        }
        return $headers;
    }
    public function request($method, $endpoint, $headers = array(), $data = null)
    {
        $handle = curl_init();
        if (!is_null($data)) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($data));
            if (is_array($data)) $headers = array_merge(array("Content-Type" => "application/json", "Accept-Language" => "th"), $headers);
        } else {
            $headers = array_merge(array("Accept-Language" => "th"), $headers);
        }

        curl_setopt_array($handle, array(
            CURLOPT_URL => rtrim($this->mobile_api_gateway, "/") . $endpoint,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Android10Xiaomi; MyMo254;" . $this->version,
            CURLOPT_VERBOSE => false,
            CURLOPT_HTTPHEADER => $this->buildHeaders($headers),
        ));

        $result = json_decode(curl_exec($handle), true);
        return $result;
    }
    function decrypt($encData)
    {
        $baseData = base64_decode($encData);
        $aes_key = hex2bin($this->aes_key);
        $aes_iv = hex2bin($this->aes_iv);
        $res = openssl_decrypt($baseData, 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv);
        return $res;
    }
    function encrypt($encData)
    {
        $aes_key = hex2bin($this->aes_key);
        $aes_iv = hex2bin($this->aes_iv);
        $res = openssl_encrypt($encData, 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv);
        return $res;
    }
    function pbkdf2($algorithm, $password, $salt, $count, $key_length)
    {
        $hash_length = strlen(hash($algorithm, '', true));
        $block_count = ceil($key_length / $hash_length);

        $output = '';
        for ($i = 1; $i <= $block_count; $i++) {
            $last = $salt . pack('N', $i);
            $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
            for ($j = 1; $j < $count; $j++) {
                $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
            }
            $output .= $xorsum;
        }
        return substr($output, 0, $key_length);
    }
    function encryptUser($encData, $seed)
    {

        $saltValue = "1461200161101" . $seed;
        $derivedKey = $this->pbkdf2('sha1', "0", $saltValue, 5, 32);
        // $transferKey = base64_encode($derivedKey);
        //ket = userid+seed
        $aes_key = $derivedKey; // hex2bin($this->aes_key);
        $aes_iv = hex2bin($this->aes_iv);
        $res = openssl_encrypt($encData, 'AES-256-CBC', $aes_key,  OPENSSL_RAW_DATA, $aes_iv);
        return $res;
    }
    function decryptUser($encData, $seed)
    {
        $baseData = base64_decode($encData);

        $saltValue = "1461200161101" . $seed;
        $derivedKey = $this->pbkdf2('sha1', "0", $saltValue, 5, 32);
        $aes_key = $derivedKey;
        $aes_iv = hex2bin($this->aes_iv);
        $decryptedData = openssl_decrypt($baseData, 'AES-256-CBC', $aes_key, OPENSSL_RAW_DATA, $aes_iv);
        return $decryptedData;
    }
    function validateVersion()
    {
        $payload = '{"citizenId":"","uniqueKey":"","version":"' . $this->version . '","lang":"th","os":"Android","deviceModel":"Redmi 8","osVersion":"29","longitude":"","latitude":"","isCDNSupported":"1"}';
        $payload_enc = $this->encrypt($payload);
        $payload_enc = base64_encode($payload_enc);
        $header = array(
            "data" => $payload_enc
        );
        $req = array(
            "app" => "MyMoGSB",
            "dom" => "MyMo",
            "op" => "validateVersion",
            "sid" => "",
            "srv" => "MyMoAuthen",
            "header" => json_encode($header, true)
        );
        print_r($req );
        return;
        $payload_array["req"] = $req;
        $result = $this->request("POST", "/json/MyMoAuthen/validateVersion", array(), $payload_array);
        return $result;
    }

    function saveAppProtectionLog()
    {
        $payload = '{"packageName":"[]","mobileTimeStamp":"20230823 22:55","citizenId":"","activity":"ActivationActivity","isBypass":"N","mode":"","isBlock":"N","type":"APPSIGN","code":"1","version":"2.15.4","lang":"th","os":"Android","deviceModel":"Redmi 8","osVersion":"29","isCDNSupported":"1"}';
        $payload_enc = $this->encrypt($payload);
        $payload_enc = base64_encode($payload_enc);
        $header = array(
            "data" => $payload_enc
        );
        $req = array(
            "app" => "MyMoGSB",
            "dom" => "MyMo",
            "op" => "saveAppProtectionLog",
            "sid" => "",
            "srv" => "Security",
            "header" => json_encode($header, true)
        );
        $payload_array["req"] = $req;
        $result = $this->request("POST", "/json/Security/saveAppProtectionLog", array(), $payload_array);
        return $result;
    }
    function checkMyMoUser($encData)
    {
        $payload = '{"citizenId":"1461200161101","version":"2.15.0","lang":"th","os":"Android","deviceModel":"Redmi 8","osVersion":"29","longitude":"","latitude":"","isCDNSupported":"1"}';
        $payload_enc = $this->encrypt($payload);
        $payload_enc = base64_encode($payload_enc);
        $header = array(
            "data" => $payload_enc
        );
        $req = array(
            "app" => "MyMoGSB",
            "dom" => "MyMo",
            "op" => "checkMyMoUser",
            "sid" => "",
            "srv" => "MyMoAuthen",
            "header" => json_encode($header, true)
        );
        $payload_array["req"] = $req;
        $result = $this->request("POST", "/json/MyMoAuthen/checkMyMoUser", array(), $payload_array);
        return $result;
    }

    function requestOtp($encData)
    {
        $payload = '{"citizenId":"1461200161101","version":"2.15.0","lang":"th","os":"Android","deviceModel":"Redmi 8","osVersion":"29","longitude":"","latitude":"","isCDNSupported":"1"}';
        $payload_enc = $this->encrypt($payload);
        $payload_enc = base64_encode($payload_enc);
        $header = array(
            "data" => $payload_enc
        );
        $req = array(
            "app" => "MyMoGSB",
            "dom" => "MyMo",
            "op" => "otpRequest",
            "sid" => "",
            "srv" => "MyMoAuthen",
            "header" => json_encode($header, true)
        );
        $payload_array["req"] = $req;
        $result = $this->request("POST", "/json/MyMoAuthen/otpRequest", array(), $payload_array);
        return $result;
    }

    function validateOtp($otp)
    {
        $payload = '{"citizenId":"1461200161101",key:"28029","version":"2.15.0","lang":"th","os":"Android","deviceModel":"Redmi 8","osVersion":"29","longitude":"","latitude":"","isCDNSupported":"1"}';
        $payload_enc = $this->encrypt($payload);
        $payload_enc = base64_encode($payload_enc);
        $header = array(
            "data" => $payload_enc
        );
        $req = array(
            "app" => "MyMoGSB",
            "dom" => "MyMo",
            "op" => "validateOtp",
            "sid" => "",
            "srv" => "MyMoAuthen",
            "header" => json_encode($header, true)
        );
        $payload_array["req"] = $req;
        $result = $this->request("POST", "/json/MyMoAuthen/validateOtp", array(), $payload_array);
        return $result;
    }
    function login($otp)
    {
        $payload = '{"user":"1461200161101",signedKey:"9cc9d018a69662a73026b4cc5b548411",pwd:"252700",ack:"{SHA2_512}2edbd066fcd5d53fad9517f4425fa42498c45461d96cd7f3f602161654dc39bc2f8a675357c2ae78ddc979f58a20392920637793b67086cc389524390da5b732|UX9VKgEEecywoLtM",uniqueKey:"{SHA2_512}2edbd066fcd5d53fad9517f4425fa42498c45461d96cd7f3f602161654dc39bc2f8a675357c2ae78ddc979f58a20392920637793b67086cc389524390da5b732|UX9VKgEEecywoLtM",rootFlag:0,checkFlag:0,"appVersion":"2.15.0","lang":"th","os":"Android","phoneModel":"Redmi 8","osVersion":"29","longitude":"","latitude":"","isCDNSupported":"1"}';
        $payload_enc = $this->encrypt($payload);
        $payload_enc = base64_encode($payload_enc);
        $header = array(
            "data" => $payload_enc
        );
        $req = array(
            "app" => "MyMoGSB",
            "dom" => "MyMo",
            "op" => "login",
            "sid" => "",
            "srv" => "MyMoAuthen",
            "header" => json_encode($header, true)
        );
        $payload_array["req"] = $req;
        $result = $this->request("POST", "/json/MyMoAuthen/login", array(), $payload_array);
        return $result;
    }
    function transaction($seed, $sid)
    {
        $payload = '{lastTransactionSeq:"",numberOfRecord:"20","deviceModel":"Redmi 8","lang":"th","os":"Android","osVersion":"29","isCDNSupported":"1"}';
        $payload_enc = $this->encryptUser($payload, $seed);
        $payload_enc = base64_encode($payload_enc);

        $header = array(
            "data" => $payload_enc
        );


        $req = array(
            "app" => "MyMoGSB",
            "dom" => "MyMo",
            "op" => "getTimeline",
            "sid" => $sid,
            "srv" => "Timeline",
            "header" => json_encode($header, true)
        );
        $payload_array["req"] = $req;

        $result = $this->request("POST", "/json/User/getTimeline", array(), $payload_array);
        return $result;
    }
    function validateSignedKey()
    {
        $payload = '{"signedKey":"9cc9d018a69662a73026b4cc5b548411","version":"' . $this->version . '","lang":"th","os":"Android","deviceModel":"Redmi 8","osVersion":"29","longitude":"","latitude":"","isCDNSupported":"1"}';
        $payload_enc = $this->encrypt($payload);
        $payload_enc = base64_encode($payload_enc);
        $header = array(
            "data" => $payload_enc
        );
        $req = array(
            "app" => "MyMoGSB",
            "dom" => "MyMo",
            "op" => "validateSignedKey",
            "sid" => "",
            "srv" => "MyMoAuthen",
            "header" => json_encode($header, true)
        );
        $payload_array["req"] = $req;
        $result = $this->request("POST", "/json/MyMoAuthen/validateSignedKey", array(), $payload_array);
        return $result;
    }
}

$gsb = new GSBClass();

$res =  $gsb->validateVersion();
//$res =  $gsb->decrypt($res["res"]["header"]["data"]);

$data = "PFcVgPTMobph9eqltjsLUJCwGgJRginVPpztWJuehlTsqhBAv8UWVp8LKnD5GP3P1iuuuPXDIzDoPa3hsdMWm52eUZKb8HKELsPwQ9IGT7AVYC76FiwDPfRSIEKZL+FUElS20cZe1LU+1aw/jzkeDmKzooG3H22ILDjryoTXiowEbEQQExtjrhUhzLlLqw1Tn+RNz4+nc4+uOu2I/ZfAVeluPRzy2HgOueBmzYo0Hd977oJ8EOufHAWfiXiG6ZiwUKsxSATjjYxgTMC7S2SCtEW2ExFelWygMcEJakf2Fbj7KqRXbW9JIT8HvZG2prX/n9UYKSUbxNNHBnI80RBYnT00W1mesCo7AA+ao36EsVJnRUrY73EQ3+boZoS0UbQkfDnt6y+B2RxHzXnxzKWIDBzhPlhqWwLkpEdIcNSIVvoTUXq4Srx5VE3Ww6LdyCZonR7Yfy6VvW2JchJea51pqofhoSL4VnVFwsWFDGQYH2HnUlWU6rRJRBLwzjOUC5VlR/jSshaSA47P4N/1Fvm01Jsw2DLINS4kLmAzcvU3Ex+eII1F19IAgPpIDlfiWfBqIgSri5mIul35XSPkGmyD8NZ8f/5hFZyCsy9jHYu/lVAxmtfq9Tjyws4ZjmZprVdXaAXYZrw56+fKkbGVogoPkJAe7Fpmf5mOzFT27maq7KxHBNxyKw4jRp5OKHDaNI0KdCdVjNlpCdhG8C8uKSTw1ViWu/nCXt+7xQc2YejJ5NXZ/+SDUiEth5njVkUrc7MP";
//$res =  $gsb->saveAppProtectionLog();
//$res =  $gsb->decrypt($res["res"]["header"]["data"]);
//$res =  $gsb->decrypt($data);
//$res =  $gsb->requestOtp($data );

//$res =  $gsb->validateOtp("");

//$res =  $gsb->decrypt($data);

/*
$res =  $gsb->login("");
$seed = $gsb->decrypt($res["res"]["header"]["data"]);

$sid = $res["res"]["header"]["sid"];
$seed = json_decode($seed, true)["seed"];
print_r($seed);

echo $sid . "\r\n";
echo $seed . "\r\n";

$res  = $gsb->transaction($seed, $sid);
print_r($res);

$res =  $gsb->decryptUser($res["res"]["header"]["data"],$seed);
$res = json_decode($res, true);

//$res =  $gsb->transaction("");
*/
print_r($res);

//$res =  $gsb->decrypt($res["res"]["header"]["data"]);

//

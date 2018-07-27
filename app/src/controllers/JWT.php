<?php

include_once('./vendor/autoload.php');



function $new_jwt($id, $passwd){
  $tokenId = base64_encode("tokenID_example");
  $issuedAt = time();
  $notBefore = $issuedAt;
  $expire = $notBefore + 60*60;
  $serverName = "test_server";


  $secret_key = "secret_key_value";

  $acco_id = "shurima";
  $server_no = 1;

  $data = array(
     'iat' => $issuedAt,
     'jti' => $tokenId,
     'iss' => $serverName,
     'nbf' => $notBefore,
     'exp' => $expire,
     'data' => [
        'acco_id' => $acco_id,
        'server_no' => $server_no,
     ]

  );

  $jwt = JWT::encode($data, $secret_key);
  //echo "encoded jwt: " . $jwt . "n";

  //$decoded = JWT::decode($jwt, $secret_key, array('HS256'));

  return $jwt;
}

?>

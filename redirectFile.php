<?php
 //naver_login_callback.php
 
 $client_id = "iuc_oIU82EUXsnt6jbfy";   //ClientID 입력
 $client_secret = "9Ab2P9sLHR"; //Client Secret 입력
 
 $code = $_GET["code"];
 $state = $_GET["state"];
 $redirectURI = urlencode("https://test.daekyung.shop/redirectFile.php"); // 현재 Callback Url 입력
 
 $url = "https://nid.naver.com/oauth2.0/token?grant_type=authorization_code&client_id=".$client_id."&client_secret=".$client_secret."&redirect_uri=".$redirectURI."&code=".$code."&state=".$state;
 $is_post = false;
 
 $ch = curl_init();
 curl_setopt($ch, CURLOPT_URL, $url);
 curl_setopt($ch, CURLOPT_POST, $is_post);
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
 $headers = array();
 $response = curl_exec ($ch);
 $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 echo "status_code:".$status_code;
 
 curl_close ($ch);
 $responseArr = json_decode($response, true);
 $id = $responseArr->response->id;
 $name = $responseArr->response->name;
 $email = $responseArr->response->email;

 if($status_code == 200) {
  echo $response;
  echo "<br>id : ".$id;
  echo "<br>name : ".$name;
  echo "<br>email : ".$email;
 } else {
  echo "Error 내용:".$response;
 }
?>

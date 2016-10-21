<?php

   require_once("data/TestCity.php");
   
   $testCity = new TestCity();
   
   $params = ['p1' => TestCity::$defaultJson, 'server' => 197, 'player' => 'test', 'test'=> 1];
   $default = array (
      CURLOPT_URL => "192.168.1.77:8000/main.php",
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $params
   );
   $ch = curl_init();
   
   curl_setopt_array($ch, $default);   
   $output = curl_exec($ch);
   
   curl_close($ch);
   
   print $output;
   
?>
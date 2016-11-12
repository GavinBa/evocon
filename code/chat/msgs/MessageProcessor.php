<?php

abstract class MessageProcessor {

  abstract public function process($cr,$cs,$msg,$dbalt);
  
}

?>
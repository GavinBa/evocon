<?php
class StateMarket extends StateProcessor {
   
  var $m_city;
  var $m_cr;
  
  
  public function __construct($city,$cr) {
	$this->m_city = $city;
   $this->m_cr = $cr;
  }
  
  public function process($cs,$state) {
     
     $result = STATE_IDLE;
     
     switch ($state) {
        case STATE_MARKET:
           $cs->addEcho("in market buy sell");
           $a = [ "server" => $this->m_cr->getServer(), 
                  "user" => $this->m_cr->getUser(), 
                  "city" => $this->m_city->getName()];
           $cs->injectScriptVars("client/scripts/BuySell.txt", $a);
           $result = STATE_IDLE;
           break;
        default:
           break;
     }
     return $result;
  }
}
?>
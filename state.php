<?php 
require_once "states.php";
require_once "code/states/StateMonitor.php";
require_once "code/newcity/NewCity.php";
require_once "code/buildings/IdleBuild.php";
require_once "code/cities/DeadCities.php";
require_once "code/market/Market.php";

class StateController {
  var $m_city;
  var $m_cs;
  var $m_cr;
  
  public function __construct($city, $cs, $cr) {
	$this->m_city = $city;
   $this->m_cs = $cs;
   $this->m_cr = $cr;
  }
  
   public function nextState($state) {
      $result       = STATE_SUSPEND;
      $ps           = SLICE_MAX;
      $group        = getGroup($state);
      $initialSlice = $this->m_cr->getDbc()->getProcessSlice();
      
      if ($this->m_city->isUnderAttack()) {
         $this->m_cs->addLine("echo 'under attack'");
         if ($group != STATE_WAR) {
            $group = $state = STATE_WAR;
         }
      }
      
      while ($result == STATE_SUSPEND && $ps != $initialSlice) {
         
         switch ($group) {

            case STATE_IDLE:
             $ps = $this->m_cr->getDbc()->getProcessSlice();
             $ps++;
             if ($ps >= SLICE_MAX) {
                $ps = SLICE_IDLE;
             }
             $this->m_cr->getDbc()->setProcessSlice($ps);
             $result = $this->slice($ps);
            break;
            
            case STATE_MONITOR:
               $p = new StateMonitor($this->m_city);
               $result = $p->process($this->m_cs,$state);
               break;
               
            case STATE_WAR:
               $result = STATE_IDLE;
               break;
               
            case STATE_NEWCITY:
               $p = new NewCity($this->m_city, $this->m_cr, $this->m_cs);
               $result = $p->process($this->m_cs,$state);
               break;
               
            case STATE_IDLEBUILDS:
               $p = new IdleBuild($this->m_city);
               $result = $p->process($this->m_cs,$state);
               break;
               
            case STATE_DEADCITIES:
               $p = new DeadCities($this->m_city,$this->m_cr);
               $result = $p->process($this->m_cs,$state);
               break;
               
            case STATE_MARKET:
               $p = new StateMarket($this->m_city,$this->m_cr);
               $result = $p->process($this->m_cs,$state);
               break;
               
           default:
             $result = STATE_IDLE;
             break;
         }
      }

      /* If all states reach a suspended state then transition back to idle. */      
      if ($result == STATE_SUSPEND) { 
         $result = STATE_IDLE;
      }

      return $result;
	  
   }

   /* Use the current process slice to set the associated state. */   
   protected function slice($ps) {
       switch ($ps) {
          case SLICE_IDLE:
            $result = STATE_IDLE;
            break;
          case SLICE_MONITOR:
            $result = STATE_MONITOR;
            break;
          case SLICE_NEWCITY:
            $result = STATE_NEWCITY;
            break;
          case SLICE_IDLEBUILDS:
            $result = STATE_IDLEBUILDS;
            break;
          case SLICE_DEADCITIES:
            $result = STATE_DEADCITIES;
            break;
          case SLICE_MARKET:
            $result = STATE_MARKET;
            break;
          default:
            $result = STATE_MARKET;
            break;
       }
       return $result;
   }
  
}
?>
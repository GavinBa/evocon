<?php

require_once "lib/util.php";

class NewCity extends StateProcessor {

   var $m_city;
   var $m_cr;
   var $m_cs;
   
   public function __construct($city, $cr, $cs) {
      $this->m_city = $city;
      $this->m_cr = $cr;
      $this->m_cs = $cs;
   }

   public function process($cs,$state) {
      
      $result = STATE_IDLE;
      
      switch ($state) {
         case STATE_NEWCITY:
            return STATE_IDLE;
            /* Check if any city has already started building */
            if ($this->m_cr->getDbc()->getNewCity() == 0) {
               $cs->injectScript("client/scripts/CheckIfCanBuild.txt");
               $result = STATE_NEWCITY_CANBUILD;
            } else {
               $result = STATE_IDLE;
            }
            break;
            
         case STATE_NEWCITY_CANBUILD:
            $p2 = util_setParam("p2", 0);
            $p2json = json_decode($p2);
            if ($p2json->result > 0) {
               $cs->addLine("echo '-----I CAN BUILD----'" . PHP_EOL);
               $result = STATE_NEWCITY_FINDFLAT;
            } else {
               $cs->addLine("echo '-----NO BUILD----'" . PHP_EOL);
               $result = STATE_IDLE;
            }
            $result = STATE_IDLE;
            break;
            
         case STATE_NEWCITY_FINDFLAT:
            $a = [ "x" => $this->m_cr->getCity()->getJson()->x, 
                   "y" => $this->m_cr->getCity()->getJson()->y];
            $cs->injectScriptVars("client/scripts/FindAnyFlatFromPoint.txt",$a);
            $result = STATE_NEWCITY_FLATS;
//            $result = STATE_IDLE;
            break;
            
         case STATE_NEWCITY_FLATS:
            /* Now have a list of candidate flats - find the best one. */
            $p2 = util_setParam("p2", 0);
            $p2json = json_decode($p2);
//            $cs->debugOn();
            $cs->addLine("m = result = f = isOwned = canAttack = user = 0" . PHP_EOL);
            if (isset($p2json->fields)) {
               /* The best flat will have the most level 5 npcs. */
               $cnt = 0;
               foreach ($p2json->fields as $field) {
                  $f = new Field($field);
                  $a = [ "x" => "GetX(".$f->getJson()->id.")", 
                         "y" => "GetY(".$f->getJson()->id.")" ];
                  $cs->injectScriptVars("client/scripts/FindNpcsLevel5.txt",$a);
                  $cs->addLine("if t.length > m result = ".$f->getJson()->id);
                  $cs->addLine("if t.length > m m = t.length");
                  $cs->addLine("if t.length > m isOwned = ".$f->isOwned());
                  $cs->addLine("if t.length > m canAttack = ".$f->canAttack());
                  $cs->addLine("if t.length > m user = '".$f->getUser()."'");
                  if (++$cnt > 5) { break; }
               }
               $cs->addLine("echo 'the best flat is at '+FieldIdToCoords(result)");
               $cs->addLine("v = { 'result' : result , ".
                                  "'isOwned' : isOwned , ".
                                  "'canAttack' : canAttack , ".
                                  "'user' : user }");
               $cs->addLine("p2v = json_encode(v)");
            }
//            $cs->debugOff();
            $result = STATE_IDLE;
//            $result = STATE_NEWCITY_BESTFLAT;
            break;
            
         case STATE_NEWCITY_BESTFLAT:
            /* The best flat has been deteremined and it is processed. */
            $p2 = util_setParam("p2", 0);
            $p2json = json_decode($p2);
            if (isset($p2json->result)) {
               if ($p2json->isOwned && $p2json->user == $this->cr->getUser()) {
                  $flatid = $p2json->result;
                  $cs->addLine("execute 'buildcity '+FieldIdToCoords(".$flatid.")");
                  $this->m_cr->getDbc()->setNewCity(1);
               } else {
                  if ($p2json->isOwned) {
                     $cs->addLine("execute 'attack '+FieldIdToCoords(".$p2json->result.")+' any w:1k'");
                  } else {
                     $cs->addLine("execute 'attack '+FieldIdToCoords(".$p2json->result.")+' any a:10k,p:1,w:1k,s:1,sw:1'");
                  }
               }
            }
            $result = STATE_IDLE;
            break;
            
         default:
            $result = STATE_IDLE;
            break;
      }
      
      return $result;
   }
   
}

?>
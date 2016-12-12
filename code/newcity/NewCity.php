<?php

require_once "lib/util.php";
require_once "code/fields/Field.php";

// NewCity
//   If the account can spawn a new city then proceed through the steps...

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

      $cs->addEcho("In NewCity: ". $state);
      
      $result = STATE_IDLE;
      
      
      if ($this->m_city->getName() == "Flat" ||
          $this->m_city->getName() == "New city") {
         $state = STATE_NEWCITY_RENAME;
      }
      
      else {
         // First check if there is an outstanding flat-grab...
         $nc = $this->m_cr->getDbc()->getNewCity();
         if ($nc == 2) {
            $state = STATE_NEWCITY_WAITONFLAT;
         } else if ($nc == 1) {
            // there should be an active city build going on - get a count on
            // current cities and get result.
            $state = STATE_NEWCITY_GETCITYCNT;
         } else if ($nc == 3) {
            $state = STATE_NEWCITY_CHECKCITYCNT;
         } else if ($nc == 4) {
            $state = STATE_NEWCITY_GETCITYNAMES;
         } else if ($nc == 5) {
            // we got here and our city name was not the default - so we are done.
            $this->m_cr->getDbc()->setNewCity(0);
            $this->m_cr->getDbc()->setNewCityFieldId(0);
         }
      }
      
      
      switch ($state) {
         case STATE_NEWCITY:
//            $cs->addEcho("No NEWCITY checking at the moment.");
//            return STATE_SUSPEND;
            /* Check if any city has already started building */
            if ($this->m_cr->getDbc()->isAnyCitySpawning() == false) {
               $cs->injectScript("client/scripts/CheckIfCanBuild.txt");
               $result = STATE_NEWCITY_CANBUILD;
            } else {
               $result = STATE_SUSPEND;
            }
            break;
            
         case STATE_NEWCITY_CANBUILD:
            $p2 = util_setParam("p2", 0);
            if ($p2) {
               $p2json = json_decode($p2);
               if ($p2json != NULL && $p2json->result > 0) {
                  $cs->addLine("echo '-----I CAN BUILD----'" . PHP_EOL);
                  $result = STATE_NEWCITY_FINDFLAT;
               } else {
                  $cs->addLine("echo '-----NO BUILD----'" . PHP_EOL);
                  $result = STATE_IDLE;
               }
            } else {
               $result = STATE_IDLE;
            }
            break;
            
         case STATE_NEWCITY_FINDFLAT:
            $a = [ "x" => $this->m_cr->getCity()->getJson()->x, 
                   "y" => $this->m_cr->getCity()->getJson()->y];
            $cs->injectScriptVars("client/scripts/FindAnyFlatFromPoint.txt",$a);
            $result = STATE_NEWCITY_FLATS;
//            $result = STATE_IDLE;
            break;
            
         case STATE_NEWCITY_FLATS:
            $result = STATE_IDLE;
            /* Now have a list of candidate flats - find the best one. */
            $p2 = util_setParam("p2", 0);
            $p2json = json_decode($p2);
//            $cs->debugOn();
            $cs->addLine("m = result = f = isOwned = canAttack = user = 0" . PHP_EOL);
            if (isset($p2json->fields) && count($p2json->fields) > 0) {
               /* The best flat will have the most level 5 npcs. */
               $cnt = 0;
               foreach ($p2json->fields as $field) {
                  $f = new Field($field);
                  // don't bother checking if the flat is not owned and can't attack
                  if ($f->isOwned() && $f->canAttack()) {
                     $a = [ "x" => "GetX(".$f->getJson()->id.")", 
                            "y" => "GetY(".$f->getJson()->id.")" ];
                     $cs->injectScriptVars("client/scripts/FindNpcsLevel5.txt",$a);
                     $cs->addLine("if t.length > m result = ".$f->getJson()->id);
                     $cs->addLine("if t.length > m m = t.length");
                     $cs->addLine("if t.length > m isOwned = ".$f->isOwned());
                     $cs->addLine("if t.length > m canAttack = ".$f->canAttack());
                     $cs->addLine("if t.length > m user = '".$f->getUser()."'");
                     if (++$cnt > 3) { break; }
                  }
               }
               $cs->addLine("echo 'the best flat is at '+FieldIdToCoords(result)");
               $cs->addLine("v = { 'result' : result , ".
                                  "'isOwned' : isOwned , ".
                                  "'canAttack' : canAttack , ".
                                  "'user' : user }");
               $cs->addLine("p2v = json_encode(v)");
               $result = STATE_NEWCITY_BESTFLAT;
            }
//            $cs->debugOff();
            break;
            
         case STATE_NEWCITY_BESTFLAT:
            $result = STATE_SUSPEND;
            /* The best flat has been deteremined and it is processed. */
            $p2 = util_setParam("p2", 0);
            $p2json = json_decode($p2);
            // also make sure at this point no city started to spawn...
            if ($p2json && isset($p2json->result) && $this->m_cr->getDbc()->isAnyCitySpawning() == false) {
               if ($p2json->isOwned && $p2json->user == $this->m_cr->getUser()) {
                  $flatid = $p2json->result;
                  $cs->addLine("execute 'buildcity '+FieldIdToCoords(".$flatid.")");
                  $this->m_cr->getDbc()->setNewCity(1);
                  $result = STATE_SUSPEND;
               } else {
                  if ($p2json->isOwned) {
                     $a = [ "user"    => $p2json->user, 
                            "fieldid" => $p2json->result];
                     $cs->injectScriptVars("client/scripts/AttackOccupiedFieldId.txt", $a);
                  } else {
                     $a = [ "user"    => $p2json->user, 
                            "fieldid" => $p2json->result,
                            "troopstr" => "a:10k,w:1,wo:1,p:1,sw:1"];
                     $cs->injectScriptVars("client/scripts/AttackUnoccupiedFieldId.txt", $a);
                  }
                  $this->m_cr->getDbc()->setNewCity(2);
                  $this->m_cr->getDbc()->setNewCityFieldId($p2json->result);
                  $result = STATE_SUSPEND;
               }
            }
            break;
            
         case STATE_NEWCITY_WAITONFLAT:
            // waiting on flat to be obtained 
            $fid = $this->m_cr->getDbc()->getNewCityFieldId();
            // if army on its way then
            if ($this->m_city->isArmyFieldId($fid)) {
               $result = STATE_SUSPEND;
            }
            
            else if ($this->m_city->hasField($fid)) {
               $cs->addLine("execute 'buildcity '+FieldIdToCoords(".$fid.")");
               $this->m_cr->getDbc()->setNewCity(1);
               $result = STATE_SUSPEND;
            }
            
            else {
               // didn't get the flat - abort
               $this->m_cr->getDbc()->setNewCity(0);
               $this->m_cr->getDbc()->setNewCityFieldId(0);
               $result = STATE_SUSPEND;
            }
            break;
            
         case STATE_NEWCITY_GETCITYCNT:
            $cs->injectScript("client/scripts/GetCityCount.txt");
            $result = STATE_NEWCITY_CHECKCITYCNT;
            $this->m_cr->getDbc()->setNewCity(3);
            break;
            
         case STATE_NEWCITY_CHECKCITYCNT:
            $result = STATE_SUSPEND;
            $p2 = util_setParam("p2", 0);
            $p2json = json_decode($p2);
            // also make sure at this point no city started to spawn...
            if ($p2json && isset($p2json->result)) {
               $newcnt = $p2json->result;
               if ($this->m_cr->getDbc()->getTotalCityCount() < $newcnt) {
                  $result = STATE_NEWCITY_GETCITYNAMES;
                  $cs->injectScript("client/scripts/GetCityNames.txt");
                  $this->m_cr->getDbc()->setNewCity(4);
               }
            }
            break;
            
         case STATE_NEWCITY_GETCITYNAMES:
            $result = STATE_SUSPEND;
            $p2 = util_setParam("p2", 0);
            $p2json = json_decode($p2);
            if ($p2json && isset($p2json->result)) {
               $idx = 0;
               foreach($p2json->result as $cityname) {
                  if (! $this->m_cr->getDbc()->cityNameExists($cityname)) {
                     $cs->addEcho("calling script on slot ".$idx);
                     $cs->addLine(
                        "cities[".$idx."].cityManager.script.callScript(\"call 'http://192.168.1.77:8000/client/scripts/NewCity.txt' \\{time:date().time\\}\")");
                  }
                  $idx++;
               }
               $this->m_cr->getDbc()->setNewCity(5);
            }
            break;
            
         case STATE_NEWCITY_RENAME:
            $names = ["A","B","C","D","E","F","G","H","I","J"];
            foreach ($names as $newname) {
               if (! $this->m_cr->getDbc()->cityNameExists($newname)) {
                  $cs->addLine("renamecity " . $newname);
                  $this->m_cr->getDbc()->updateName($newname);
                  break;
               }
            }
            $result = STATE_IDLE;
            break;

         default:
            $result = STATE_SUSPEND;
            break;
      }
      
      return $result;
   }
   
}

?>
<?php
require_once "lib/db.php";


   define ("ID_COL","id");
   define ("SERVER_COL","server");
   define ("PLAYER_COL","player");
   define ("CITY_COL","city");
   define ("TALLY_COL","tally");
   define ("BUY_IRON_COL","biron");
   define ("SELL_IRON_COL","siron");
   define ("BUY_FOOD_COL","bfood");
   define ("SELL_FOOD_COL","sfood");
   define ("BUY_WOOD_COL","bwood");
   define ("SELL_WOOD_COL","swood");
   define ("BUY_STONE_COL","bstone");
   define ("SELL_STONE_COL","sstone");


class InlineBuySell {
      
   var $m_city;
   var $m_cr;
   var $m_cs;
  
   const GOLD     = 0;
   const FOOD     = 1;
   const WOOD     = 2;
   const STONE    = 3;
   const IRON     = 4;
    
   const BUY      = 0;
   const SELL     = 1;
   const DIVISOR_FOR_BIG_RESERVES = 10;
  
   public function __construct($cr, $city) {
	   $this->m_city = $city;
      $this->m_cr = $cr;
   }
      

   public function process($cs) {

      $this->m_cs = $cs;
      $dblink = $this->m_cr->getDbconnect();
      
      $goldTally    = 0;
      $rowId        = -1;
      $server       = $this->m_cr->getServer();
    
    
    
      $resColNames = array( "", "", 
                     BUY_FOOD_COL, SELL_FOOD_COL,
                     BUY_WOOD_COL, SELL_WOOD_COL,
                     BUY_STONE_COL, SELL_STONE_COL,
                     BUY_IRON_COL, SELL_IRON_COL );
    

    /*
        Parameters:
            buyAmt  - amount to buy in each transaction, a progressive buy
                      uses the buyAmt as a basis for the progressive amount
            avgSpan - number of most recent samples to include in calculations
            
            wood    - current amount of 
            stone   - current amount of 
            iron    - current amount of 
            gold    - current amount of 
            food    - current amount of 
            
            rwood   - reserved amount of
            rstone  - reserved amount of
            riron   - reserved amount of
            rgold   - reserved amount of
            rfood   - reserved amount of
            
            foodrate - food production rate - troop cost
            
            troopres - scale factor for reserved amount to account for troop
                       production
                       
            citytype - see res constants for values
    */
    
    
    
	
    // Get parameters 
    
   $buyAmt               = $this->m_city->getBuyAmt();
   $woodAmt              = $this->m_city->getWoodAmt();
   $stoneAmt             = $this->m_city->getStoneAmt();
   $ironAmt              = $this->m_city->getIronAmt();
   $goldAmt              = $this->m_city->getGoldAmt();
    
   $maxWoodAmt           = $this->m_city->getMaxWoodAmt();
   $maxStoneAmt          = $this->m_city->getMaxStoneAmt();
   $maxIronAmt           = $this->m_city->getMaxIronAmt();
    
 	$rwoodAmt             = $this->m_city->getReservedWoodAmt();
   $rstoneAmt            = $this->m_city->getReservedStoneAmt();
   $rironAmt             = $this->m_city->getReservedIronAmt();
   $rgoldAmt             = $this->m_city->getReservedGoldAmt();
   $rfoodAmt             = $this->m_city->getReservedFoodAmt();
	
	$minFood              = max(100000,$rfoodAmt);
    
   $foodAmt              = $this->m_city->getFoodAmt();
   $foodRate             = $this->m_city->getFoodRate();
   $troopResScale        = $this->setParam("troopres",1);
    
   $citytype             = $this->setParam("citytype",self::GOLD);
    
   $useStdDev            = isset($_POST["usesd"]);
    
    // Adjust reserved amounts by troop scaling factor
    
   $rwoodAmtOrig   = $rwoodAmt;
   $rstoneAmtOrig  = $rstoneAmt;
   $rironAmtOrig   = $rironAmt;
   $rfoodAmtOrig   = $rfoodAmt;
    
  
//    $rwoodAmt   = $rwoodAmt  * (($citytype == WOOD) ? 1 : $troopResScale);
   $rwoodAmt   = $rwoodAmt  * $troopResScale;
   $rstoneAmt  = $rstoneAmt * $troopResScale;
//    $rironAmt   = $rironAmt  * (($citytype == IRON) ? 1 : $troopResScale);
   $rironAmt   = $rironAmt  * $troopResScale;
   $rfoodAmt   = $rfoodAmt  * $troopResScale;
	
   
    // Initialize the price arrays
    
	$ironBuyPrices   = array();
	$ironSellPrices  = array();
	$stoneBuyPrices  = array();
	$stoneSellPrices = array();
	$woodBuyPrices   = array();
	$woodSellPrices  = array();
    
	// Use simulator to find good value for this
    
    $avgSpan = $this->m_city->getAvgSpan();

    // Initialize averages
    
	$avgBuySum      = 0;
	$avgSellSum     = 0;
	$buyIronAvg     = 0;
	$sellIronAvg    = 0;
	$buyWoodAvg     = 0;
	$sellWoodAvg    = 0;
	$buyStoneAvg    = 0;
	$sellStoneAvg   = 0;
    
    $count = min($avgSpan,2000);

    $q = "(SELECT * from prices where server='".$server."' ORDER BY id DESC LIMIT ".$count.") ORDER BY id ASC";
    $qr = $dblink->query($q) or die('Query failed: ');
    
    $rowCnt = 0;
    while ($row = $qr->fetch_assoc()) {
        $rowCnt++;
        $ipb = $row["ipb"];
        $ips = $row["ips"];
        $wpb = $row["wpb"];
        $wps = $row["wps"];
        $spb = $row["spb"];
        $sps = $row["sps"];
        $fpb = $row["fpb"];
        
		if (array_push($ironBuyPrices, $ipb) > $avgSpan) {
			array_shift($ironBuyPrices);
		}
		if (array_push($ironSellPrices, $ips) > $avgSpan) {
			array_shift($ironSellPrices);
		}
		if (array_push($woodBuyPrices, $wpb) > $avgSpan) {
			array_shift($woodBuyPrices);
		}
		if (array_push($woodSellPrices, $wps) > $avgSpan) {
			array_shift($woodSellPrices);
		}
		if (array_push($stoneBuyPrices, $spb) > $avgSpan) {
			array_shift($stoneBuyPrices);
		}
		if (array_push($stoneSellPrices, $sps) > $avgSpan) {
			array_shift($stoneSellPrices);
		}
	}
   
   if ($rowCnt == 0) {
      $this->m_cs->addEcho("Error getting price data (probably bad parms)");
      return false;
   }
   
  
   if ($qr) {
      $qr->free();
   }
    
    // Compute averages based on accumulators
    
	$avgBuySum     = array_sum($ironBuyPrices);
	$buyIronAvg    = ($avgBuySum / $avgSpan);
	$avgSellSum    = array_sum($ironSellPrices);
	$sellIronAvg   = ($avgSellSum / $avgSpan);
	$avgBuySum     = array_sum($woodBuyPrices);
	$buyWoodAvg    = ($avgBuySum / $avgSpan);
	$avgSellSum    = array_sum($woodSellPrices);
	$sellWoodAvg   = ($avgSellSum / $avgSpan);
	$avgBuySum     = array_sum($stoneBuyPrices);
	$buyStoneAvg   = ($avgBuySum / $avgSpan);
	$avgSellSum    = array_sum($stoneSellPrices);
	$sellStoneAvg  = ($avgSellSum / $avgSpan);

    // Set base thresholds for iron
    
	$buyThreshold = $sellIronAvg / 1.1;
	$sellThreshold = $buyIronAvg * 1.1;
    
    // Compute standard deviations
    
    if ($useStdDev) {
        $ironStdDevB = $this->stats_standard_deviation($ironBuyPrices);
        $ironStdDevS = $this->stats_standard_deviation($ironSellPrices);
        $woodStdDevB = $this->stats_standard_deviation($woodBuyPrices);
        $woodStdDevS = $this->stats_standard_deviation($woodSellPrices);
        $stoneStdDevB = $this->stats_standard_deviation($stoneBuyPrices);
        $stoneStdDevS = $this->stats_standard_deviation($stoneSellPrices);
        
        $buyThreshold = $sellIronAvg - $ironStdDevS;
        $sellThreshold = $buyIronAvg + $ironStdDevB;
    }
	
	
	$calcBuyAmt = $buyAmt;
    
    $needMoreGold = false;
	
	$msg = "//No action";
	
	if ($ipb < $buyThreshold) {
		$cost = ($ipb * $calcBuyAmt);
		if (($goldAmt > ($rgoldAmt+$cost)) && ($ironAmt < $maxIronAmt)) {
			if ($this->isLargeProgBuy($buyThreshold,$ipb) == true) {
				if ((($cost*100)+$rgoldAmt) < $goldAmt) {
					$calcBuyAmt *= 100;
//					$progBy100++;
				}
			} else if ($this->isSmallProgBuy($buyThreshold,$ipb) == true) {
				if ((($cost*10)+$rgoldAmt) < $goldAmt) {
					$calcBuyAmt *= 10;
//					$progBy10++;
				}
			}
//			echo "buy iron ".$calcBuyAmt." ".$ipb;
            $this->emitTrade("buy iron",$calcBuyAmt,$ipb,"");
			return false;
		}
		$msg = $msg . "want to buy iron ";
        $needMoreGold = true;
	} else if ($ips > $sellThreshold) {
		if ($ironAmt > ($rironAmt+$calcBuyAmt)) {
			if ($this->isLargeProgSell($sellThreshold,$ips) == true) {
				if (($calcBuyAmt*100) < ($ironAmt-$rironAmt)) {
					$calcBuyAmt *= 100;
//					$progBy100++;
				}
			} else if ($this->isSmallProgSell($sellThreshold,$ips) == true) {
				if (($calcBuyAmt*10) < ($ironAmt-$rironAmt)) {
					$calcBuyAmt *= 10;
//					$progBy10++;
				}
			}
//			echo "sell iron ".$calcBuyAmt." ".$ips;
            $this->emitTrade("sell iron",$calcBuyAmt,$ips,"");
			return false;
		}
		$msg = $msg . "want to sell iron ";
	}
	$buyThreshold = $sellWoodAvg / 1.1;
	$sellThreshold = $buyWoodAvg * 1.1;
	$calcBuyAmt = $buyAmt;

    if ($useStdDev) {
        $buyThreshold = $sellWoodAvg - $woodStdDevS;
        $sellThreshold = $buyWoodAvg + $woodStdDevB;
    }
    
	if ($wpb < $buyThreshold ) {
		$cost = ($wpb * $calcBuyAmt);
		if (($goldAmt > ($rgoldAmt+$cost)) && ($woodAmt < $maxWoodAmt)) {
			if ($this->isLargeProgBuy($buyThreshold,$wpb) == true) {
				if ((($cost*100)+$rgoldAmt) < $goldAmt) {
					$calcBuyAmt *= 100;
//					$progBy100++;
				}
			} else if ($this->isSmallProgBuy($buyThreshold,$wpb) == true) {
				if ((($cost*10)+$rgoldAmt) < $goldAmt) {
					$calcBuyAmt *= 10;
//					$progBy10++;
				}
			}
//			echo "buy wood ".$calcBuyAmt." ".$wpb;
            $this->emitTrade("buy wood",$calcBuyAmt,$wpb,"");
			return false;
		}
		$msg = $msg . "want to buy wood ";
        $needMoreGold = true;
	} else if ($wps > $sellThreshold ) {
		if ($woodAmt > ($rwoodAmt+$calcBuyAmt)) {
			if ($this->isLargeProgSell($sellThreshold,$wps) == true) {
				if (($calcBuyAmt*100) < ($woodAmt-$rwoodAmt)) {
					$calcBuyAmt *= 100;
//					$progBy100++;
				}
			} else if ($this->isSmallProgSell($sellThreshold,$wps) == true) {
				if (($calcBuyAmt*10) < ($woodAmt-$rwoodAmt)) {
					$calcBuyAmt *= 10;
//					$progBy10++;
				}
			}
//			echo "sell wood ".$calcBuyAmt." ".$wps;
            $this->emitTrade("sell wood",$calcBuyAmt,$wps,"");
			return false;
		}
		$msg = $msg . "want to sell wood ";
	}
	$buyThreshold = $sellStoneAvg / 1.1;
	$sellThreshold = $buyStoneAvg * 1.1;
	$calcBuyAmt = $buyAmt;

    if ($useStdDev) {
        $buyThreshold = $sellStoneAvg - $stoneStdDevS;
        $sellThreshold = $buyStoneAvg + $stoneStdDevB;
    }
    
	if ($spb < $buyThreshold) {
		$cost = ($spb * $calcBuyAmt);
		if (($goldAmt > ($rgoldAmt+$cost)) && ($stoneAmt < $maxStoneAmt)) {
			if ($this->isLargeProgBuy($buyThreshold,$spb) == true) {
				if ((($cost*100)+$rgoldAmt) < $goldAmt) {
					$calcBuyAmt *= 100;
//					$progBy100++;
				}
			} else if ($this->isSmallProgBuy($buyThreshold,$spb) == true) {
				if ((($cost*10)+$rgoldAmt) < $goldAmt) {
					$calcBuyAmt *= 10;
//					$progBy10++;
				}
			}
//			echo "buy stone ".$calcBuyAmt." ".$spb;
            $this->emitTrade("buy stone",$calcBuyAmt,$spb,"");
			return false;
		}
		$msg = $msg . "want to buy stone ";
        $needMoreGold = true;
	} else if ($sps > $sellThreshold ) {
		if ($stoneAmt > ($rstoneAmt+$calcBuyAmt)) {
			if ($this->isLargeProgSell($sellThreshold,$sps) == true) {
				if (($calcBuyAmt*100) < ($stoneAmt-$rstoneAmt)) {
					$calcBuyAmt *= 100;
//					$progBy100++;
				}
			} else if ($this->isSmallProgSell($sellThreshold,$sps) == true) {
				if (($calcBuyAmt*10) < ($stoneAmt-$rstoneAmt)) {
					$calcBuyAmt *= 10;
//					$progBy10++;
				}
			}
//			echo "sell stone ".$buyAmt." ".$sps;
            $this->emitTrade("sell stone",$buyAmt,$sps,"");
			return false;
		}
		$msg = $msg . "want to sell stone ";
	}
	
    
	// If we get this far then check food situation
	if ($foodAmt < $minFood && $goldAmt > $rgoldAmt && $foodRate < 0) {
//		echo "buy food ".$buyAmt." ".$fpb;
        $this->emitTrade("buy food",$buyAmt,$fpb,"");
		return false;
	}
    
    // compute 4 hours of food
    $foodReserve = abs($foodRate * 4);
    
    $needFood = ($foodRate < 0 && $foodAmt < $foodReserve );
    if ( $needFood ) {
        // compute a buy amount based on rate
        $buyAmt = (int) max(1000,($foodReserve / 10));
        if (($buyAmt * $fpb) > ($goldAmt - $rgoldAmt)) {
            // reduce to what is available
            $buyAmt = max(max(($goldAmt - $rgoldAmt),$rgoldAmt),1) / $fpb;
        }
//		echo "buy food ".$buyAmt." ".$fpb." // emergency food buy";
        $this->emitTrade("buy food",$buyAmt,$fpb,"emergency food buy");
		return false;
    }


    // If we get this far then check if resources under reserved - must buy some
    
    $noGoldForReservedBuy = false;
    
    if ($stoneAmt < $rstoneAmt) {
        // compute how deficient
        $resDiff = ($rstoneAmt - $stoneAmt);
        $calcBuyAmt = (int) max(10,($resDiff / 10));
        $cost = ($spb * $calcBuyAmt);
        if ($cost < ($goldAmt - $rgoldAmt)) {
//            echo "buy stone ".$calcBuyAmt." ".$spb." // below reserved buy";
            $this->emitTrade("buy stone",$calcBuyAmt,$spb,"below reserved buy");
            return false;
        }
        $noGoldForReservedBuy = true;
    }

    if ($ironAmt < $rironAmt) {
        // compute how deficient
        $resDiff = ($rironAmt - $ironAmt);
        $calcBuyAmt = (int) max(10,($resDiff / 10));
        $cost = ($ipb * $calcBuyAmt);
        if ($cost < ($goldAmt - $rgoldAmt)) {
//            echo "buy iron ".$calcBuyAmt." ".$ipb." // below reserved buy";
            $this->emitTrade("buy iron",$calcBuyAmt,$ipb,"below reserved buy");
            return false;
        }
        $noGoldForReservedBuy = true;
    }

    if ($woodAmt < $rwoodAmt) {
        // compute how deficient
        $resDiff = ($rwoodAmt - $woodAmt);
        $calcBuyAmt = (int) max(10,($resDiff / 10));
        $cost = ($wpb * $calcBuyAmt);
        if ($cost < ($goldAmt - $rgoldAmt)) {
//            echo "buy wood ".$calcBuyAmt." ".$wpb." // below reserved buy";
            $this->emitTrade("buy wood",$calcBuyAmt,$wpb,"below reserved buy");
            return false;
        }
        $noGoldForReservedBuy = true;
    }

    if ($foodAmt < $rfoodAmt) {
        // compute how deficient
        $resDiff = ($rfoodAmt - $foodAmt);
        $calcBuyAmt = (int) max(10,($resDiff / 10));
        $cost = ($fpb * $calcBuyAmt);
        if ($cost < ($goldAmt - $rgoldAmt)) {
//            echo "buy food ".$calcBuyAmt." ".$fpb." // below reserved buy ($foodAmt,$rfoodAmt)";
            $this->emitTrade("buy food",$calcBuyAmt,$fpb,"below reserved buy ($foodAmt,$rfoodAmt)");
            return false;
        }
        $noGoldForReservedBuy = true;
    }
    
    // If we need to buy resources below reserve levels but do not have
    // enough gold, then sell any largely excess resources.
    
    if ($noGoldForReservedBuy == TRUE || $goldAmt < $rgoldAmt) {
        // check if we have a lot of something to sell in small amounts
        if ($woodAmt > $rwoodAmt || $citytype == self::WOOD) {
            $rAmt = max($rwoodAmt,1);
            if (($woodAmt / $rAmt) > self::DIVISOR_FOR_BIG_RESERVES || ($citytype == self::WOOD && ($woodAmt-$buyAmt) > $rwoodAmtOrig)) { 
//                echo "sell wood ".$buyAmt." ".$wps." // generate small amt of gold ".$woodAmt." ".$rwoodAmt;
                $this->emitTrade("sell wood",$buyAmt,$wps,"generate small amt of gold");
                return false;
            }
        }
        if ($ironAmt > $rironAmt || $citytype == self::IRON) {
            $rAmt = max($rironAmt,1);
            if (($ironAmt / $rAmt) > self::DIVISOR_FOR_BIG_RESERVES || ($citytype == self::IRON && ($ironAmt-$buyAmt) > $rironAmtOrig)) { 
//                echo "sell iron ".$buyAmt." ".$ips." // generate small amt of gold";
                $this->emitTrade("sell iron",$buyAmt,$ips,"generate small amt of gold");
                return false;
            }
        }
        if ($stoneAmt > $rstoneAmt) {
            $rAmt = max($rstoneAmt,1);
            if (($stoneAmt / $rstoneAmt) > self::DIVISOR_FOR_BIG_RESERVES) { 
//                echo "sell stone ".$buyAmt." ".$sps." // generate small amt of gold";
                $this->emitTrade("sell stone",$buyAmt,$sps,"generate small amt of gold");
                return false;
            }
        }

        // If we get this far and we do not have gold to buy reserves and we couldn't
        // sell anything - let's try requesting resources from other city.
        
        if ($woodAmt < $rwoodAmt) {
//            $msg = "requestresources any w ".$rwoodAmt." -1 * * s ".$msg;
        }
        else if ($ironAmt < $rironAmt) {
//            $msg = "requestresources any i ".$rironAmt." -1 * * s ".$msg;
        }
        else if ($stoneAmt < $rstoneAmt) {
//            $msg = "requestresources any s ".$rstoneAmt." -1 * * s ".$msg;
        } else if ($goldAmt < $rgoldAmt) {
//            $msg = "requestresources any g ".$rgoldAmt." -1 * * s ".$msg;
        } else {
//            $msg = "loadgoals ".$msg;
        }

    }
    
    // If gold is low - check city type and sell over min
    if ($goldAmt < $rgoldAmt) {
        if ($citytype == self::WOOD && $woodAmt > ($rwoodAmtOrig+$buyAmt)) {
//            echo "sell wood ".$buyAmt." ".$wps." // sell to min by city type";
            $this->emitTrade("sell wood",$buyAmt,$wps,"sell to min by city type");
            return false;
        }
        if ($citytype == self::IRON && $ironAmt > ($rironAmtOrig+$buyAmt)) {
//            echo "sell iron ".$buyAmt." ".$ips." // sell to min by city type";
            $this->emitTrade("sell iron",$buyAmt,$ips,"sell to min by city type");
            return false;
        }
    }
    
    // if lots of food and not so much gold sell food at reasonable price
    if ($foodAmt > 2000000000) {
       if ($fpb > 1.5 && $goldAmt < 1000000000) {
          $this->emitTrade("sell food", 2000000,$fpb,"sell food at good price");
          return false;
       }
       if ($fpb > 2.5 && $goldAmt < 3000000000) {
          $this->emitTrade("sell food", 5000000,$fpb,"sell food at good price");
          return false;
       }
       if ($fpb > 3.5 && $goldAmt < 10000000000) {
          $this->emitTrade("sell food", 5000000,$fpb,"sell food at good price");
          return false;
       }
    }
    
   $this->m_cs->addEcho("[inline] No action"); 
	$this->m_cs->addLine($msg . " ; ( " . number_format(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"],3) . " )");
	
	return false;
   }
   

   protected function isSmallProgBuy ($threshold, $buyPrice)
   {
      $result = false;
      if ($threshold > 0) {
         $pctdiff = (($threshold - $buyPrice) / $threshold);
         $result = ($pctdiff > .1);
      }
      return $result;
   }

   protected function isLargeProgBuy ($threshold, $buyPrice)
   {
      $result = false;
      if ($threshold > 0) {
         $pctdiff = (($threshold - $buyPrice) / $threshold);
         $result = ($pctdiff > .3);
      }
      return $result;
   }

   protected function isSmallProgSell ($threshold, $sellPrice)
   {
      $result = false;
      if ($threshold > 0) {
         $pctdiff = (($sellPrice - $threshold) / $threshold);
         $result = ($pctdiff > .1);
      }
      return $result;
   }
   protected function isLargeProgSell ($threshold, $sellPrice)
   {
      $result = false;
      if ($threshold > 0) {
         $pctdiff = (($sellPrice - $threshold) / $threshold);
         $result = ($pctdiff > .3);
      }
      return $result;
   }



   # Returns the parameter value or a default setting.

   protected function setParam ($pStr,$dVal)
   {
      $result = $dVal;
      if (isset($_POST[$pStr]) == true) {
         $result = $_POST[$pStr];
      } else if (isset($_GET[$pStr]) == true) {
         $result = $_GET[$pStr];
      }
       return $result;
   }

   protected function emitTrade ($tradeStr,$amt,$price,$msg) {
   //    echo $tradeStr." ".$amt." ".$price." // (".number_format($amt*$price).") ".$msg;
   //    echo $tradeStr." ".$amt." // (p=".$price." a*p=".number_format($amt*$price).") ".$msg;
      $this->m_cs->addEcho("Using [inline]: " . $tradeStr . " " . $amt);
      $this->m_cs->addLine("if city.tradesArray.length > 0 execute 'canceltrade ' + city.tradesArray[0].id");
      $this->m_cs->addLine($tradeStr . " " . $amt . " //[inline] (p=".$price." a*p=".number_format($amt*$price).") ".$msg);
   }

   # Compute standard deviation

   protected function stats_standard_deviation(array $a, $sample = false) {
       $n = count($a);
       if ($n === 0) {
           trigger_error("The array has zero elements", E_USER_WARNING);
           return false;
       }
       if ($sample && $n === 1) {
           trigger_error("The array has only 1 element", E_USER_WARNING);
           return false;
       }
       $mean = array_sum($a) / $n;
       $carry = 0.0;
       foreach ($a as $val) {
           $d = ((double) $val) - $mean;
           $carry += $d * $d;
       };
       if ($sample) {
           --$n;
       }
       return sqrt($carry / $n);
   }	

}
?>
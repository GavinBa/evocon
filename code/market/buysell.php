<?php
   require_once "../../lib/db.php";


    $server       = "";
    $player       = "";
    $city         = "";
    $goldTally    = 0;
    $updateGold   = FALSE;
    $rowId        = -1;
    
    $server = setParam("server","");
    $player = setParam("player","");
    $city   = setParam("city","");

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
    
    const GOLD     = 0;
    const FOOD     = 1;
    const WOOD     = 2;
    const STONE    = 3;
    const IRON     = 4;
    
    const BUY      = 0;
    const SELL     = 1;
    
    $resColNames = array( "", "", 
                     BUY_FOOD_COL, SELL_FOOD_COL,
                     BUY_WOOD_COL, SELL_WOOD_COL,
                     BUY_STONE_COL, SELL_STONE_COL,
                     BUY_IRON_COL, SELL_IRON_COL );
    
     $dblink = db_connectDB();
     if ($dblink != NULL) {

//        $updateGold = getPlayerData($server,$player,$city,$rowId,$goldTally);

    }

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
    
    
    const DIVISOR_FOR_BIG_RESERVES = 10;
    
	$version     = "1.0.05";
	$versionInfo = "Added processing time ";
	
    // Get parameters 
    
    $buyAmt               = setParam("buyAmt",10000);
    $woodAmt              = setParam("wood",50000);
    $stoneAmt             = setParam("stone",50000);
    $ironAmt              = setParam("iron",50000);
    $goldAmt              = setParam("gold",100000);
    
    $maxWoodAmt           = setParam("mwood",100000000);
    $maxStoneAmt          = setParam("mstone",100000000);
    $maxIronAmt           = setParam("miron",100000000);
    
	$rwoodAmt             = setParam("rwood",0);
    $rstoneAmt            = setParam("rstone",0);
    $rironAmt             = setParam("riron",0);
    $rgoldAmt             = setParam("rgold",0);
    $rfoodAmt             = setParam("rfood",0);
	
	$minFood              = max(100000,$rfoodAmt);
    
    $foodAmt              = setParam("food",$minFood);
    $foodRate             = setParam("foodrate",0);
    $troopResScale        = setParam("troopres",1);
    
    $ranking              = setParam("ranking",0);
//    addRanking ($server,$player,$city,$ranking);
    
    $citytype             = setParam("citytype",GOLD);
    
    $useStdDev            = isset($_POST["usesd"]);
    $testParms            = setParam("test",-1);
    
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
	
   
   if ($testParms >= 0) {
      printf("//buyAmt=".$buyAmt);
      printf("//woodAmt=".$woodAmt);
      printf("//stoneAmt=".$stoneAmt);
      printf("//ironAmt=".$ironAmt);
      printf("//goldAmt=".$goldAmt);
      printf("//maxWoodAmt=".$maxWoodAmt);
      printf("//maxStoneAmt=".$maxStoneAmt);
      printf("//maxIronAmt=".$maxIronAmt);
      printf("//rironAmt=".$rironAmt);
      printf("//rwoodAmt=".$rwoodAmt);
      printf("//rgoldAmt=".$rgoldAmt);
      printf("//minFood=".$minFood);
      printf("//foodAmt=".$foodAmt);
      printf("//foodRate=".$foodRate);
      printf("//troopResScale=".$troopResScale);
   }
    // Initialize the price arrays
    
	$ironBuyPrices   = array();
	$ironSellPrices  = array();
	$stoneBuyPrices  = array();
	$stoneSellPrices = array();
	$woodBuyPrices   = array();
	$woodSellPrices  = array();
    
	// Use simulator to find good value for this
    
    $avgSpan = setParam("avgSpan",50);

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
    error_log("server=".$server); 

    $q = "(SELECT * from prices where server='".$server."' ORDER BY id DESC LIMIT ".$count.") ORDER BY id ASC";
    if ($testParms >= 0) {
       printf("//q=".$q);
    }
    $qr = $dblink->query($q) or die('Query failed: ');
    
    // Read price file and keep last 'avgSpan' values
/*    
	while (($buffer = fgets($handle, 4096)) !== false) {
		list($fps,$fpb,$wps,$wpb,$sps,$spb,$ips,$ipb) =
			preg_split("/[\s,]+/",$buffer, 8);
*/
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
      printf("//no rows in price dB");
      return false;
   }
   
   if ($testParms >= 0) {
      printf("//ipb=".$ipb." ips=".$ips." wpb=".$wpb." wps=".$wps." spb=".$spb." sps=".$sps);
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
        $ironStdDevB = stats_standard_deviation($ironBuyPrices);
        $ironStdDevS = stats_standard_deviation($ironSellPrices);
        $woodStdDevB = stats_standard_deviation($woodBuyPrices);
        $woodStdDevS = stats_standard_deviation($woodSellPrices);
        $stoneStdDevB = stats_standard_deviation($stoneBuyPrices);
        $stoneStdDevS = stats_standard_deviation($stoneSellPrices);
        
        $buyThreshold = $sellIronAvg - $ironStdDevS;
        $sellThreshold = $buyIronAvg + $ironStdDevB;
    }
	
	
	$calcBuyAmt = $buyAmt;
    
    $needMoreGold = false;
	
	$msg = "//No action - ".$version." - ";
	
	if ($ipb < $buyThreshold) {
		$cost = ($ipb * $calcBuyAmt);
		if (($goldAmt > ($rgoldAmt+$cost)) && ($ironAmt < $maxIronAmt)) {
			if (isLargeProgBuy($buyThreshold,$ipb) == true) {
				if ((($cost*100)+$rgoldAmt) < $goldAmt) {
					$calcBuyAmt *= 100;
//					$progBy100++;
				}
			} else if (isSmallProgBuy($buyThreshold,$ipb) == true) {
				if ((($cost*10)+$rgoldAmt) < $goldAmt) {
					$calcBuyAmt *= 10;
//					$progBy10++;
				}
			}
            updateTallys($goldTally,-($ipb * $calcBuyAmt),IRON,BUY,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//			echo "buy iron ".$calcBuyAmt." ".$ipb;
            emitTrade("buy iron",$calcBuyAmt,$ipb,"");
			return false;
		}
		$msg = $msg . "want to buy iron ";
        $needMoreGold = true;
	} else if ($ips > $sellThreshold) {
		if ($ironAmt > ($rironAmt+$calcBuyAmt)) {
			if (isLargeProgSell($sellThreshold,$ips) == true) {
				if (($calcBuyAmt*100) < ($ironAmt-$rironAmt)) {
					$calcBuyAmt *= 100;
//					$progBy100++;
				}
			} else if (isSmallProgSell($sellThreshold,$ips) == true) {
				if (($calcBuyAmt*10) < ($ironAmt-$rironAmt)) {
					$calcBuyAmt *= 10;
//					$progBy10++;
				}
			}
            updateTallys($goldTally,($ips * $calcBuyAmt),IRON,SELL,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//			echo "sell iron ".$calcBuyAmt." ".$ips;
            emitTrade("sell iron",$calcBuyAmt,$ips,"");
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
			if (isLargeProgBuy($buyThreshold,$wpb) == true) {
				if ((($cost*100)+$rgoldAmt) < $goldAmt) {
					$calcBuyAmt *= 100;
//					$progBy100++;
				}
			} else if (isSmallProgBuy($buyThreshold,$wpb) == true) {
				if ((($cost*10)+$rgoldAmt) < $goldAmt) {
					$calcBuyAmt *= 10;
//					$progBy10++;
				}
			}
            updateTallys($goldTally,-($wpb * $calcBuyAmt),WOOD,BUY,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//			echo "buy wood ".$calcBuyAmt." ".$wpb;
            emitTrade("buy wood",$calcBuyAmt,$wpb,"");
			return false;
		}
		$msg = $msg . "want to buy wood ";
        $needMoreGold = true;
	} else if ($wps > $sellThreshold ) {
		if ($woodAmt > ($rwoodAmt+$calcBuyAmt)) {
			if (isLargeProgSell($sellThreshold,$wps) == true) {
				if (($calcBuyAmt*100) < ($woodAmt-$rwoodAmt)) {
					$calcBuyAmt *= 100;
//					$progBy100++;
				}
			} else if (isSmallProgSell($sellThreshold,$wps) == true) {
				if (($calcBuyAmt*10) < ($woodAmt-$rwoodAmt)) {
					$calcBuyAmt *= 10;
//					$progBy10++;
				}
			}
            updateTallys($goldTally,($wps * $calcBuyAmt),WOOD,SELL,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//			echo "sell wood ".$calcBuyAmt." ".$wps;
            emitTrade("sell wood",$calcBuyAmt,$wps,"");
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
			if (isLargeProgBuy($buyThreshold,$spb) == true) {
				if ((($cost*100)+$rgoldAmt) < $goldAmt) {
					$calcBuyAmt *= 100;
//					$progBy100++;
				}
			} else if (isSmallProgBuy($buyThreshold,$spb) == true) {
				if ((($cost*10)+$rgoldAmt) < $goldAmt) {
					$calcBuyAmt *= 10;
//					$progBy10++;
				}
			}
            updateTallys($goldTally,-($spb * $calcBuyAmt),STONE,BUY,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//			echo "buy stone ".$calcBuyAmt." ".$spb;
            emitTrade("buy stone",$calcBuyAmt,$spb,"");
			return false;
		}
		$msg = $msg . "want to buy stone ";
        $needMoreGold = true;
	} else if ($sps > $sellThreshold ) {
		if ($stoneAmt > ($rstoneAmt+$calcBuyAmt)) {
			if (isLargeProgSell($sellThreshold,$sps) == true) {
				if (($calcBuyAmt*100) < ($stoneAmt-$rstoneAmt)) {
					$calcBuyAmt *= 100;
//					$progBy100++;
				}
			} else if (isSmallProgSell($sellThreshold,$sps) == true) {
				if (($calcBuyAmt*10) < ($stoneAmt-$rstoneAmt)) {
					$calcBuyAmt *= 10;
//					$progBy10++;
				}
			}
            updateTallys($goldTally,($sps * $calcBuyAmt),STONE,SELL,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//			echo "sell stone ".$buyAmt." ".$sps;
            emitTrade("sell stone",$buyAmt,$sps,"");
			return false;
		}
		$msg = $msg . "want to sell stone ";
	}
	
    
	// If we get this far then check food situation
	if ($foodAmt < $minFood && $goldAmt > $rgoldAmt && $foodRate < 0) {
        updateTallys($goldTally,-($fpb * $buyAmt),FOOD,BUY,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//		echo "buy food ".$buyAmt." ".$fpb;
        emitTrade("buy food",$buyAmt,$fpb,"");
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
        updateTallys($goldTally,-($fpb * $buyAmt),FOOD,BUY,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//		echo "buy food ".$buyAmt." ".$fpb." // emergency food buy";
        emitTrade("buy food",$buyAmt,$fpb,"emergency food buy");
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
            updateTallys($goldTally,-($spb * $calcBuyAmt),STONE,BUY,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//            echo "buy stone ".$calcBuyAmt." ".$spb." // below reserved buy";
            emitTrade("buy stone",$calcBuyAmt,$spb,"below reserved buy");
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
            updateTallys($goldTally,-($ipb * $calcBuyAmt),IRON,BUY,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//            echo "buy iron ".$calcBuyAmt." ".$ipb." // below reserved buy";
            emitTrade("buy iron",$calcBuyAmt,$ipb,"below reserved buy");
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
            updateTallys($goldTally,-($wpb * $calcBuyAmt),WOOD,BUY,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//            echo "buy wood ".$calcBuyAmt." ".$wpb." // below reserved buy";
            emitTrade("buy wood",$calcBuyAmt,$wpb,"below reserved buy");
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
            updateTallys($goldTally,-($fpb * $calcBuyAmt),FOOD,BUY,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//            echo "buy food ".$calcBuyAmt." ".$fpb." // below reserved buy ($foodAmt,$rfoodAmt)";
            emitTrade("buy food",$calcBuyAmt,$fpb,"below reserved buy ($foodAmt,$rfoodAmt)");
            return false;
        }
        $noGoldForReservedBuy = true;
    }
    
    // If we need to buy resources below reserve levels but do not have
    // enough gold, then sell any largely excess resources.
    
    if ($noGoldForReservedBuy == TRUE || $goldAmt < $rgoldAmt) {
        // check if we have a lot of something to sell in small amounts
        if ($woodAmt > $rwoodAmt || $citytype == WOOD) {
            $rAmt = max($rwoodAmt,1);
            if (($woodAmt / $rAmt) > DIVISOR_FOR_BIG_RESERVES || ($citytype == WOOD && ($woodAmt-$buyAmt) > $rwoodAmtOrig)) { 
                updateTallys($goldTally,($wps * $buyAmt),WOOD,SELL,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//                echo "sell wood ".$buyAmt." ".$wps." // generate small amt of gold ".$woodAmt." ".$rwoodAmt;
                emitTrade("sell wood",$buyAmt,$wps,"generate small amt of gold");
                return false;
            }
        }
        if ($ironAmt > $rironAmt || $citytype == IRON) {
            $rAmt = max($rironAmt,1);
            if (($ironAmt / $rAmt) > DIVISOR_FOR_BIG_RESERVES || ($citytype == IRON && ($ironAmt-$buyAmt) > $rironAmtOrig)) { 
                updateTallys($goldTally,($ips * $buyAmt),IRON,SELL,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//                echo "sell iron ".$buyAmt." ".$ips." // generate small amt of gold";
                emitTrade("sell iron",$buyAmt,$ips,"generate small amt of gold");
                return false;
            }
        }
        if ($stoneAmt > $rstoneAmt) {
            $rAmt = max($rstoneAmt,1);
            if (($stoneAmt / $rstoneAmt) > DIVISOR_FOR_BIG_RESERVES) { 
                updateTallys($goldTally,-($sps * $buyAmt),STONE,SELL,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//                echo "sell stone ".$buyAmt." ".$sps." // generate small amt of gold";
                emitTrade("sell stone",$buyAmt,$sps,"generate small amt of gold");
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
        if ($citytype == WOOD && $woodAmt > ($rwoodAmtOrig+$buyAmt)) {
            updateTallys($goldTally,($wps * $buyAmt),WOOD,SELL,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//            echo "sell wood ".$buyAmt." ".$wps." // sell to min by city type";
            emitTrade("sell wood",$buyAmt,$wps,"sell to min by city type");
            return false;
        }
        if ($citytype == IRON && $ironAmt > ($rironAmtOrig+$buyAmt)) {
            updateTallys($goldTally,($ips * $buyAmt),IRON,SELL,$updateGold,$rowId);
        db_disconnectDB ($dblink);
//            echo "sell iron ".$buyAmt." ".$ips." // sell to min by city type";
            emitTrade("sell iron",$buyAmt,$ips,"sell to min by city type");
            return false;
        }
    }
    
    
    if ($dblink != NULL) {
        db_disconnectDB ($dblink);
    }

    
	echo $msg . " ; ( " . number_format(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"],3) . " )" . " tally: " . number_format($goldTally);
	
	return false;

function updateTallys ($g,$amt,$r,$t,$ug,$rowId)
{
    global $resColNames;
/*    
    if ($ug == TRUE) {
        $q = "SELECT * from gold where id = ".$rowId;
        $qresult = mysql_query($q) or die('Query failed: ' . mysql_error());
        if (($row = mysql_fetch_assoc($qresult))) {
            $row[TALLY_COL] = ($g + $amt);
            $idx = (($r * 2) + ($t % 2));
            $row[$resColNames[$idx]] += $amt;
            
            $stmt = "UPDATE gold SET tally=".$row[TALLY_COL].",".
                $resColNames[$idx]."=".$row[$resColNames[$idx]].
                " WHERE id=".$rowId;
            if (mysql_query($stmt) == TRUE) {
            }
        }
    }
*/
}

function isSmallProgBuy ($threshold, $buyPrice)
{
	$result = false;
	if ($threshold > 0) {
		$pctdiff = (($threshold - $buyPrice) / $threshold);
		$result = ($pctdiff > .1);
	}
	return $result;
}

function isLargeProgBuy ($threshold, $buyPrice)
{
	$result = false;
	if ($threshold > 0) {
		$pctdiff = (($threshold - $buyPrice) / $threshold);
		$result = ($pctdiff > .3);
	}
	return $result;
}

function isSmallProgSell ($threshold, $sellPrice)
{
	$result = false;
	if ($threshold > 0) {
		$pctdiff = (($sellPrice - $threshold) / $threshold);
		$result = ($pctdiff > .1);
	}
	return $result;
}
function isLargeProgSell ($threshold, $sellPrice)
{
	$result = false;
	if ($threshold > 0) {
		$pctdiff = (($sellPrice - $threshold) / $threshold);
		$result = ($pctdiff > .3);
	}
	return $result;
}


# Returns a boolean indicating whether the gold tally is initialized

function getPlayerData ($s,$p,$c,&$rowId,&$goldTally)
{
    $retval = FALSE;
 
/*    
    if (strlen($s) > 0 && strlen($p) > 0 && strlen($c) > 0) {
        $qualQuery = "SELECT * from gold where player = '".$p."' AND server = '".$s."' AND city = '".$c."'";
        $qualResult = mysql_query($qualQuery) or die('Query failed: ' . mysql_error());
        $num_rows = mysql_num_rows($qualResult);
        if ($num_rows == 0) {
            $stmt = "INSERT into gold (server,player,city) values ('".$s."','".$p."','".$c."')";
            if (mysql_query($stmt) == TRUE) {
                $retval = TRUE;
                $rowId = mysql_insert_id();
            }
        } else {
            $result = mysql_fetch_assoc($qualResult);
            $goldTally = $result[TALLY_COL];
            $rowId = $result[ID_COL];
            $retval = TRUE;
        }
    }
*/    
    return $retval;
}

# Add the ranking for this player/server if the city is the main ("A")

function addRanking ($s,$p,$c,$r)
{
/*
    if ($r > 0 && strcmp($c,"A") == 0) {
        $stmt = "INSERT into ranking (server,player,ranking) values ('".$s."','".$p."','".$r."')";
        if (mysql_query($stmt) == TRUE) {
        }
    }
*/
    return;
}

# Returns the parameter value or a default setting.

function setParam ($pStr,$dVal)
{
   $result = $dVal;
   if (isset($_POST[$pStr]) == true) {
		$result = $_POST[$pStr];
	} else if (isset($_GET[$pStr]) == true) {
      $result = $_GET[$pStr];
   }
    return $result;
}

function emitTrade ($tradeStr,$amt,$price,$msg) {
//    echo $tradeStr." ".$amt." ".$price." // (".number_format($amt*$price).") ".$msg;
    echo $tradeStr." ".$amt." // (p=".$price." a*p=".number_format($amt*$price).") ".$msg;
}

# Compute standard deviation

function stats_standard_deviation(array $a, $sample = false) {
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

?>
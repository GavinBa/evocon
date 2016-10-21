<?php
   require_once "../../lib/db.php";
/*
 This PHP script appends price data to a file (prices.txt) as a single line of text in
 this format:
    <food sell>,<food buy> <wood sell>,<wood buy> <stone sell>,<stone buy> <iron sell>,<iron buy>
	
 This script handles a POST request with the following parameters:
    fps - food price sell
	fpb
	wps
	wpb
	sps
	spb
	ips
	ipb

 If there is no "fps" parameter, then this script will print out the contents of
 the price file in a html table.  Useful for examining the data as opposed to 
 updating the data.
 
 A database is also updated with the price information.  The table updated
 is called 'prices' and contain a column for each price data point, with the
 column names the same as the above parameter names.
 */
    $priceFileName = "prices.txt";
	
//	$handle = fopen($priceFileName, "a+");
//	if ($handle == false) {
//		return true;
//	}
    error_log("top"); 
    
    $dblink = db_connectDB();
    
    error_log("here"); 
    

    error_log("here2"); 
	
	$fps = "0";
	if (isset($_POST["fps"]) == true) {
		$fps = $_POST["fps"];
	}
	
	$fpb = "0";
	if (isset($_POST["fpb"]) == true) {
		$fpb = $_POST["fpb"];
	}

	$wps = "0";
	if (isset($_POST["wps"]) == true) {
		$wps = $_POST["wps"];
	}
	
	$wpb = "0";
	if (isset($_POST["wpb"]) == true) {
		$wpb = $_POST["wpb"];
	}
	
	$sps = "0";
	if (isset($_POST["sps"]) == true) {
		$sps = $_POST["sps"];
	}
	
	$spb = "0";
	if (isset($_POST["spb"]) == true) {
		$spb = $_POST["spb"];
	}
	
	$ips = "0";
	if (isset($_POST["ips"]) == true) {
		$ips = $_POST["ips"];
	}
	
	$ipb = "0";
	if (isset($_POST["ipb"]) == true) {
		$ipb = $_POST["ipb"];
	}
    
    $server = "197";
    if (isset($_POST["server"]) == true) {
       $server = $_POST["server"];
    }
    
    if ($dblink != NULL) {
       $dblink->query ("INSERT into prices (fps,fpb,wps,wpb,sps,spb,ips,ipb,server) values (".
                floatval($fps).",".floatval($fpb).",".
                floatval($wps).",".floatval($wpb).",".
                floatval($sps).",".floatval($spb).",".
                floatval($ips).",".floatval($ipb).",'".$server."')");
        db_disconnectDB ($dblink);
    }

    return false;
	
	
//	$line = $fps . "," . $fpb . " " . $wps . "," . $wpb . " " . $sps . "," . $spb . " " . $ips . "," . $ipb . "\n";
//	$status = fwrite($handle,$line);
//	if ($status == false) {
//		return true;
//	}
	
//	fclose($handle);
    
?>

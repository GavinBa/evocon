<?php

# Compute standard deviation

function mktutil_stats_standard_deviation(array $a, $sample = false) {
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


function mktutil_stats_standard_deviation_sub(array $a, $offset, $sample = false) {
   $len = count($a);
   $n = max($len-$offset,0);
   if ($n === 0) {
      trigger_error("The array has zero elements", E_USER_WARNING);
      return false;
   }
   if ($sample && $n === 1) {
      trigger_error("The array has only 1 element", E_USER_WARNING);
      return false;
   }
   $mean = mktutil_sumArray($a,$offset) / $n;
   $carry = 0.0;
   for ($i = $offset; $i < $len; $i++) {
   	  $val = $a[$i];
      $d = ((double) $val) - $mean;
      $carry += $d * $d;
   };
   if ($sample) {
      --$n;
   }
   return sqrt($carry / $n);
}


# Returns the parameter value or a default setting.

function util_setParam ($pStr,$dVal)
{
	$result = $dVal;
	if (isset($_POST[$pStr]) == true) {
		$result = $_POST[$pStr];
	} else if (isset($_GET[$pStr]) == true) {
		$result = $_GET[$pStr];
	}
	return $result;
}

# Returns a formatted number string as the difference between current time
# and original request time.

function util_getPageLoadTime() {
	return number_format(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"],3);
}


# Returns a formatted message containing the computed page load time.

function util_getPageLoadTimeMsg() {
	return "Page generated in ".util_getPageLoadTime()." seconds.";
}

function mktutil_isSmallProgBuy ($threshold, $buyPrice)
{
	$result = false;
	if ($threshold > 0) {
		$pctdiff = (($threshold - $buyPrice) / $threshold);
		$result = ($pctdiff > .1);
	}
	return $result;
}

function mktutil_isLargeProgBuy ($threshold, $buyPrice)
{
	$result = false;
	if ($threshold > 0) {
		$pctdiff = (($threshold - $buyPrice) / $threshold);
		$result = ($pctdiff > .3);
	}
	return $result;
}

function mktutil_isSmallProgSell ($threshold, $sellPrice)
{
	$result = false;
	if ($threshold > 0) {
		$pctdiff = (($sellPrice - $threshold) / $threshold);
		$result = ($pctdiff > .1);
	}
	return $result;
}

function mktutil_isLargeProgSell ($threshold, $sellPrice)
{
	$result = false;
	if ($threshold > 0) {
		$pctdiff = (($sellPrice - $threshold) / $threshold);
		$result = ($pctdiff > .3);
	}
	return $result;
}


function mktutil_sumArray ($a, $start = 0, $end = -1) {
	$xx = microtime();
	
	if ($start == 0 && $end == -1) {
		return array_sum($a);
	}
	
	if (count($a) == 0) {
		return 0;
	}
	
	if ($end == -1) {
		$len = count($a);
	} else {
		$len = ($end + 1);
	}
	
	$result = 0;
	for ($i = $start; $i < $len; $i++) {
		$result += $a[$i];
	}
//	print "sumtime(".number_format(microtime()-$xx,3).")";
	return $result;
}



?>
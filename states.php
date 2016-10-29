<?php

define("STATE_IDLE",                      0x00000000);
define("STATE_SUSPEND",                   0x00000001);

define("STATE_MONITOR",                   0x00000100);
define("STATE_WAR",                       0x00000200);
define("STATE_NEWCITY",                   0x00000400);
define("STATE_IDLEBUILDS",                0X00000800);
define("STATE_DEADCITIES",                0x00001000);
define("STATE_MARKET",                    0x00002000);
define("STATE_GROUP_MASK",                0xFFFFFF00);

/* Monitor States */
define("STATE_MONITOR_FIELDS",              0x00000101);
define("STATE_MONITOR_FIELDS_RESULTS",      0x00000102);
define("STATE_MONITOR_REPORT_BUFFER",       0x00000103);
define("STATE_MONITOR_STORE_REPORT_BUFFER", 0x00000104);

/* War states */

/* Build City states */
define("STATE_NEWCITY_CANBUILD",          0x00000401);
define("STATE_NEWCITY_FINDFLAT",          0x00000402);
define("STATE_NEWCITY_FLATS",             0x00000403);
define("STATE_NEWCITY_BESTFLAT",          0x00000404);
define("STATE_NEWCITY_WAITONFLAT",        0x00000405);
define("STATE_NEWCITY_GETCITYCNT",        0x00000406);
define("STATE_NEWCITY_CHECKCITYCNT",      0x00000407);
define("STATE_NEWCITY_GETCITYNAMES",      0x00000408);
define("STATE_NEWCITY_PAUSE",             0x00000409);
define("STATE_NEWCITY_RENAME",            0x0000040A);

/* Dead cities */
define("STATE_DEADCITIES_CASTLES",        0x00001001);
define("STATE_DEADCITIES_WAITSCOUT",      0x00001002);
define("STATE_DEADCITIES_SCOUTING",       0x00001003);
define("STATE_DEADCITIES_REPORT",         0x00001004);
define("STATE_DEADCITIES_SEARCH",         0x00001005);

/* Market */
define("STATE_MARKET_BUYSELL",            0x00002001);

/* Process Slices */
/*   Unspecified slices between 0 and SLICE_MAX (e.g. 1) will default */
/*   to the MARKET state.                                             */
define("SLICE_IDLE",            0);
define("SLICE_MONITOR",         2);
define("SLICE_NEWCITY",         4);
define("SLICE_IDLEBUILDS",      6);
define("SLICE_DEADCITIES",      8);
define("SLICE_MARKET",         10);
define("SLICE_MAX",            11);


function getGroup($state) {
   return ($state & STATE_GROUP_MASK);
}
?>
<?php

define("STATE_IDLE",                      0x00000000);
define("STATE_SUSPEND",                   0x00000001);

define("STATE_MONITOR",                   0x00000100);
define("STATE_WAR",                       0x00000200);
define("STATE_NEWCITY",                   0x00000400);
define("STATE_IDLEBUILDS",                0X00000800);
define("STATE_DEADCITIES",                0x00001000);
define("STATE_GROUP_MASK",                0xFFFFFF00);

/* Monitor States */
define("STATE_MONITOR_FIELDS",            0x00000101);
define("STATE_MONITOR_FIELDS_RESULTS",    0x00000102);

/* War states */

/* Build City states */
define("STATE_NEWCITY_CANBUILD",          0x00000401);
define("STATE_NEWCITY_FINDFLAT",          0x00000402);
define("STATE_NEWCITY_FLATS",             0x00000403);
define("STATE_NEWCITY_BESTFLAT",          0x00000404);

/* Dead cities */
define("STATE_DEADCITIES_CASTLES",        0x00001001);

/* Process Slices */
define("SLICE_IDLE", 0);
define("SLICE_MONITOR", 1);
define("SLICE_NEWCITY", 2);
define("SLICE_IDLEBUILDS", 3);
define("SLICE_DEADCITIES", 4);
define("SLICE_MAX", 5);


function getGroup($state) {
   return ($state & STATE_GROUP_MASK);
}
?>
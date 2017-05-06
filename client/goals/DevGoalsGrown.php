<?php
require_once "../../lib/db.php";
require_once "../../lib/util.php";
require_once "../../code/db/DbGoals.php";
?>
# start hero management
config hero:11
config comfort:1

# Get res fields to minimum levels
build f:5,q:3,i:3,s:3:13
# 

# Basic goals from existing city
reportstokeep 1 a:500 b:2 a:3800 a:6000
distancepolicy 10 15 10 15 15
taxpolicy 0 100
config training:1
config trainint:0
config feastinghallspace:0
keepcapturedheroes any:level>=100,base>=79|any:level>=150,base>=69|any:level>=200
keepheroes any:level>=50|any:base>=69
config keepatthome:0
config nomayor:0
#nolevelheroes violet
config fasthero:70
config attackgap:6
config gate:0.2
gatepolicy 2 0 2 0 0
defensepolicy /junktroop:1000 /usewarhorn:0 /usecorselet:0 /usepenicillin:0
config embassy:1
config defensecooldown:30
config hiding:0.5
config troopqueuetime:.25
config troopincrement:1
config troopsusereserved:0
config troopsusepopmax:1 
config troopidlequeuetime:15
config troopdelbadque:1						  
config reservedbarrack:0

config fortsusereserved:0 
config wallqueuetime:0.5
fortification trap:1,ab:1
fortification trap:100,ab:100,at:100

config valleymin:1
config valley:9
config hunting:4

valleyheroes any
valleytroops 1 a:50
valleytroops 2 a:100
valleytroops 3 a:200
valleytroops 4 a:400,sw:1,p:1,w:1
valleytroops 5 a:800,sw:1,p:1,w:1
valleytroops 6 a:1600,sw:1,p:1,w:1
valleytroops 7 a:10000,sw:1,p:1,s:1,w:1,c:1
valleytroops 8 a:15000,sw:1,p:1,s:1,w:1,c:1
valleytroops 9 a:20000,sw:1,p:1,s:1,w:1,c:1,b:1
valleytroops 10 a:24000,sw:1,p:1,s:1,w:1,c:1,b:1


build c:4:8
build b:2:12
build fh:3,fo:2,ws:2
build w:2,s:3:16
build be:3

build st:2
build b:3:12
build m:3
build fo:4

build b:4:12
build r:5
build fh:5,be:5
build c:6:8
build s:4:16,i:4,q:4
build fo:5,ws:5,st:5
build b:9:2
build s:5:22
build b:9:4
build a:7,be:7,w:4
build t:6
build s:4:19
build r:7
build be:9
build fo:6,ws:6
build w:5
build c:8:8
build b:5:13
build a:8
build fo:7,ws:7
build s:6:28
build r:9
build rs:2
build s:6:31
build b:6:13
build a:9
build rs:6
# FIRST MICH USED
build r:10
build t:9
# need lvl10 th for general
build t:10

troop w:10,wo:10,p:10,sw:10,a:10
troop a:2k,s:100
troop b:400
troop t:500
troop b:800,t:1k,s:5k
troop wo:1k
troop w:1k
troop t:5k,c:5k
troop s:25k,a:10k
troop c:10k,b:1650
troop a:25k
troop t:50k
troop c:50k
troop a:35k

research ag:1,lu:1,mas:1,mi:1
research in:1,mt:1,ir:1
research ag:2,lu:2,mas:2,mi:2
research met:1,lo:1
research com:1,met:2,in:2,mt:2,ir:2,lo:2
research ms:4
research mt:4,in:4
research ar:1,ho:1
research ar:2,ho:2
research met:5
research ar:8,ho:6,mt:5,in:7
research ag:3,lu:3,mas:3,mi:3
research ag:4,lu:4,mas:4,mi:4
research med:1,con:1
research in:9
research ir:5,lo:5,com:5
research met:6,ms:6,mt:6
research en:1,med:3
research ir:6,lo:6,com:6
research en:3,con:5
research med:5
research in:10
research ms:7,met:7,ir:7
research en:5,lo:7
research med:6,con:6,en:6
research con:7
research lo:8

npctroops 5 b:400,t:400
npcheroes 5 any:att>65
config npc:5

rallypolicy v:1 m:1 r:3

//n = npc farming
//b = buildnpc
//m = medal hunting
//v = valley farming & safevalleyfarming
//starting in version 3057:
//t = troop reinforcements
//r = resource transports
//v = safe/valley farming & valley acquisition
<?php
   // get db connection
   $dbc = db_connectDB();
   if (is_null($dbc)) {
      return;
   }

   // get server, user, city
   $server = util_setParam("server", 0);
   $user   = util_setParam("player", "None");
   $city   = util_setParam("city", "None");
   // query database for goals
   $goals = new DbGoals($dbc,$server,$user,$city);
   foreach ($goals->getGoals() as $goal) {
      printf("%s\n", $goal);
   }
   // append here
   db_disconnectDB($dbc);
   
   return false;
?>
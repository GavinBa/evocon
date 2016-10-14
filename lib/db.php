<?php

   /**
    * Connect to the database for this application.  Will 'die' on failure.
    */
   function db_connectDB () 
   {
      // connect to database
//      $link = mysql_connect('127.0.0.1','root','mysql')
//            or die('Could not connect: ' . mysql_error());

//      mysql_select_db('evo') or die('Could not select database');

      $mysqli = new mysqli("localhost", "root", "mysql", "new_schema");
	  if ($mysqli->connect_errno) {
		  print "Failed";
		  return NULL;
	  }
      
      return $mysqli;
   }
   
   
   function db_disconnectDB ($mysqli)   
   {
	   $mysqli->close();
   }

?>
   
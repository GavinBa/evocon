<?php

   /**
    * Connect to the database for this application.  Will 'die' on failure.
    */
   function db_connectDB () 
   {
      // connect to database
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
   
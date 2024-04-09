<?php
  error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
  require 'include.php';
  @mkdir("C:\\temp",0777);
  $DB_PATH = "C:\\temp\\db.data";
  $DB_JDATA = ARRAY();
  if(is_file($DB_PATH))
  {    
    $DB_JDATA = json_decode(file_get_contents($DB_PATH),true);
  }  
  putenv("NLS_LANG=AMERICAN_AMERICA.UTF8");
<?php
require_once "lib/util.php";

define("DATA_PATH",       "data/scripts");

class ClientScript {


  var $m_clientReq;
  var $m_fileName;
  var $fp;
  var $m_path;
  
  public function __construct($cReq) {
	$this->m_clientReq = $cReq;
   $this->m_fileName = $this->createFilename();
   $this->m_path = $this->createDataPath();
  }
  
  protected function getFilePrefix() {
     return 'evo_' . $this->m_clientReq->getCity()->getName();
  }
  protected function createFilename() {
     return uniqid($this->getFilePrefix() . "_");
  }
  
  protected function createDataPath() {
     $result = DATA_PATH . "/" . $this->m_clientReq->getServer() . "/" . $this->m_clientReq->getUser();
     if (! file_exists($result)) {
        mkdir($result, 0777, true);
     }
     return $result;
  }
  
  public function getFilename() {
     return $this->m_fileName;
  }
  
  public function purge() {
     array_map('unlink', glob($this->m_path . "/" . $this->getFilePrefix() . "*" ));
  }
  
  public function getFullPath() {
     return $this->m_path . "/" . $this->getFilename();
  }
  
  public function startFile() {
   $this->fp = fopen($this->getFullPath(), 'w');
  }
  
  public function addLine($line) {
     fwrite($this->fp, $line . PHP_EOL);
  }
  
  public function addEcho($msg) {
     $this->addLine("echo '" . $msg . "'");
  }
  
  
  public function debugOn() {
     $this->addLine("city.script.debug = true");
  }
  
  public function debugOff() {
     $this->addLine("city.script.debug = false");
  }
  
  public function isOpen() {
     return ($this->fp != NULL);
  }
  
  public function endFile() {
   fwrite($this->fp, 'echo "' . util_getPageLoadTimeMsg() . '"' . PHP_EOL);
   fclose($this->fp);
   $this->fp = NULL;
     
  }
  
  public function injectScript($pathToScript) {
    $handle = @fopen($pathToScript, "r");
    if ($handle) {
       while (($buffer = fgets($handle, 4096)) !== false) {
          $this->addLine($buffer);
       }
       fclose($handle);
    } else {
       printf("Unable to open %s\n", $pathToScript);
    }
  }
  
  public function injectScriptVars($pathToScript, $v) {
    $handle = @fopen($pathToScript, "r");
    if ($handle) {
       while (($buffer = fgets($handle, 4096)) !== false) {
          foreach ($v as $k => $val) {
             $buffer = str_replace("__".$k."__",$val,$buffer);
          }
         $this->addLine($buffer);
       }
       fclose($handle);
    }
  }

}

?>
<?php
namespace Concrete\Package\MyAbc\Job;
use \Concrete\Core\Job\Job;

class RepairformLinkCustomer extends Job{
  public function getJobName() {
    return t("Repairform link customer");
  }

  public function getJobDescription() {
    return t("Create a link to prefill a repair form");
  }

  public function run(){
    
  }
}

?>

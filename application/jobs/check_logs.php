<?php
  namespace Application\Jobs;
  use \Concrete\Core\Job\Job as AbstractJob;

  class CheckLogs extends AbstractJob{
    public function getJobName() {
  		return t("Check MyAbc logs");
	  }

  	public function getJobDescription() {
  		return t("Check MyAbc logs and send email it on error");
  	}

    public function run(){

    }

  }

?>

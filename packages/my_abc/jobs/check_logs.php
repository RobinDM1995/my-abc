<?php
  namespace Concrete\Package\MyAbc\Job;
  use \Concrete\Core\Job\Job;

  use Loader;
  use Core;

  class CheckLogs extends Job{
    public function getJobName() {
  		return t("Check MyAbc logs");
	  }

  	public function getJobDescription() {
  		return t("Check MyAbc logs and send email it on error");
  	}

    public function run(){
      // $this->setTestLogs();
      $logs = $this->getLogs();
      $this->getMailBody($logs);
      // \Log::addEntry('Check_logs Finished running, ' date('d/m/Y H:i:s'));
    }

    public function setTestLogs(){
      \Log::AddWarning('Test Warning');
      \Log::AddError('Test Error');
      \Log::AddCritical('Test Critical');
      \Log::AddAlert('Test Alert');
      \Log::AddEmergency('Test Emergency');
    }

    public function getLogs(){
      $db = Loader::db();
      $onehourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));
      $results = $db->getAll('SELECT * FROM Logs WHERE level IN (300, 400, 500, 550, 600) AND time >= ?', array(strtotime($onehourAgo)));

      return $results;
    }

    public function sendMailIT($mailBody, $count){
      // $count = 0;
      if($count > 0){ // Only send mail if there are new log entries
        $mail = Core::make('mail');
        $mail->setSubject('Nieuwe Log entries MyAbc');
        $mail->setBodyHTML($mailBody);
        $mail->getBodyHTML();
        // $mail->to('rdm@abcparts.be');
        $mail->to('rdm@abcparts.be, ch@abcparts.be');
        $mail->from('info@abcparts.be');

        $mail->sendMail();
      }
    }

    public function getMailBody($logs){
      $logCount = count($logs);

      $levels[300] = 'Warning';
      $levels[400] = 'Error';
      $levels[500] = 'Critical';
      $levels[550] = 'Alert';
      $levels[600] = 'Emergency';

      $bodyHeader = '<h1>Het voorbije uur zijn volgende entries in de logs van MyAbc gekomen</h1>';

      $body = '<table border=1>';

      $body .= '<tr>';
      $body .= '<td>Date</td>';
      $body .= '<td>Level</td>';
      $body .= '<td>Message</td>';
      $body .= '</tr>';

      foreach($logs as $log){
        $body .= '<tr>';

        $body .= '<td>' . date('d/m/Y H:i:s', $log['time']) . '</td>';
        $body .= '<td>' . $levels[$log['level']] . '</td>';
        $body .= '<td>' . $log['message']. '</td>';

        $body .= '</tr>';
      }
      $body .= '</table>';

      $bodyFooter = '<a href="https://my.abcparts.be/login">Klik hier om naar MyAbc te gaan</a>';

      $bodyFull = $bodyHeader . $body . $bodyFooter;

      $this->sendMailIT($bodyFull, $logCount);
    }

  }

?>

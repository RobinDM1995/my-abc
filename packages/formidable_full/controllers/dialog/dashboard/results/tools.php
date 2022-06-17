<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results;

use \Concrete\Package\FormidableFull\Src\Formidable\Form;
use \Concrete\Package\FormidableFull\Src\Formidable\Result as Result;
use \Concrete\Package\FormidableFull\Src\Formidable\ResultList;
use \Concrete\Package\FormidableFull\Src\Formidable\Search\ColumnSet\Available as AvailableColumnSet;
use \Concrete\Package\FormidableFull\Src\Formidable\Search\Result\Result as SearchResult;
use \Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\BackendInterfaceController;
use \Concrete\Core\Application\EditResponse;
use \Concrete\Core\Http\Request;
use \Concrete\Core\Support\Facade\Url;
use Exception; 

class Tools extends BackendInterfaceController {
	
	protected $token = 'formidable_result';
	
	public function delete() {
		$r = $this->validateAction();
		if ($r === true) {
			$post = Request::getInstance()->post();
			$fr = new EditResponse();
			$fr->setRedirectURL(URL::to('/dashboard/formidable/results/'));
			if (is_array($post['answerSetIDs']) && count($post['answerSetIDs'])) {	
				foreach ($post['answerSetIDs'] as $answerSetID) {
					$result = Result::getByID($answerSetID);
					if (is_object($result))	{
						if (!$result->delete()) {
	                    	throw new Exception(t('Unable to delete one or more files.'));
						}
					}
				}
			}
			$fr->setMessage(t2('%s result deleted successfully.', '%s results deleted successfully.', count($post['answerSetIDs'])));
	        $fr->outputJSON();
	    }
	    $this->json($r);	
	}

	public function delete_all() {
		$r = $this->validateAction();
		if ($r === true) {
			$post = Request::getInstance()->post();
			$fr = new EditResponse();
			$fr->setRedirectURL(URL::to('/dashboard/formidable/results/'));
			$f = Form::getByID($post['formID']);
			if (!is_object($f)) $r = array('type' => 'error', 'message' => t('Can\'t find form'));
			else {
				$r = $this->checkFormPermissions($f, 'results');
				if ($r === true) {
					$results = $f->getResults();
					if (is_array($results) && count($results)) {
						foreach ($results as $result) {	
							$result->delete();
						}
					}
					$fr->setMessage(t('All results deleted successfully'));
					$fr->outputJSON();
				}
			}
	    }
	    $this->json($r);	
	}

	public function resend() {
		$r = $this->validateAction();
		if ($r === true) {	
			$post = Request::getInstance()->post();		
			if (is_array($post['answerSetIDs']) && count($post['answerSetIDs'])) {	
				foreach ($post['answerSetIDs'] as $answerSetID) {
					$result = Result::getByID($answerSetID);
					if (!is_object($result)) $r = array('type' => 'error', 'message' => t('Can\'t find result'));
					else {
						$f = Form::getByID($result->getFormID());
						if (!is_object($f)) $r = array('type' => 'error', 'message' => t('Can\'t find form'));
						else {
							$r = $this->checkFormPermissions($f, 'results');
							if ($r === true) {
								$f->setResult($result);	
								
								$mailings = $f->getMailings();
								if (is_array($mailings) && count($mailings)) {
									foreach ($mailings as $mailing) {								
										$mailing->send(true);
									}
								}
								$r = array( 'type' => 'info', 'message' => t('Result successfully resend'));	
							}
						}	
					}
				}
			}
	    }
	    $this->json($r);	    
	}

	public function xls() {
		$r = $this->validateAction();
		if ($r === true) {			
			$request = Request::getInstance()->request();
			$f = Form::getByID($request['formID']);
			if (!is_object($f)) $r = array('type' => 'error', 'message' => t('Can\'t find form'));
			else {
				$r = $this->checkFormPermissions($f, 'results');
				if ($r === true) {

					$date = date('Ymd');
					
					// Generate a dirty XLS (HTML though...)
					header("Content-Type: application/vnd.ms-excel; charset=utf-8");
					header("Cache-control: private");
					header("Pragma: public");
					header("Content-Disposition: inline; filename=formidable_{$date}.xls"); 
					header("Content-Title: Formidable Results - Run on {$date}");

					$html = array();
					$html[] = '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />';
					$html[] = '<table>';
					$fldca = new AvailableColumnSet(true);    
					$columns = $fldca->getColumns();
					if (is_array($columns) && count($columns)) {
						$html[] = '<tr>';
						foreach ($columns as $col) {
							$html[] = '<td>'.$col->getColumnName().'</td>';
						}
						$html[] = '</tr>';
					}
					// Get rows			
					$list = new ResultList();	     	
					if (is_array($request['item']) && count($request['item'])) $list->filterByIDs($request['item']);
					$col = $fldca->getDefaultSortColumn();
					$list->sanitizedSortBy($col->getColumnKey(), $col->getColumnDefaultSortDirection());
					$list->setItemsPerPage(99999);
					$result = new SearchResult($fldca, $list);
					foreach ($result->getItems() as $r) {
						$html[] = '<tr>';
						foreach ($r->getColumns() as $c) {
							$html[] = '<td>'.$c->getColumnValue().'</td>'; //strip_tags($c->getColumnValue());
						}
						$html[] = '</tr>';
					}
					$html[] = '</table>';

					echo @implode(PHP_EOL, $html);
					die();
				}
			}
		}
		$this->json($r);	
	}

	public function csv() {
		$r = $this->validateAction();
		if ($r === true) {			
			$request = Request::getInstance()->request();
			$f = Form::getByID($request['formID']);
			if (!is_object($f)) $r = array('type' => 'error', 'message' => t('Can\'t find form'));
			else {
				$r = $this->checkFormPermissions($f, 'results');
				if ($r === true) {

					$date = date('Ymd');
					
					// Generate a proper CSV (no-HTML though...)
					$csv_delimiter = ';';
					$csv_enclosure = '"';
					
					$fp = fopen('php://output', 'w');
					
					header("Content-Type: text/csv; charset=utf-8");
					header("Cache-control: private");
					header("Pragma: public");       
					header("Content-Disposition: attachment; filename=formidable_{$date}.csv");
					header("Content-Title: Formidable Results - Run on {$date}");
	
					// Columns
					$fldca = new AvailableColumnSet(true);    
					$columns = $fldca->getColumns();
					foreach ($columns as $col) {
						$row[] = $col->getColumnName();
					}
					fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
					fputcsv($fp, $row, $csv_delimiter, $csv_enclosure);

					// Get rows
					$list = new ResultList(); 
					if (isset($request['item']) && count($request['item'])) $list->filterByIDs($request['item']);
					$col = $fldca->getDefaultSortColumn();
					$list->sanitizedSortBy($col->getColumnKey(), $col->getColumnDefaultSortDirection());
					$list->setItemsPerPage(99999);

					$result = new SearchResult($fldca, $list);

					foreach ($result->getItems() as $r) {
						$row = array();
						foreach ($r->getColumns() as $c) {
							$row[] = strip_tags($c->getColumnValue());
						}
						fputcsv($fp, $row, $csv_delimiter, $csv_enclosure);
					}
					fclose($fp);
					die;
				}
			}
		}
		$this->json($r);	
	}

}

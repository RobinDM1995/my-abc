<?php   
namespace Concrete\Package\FormidableFull\Job;

use \Concrete\Core\Job\Job as AbstractJob;
use Concrete\Core\File\Service\File as FileService;
use \Concrete\Package\FormidableFull\Src\Formidable\FormList AS FFList;
use \Concrete\Package\FormidableFull\Src\Formidable\ResultList AS FFResults;
use \Concrete\Package\FormidableFull\Src\Formidable\Result AS FFResult;
class CleanFormidable extends AbstractJob {

	public function getJobName() {
		return t('Clean Formidable');
	}
	
	public function getJobDescription() {
		return t("Removes temporary files.");
	}
	
	public function run() {
		$f = new FileService();
		$f->removeAll(DIR_FILES_UPLOADED_STANDARD.'/formidable_tmp/');

		$deleted = 0;

		$list = new FFList();
		$list->filter("ff.gdpr", 1);
		$forms = $list->getResults();
		if (is_array($forms) && count($forms)) {
			foreach ($forms as $f) {
				$val = $f->getAttribute('gdpr_value');
				$type = $f->getAttribute('gdpr_type');
				if (intval($val) == 0 || empty($type)) continue;

				$rlist = new FFResults($f->getFormID());
				$rlist->filterByDateSubmitted(date('Y-m-d H:i:s', strtotime("-".$val." ".$type)), '<=');
				$rlist->setItemsPerPage(100);
				$results = $rlist->getResults();

				if (Count($results)) {
					foreach ($results as $result) {
						$r = FFResult::getByID($result->answerSetID);
						$r->delete();
						$deleted++;
					}
				}
			}
		}

		return t('%s Results deleted, all temporary files deleted', $deleted);
	}

}
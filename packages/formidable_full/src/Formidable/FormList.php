<?php 
namespace Concrete\Package\FormidableFull\Src\Formidable;

use \Concrete\Package\FormidableFull\Src\Formidable;
use \Concrete\Package\FormidableFull\Src\Formidable\Form;
use \Pagerfanta\Adapter\DoctrineDbalAdapter;
use \Concrete\Core\Search\Pagination\Pagination;
use \Concrete\Core\Search\ItemList\Database\ItemList;

class FormList extends ItemList {
	
	public function createQuery() {
        $this->query->select('ff.formID AS formID, ff.label AS label')->from('FormidableForms', 'ff');
    }

    public function filterByPermissions($groups = array()) {
        if (empty($groups)) {
			$ui = Formidable::getUser();
			if ($ui) {
				$groups = $ui->getUserObject()->getUserGroups();
				if ($ui->getUserID() == USER_SUPER_ID) return true;
			}
		}
        if (is_array($groups) && !count($groups)) return true;
        
        $query = array();
        if (is_array($groups) && count($groups)) {
            foreach ($groups as $group) {
                $query[] = "OR (ff.permission = 1 && FIND_IN_SET(".intval($group).", REPLACE(ff.permission_form, ';', ',')))";
            }
        }
        $this->filter(false, "(ff.permission = 0 ".@implode(' ', $query).")");
    }

    public function getResult($queryRow) {
        return Form::getByID($queryRow['formID']);
    }

    protected function createPaginationObject() {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->select('count(distinct ff.formID)')->setMaxResults(1);
        });
        $pagination = new Pagination($this, $adapter);
        return $pagination;
    }

    public function getTotalResults() {
        $query = $this->deliverQueryObject();
        return $query->select('count(distinct ff.formID)')->setMaxResults(1)->execute()->fetchColumn();
    }
}

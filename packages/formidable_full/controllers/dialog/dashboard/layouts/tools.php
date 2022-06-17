<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Layouts;

use \Concrete\Package\FormidableFull\Src\Formidable\Form;
use \Concrete\Package\FormidableFull\Src\Formidable\Layout;
use \Concrete\Package\FormidableFull\Src\Formidable\Element;
use \Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\BackendInterfaceController;
use \Concrete\Core\Support\Facade\Database;

class Tools extends BackendInterfaceController {
	
	protected $layouts = array();
	protected $token = 'formidable_layout';

	public function __construct() {
		$r = $this->validateAction();
		if ($r === true) {
			$f = Form::getByID($this->get('formID'));
			if (is_object($f)) {
				$r = $this->checkFormPermissions($f);	
				if ($r === true) {
					$this->layouts = $f->getLayout();
				}
			}	
		}
		$this->set('errors', $r);
	}
	
	public function save() {
		$r = $this->validateAction();
		if ($r === true) {
			$r = array(
				'type' => 'error', 
				'message' => t('Error: Layout can\'t be added or updated')
			);

			$suc = array(
				'type' => 'info', 
				'message' => t('Layout is successfully added or updated')
			);

			$f = Form::getByID($this->post('formID'));
			if (is_object($f)) {	
				$r = $this->checkFormPermissions($f);	
				if ($r === true) {			
					$l = Layout::getByID($this->post('layoutID'));		
					if (!is_object($l)) {					
						if (intval($this->post('rowID')) < 0 && !$this->post('cols')) $this->json($r);						
						
						if ($this->post('rowID') < 0) {					
							$layouts = $this->layouts;
							$newRowID = max(array_keys((array)$layouts));     
							
							$v = array(
								'layoutID' => 0, 
								'formID' => intval($this->post('formID')), 
								'rowID' => intval($newRowID)+1
							);	
							if ($this->post('appearance') == 'step') {
								$v['label'] = h($this->post('label'));
								$v['appearance'] = $this->post('appearance');
								$v['custom_buttons'] = intval($this->post('custom_buttons'));
								$v['btn_prev'] = h($this->post('btn_prev'));
								$v['btn_prev_css'] = intval($this->post('btn_prev_css'));
								$v['btn_prev_css_value'] = intval($this->post('btn_prev_css'))!=0?$this->post('btn_prev_css_value'):'';
								$v['btn_next'] = h($this->post('btn_next'));	
								$v['btn_next_css'] = intval($this->post('btn_next_css'));
								$v['btn_next_css_value'] = intval($this->post('btn_next_css'))!=0?$this->post('btn_next_css_value'):'';
								$v['css'] = intval($this->post('css'));
								$v['css_value'] = intval($this->post('css'))!=0?$this->post('css_value'):'';
								// Force just one save!

								$_POST['cols'] = 1;
							}							   
							for ($i=0; $i<$this->post('cols'); $i++) {
								$l = new Layout();
								$l->save($v);
							}	
							$this->json($suc);
						}						
					
						$layouts = $this->layouts;
						$row = $layouts[intval($this->post('rowID'))];	
						$row_columns = 0;
						if (is_array($row) && count($row)) $row_columns = intval(count($row));
						
						if ($row_columns < intval($this->post('cols'))) {
							$v = array(
								'layoutID' => 0, 
								'formID' => intval($this->post('formID')), 
								'rowID' => intval($this->post('rowID'))
							);
									
							for ($i=0; $i<( intval($this->post('cols')) - $row_columns); $i++) {
								$l = new Layout();
								if ($l->save($v)) $this->json($suc);
							}
						}
						elseif ($row_columns > intval($this->post('cols'))) {
							$delete = array_slice((array)$row, intval($this->post('cols')), NULL, true);		
							foreach ($delete as $l) {
								$elements = $l->getElements();
								if (is_array($elements) && count($elements)) return false;
							}
							foreach($delete as $l) {
								$l = Layout::getByID($l->layoutID);
								if (is_object($l)) $l->delete();
							}
							$this->json($suc);
						}
					}		
					
					if (intval($this->post('rowID')) < 0 && !$this->post('label')) $this->json($r);				
					
					$v = array();
					$v['rowID'] = intval($this->post('rowID'));
					$v['label'] = h($this->post('label'));
					$v['appearance'] = $this->post('appearance');
					$v['custom_buttons'] = intval($this->post('custom_buttons'));
					$v['btn_prev'] = h($this->post('btn_prev'));
					$v['btn_prev_css'] = intval($this->post('btn_prev_css'));
					$v['btn_prev_css_value'] = intval($this->post('btn_prev_css'))!=0?$this->post('btn_prev_css_value'):'';
					$v['btn_next'] = h($this->post('btn_next'));	
					$v['btn_next_css'] = intval($this->post('btn_next_css'));
					$v['btn_next_css_value'] = intval($this->post('btn_next_css'))!=0?$this->post('btn_next_css_value'):'';
					$v['css'] = intval($this->post('css'));
					$v['css_value'] = intval($this->post('css'))!=0?$this->post('css_value'):'';
		
					if (!is_object($l)) $l = new Layout();			
					if ($l->save($v)) $this->json($suc);
				}
			}
		}			
		$this->json($r);
	}
	
	public function delete() {
		$r = $this->validateAction();
		if ($r === true) {
			$err = array(
				'type' => 'error', 
				'message' => t('Error: Layout can\'t be deleted')
			);
			$suc = array(
				'type' => 'info', 
				'message' => t('Layout is successfully deleted')
			);
			$l = Layout::getByID($this->post('layoutID'));
			if (!is_object($l)) {
				if (intval($this->post('rowID')) < 0 && !$this->post('cols')) $this->json($err);
				$row = $this->layouts[intval($this->post('rowID'))];	
				if (is_object($row)) $row->delete();
				else {
					foreach ($row as $layout) {
						if (is_object($layout)) {
							$elements = $layout->getElements();
							if (is_array($elements) && count($elements)) {
								if (intval($this->get('all')) != 1) {
									$this->json(array(
										'type' => 'error', 
										'message' => t('Layout isn\'t empty and can\'t be deleted. Please move or delete elements, or check the "remove all" checkbox.'))
									);
								}
								else {
									foreach ($elements as $e) {
										$e->delete();
									}
								}
							}
						}
					}
					foreach($row as $layout) {
						$l = Layout::getByID($layout->layoutID);
						if (is_object($l)) $l->delete();
					}
				}
				$this->json($suc);
			}	
			$elements = $l->getElements();		
			if (is_array($elements) && count($elements)) {
				if (intval($this->get('all')) != 1) {
					$this->json(array(
						'type' => 'error', 
						'message' => t('Layout isn\'t empty and can\'t be deleted. Please move or delete elements, or check the "remove all" checkbox.'))
					);
				}
				else {
					foreach ($elements as $e) {
						$e->delete();
					}
				}
			}		
			$l->delete();	
			
			$this->json($suc);
		}
		$this->json($r);
	}

	public function order() {
		$r = $this->validateAction();
		if ($r === true) {
			$row_query = $sort_query = '';
			$f = Form::getByID($this->post('formID'));
			if (is_object($f)) {
				$r = $this->checkFormPermissions($f);	
				if ($r === true) {
					$db = Database::connection();
					if (is_array($this->post('rows')) && count($this->post('rows'))) {
						$i = 0;				
						foreach ($this->post('rows') AS $key => $row) {
							$layouts = $db->fetchAll("SELECT layoutID FROM FormidableFormLayouts WHERE rowID = ? AND formID = ? ORDER BY rowID ASC, sort ASC", array($row, $f->getFormID()));
							if (is_array($layouts) && count($layouts)) { 
								foreach ($layouts as $layoutID) {
									$row_query .= ' WHEN layoutID = '.intval($layoutID['layoutID']).' THEN '.intval($key).' ';
									$sort_query .= ' WHEN layoutID = '.intval($layoutID['layoutID']).' THEN '.intval($i).' ';
									$i++;
								}
							}
						}
						if (!empty($row_query) && !empty($sort_query)) $db->executeQuery("UPDATE FormidableFormLayouts SET rowID = CASE ".$row_query." END, sort = CASE ".$sort_query." END WHERE formID = ?", array($f->getFormID()));
					}
					if (is_array($this->post('cols')) && count($this->post('cols'))) {
						$i = 0;
						foreach ($this->post('cols') AS $key => $col) {		
							$sort_query .= ' WHEN layoutID = '.intval($col).' THEN '.intval($i).' ';
							$i++;
						}
						if (!empty($sort_query)) $db->executeQuery("UPDATE FormidableFormLayouts SET sort = CASE ".$sort_query." END WHERE rowID = ? AND formID = ?", array(intval($this->post('rowID')), $f->getFormID()));
					}	
					$r = array(
						'type' => 'info',
						'message' => t('Successfully moved layout')
					);
				}
			}
		}
		$this->json($r);
	}

	public function duplicate() {
		$r = $this->validateAction();
		if ($r === true) {
			$f = Form::getByID($this->post('formID'));
			if (is_object($f)) {
				$r = $this->checkFormPermissions($f);	
				if ($r === true) {
					$layouts = $f->getLayout();
					$err = array(
						'type' => 'error', 
						'message' => t('Error: Layout can\'t be duplicated')
					);
					$suc = array(
						'type' => 'info', 
						'message' => t('Layout is successfully duplicated')
					);
					$l = Layout::getByID($this->post('layoutID'));
					if (is_object($l)) {						
						$elements = $l->getElements();		
						if (is_array($elements) && count($elements)) {
							$nl = $l->duplicate($f->getFormID());				
							foreach ($elements as $e) {
								$nfe = $e->duplicate($f->getFormID(), $nl->getLayoutID());					
								$new_elements[$e->getElementID()] = $nfe->getElementID();					
							}
						}	
					}
					else {
						if (intval($this->post('rowID')) < 0) $this->json($err);
						$row = $layouts[intval($this->post('rowID'))];	
						if (is_object($row)) $nl = $row->duplicate($f->getFormID());
						else {
							$rowID = $f->getNextRowID();
							foreach ($row as $layout) {
								$nl = $layout->duplicate($f->getFormID(), $rowID);
								foreach ((array)$layout->getElements() as $e) {			
									$nfe = $e->duplicate($f->getFormID(), $nl->getLayoutID());					
									$new_elements[$e->getElementID()] = $nfe->getElementID();
								}
							}
						}
					}						

					foreach ($new_elements as $newElementID) {
								
						$nfe = Element::getByID($newElementID);
						if (!is_object($nfe)) continue;
						
						$depends = $nfe->getDependency('raw');
						if (empty($depends)) continue;		
			
						$dependencies = array();
						foreach ((array)$depends as $dep) {						
							$dependencyActions = $dependencyElements = array();
							foreach ((array)$dep['actions'] as $action) {	
								$dependencyActions[] = array_filter(array(
									'action' => (string)$action['action'],
									'action_value' => (string)$action['action_value'],
									'action_select' => (string)$action['action_select']
								));
							}
							foreach ((array)$dep['elements'] as $element) {	
								$dependencyElements[] = array_filter(array(
									'element' => $new_elements[$element['element']],
									'element_value' => (string)$element['element_value'],
									'condition' => (string)$element['condition'],
									'condition_value' => (string)$element['condition_value']
								));
							}
							$dependencies[] = array(
								'actions' => $dependencyActions,
								'elements' => $dependencyElements
							);
						}				
						$nfe->save(array('dependencies' => serialize($dependencies)));
					}				
					$this->json($suc);
				}
			}
		}
		$this->json($r);
	}

}

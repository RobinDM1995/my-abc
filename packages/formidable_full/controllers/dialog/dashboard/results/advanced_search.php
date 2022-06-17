<?php
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results;

use Concrete\Controller\Dialog\Search\AdvancedSearch as AdvancedSearchController;
use Concrete\Core\Entity\Search\SavedSearch;
use Concrete\Package\FormidableFull\Src\Formidable\Search\ColumnSet\ColumnSet as ColumnSet;
use Concrete\Package\FormidableFull\Src\Formidable\Search\ColumnSet\Available as Available;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\Http\Request;
use \Concrete\Core\Page\Page;
use Permissions;
use \Concrete\Core\Support\Facade\Url;
use Core;
use \Concrete\Core\User\User;

class AdvancedSearch extends AdvancedSearchController {
    
    protected $viewPath = '/dialogs/search/advanced_search';
    protected $supportsSavedSearch = false;

    protected function canAccess() {
        $c = Page::getByPath('/dashboard/formidable/results');
        $cp = new Permissions($c);
        return $cp->canRead();
    }
  
    
    public function submit()
    {
        if ($this->validateAction()) {
            
            $post = Request::getInstance()->post();

            $u = new User();
            $fdc = new ColumnSet();
            $fldca = new Available();
            foreach($post['column'] as $key) {
                $fdc->addColumn($fldca->getColumnByKey($key));
            }  
            $sortCol = $fldca->getColumnByKey($post['fSearchDefaultSort']);
            $fdc->setDefaultSortColumn($sortCol, $post['fSearchDefaultSortDirection']);
            
            $session = Core::make('app')->make('session');
            $formID = $session->get('formidableFormID');

            $u->saveConfig('FORMIDABLE_LIST_DEFAULT_COLUMNS_'.$formID, serialize($fdc));
            
            $query = $this->getQueryFromRequest();            
            $itemsPerPage = (int) $post['fSearchItemsPerPage'];
            if ($itemsPerPage != 0) $query->setItemsPerPage($itemsPerPage);

            $provider = $this->getSearchProvider();
            $provider->setSessionCurrentQuery($query);
                    
            $result = $provider->getSearchResultFromQuery($query);
            $result->setBaseURL($this->getBasicSearchBaseURL());

            return new JsonResponse($result->getJSONObject());
        }
    }
    
    public function getSearchProvider()
    {
        $provider = $this->app->make('Concrete\Package\FormidableFull\Src\Formidable\Search\SearchProvider');
        return $provider;
    }

    public function getFieldManager()
    {
        return $this->app->make('Concrete\Package\FormidableFull\Src\Formidable\Search\Field\Manager');
    }

    public function getSavedSearchBaseURL(SavedSearch $search)
    {
        return (string) URL::to('/formidable/dialog/dashboard/results/search/preset', $search->getID());
    }

    public function getCurrentSearchBaseURL()
    {
        return (string) URL::to('/formidable/dialog/dashboard/results/search/current');
    }

    public function getBasicSearchBaseURL()
    {
        return (string) URL::to('/formidable/dialog/dashboard/results/search/basic');
    }

    public function getSavedSearchEntity()
    {
        $em = $this->app->make(EntityManager::class);
        if (is_object($em)) {
            return $em->getRepository('Concrete\Core\Entity\Search\SavedSearch');
        }
        return null;
    }
}

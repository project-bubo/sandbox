<?php

namespace AdminModule;

use Nette\Http\User,
    \AdminModule\DataGrids\TestDataGrid,
    AdminModule\Forms\LoginForm;

final class ExtParamPresenter extends SecuredPresenter {

    /**
     * @persistent
     */
    public $labelId;
    
    /**
     * @persistent
     */
    public $id;
    
    /**
     * @persistent
     */
    public $identifier;
    
    private $tags;
    
    public function renderParams($id, $labelId, $identifier) {

//        $treeNodeId = $this->presenter->getParam('id');
      
        
        //dump($id, $labelId, $identifier);
        
    }
    
    public function beforeRender() {
        parent::beforeRender();
        
        $id = $this->getParam('id');
        
        $params = array('treeNodeId' => $id, 'lang' => $this->langManagerService->getDefaultLanguage());
        $page = $this->pageManagerService->getPage($params);
        $this->template->page = $page;
    }
    
    
    public function getTags() {
        return $this->tags;
    }
    
    
    
    public function actionValues($id, $labelId, $identifier, $parentId, $extTreeNodeId) {
        $activatedLanguages = $this->langManagerService->getLangs();
        $params = $this->extModel->getParams($parentId, $identifier);
        
        
        $this->template->activatedLanguages = $activatedLanguages;
        $this->template->params = $params;
        
        
        // how to get units?
        // identifier is available in url $identifier BUT values must be prefixed with val_
        
        $valIdentifier = 'val_'.$identifier;
        
        $config = $this->pageManagerService->getExtPropertiesByIdentifier($valIdentifier);
        
        $this->template->units = isset($config['units']) ? $config['units'] : NULL;

    }
    
    public function actionStructuredParams($id, $labelId, $identifier, $parentId, $extTreeNodeId) {
        $activatedLanguages = $this->langManagerService->getLangs();
        
        $this->template->activatedLanguages = $activatedLanguages;
        
        
        
//        dump('sutu');
//        die();
    }
    
    
    public function actionAddStructuredParam($id, $labelId, $identifier) {
        $activatedLanguages = $this->langManagerService->getLangs();
        
        // get product tags how??
        // get extension config by identifer
        
        $extConfig = $this->presenter->configLoaderService->loadLabelExtentsionProperties();
        
        $this->template->activatedLanguages = $activatedLanguages;
        

        
    }
    
    public function actionEditStructuredParam($id, $labelId, $identifier) {
        $activatedLanguages = $this->langManagerService->getLangs();
        
        // get product tags how??
        // get extension config by identifer
        
        //$extConfig = $this->presenter->configLoaderService->loadLabelExtentsionProperties();
        
        $this->template->activatedLanguages = $activatedLanguages;
        
    }
    
    
    public function actionEditParam($id, $labelId, $identifier){
        $activatedLanguages = $this->langManagerService->getLangs();
        
        // get product tags how??
        // get extension config by identifer

        $config = $this->pageManagerService->getExtPropertiesByIdentifier($identifier);
        $tags = $config ? $config['tags'] : array();
        
//        dump('tu');
//        die();
        
        $this->template->activatedLanguages = $activatedLanguages;
        $this->template->tags = $this->tags = $tags;
    }
    
    public function actionAddParam($id, $labelId, $identifier) {
        $activatedLanguages = $this->langManagerService->getLangs();
        
        // get product tags how??
        // get extension config by identifer
        
        $config = $this->pageManagerService->getExtPropertiesByIdentifier($identifier);
        $tags = $config ? $config['tags'] : array();
        
        
        $this->template->activatedLanguages = $activatedLanguages;
        $this->template->tags = $this->tags = $tags;

        
    }
    
    
    public function handleSaveParamSortOrder($data) {
        parse_str($data);
        // order is in $img
        
//        dump($param);
//        die();
        
        $id = $this->getParam('id');
        $identifier = $this->getParam('identifier');
        $tag = $this->getParam('tag');
        
        $this->extModel->sortParams($tag, $id, $identifier, $param);
        $this->flashMessage("Parametry setříděny");
        $this->redirect('this');
    }
    
    
    public function actionSortParams($id, $labelId, $identifier, $tag) {
        
        // try to load all param names assigned to 
        // treeNodeId -> $id / $identifier and tagged by $tag
        
        $config = $this->pageManagerService->getExtPropertiesByIdentifier($identifier);
        
//        dump($config);
//        die();
        
        $lang = $this->langManagerService->getDefaultLanguage();
        $this->template->params = $params = $this->extModel->loadParamsForSorting($tag, $identifier, $id, $lang);
        
        
        $this->template->tag = isset($config['tags'][$tag]) ? $config['tags'][$tag] : NULL;
        
//        dump($this->template->tag);
//        die();
        
        
        
    }

    
}

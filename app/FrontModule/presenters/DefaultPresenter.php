<?php

namespace FrontModule;

use Nette\Http\User,
    \BuboApp\AdminModule\DataGrids\TestDataGrid,
    AdminModule\Forms\LoginForm;

class DefaultPresenter extends BasePresenter {

    public $currentTreeNodeId = NULL;
    
    public $page = NULL;
    
    public $isLink = FALSE;
    
    public $linkTreeNodeId = NULL;
    
//    public function actionShowDraft($url) {
//        dump('já jsem draft');
//        die();
//        
//    }
    
    
    
    
    public function loadPage($lang, $url) {
        
        // lookup in url table
        $this->url = $url;
        
        if ($lang === NULL) {
            $lang = $this->langManagerService->getDefaultLanguage();
        }
        
        
        // get all langs
        $allLangs = $this->langManagerService->getLangs();

        if (!isset($allLangs[$lang])) {
            throw new \Nette\Application\BadRequestException('Stránka nenalezena');
        }
        
        //$currentLang = $this->lang ?: $this->langManagerService->getDefaultLanguage();
        
        $treeNodeId = NULL;
        
        $module = $this->pageManagerService->getCurrentModule();
        
        if ($url === NULL) {
            // try to find homepage
            $treeNodeId = $this->pageManagerService->getHomepageTreeNodeId($lang);
        } else {
            
            $urlParams = array(
                            'url%s'           =>  $url,
                            'lang_%s'         =>  $lang,
                            'page_deleted'    =>  NULL,
                            'access_through'  =>  $this->pageManagerService->getCurrentModule()
                            //'temporary'     =>  0
            );
            
//            dump($urlParams);
//            die();
            
            $urlRecord = $this->context->database->fetch('SELECT * FROM [:core:urls] WHERE %and ORDER BY [url_id] DESC LIMIT 1', $urlParams);
            
//            dump($urlRecord);
//            die();
            
            $module = $urlRecord['module_'];

            $treeNodeId = $urlRecord ? $urlRecord['tree_node_id_'] : NULL;
            
            $this->isLink = $urlRecord['module_'] != $urlRecord['access_through'];
//            dump($urlRecord);
//            die();
            
//            dump($this->isLink);
//            die();
            
            if ($this->isLink) {
                $this->linkTreeNodeId = $this->context->database->fetchSingle('SELECT [tree_node_id] FROM [:core:page_tree] WHERE [pattern] = %i', $treeNodeId);
            }
            
//            dump($this->linkTreeNodeId);
//            die();
            
            //if ()            
        }
        
//        dump($treeNodeId);
//        die();
        
        if (!$treeNodeId || !$this->pageManagerService->isAllowedModule($module)) {
            throw new \Nette\Application\BadRequestException('Stránka nenalezena');
        } 

//        
//        dump($module);
//        die();
        
        
        
        $params = array(
                    'treeNodeId' => $treeNodeId,
                    'lang'       => $lang,
                    'states'     => array('published'),
                    'searchAllTimeZones' => FALSE,
                    'module'    =>  $module,
                    'allLangs'  =>  array($lang),
                    'searchGhosts'  => TRUE
        );
        
        $page = $this->pageManagerService->getPage($params);
        
//        dump($page->_ext_val_categ_products);
//        die();
        
//        \Nette\Diagnostics\Debugger::$maxDepth = 6;
//        dump($page->getData());
//        die();
        
//        dump()
        
        //$treeNodeId = 42;
        $this->currentTreeNodeId = $treeNodeId;
        
//        dump($page->_layout);
//        die();
        
        $modulePaths = $this->projectManagerService->getOrderedModulePaths($module);
//        dump($modulePaths);
//        die();
        $layoutFilename = NULL;
        if (!empty($modulePaths)) {
            foreach ($modulePaths as $modulePath) {
                $fileName = APP_DIR . '/' . $modulePath . 'templates/' . $page->_layout;
                if (is_file($fileName)) {
                    $layoutFilename = $fileName;
                    break;
                }
            }
        }

        $this->template->layout = $layoutFilename;//APP_DIR . '/FrontModule/templates/'.$page->_layout;
        $this->template->themePath =  $this->template->basePath . '/' . strtolower($this->pageManagerService->getCurrentModule());
        
        if (!empty($page)) {    
//            if($page->isHomePage){
//                $this->template->layout = '../homepageLayout.latte';
//            }
            
            $this->template->page = $this->page = $page;            
            $this->template->moduleLayout = \Nette\Utils\Strings::webalize($this->pageManagerService->getCurrentModule());
            
            
        } else {
            // page not found
            $this->template->page = array();
            $this->template->page['title'] = 'Error 404';
            $this->template->page['url_chunk'] = 'error';
            $this->template->page['isHomePage'] = false;
            $this->template->page['content'] = "Stránka nenalezena";
        }
    }
    
    /**
     * Frontend dispatcher
     * 
     * @param type $url - url (without first slash) 
     */
    public function actionDefault($lang, $url) {
        
        $this->loadPage($lang, $url);
    }
    
}
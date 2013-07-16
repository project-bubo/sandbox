<?php

namespace AdminModule;

use Nette\Http\User,
    \AdminModule\DataGrids\TestDataGrid,
    AdminModule\Forms\LoginForm;

final class DefaultPresenter extends SecuredPresenter {

    
    
//    public function createComponent($name) {
//        
//        $pluginName = ucfirst($name);
//        // namespace class name
//        $npClassName = "Plugins\\" . $pluginName;
//        
//        $instantiatedClasses = array();
//        
//        foreach ($this->plugins as $plugin) {
//            $instantiatedClasses[] = $plugin['class'];
//        }
//        
//        if (in_array($npClassName, $instantiatedClasses)) {            
//            return new $npClassName($this, $name, $this->context->database);
//        }     
//        
//        return parent::createComponent($name);
//    }
    
    
    public function actionRemovePages() {
        
//        $treeNodeIds = array(67);
//        
//        $res = $this->context->database->query('SELECT [page_id] FROM [:core:pages] WHERE [tree_node_id] IN %in', $treeNodeIds)->fetchAssoc('page_id');
//        $pageIds = array_keys($res);
//        
//        // delete pages from cms_page_tree
//        $this->context->database->query('DELETE FROM [:core:page_tree] WHERE [tree_node_id] IN %in', $treeNodeIds);
//        
//        // delete from cms_urls
//        $this->context->database->query('DELETE FROM [:core:urls] WHERE [tree_node_id_] IN %in', $treeNodeIds);
//        
//        
//        // delete from cms_pages_labels
//        $this->context->database->query('DELETE FROM [:core:pages_labels] WHERE [tree_node_id] IN %in', $treeNodeIds);
//        
//        // delete from cms_vd_pages_files
//        $this->context->database->query('DELETE FROM [:core:vd_pages_files] WHERE [page_id] IN %in', $pageIds);
//        
//        dump($pageIds);
//        
//        die();
    }
    
    
    public function actionCopyLayout() {
        
        
//        $res = $this->context->database->query('SELECT tree_node_id, layout FROM cms_pages WHERE layout IS NOT NULL GROUP BY tree_node_id')->fetchPairs('tree_node_id','layout');
//        
//        if (!empty($res)) {
//            foreach ($res as $treeNodeId => $layout) {
//                $this->context->database->query('UPDATE [:core:page_tree] SET [layout] = %s WHERE [tree_node_id] = %i', $layout, $treeNodeId);
//            }
//        }
        
        
        dump('hotovo');
        die();
        
    }
    
    
    public function actionRepairUrls() {
        
        $allUrls = $this->context->database->fetchAll('SELECT * FROM [:core:urls]');
        
        foreach ($allUrls as $url) {
            
            
            if (\Nette\Utils\Strings::startsWith($url['url'], '/')) {
                $this->context->database->query('UPDATE [:core:urls] SET [url] = %s, [lang_] = %s WHERE [url_id] = %i', substr($url['url'], 1), 'cs', $url['url_id']);
            }
            
        }
        
        dump('repair urls');
        die();
        
    }
    
    public function renderDefault($plugin, $view) {
        
        $args = $this->getParam();
        
        unset($args['action'], $args['plugin'], $args['view']);
        
        if (!empty($plugin)) {
            
            $this->template->plugin = $plugin;
            $this->template->view = $view;
            $this->template->args = $args;
        }
        
        $this->template->numberOfConcepts = 0;
        $this->template->numberOfTrashed = 0;
                
    }

    
    public function actionLogout() {
        
        //$this['clipboard']->clean();
        
        $this->getUser()->logOut(TRUE);
        $this->flashMessage('Právě jste se odhlásili z administrace.');
        $this->redirect('Auth:login');
    }
    

    


}
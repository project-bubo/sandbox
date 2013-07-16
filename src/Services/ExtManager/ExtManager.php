<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bubo\Services;

use Nette, Bubo,
    Nette\Utils\Strings;

class ExtManager extends BaseService {

    private $context;
    private $presenter;

    public function __construct($context) {
        $this->context = $context;
    }

    public function setPresenter($presenter) {
        $this->presenter = $presenter;
    }

    public function isPresenterSet() {
        return $this->presenter !== NULL;
    }

    private function _getRealName($nameWithoutPrefix) {
        //return Strings::substring($nameWithoutPrefix, 4);
        return Strings::startsWith($nameWithoutPrefix, 'ext_') ? Strings::substring($nameWithoutPrefix, 4) : $nameWithoutPrefix;
    }
    
    
    public function redirect($nameWithoutPrefix) {

        
        if (preg_match('#ext_val_(.*)#', $nameWithoutPrefix, $matches)) {
        
            // we are gonna fill values for params is $matches[1]
            
            // but how to determine params?????
            // by identifier and parentId of this page
            $parentId = $this->context->database->fetchSingle('SELECT [parent] FROM [:core:page_tree] WHERE [tree_node_id] = %i', $this->presenter->getParam('id'));
            
            
            $where = array(
                        'identifier'    =>  $matches[1],
                        'tree_node_id'  =>  $parentId
            );
            
            $extTreeNodeId = $this->context->database->fetchSingle('SELECT [ext_tree_node_id] FROM [:core:ext_tree] WHERE %and', $where);
            
            $params = array(
                        'labelId'           =>  $this->presenter->getParam('labelId'),
                        'id'                =>  $this->presenter->getParam('id'),
                        'parentId'          =>  $parentId,
                        'identifier'        =>  $matches[1],
                        'extTreeNodeId'     =>  $extTreeNodeId
            );
            
            $this->presenter->redirect('ExtParam:values', $params);
        }
        
//        die();
        $realName = $this->_getRealName($nameWithoutPrefix);
        $labelExtensions = $this->presenter->pageManagerService->getAllLabelExtensions();
        $labelExtensionsProperties = $this->presenter->configLoaderService->loadLabelExtentsionProperties();
//        dump($realName, $labelExtensions, $labelExtensionsProperties);
        
        if (isset($labelExtensions[$realName])) {
            switch ($labelExtensions[$realName]['name']) {
                case 'parameters':
                    $params = array(
                                'labelId'       =>  $this->presenter->getParam('labelId'),
                                'id'            =>  $this->presenter->getParam('id'),
                                'identifier'    =>  $this->_getRealName($nameWithoutPrefix)
                    )           ;
                    $this->presenter->redirect('ExtParam:params', $params);
                    break;
                case 'structuredParams':
                    $params = array(
                                'labelId'       =>  $this->presenter->getParam('labelId'),
                                'id'            =>  $this->presenter->getParam('id'),
                                'identifier'    =>  $this->_getRealName($nameWithoutPrefix)
                    )           ;
                    $this->presenter->redirect('ExtParam:structuredParams', $params);
                    break;
            }
        }
        
        
        die();
        
        
        
    }
    
    
    
    public function getExt($page, $nameWithoutPrefix, $args = NULL) {

        
        $retValue = NULL;

        $labelExtensions = $this->presenter->pageManagerService->getAllLabelExtensions();
        $labelExtensionsProperties = $this->presenter->configLoaderService->loadLabelExtentsionProperties();

        $realName = $this->_getRealName($nameWithoutPrefix);
        
        $isEntityParam = FALSE;
        
        $name = NULL;
        $entity = $page->_entity;
        if ($entity) {
            $entityConfig = $this->presenter->configLoaderService->loadEntityConfig($entity);
            if (isset($entityConfig['properties'][$realName])) {
                //$realName = $entityConfig['properties'][$realName]['extName'];
                $name = $entityConfig['properties'][$realName]['extName'];
                $isEntityParam = TRUE;
            }
        }
        
        if (isset($labelExtensions[$realName]) || $name !== NULL) {
            // extension exists
            // what type is it?? get is by name

            $name = $name ?: $labelExtensions[$realName]['name'];
            if (isset($labelExtensionsProperties['properties'][$name])) {
                // identifier exists
                $extensionConfig = $labelExtensionsProperties['properties'][$name];
                
                $extEngineName = isset($extensionConfig['engine']) ? $extensionConfig['engine'] : 'default';
                
                $engineClassName = 'Bubo\\ExtEngines\\' . ucfirst($extEngineName) . 'ExtEngine';
                
                if (class_exists($engineClassName)) {
                    $reflect  = new \ReflectionClass($engineClassName);
                    
                    $engine = $reflect->newInstanceArgs(array('page' => $page));
                    //$retValue = $engine->getExt($realName, $extensionConfig, $args);
                    $retValue = $engine->getExt($realName, $extensionConfig, $args, $isEntityParam);
                } else {
                    throw new \Nette\InvalidStateException('Class '.$engineClassName.' not found');
                }

            }
        }
        return $retValue;
    }

}

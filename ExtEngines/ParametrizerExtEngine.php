<?php

namespace Bubo\ExtEngines;

use Nette,
    Nette\Utils\Strings;

/**
 * 
 * @author Marek Juras
 */
class ParametrizerExtEngine extends BaseExtEngine {

    public function getExt($realName, $extensionConfig, $args = NULL) {
        
       $nameWithoutPrefix = 'ext_'. $realName; 
        
       switch ($extensionConfig['type']) {
            case 'parameters': // handle single files
//                $files = $this->page->presenter->virtualDriveService->getFilesByPageId($this->page->_page_id);
//
//                if (isset($files[$this->page->_page_id]) && isset($files[$this->page->_page_id][$nameWithoutPrefix])) {
//                    $retValue = $files[$this->page->_page_id][$nameWithoutPrefix];
//                }
//                dump('parameters');
                
                break;
            case 'parameterValues': 
                // treeNodeId and identifier is required                
                $ident = substr($realName,4);

                $presenter = $this->page->presenter;

                $tag = $args[0];
                $paramValues = $presenter->extModel->getNamedParamValues($this->page->_parent, $this->page->_tree_node_id, $ident, $presenter->getFullLang(), $tag);
                return $paramValues;
                break;
            case 'structuredParams':
                $presenter = $this->page->presenter;
                return $presenter->extModel->getNamedStructuredParamValues($this->page->_parent, $realName, $presenter->getFullLang());
                break;
        }

    }
    
    

}
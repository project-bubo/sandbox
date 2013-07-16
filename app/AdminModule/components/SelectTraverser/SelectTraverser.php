<?php

namespace AdminModule\Components;

use Netstars;

class SelectTraverser extends Netstars\Traversing\Traverser {   
    
    const LAST_MARK             = '`--';
    const NON_LAST_MARK         = '|--';
    const CONTINUOS_MARK        = '|&nbsp;&nbsp;';
    const NON_CONTINUOS_MARK    = '&nbsp;&nbsp;&nbsp;';
    
    private $presenter;
    
    private $currentTreeNodeId;
    
    private $lang;
    
    // additional boolean condifition
    // if TRUE then only entities with url are traversed
    private $disablePagesWithNoUrl;
    
    // Which module to traverse?
    // NULL means current module
    private $traverseModule;
    
//    private $roots = FALSE;
//    private $descendantParams = NULL;
    
    /**
     * When set to TRUE,
     * $selectData will contain compound key in form
     * $treeNodeId - $pattern
     * @var type 
     */
    private $includeLinks;
    
    private $excludedPages;
    
    public function __construct($presenter, $currentTreeNodeId = NULL, $excludedPages = NULL, $disablePagesWithNoUrl = FALSE) {
        parent::__construct();
        $this->presenter = $presenter;
        $this->currentTreeNodeId = $currentTreeNodeId;
        
        $this->excludedPages = $excludedPages ?: array();
        $this->disablePagesWithNoUrl = $disablePagesWithNoUrl;
    }
    
    public function getSelectMenu($lang, $includeLinks = FALSE, $traverseModule = NULL) {    
        $this->lang = $lang;
        $this->includeLinks = $includeLinks;
        $this->traverseModule = $traverseModule;
        return $this->traverse();
    }

    
    public function traverse() {
        
        $selectData = array();
        
        if (!$this->includeLinks) {
            $rootElement = \Nette\Utils\Html::el('option');
            $rootElement->value = $this->includeLinks ? '0-x' : 0;
            $rootElement->setHtml("Nejvyšší úroveň");
            $selectData[$this->includeLinks ? '0-x' : 0] = $rootElement;
        }
        
        $openedLevels = array();
        $topLevelPage = $this->presenter['pages'];
        
        $descendantsParams = array(
                                    'lang'                  =>  $this->lang,
                                    'states'                =>  array('draft', 'published'),
                                    'searchGhosts'          =>  TRUE,
                                    'searchAllTimeZones'    =>  TRUE
        );
        
        if ($this->traverseModule !== NULL) {
            $descendantsParams['module'] = $this->traverseModule;
        }
        
//        dump($descendantsParams);
//        die();
        
        $this->_recursiveDFSRender($topLevelPage, $descendantsParams, $selectData, $openedLevels, 0);
        return $selectData;
    }

    /**
     * Traverse the page tree.
     * 
     * Use $rootId as root.
     * During the traversing, accept pages only with state $acceptedStates
     */
    private function _recursiveDFSRender($node, $descendantsParams, &$selectData, &$openedLevels, $level) {
        
            $descendants = $node->getDescendants($descendantsParams);
            
            $cd = count($descendants);
            if ($cd > 0) {

                $furtherLevel = $level + 1;
                $c = 1;
                
                //$this->disablePagesWithNoUrl = FALSE;
                
                // count the number of decsendants
                foreach ($descendants as $descendant) {
                   
                    
                    
                    //dump(!$this->disablePagesWithNoUrl || $descendant->createsUrl());
                    
                    if (($this->currentTreeNodeId !== NULL) 
                            && 
                        ($descendant->treeNodeId == $this->currentTreeNodeId) 
                                || 
                        array_search($descendant->treeNodeId, $this->excludedPages) !== FALSE 
                                || 
                        ($this->disablePagesWithNoUrl && !$descendant->createsUrl())
                        ) {
                        $cd = $cd - 1;
                        break;
                    }
                }
                
                foreach ($descendants as $descendant) {

                    
//                    dump(!$this->disablePagesWithNoUrl || $descendant->createsUrl());
                    
                    if (array_search($descendant->treeNodeId, $this->excludedPages) === FALSE) {
                    
                        if (
                                (
                                    ($this->currentTreeNodeId === NULL) 
                                            || 
                                    ($this->currentTreeNodeId !== NULL) 
                                        && 
                                    ($descendant->treeNodeId != $this->currentTreeNodeId)
                                ) 
                                && 
                                (!$this->disablePagesWithNoUrl || $descendant->createsUrl())
                                ) 
                            {

                            if (($c == 1) && ($c < $cd)) {
                                $openedLevels[$level] = $level;
                            }

                            if ($c == $cd) {
                                unset($openedLevels[$level]);
                            }

                            $option = \Nette\Utils\Html::el('option');
                            $option->value = $descendant->_tree_node_id.($this->includeLinks ? '-'.($descendant->_pattern === NULL ? 'x' : $descendant->_pattern) : '');
                            $option->setHtml($this->_generateSelectRowPrefix($furtherLevel, $openedLevels, $c == $cd) . (strlen($descendant->_name) > 40 ? mb_substr($descendant->_name, 0, 40).'...' : $descendant->_name));
                            $selectData[$descendant->_tree_node_id.($this->includeLinks ? '-'.($descendant->_pattern === NULL ? 'x' : $descendant->_pattern) : '')] = $option;
                            $this->_recursiveDFSRender($descendant, $descendantsParams, $selectData, $openedLevels, $furtherLevel);                

                            $c++;

                        }
                    
                    }
                }
            }

        
    }
    
    
    private function _generateSelectRowPrefix($actualLevel, $openedLevels, $isLast) {
        
        $prefix = "";
        
        $actualLevelMarker = $actualLevel == 1 ? "" : ($isLast ? self::LAST_MARK : self::NON_LAST_MARK);
        
        if ($actualLevel > 2) {
            for ($i = 2; $i < $actualLevel; $i++) {
                $prefix .= array_key_exists($i-1, $openedLevels) !== FALSE ? self::CONTINUOS_MARK : self::NON_CONTINUOS_MARK;
            }
        }
        
        return $prefix . $actualLevelMarker;
    }
    
    
}


    
    

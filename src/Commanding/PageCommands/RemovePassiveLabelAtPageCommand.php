<?php

namespace Bubo\Commanding\PageCommands;

use Bubo;

/**
 * Remove passive label command
 * 
 * @author Marek Juras
 */
final class RemovePassiveLabelAtPageCommand extends Bubo\Commanding\Command {
    
    private $treeNodeId;
    private $label;
    private $lang;
    
    public function __construct($treeNodeId, $label, $lang) {
        parent::__construct();
        $this->treeNodeId = $treeNodeId;
        $this->label = $label;
        $this->lang = $lang;
    }
    
    public function setUpCommander($commander) {
        $commander->onPerformCommand = callback($this, 'removePassiveLabel');
        return $commander;
    }
    
    public function getTraverser() {
        
        $traverser = new Bubo\Traversing\CommandingTraverser();
        
        $params = array('treeNodeId' => $this->treeNodeId, 'lang' => 'cs', 'searchAllTimeZones' => TRUE);
        
//        dump($params, $this->getPageManager()->getPage($params));
//        die();
        
        return $traverser
                    ->setRoots($this->getPageManager()->getPage($params));
      
    }

    /**
     * Odstraní pasivní štítek.
     * 
     * @param type $page
     * @param type $level
     * @param type $data 
     */
    public function removePassiveLabel($page, $level, $data) {
        
        //dump($this->label, $page->_labels);
        
        $page->removePassiveLabel($this->label['label_id']);
        
        $page->refresh();
        
        
    }

}
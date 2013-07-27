<?php

namespace Bubo\Traversing;

use Nette;

/**
 * Page Traverser
 * 
 * @author Marek Juras
 */
abstract class Traverser extends Nette\Object {
    
    
    private $defaults = array(
                            'acceptedStates'    =>  array('published')
    );
    
    
    // parameters for descendant traversing

    
    
    /**
     * Array of states to traverse
     * 
     * default: published
     * usage: $this->setAcceptedStates(array('published', 'draft'))
     * @var type 
     */
    private $acceptedStates;
    
    /**
     * Enables ghost searching
     * 
     * default: FALSE
     * usage: $this->searchGhosts()
     * @var type 
     */
    private $searchGhosts;
    
    
    /**
     * Searching all time zones (present and future)
     * 
     * default: FALSE
     * usage: $this->searchAllTimeZones()
     * @var type 
     */
    private $searchAllTimeZones;
    
    // array of treeNodeIds
    // roots for traversing
    private $roots;
    
    private $entityConfig;
    
    private $groupName;
    
    
    public function __construct() {
        $this->searchGhosts = FALSE;
        $this->searchAllTimeZones = FALSE;
        return $this;
    }
    
    
    public function searchGhosts() {
        $this->searchGhosts = TRUE;
        return $this;
    }
    
    public function searchAllTimeZones() {
        $this->searchAllTimeZones = TRUE;
        return $this;
    }
    
    public function canSearchGhosts() {
        return $this->searchGhosts;
    }
    
    public function canSearchAllTimeZones() {
        return $this->searchAllTimeZones;
    }
    
    public function getGroupName() {
        return $this->groupName;
    }
    
    public function getEntityConfig() {
        return $this->entityConfig;
    }
    
    public function configureLoading($entityConfig, $groupName = NULL) {
        $this->entityConfig = $entityConfig;
        $this->groupName = $groupName;
        return $this;
    }
    
    public function setAcceptedStates($acceptedStates) {
        $this->acceptedStates = $acceptedStates;
        return $this;
    }
    
    public function getAcceptedStates() {
        if ($this->acceptedStates === NULL) {
            $this->acceptedStates = $this->defaults['acceptedStates'];
        }
        
        return $this->acceptedStates;
    }
    
    public function setRoots($roots) {
        if (!is_array($roots)) {
            $this->roots[] = $roots;
        } else {
            $this->roots = $roots;
        }
        
        return $this;
    }
    
    
    public function getRoots() {
        return $this->roots;
    }
    

    public function traverse() {        
    }
    
}
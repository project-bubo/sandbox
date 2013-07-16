<?php

namespace FrontModule\Components\Traversers;

use Netstars;

/**
 * Label traverser
 * 
 * - for creating label menus
 * 
 * @author Marek Juras
 */
final class ShopTraverser extends Netstars\Traversing\RenderingTraverser {
    
    private $label;
    
    private $lastRegion;
    
    
    public function setLabel($nameName, $label) {
        
        if (empty($label)) {
            throw new \Nette\InvalidStateException("Label '$nameName' was not found");
        }
        
        $this->label = $label;
        return $this;
    }
    
    public function getLabel() {
        return $this->label;
    }
    
    public function traverse() {
        // roots are pages labeled by active labels
        $roots = $this->getRoots();
        
        $topLevelContainer = $this->getRenderer()->createTopLevelContainer();
        
//        dump($this->label);
//        die();
        
        $descendantsParams = array(
                                'lang'                  =>  $this->getLang(),
                                'labelId'               =>  $this->label['label_id'],
                                'entityConfig'          =>  $this->getEntityConfig(),
                                'groupName'             =>  $this->getGroupName(),
                                'searchGhosts'          =>  $this->canSearchGhosts(),
                                'states'                =>  $this->getAcceptedStates(),
                                'searchAllTimeZones'    =>  $this->canSearchAllTimeZones(),
                                'orderDirection'        =>  $this->getOrderDirection(),
                                'limit'                 =>  $this->getLimit(),
                                'sortingCallback'        =>  $this->getSortingCallback()
                                
        );
        
        
        
        if (!empty($roots)) {
        
            $rel = 0;
            
            foreach ($roots as $root) {
    
                $descendants = $root->getDescendants($descendantsParams);

                if (count($descendants) > 0) {
                    $horizontalLevel = 1;
                    $regionElement = NULL;
                    foreach ($descendants as $descendant) {  
                        
                        $firstInRegion = FALSE;
                        if ($descendant->_ext_region['key'] != $this->lastRegion) {
                            $firstInRegion = TRUE;
                            $this->lastRegion = $descendant->_ext_region['key'];
                        }

                        if ($firstInRegion) {                                        
                            $element = \Nette\Utils\Html::el('div');
                            
                            $element->class = 'region r'.$rel++;
                            $regionElement = $topLevelContainer->add($element);
                        }

                        $this->getRenderer()->renderMenuItem($descendant, $descendantsParams, $element, 1, $horizontalLevel++, FALSE, $firstInRegion);

                    }
                }
                
            }
        }
        return $topLevelContainer;
    }

 
    
    
}
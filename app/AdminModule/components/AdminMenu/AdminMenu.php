<?php

namespace AdminModule\Components;

class AdminMenu extends \Nette\Application\UI\Control {   
    
    public $sections = array();

    public function registerSection($sectionId, $index = NULL) {
        
        if ($sectionId !== NULL) {
        
            if (in_array($sectionId, $this->sections)) {
                throw new \Nette\InvalidStateException("Section $sectionId is already registered in admin menu");
            }        

            if ($index === NULL) {
                $this->sections[] = $sectionId;
            } else {
                if (!isset($this->sections[$index])) {
                    $this->sections[$index] = $sectionId;
                } else {
                    throw new \Nette\InvalidStateException("Index $index in admin menu is already reserved");
                }           
            }
        
        }
    }
    
    public function registerSectionAsObject($section, $index = NULL) {
        if ($section !== NULL) {
        
            if (in_array($section->name, $this->sections)) {
                throw new \Nette\InvalidStateException("Section $section->name is already registered in admin menu");
            }        

            if ($index === NULL) {
                $this->sections[] = $section->setParent($this);
            } else {
                if (!isset($this->sections[$index])) {
                    $this->sections[$index] = $section->setParent($this);
                } else {
                    throw new \Nette\InvalidStateException("Index $index in admin menu is already reserved");
                }           
            }
        
        }
    }
    
  
    public function createComponent($name) {
        
        if (preg_match('([a-zA-Z0-9]+Section)', $name)) {
            // detect section            
            $classname = "AdminMenu\\Sections\\" . ucfirst($name);
            if (class_exists($classname)) {
                $section = new $classname($this, $name);
                //$section->setTranslator($this->presenter->context->translator);
                return $section;
            }
        } 
        
        return parent::createComponent($name);
        
    }
    

    
    public function render() {
        
        
        $template = $this->template;
        $template->setFile(dirname(__FILE__) . '/adminMenu.latte');
        $template->setTranslator($this->getPresenter()->context->translator);
        
        $template->sections = $this->sections;
        
        $template->render();
        
    }

    
}

<?php

namespace AdminModule\Forms;

use Nette\Application\UI\Form,
    Nette\Templating\Helpers,
    Nette\Utils\Html,
    Nette\Image;

class MediaFileSettingForm extends BaseForm {

    private $media;
    
    public function __construct($parent, $name) {
        parent::__construct($parent, $name);
        $this->media = $this->lookup('Bubo\\Media');
        
        $insertionMethods = $this->getInsertionMethods();
        $this->addSelect('insertionMethod', 'Vložit jako', $insertionMethods);
        
        $subForms = $this->getSubformsData();
        
        foreach ($subForms as $key => $subForm) {
            
            if ($subForm !== NULL) {
                
                $_subForm[$key] = $this->addContainer('subform_'.$key);
                
                foreach ($subForm as $subFormComponentName => $subFormComponent) {
                    
                    switch ($subFormComponent['control']) {
                        case 'text':
                                $_subForm[$key]->addText($subFormComponentName, $subFormComponent['title']);
                                break;
                        case 'checkbox':
                                $_subForm[$key]->addCheckbox($subFormComponentName, $subFormComponent['title']);
                                if (isset($subFormComponent['default'])) {
                                    $_subForm[$key][$subFormComponentName]->setDefaultValue($subFormComponent['default']);
                                }
                                break;
                    }
                    
                }
                
                
            }
            
        }
        
        
        $this->addHidden('fileId', $this->parent->id);
        
        $this->addSubmit('send', 'Uložit');

        $this->onSuccess[] = array($this, 'formSubmited');
        $this->getElementPrototype()->class = 'ajax media-image-setting-form';
        
        
        
        if ($this->media->getParam('formValues') !== NULL) {
            $defaults = json_decode($this->media->getParam('formValues'), TRUE);
            
            if ($defaults) {
                $this->setDefaults($defaults);
            }
        }
        
        //$this['send']->getControlPrototype()->class = "submit";
    }
    
    
    public function getInsertionMethods() {
        $config = $this->media->getConfig();
        $insertionMethods = $config['insertionMethods']['file'];
        
        return array_map(function($control) {
                                return $control['title'];
        }, $insertionMethods);
        
    }
    
    public function getSubformsData() {
        $config = $this->media->getConfig();
        $insertionMethods = $config['insertionMethods']['file'];
        
        return array_map(function($control) {
                                return isset($control['subform']) ? $control['subform'] : NULL;
        }, $insertionMethods);
    }
    
    
    public function formSubmited($form) {
        $formValues = $form->getValues();
        
//        dump($formValues);
//        die();
        
        $fileId = $formValues['fileId'];
        
        $_file = $this->presenter->mediaManagerService->getFile($fileId);
        
        $file = $this->presenter->mediaManagerService->loadFile($fileId);
        
        $paths = $file->getPaths();        
        $section = $this->presenter->mediaManagerService->getFileSection($fileId);
       
        $el = NULL;        
        $restoreUrl = $this->presenter->mediaManagerService->getFileRestoreUrl($_file['folder_id'], $fileId, $section, FALSE, $formValues);
        
        switch ($formValues['insertionMethod']) {
            case 0: // icon linking to file (default)
                $a = Html::el('a');                
                $linkAttribs = array(
                                    'href'              =>  $paths['urls'][0],
                                    'data-restoreUrl'   =>  $restoreUrl
                );                
                $a->addAttributes($linkAttribs);
                
                $imageAttributes = array(
                                    'src'               =>  $this->presenter->mediaManagerService->getIconBasePath() . '/default.png',
                );

                $img = Html::el('img');
                $img->addAttributes($imageAttributes);
                
                $a->add($img);
                $el = $a;                
                break;
            case 1: // text linking to file
                $text = $formValues['subform_1']['text'];
                $size = '';

                // create link
                $attribs = array(
                            'href'              =>  $paths['urls'][0],
                );
                $el = Html::el('a');
                $el->addAttributes($attribs);

                if ($formValues['subform_1']['appendSize']) {
                    $size = '(' . Helpers::bytes($_file['size']) . ')';
                }
                
                $imageAttribs = array(
                                    'data-restoreUrl'   =>  $restoreUrl
                );

                $image = $el->create('span');
                $image->addAttributes($imageAttribs);
                $image->setText($text);
                break;

        }
        
        $tinyArgs = array(
                        'html'   => $el->__toString() 
        );
        
        $this->media->sendTinyMceCommand($tinyArgs);
        
    }
}
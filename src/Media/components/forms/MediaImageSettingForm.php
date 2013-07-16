<?php

namespace AdminModule\Forms;

use Nette\Application\UI\Form,
    Nette\Utils\Html,
    Nette\Image;

class MediaImageSettingForm extends BaseForm {

    private $media;
    
    public function __construct($parent, $name) {
        parent::__construct($parent, $name);
        $this->media = $this->lookup('Bubo\\Media');
        
        $dimensions = $this->addContainer('dimensions');
//        $fileDirPath = $this->presenter->mediaManagerService->getOriginalFileDirPath($this->parent->id);
//        $filePath = $this->presenter->mediaManagerService->getBaseDir() . '/' . $fileDirPath;
//        $im = Image::fromFile($filePath);
        
        $file = $this->presenter->mediaManagerService->loadFile($this->parent->id);
        $paths = $file->getPaths();
        $im = Image::fromFile($paths['dirPaths'][0]);
        
        $dimensions->addText('width')->setDefaultValue($im->getWidth());
        $dimensions->addText('height')->setDefaultValue($im->getHeight());        
      
        
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
        
        $dimensions['width']->getControlPrototype()->class = 'itemWidth';
        $dimensions['height']->getControlPrototype()->class = 'itemHeight';
        
        $this->addSubmit('send', 'Uložit');
        
//        $this->addHidden('editor_id', $editorId);
//        $this->addHidden('parent', $fid);
//        $this->addHidden('id', false);
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
        $insertionMethods = $config['insertionMethods']['image'];
        
        return array_map(function($control) {
                                return $control['title'];
        }, $insertionMethods);
        
    }
    
    public function getSubformsData() {
        $config = $this->media->getConfig();
        $insertionMethods = $config['insertionMethods']['image'];
        
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
        
        $params = array(
                    'width'     => $formValues['dimensions']['width'],
                    'height'    => $formValues['dimensions']['height'],
                    'method'    => 'SHRINK_ONLY'
        );
        
        $modChunk = $this->presenter->mediaManagerService->createModChunk($params);        
        $file = $this->presenter->mediaManagerService->loadFile($fileId, $modChunk);
        $paths = $file->getPaths();        
        $section = $this->presenter->mediaManagerService->getFileSection($fileId);
        

        $el = NULL;        
        $restoreUrl = $this->presenter->mediaManagerService->getFileRestoreUrl($_file['folder_id'], $fileId, $section, FALSE, $formValues);
        
        switch ($formValues['insertionMethod']) {
            case 0: // plain image
                $imageAttributes = array(
                                    'src'               =>  $paths['urls'][1],
                                    'data-restoreUrl'   =>  $restoreUrl
                );

                $el = Html::el('img');
                $el->addAttributes($imageAttributes);
                break;
            case 1: // link to bigger image
                $attribs = array(
                            'href'              =>  $paths['urls'][0]
                );

                if ($formValues['subform_1']['colorbox']) {
                    $attribs['class'] = 'colorbox';
                }
                
                $el = Html::el('a');
                $el->addAttributes($attribs);

                $imageAttribs = array(
                                    'data-restoreUrl'   =>  $restoreUrl,
                                    'src'               =>  $paths['urls'][1]
                );

                $image = $el->create('img');
                $image->addAttributes($imageAttribs);
                break;
            case 2: // text linking to image
                $attribs = array(
                            'href'              =>  $paths['urls'][0],
                );

                if ($formValues['subform_2']['colorbox']) {
                    $attribs['class'] = 'colorbox';
                }
                
                $el = Html::el('a');
                $el->addAttributes($attribs);

                $imageAttribs = array(
                                    'data-restoreUrl'   =>  $restoreUrl
                );

                $image = $el->create('span');
                $image->addAttributes($imageAttribs);
                $image->setText($formValues['subform_2']['text']);
                break;

        }
        
        $tinyArgs = array(
                        'html'   => $el->__toString() 
        );
        
        $this->media->sendTinyMceCommand($tinyArgs);
        
    }
}
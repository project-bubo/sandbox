<?php

namespace BuboApp\AdminModule\Forms;

use Nette\Application\UI\Form,
    Nette\Environment;

class LabelForm extends BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
        
            
        /* PREPARE DATA */
        
        //$pageId = $this->getPresenter()->getParam('id');
        
        
//        dump($labelId);
        
        
        //$this->addHidden('label_id');
        
        $this->addGroup('Jméno a typ');
        $this->addText('name','Jméno štítku')
                ->addRule(Form::FILLED, 'Zadejte jméno štítku.');
        
        $this->addHidden('color', '0000ff');
        
        $this->addHidden('module', $this->presenter->pageManagerService->getCurrentModule());
        
        $selectData = array(
                        NULL    =>  'Single',
                        '1'     =>  '1. úroveň',
                        '2'     =>  '2. úroveň',
                        '3'     =>  '3. úroveň',
                        '4'     =>  '4. úroveň',
                        '0'     =>  'Celý podstrom'
        );
        
        $this->addSelect('depth_of_recursion', 'Typ', $selectData);
        $this->addGroup('Konfigurace');
        
        $this->addCheckBox('create_button', 'Vytvářet tlačítko');
//                                  ->addConditionOn($this['is_singleton'], Form::EQUAL, NULL)
//                                  ->addRule(Form::EQUAL, 'Tlačítko lze vytvořit pouze u jedináčka', FALSE);
//                                ->addConditionOn($this['depth_of_recursion'], ~Form::EQUAL, NULL)
                                //->addRule(Form::EQUAL, 'Rekurzivní štítek nemůže být jedináček', FALSE);
        
        $this->addCheckBox('show_button', 'Zobrazit tlačítko');
        
        // load templates -> get only existing and with nice names (if possible)
        $_templates = $this->presenter->projectManagerService->getListOfTemplates(TRUE); 
//        dump($_templates);
//        die();
//        $templateConfig = $this->presenter->configLoaderService->loadLayoutConfig();        
//        $res = \Nette\Utils\Arrays::mergeTree($templateConfig['layouts'], $_templates['head']);
//        $templates['Normální stránky'] = array_intersect_key($res, $_templates['head']);
//        if (isset($_templates['headless'])) {
//            $res = \Nette\Utils\Arrays::mergeTree($templateConfig['layouts'], $_templates['headless']);
//            $templates['Stránky bez url'] = array_intersect_key($res, $_templates['headless']);
//        }
        
        
        $templateConfig = $this->presenter->configLoaderService->loadLayoutConfig(); 
        $res = \Nette\Utils\Arrays::mergeTree($templateConfig['layouts'], $_templates);
        $templates = array_intersect_key($res, $_templates);
        
        $this->addSelect('layout', 'Šablona', $templates);
        
        $entities['Stránky'] = $this->presenter->pageManagerService->getAllPageEntities();
        $entities['Útržky'] = $this->presenter->pageManagerService->getAllScrapEntities();

        $this->addSelect('entity', 'Entita', $entities);
        
        $this->addCheckbox('lock_layout', 'Uzamknout šablonu');
        
//        $this->addCheckBox('is_resource','Chráněný zdroj');
        //$this->addCheckBox('is_singleton', 'Jedináček');
        
        $this->addGroup('Jazyky');
        
        //$this->addSelect('is_global', 'Viditelnost', $visibilityData);
        
        
        
        
        $langs = $this->addContainer('langs');
        
        foreach ($this->presenter->langManagerService->getLangs() as $langCode => $langTitle) {
            $c = \Nette\Utils\Html::el();
            $el = $c->create('img');
            $el->src = $this->presenter->baseUri . '/images/flags/'.strtolower($langCode).'.png';
            $c->add(\Nette\Utils\Html::el('span')->setHtml('&nbsp;'.$langTitle));
            $langs->addCheckbox(strtolower($langCode), $c);
        }
        
        
        $allLangs = array_keys($this->presenter->langManagerService->getLangs());
        $this['langs'][strtolower(reset($allLangs))]
                    ->addRule(array($this, 'atLeastOneCheckBoxChecked'), 'Alespoň jeden jazyk musí být vybrán', $this['langs']);
        
        $this['name']->getControlPrototype()->class[] = 'fleft';
        $this['color']->getControlPrototype()->class[] = 'color-listener';
        
        
        switch ($this->getPresenter()->getAction()){
            case 'addLabel':                
                $this->setCurrentGroup(NULL);
                $this->addSubmit('send', 'Vytvořit');
                
                $this->onSuccess[] = array($this, 'addformSubmited');
                break;
            case 'editLabel': // edit
         
                $labelId = $this->presenter->labelId;

                $this->setCurrentGroup(NULL);
                $this->addSubmit('send', 'Uložit');
                $this->addHidden('label_id', $labelId);
                
                
                //$this['is_global']->setDisabled();
                
                $defaults = $this->presenter->labelModel->getLabel($labelId);
                if ($defaults['langs'] === NULL) $defaults['langs'] = array();  
                
                $this->addSubmit('manage_extensions', 'Rozšíření...')->setValidationScope(NULL);
                
                $this->addSubmit('sort', 'Třídění...')->setValidationScope(NULL);
                $this->addSubmit('entityParams', 'Parametry entity...')->setValidationScope(NULL);
                $this->addSubmit('delete', 'Smazat štítek');
                $this->addSubmit('createPage', 'Vytvořit stránku');
                $this->onSuccess[] = array($this, 'editFormSubmited');                                
                $this->setDefaults($defaults);
                break;

        }
        
        $this->addSubmit('cancel', 'Cancel')->setValidationScope(NULL);
        
//        $this['send']->getControlPrototype()->class = "submit";
//        $this['cancel']->getControlPrototype()->class = "submit";
//        $this['sort']->getControlPrototype()->class = "submit";
    }


    
    
    public function addformSubmited($form) {
        
        if ($form['send']->isSubmittedBy()) {
        
            $values = $form->getValues();

//            dump($values);
//            die();
            
            
            try {
                // webalize name
                $values['nicename'] = \Nette\Utils\Strings::webalize($values['name']);

                $values['langs'] = serialize($this->presenter->pageModel->extractTrueValues($values['langs']));
                                
//                dump($values);
//                die();
                
                $res = $this->presenter->labelModel->createLabel($values);

                if ($res) {
                    $this->getPresenter()->flashMessage('Štítek byl vytvořen');
                } else {
                    $reason = 'Label creation failed';
                    $this->getPresenter()->flashMessage($reason,'error');
                }            

                $this->getPresenter()->redirect('Label:editLabel', array('labelId' => $res));
            } catch (AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        }
    }
    
    public function editFormSubmited($form) {
        $values = $form->getValues();
        $labelId = $this->presenter->labelId;
        //if ($form['sort']->isSubmittedBy()) {
            
//            dump($values);
//            die();
            
            //$this->presenter->redirect('Page:sortLabelPages', array('id' => $values['label_id']));
            
        //} else if ($form['send']->isSubmittedBy()) {
        
            //$model = $this->modelLoader->loadModel('PageModel');
        if ($form['send']->isSubmittedBy()) {
            try {
                $values['nicename'] = \Nette\Utils\Strings::webalize($values['name']);
                $values['langs'] = serialize($this->presenter->pageModel->extractTrueValues($values['langs']));
                $res = $this->presenter->labelModel->editLabel($values);

                if ($res) {
                    $this->getPresenter()->flashMessage('Štítek byl upraven');
                }else{
                    $reason = 'Źádné změny nebyly provedeny';
                    $this->getPresenter()->flashMessage($reason);
                }
                $this->getPresenter()->redirect('this');
            } catch (AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        } else if ($form['manage_extensions']->isSubmittedBy()) {
            $this->presenter->labelId = $labelId;
//            dump($labelId);
//            die();
            $this->presenter->redirect('Label:manageLabelExtensions', array('labelId' => $labelId));
        } else if ($form['delete']->isSubmittedBy()) {
            
            $this->presenter->labelModel->deleteLabel($labelId);
            $this->getPresenter()->flashMessage('Štítek byl smazán');
            $this->getPresenter()->redirect('Default:default');
        } else if ($form['sort']->isSubmittedBy()) {
            $this->presenter->labelId = $labelId;
            $this->presenter->redirect('Label:sort', array('labelId' => $labelId));
        } else if ($form['entityParams']->isSubmittedBy()) {
            $this->presenter->labelId = $labelId;
            $this->presenter->redirect('Label:entityParams', array('labelId' => $labelId));
         } else if ($form['createPage']->isSubmittedBy()) {
             // create page or scrap?
             // need to load entity config and determine based on createUrl flag
             
             $label = $this->presenter->pageManagerService->getLabel($labelId);
             $entity = $label['entity'];
             
             $entityConfig = $this->presenter->configLoaderService->loadEntityConfig($entity);
             
//             dump($label, $entity, $entityConfig);
//             die();
             
             $createUrl = isset($entityConfig['entityMeta']['createUrl']) ? $entityConfig['entityMeta']['createUrl'] : TRUE;
             
             if ($createUrl) {
                 $this->presenter->redirect('Page:addPage', array('labelId' => $labelId, 'entity' => $entity));
             } else {
                 $this->presenter->redirect('Page:addScrap', array('labelId' => $labelId, 'entity' => $entity));
             }
                 
             
         }
    }
    
}
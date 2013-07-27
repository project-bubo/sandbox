<?php

namespace AdminModule\Forms;

use Nette\Application\UI\Form,
    Nette\Environment;

class ChooseParamTagForm extends BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        


        $identifier = $this->presenter->getParam('identifier');
        $config = $this->presenter->pageManagerService->getExtPropertiesByIdentifier($identifier);
        
        $selectData = $config['tags'];

        $this->addSelect('tag', 'Vyberte tag', $selectData)
                            ->setPrompt(':: Vyberte tag ::')
                            ->setRequired();
    
        
        $this->addSubmit('send', 'Řadit atributy');

        $this->onSuccess[] = array($this, 'editFormSubmited');
        
        
        $treeNodeId = $this->presenter->getParam('id');
        $labelId = $this->presenter->getParam('labelId');
        $this->addHidden('label_id', $labelId);
        $this->addHidden('tree_node_id', $treeNodeId);
        $this->addHidden('identifier', $identifier);
       
    }


    public function editFormSubmited($form) {
        $formValues = $form->getValues();
//        dump($formValues);
//        die();
        
        
        $args = array(
                    'id'            =>  $formValues['tree_node_id'],
                    'identifier'    =>  $formValues['identifier'],
                    'tag'           =>  $formValues['tag']
        );
        
        $this->presenter->redirect('sortParams', $args);
        die();
        
        $extId = $formValues['ext_id'];

        unset($formValues['ext_id']);
        
        if ($form['send']->isSubmittedBy()) {
           
            $res = $this->presenter->labelModel->editExtension($formValues, $extId);

            if ($res) {
                $this->getPresenter()->flashMessage('Štítek byl upraven');
            }else{
                $reason = 'Źádné změny nebyly provedeny';
                $this->getPresenter()->flashMessage($reason);
            }
            $this->getPresenter()->redirect('manageLabelExtensions');
        } 
    }
    
}
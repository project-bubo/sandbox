<?php

namespace AdminModule\Forms;

use Nette\Application\UI\Form,
    Nette\Environment;

class ExtStructuredParamForm extends BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);


        // getLanguages
        $langs = $this->presenter->langManagerService->getLangs();



        $langForms = array();


        $parentData = array(
                        0   =>  'Nejvyšší úroveň'
        );


        $identifier = $this->presenter->getParam('identifier');
        $treeNodeId = $this->presenter->getParam('id');
//        $this->addHidden('tree_node_id', $this->presenter->getParam('id'));
//        $this->addHidden('identifier', $this->presenter->getParam('identifier'));

        // identifier and treeNodeId serve as clues to find all parent attribs

        // current ext_tree_node_id (is any) must be excluded
        //dump();
        $structuredParamsParentSelectData = $this->presenter->extModel->getStructuredParamsParentSelectData($identifier, $treeNodeId, $this->presenter->langManagerService->getDefaultLanguage(), $this->presenter->getParam('ext_tree_node_id'));

        //dump($structuredParamsParentSelectData);

        $this->addSelect('parent', 'Rodičovský parametr', $parentData + $structuredParamsParentSelectData);
        $this->addText('x_coord', 'X');
        $this->addText('y_coord', 'Y');
        $this->addTextArea('image', 'Obrázek')->getControlPrototype()->class = 'wysiwyg';
        //dump($langs);

        // language containers
        foreach ($langs as $langCode => $language) {

            // !! create language section
            $langForms[$langCode] = $this->addContainer($langCode);

            $langForms[$langCode]->addText('param_name', 'Jméno parametru');

            $langForms[$langCode]->addTextArea('text', 'Text')->getControlPrototype()->class = 'wysiwyg';

        }


        //dump($langForms);

        $labelId = $this->presenter->getParam('labelId');
        $this->addHidden('label_id', $this->presenter->getParam('labelId'));
        $this->addHidden('tree_node_id', $this->presenter->getParam('id'));
        $this->addHidden('identifier', $this->presenter->getParam('identifier'));



        switch ($this->getPresenter()->getAction()){
            case 'addStructuredParam':
                $this->addSubmit('send', 'Vytvořit');


                $this->onSuccess[] = array($this, 'addformSubmited');
                break;
            case 'editStructuredParam': // edit

                $this->addSubmit('send', 'Uložit');

                $extTreeNodeId = $this->presenter->getParam('ext_tree_node_id');
                $this->addHidden('ext_tree_node_id', $extTreeNodeId);

                $defaults = $this->presenter->extModel->getParam($extTreeNodeId, $this->presenter->tags);

//                dump($extTreeNodeId);

//                dump($defaults);
//                die();

                foreach ($langs as $langCode => $lang) {
                    if (isset($defaults[$langCode])) {
                        if (isset($defaults[$langCode]['param_value'])) {
//                            dump((array)\Bubo\Utils\MultiValues::unserialize($defaults[$langCode]['param_value']));
//                            die();
                            $defaults['parent'] = isset($defaults[$langCode]['parent']) ? $defaults[$langCode]['parent'] : NULL;
                            $defaults[$langCode] = (array)$defaults[$langCode] + (array)\Bubo\Utils\MultiValues::unserialize($defaults[$langCode]['param_value']);
                            $defaults['x_coord'] = isset($defaults[$langCode]['x_coord']) ? $defaults[$langCode]['x_coord'] : '';
                            $defaults['y_coord'] = isset($defaults[$langCode]['y_coord']) ? $defaults[$langCode]['y_coord'] : '';
                            $defaults['image'] = isset($defaults[$langCode]['image']) ? $defaults[$langCode]['image'] : '';
                        }
                    }
                }

//                dump($defaults);
//                die();

                $this->onSuccess[] = array($this, 'editFormSubmited');
                $this->setDefaults((array) $defaults);
                break;

        }

    }



    public function addformSubmited($form) {
        $langs = $this->presenter->langManagerService->getLangs();
        $formValues = $form->getValues();
//        dump($formValues);
//        die();

        if ($form['send']->isSubmittedBy()) {

            try {

                $res = $this->presenter->extModel->addStructuredParam($formValues, $langs);

                $this->getPresenter()->flashMessage('Rozšíření bylo vytvořeno');

                $this->getPresenter()->redirect('structuredParams');
            } catch (AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        }
    }

    public function editFormSubmited($form) {
        $langs = $this->presenter->langManagerService->getLangs();
        $formValues = $form->getValues();
//        dump($formValues);
//        die();
        $extTreeNodeId = $formValues['ext_tree_node_id'];
        unset($formValues['ext_tree_node_id']);

        if ($form['send']->isSubmittedBy()) {

            $res = $this->presenter->extModel->editStructuredParam($formValues, $extTreeNodeId, $langs);

            $this->getPresenter()->flashMessage('Parametr byl upraven');

            $this->getPresenter()->redirect('structuredParams');
        }
    }

}
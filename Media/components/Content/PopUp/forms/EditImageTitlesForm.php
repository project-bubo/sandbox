<?php

namespace Bubo\Media\Components\Content\PopUp;

use Utils;

/**
 * Edit image titles form
 */
class EditImageTitlesForm extends BaseForm
{

    /**
     * Constructor
     * @param Nette\Application\UI\Control $parent
     * @param string $name
     */
    public function __construct($parent, $name)
    {
        parent::__construct($parent, $name);
        $media = $this->lookup('Bubo\\Media');

        $langs = $this->presenter->langManagerService->getLangs();
        $config = $media->getConfig();
        $allTitles = $this->addContainer('titles');

        foreach ($langs as $code => $langTitle) {

            $langContainer = $allTitles->addContainer($code);

            foreach ($config['titles'] as $titleName => $title) {

                switch ($title['control']) {
                    case 'text':
                        $langContainer->addText($titleName, $title['title']);
                        break;
                    case 'textArea':
                        $langContainer->addTextArea($titleName, $title['title']);
                        break;
                }

            }

        }

        $file = $this->presenter->mediaManagerService->getFile($this->parent->id);

        $defaults = array('titles' => Utils\Multivalues::unserialize($file['ext']));

        if ($defaults['titles']) $this->setDefaults((array) $defaults);

        $this->addHidden('fileId', $this->parent->id);

        $this->addSubmit('send', 'Uložit');
        $this->onSuccess[] = array($this, 'formSubmited');


        $this->getElementPrototype()->class = 'ajax';

        $this['send']->getControlPrototype()->class = "submit";
    }

    /**
     * Process form
     * @param EditImageTitlesForm $form
     */
    public function formSubmited($form)
    {
        $formValues = $form->getValues();

        $media = $this->lookup('Bubo\\Media');

        $this->presenter->mediaManagerService->saveImageTitles($formValues['titles'], $formValues['fileId']);

        //$this->parent->view = NULL;
        $media->invalidateControl();
    }
}
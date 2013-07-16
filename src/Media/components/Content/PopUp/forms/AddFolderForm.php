<?php

namespace Bubo\Media\Components\Content\PopUp;

use Nette;

/**
 * Add folder popup form
 */
class AddFolderForm extends BaseForm
{

    /**
     * Constructor
     * @param Nette\Application\UI\Control $parent
     * @param string $name
     */
    public function __construct($parent, $name)
    {
        parent::__construct($parent, $name);

        $parentFolderId = $this->parent->getFolderId();
        $currentSection = $this->parent->getCurrentSection();

        $this->addText('folderName', 'Název složky')
                        ->setRequired('Zadejte název složky');

        $this->addSubmit('send', 'OK');

        $this->addHidden('parentFolderId', $parentFolderId);
        $this->addHidden('currentSection', $currentSection);

        $this->onSuccess[] = array($this, 'formSubmited');
    }

    /**
     * Process form
     * @param AddFolderForm $form
     */
    public function formSubmited($form)
    {
        $values = $form->getValues();

        try {
            $this->presenter->mediaManagerService->createFolder($values);
        } catch (Nette\InvalidStateException $ex) {
            $this->presenter->flashMessage($ex->getMessage(), 'error');
            $this->presenter->invalidateControl();
        }

        $p = $this->lookup('Bubo\\Media');
        $p['content']->view = NULL;
        $p->invalidateControl();
    }

}
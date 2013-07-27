<?php

namespace Bubo\Media\Components\Content\PopUp;

use Nette;

/**
 * Rename folder popup form
 */
class RenameFolderForm extends BaseForm
{

    /**
     * Constructor
     * @param Nette\Application\UI\Control $parent
     * @param string $name
     */
    public function __construct($parent, $name)
    {
        parent::__construct($parent, $name);

        $folderId = $this->parent->getId();

        $folder = $this->presenter->mediaManagerService->getFolder($folderId);

        $this->addText('folderName', 'Nový název složky')
                        ->setDefaultValue($folder['name'])
                        ->setRequired('Zadejte název složky');

        $this->addSubmit('send', 'OK');
        $this->addHidden('folderId', $folderId);
        $this->onSuccess[] = array($this, 'formSubmited');
    }

    /**
     * Process form
     * @param RenameFolderForm $form
     */
    public function formSubmited($form)
    {
        $values = $form->getValues();

        try {
            $folderName = $values['folderName'];
            $folderId = $values['folderId'];

            $this->presenter->mediaManagerService->renameFolder($folderName, $folderId);
        } catch (Nette\InvalidStateException $ex) {
            $this->presenter->flashMessage($ex->getMessage(), 'error');
            $this->presenter->invalidateControl();
        }

        $p = $this->lookup('Bubo\\Media');
        $p->invalidateControl();
    }

}
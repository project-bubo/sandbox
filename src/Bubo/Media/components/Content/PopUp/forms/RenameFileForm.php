<?php

namespace Bubo\Media\Components\Content\PopUp;

use Nette;

/**
 * Rename file popup form
 */
class RenameFileForm extends BaseForm
{

    /**
     * Constructor
     * @param Nette\Application\UI\Control $parent
     * @param string $name
     */
    public function __construct($parent, $name)
    {
        parent::__construct($parent, $name);

        $fileId = $this->parent->getId();
        $file = $this->presenter->mediaManagerService->getFile($fileId);
        $this->addText('fileName', 'Nový název souboru')
                        ->setDefaultValue($file['name'])
                        ->setRequired('Zadejte název souboru');

        $this->addSubmit('send', 'OK');

        $this->addHidden('fileId', $fileId);
        $this->onSuccess[] = array($this, 'formSubmited');
    }

    /**
     * Process form
     * @param RenameFileForm $form
     */
    public function formSubmited($form)
    {

        $values = $form->getValues();

        try {
            $fileName = $values['fileName'];
            $fileId = $values['fileId'];

            $this->presenter->mediaManagerService->renameFile($fileName, $fileId);
        } catch (Nette\InvalidStateException $ex) {
            $this->presenter->flashMessage($ex->getMessage(), 'error');
            $this->presenter->invalidateControl();
        }

        $p = $this->lookup('Bubo\\Media');
        $p->invalidateControl();
    }

}
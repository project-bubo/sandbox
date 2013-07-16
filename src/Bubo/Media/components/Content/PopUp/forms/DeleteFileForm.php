<?php

namespace Bubo\Media\Components\Content\PopUp;

/**
 * Delete file popup form
 */
class DeleteFileForm extends BaseForm
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
        $this->addSubmit('send', 'OK');

        $this->addHidden('fileId', $fileId);

        $this->onSuccess[] = array($this, 'formSubmited');
    }

    /**
     * Process form
     * @param DeleteFileForm $form
     */
    public function formSubmited($form)
    {

        $values = $form->getValues();

        $media = $this->lookup('Bubo\\Media');

        $this->presenter->mediaManagerService->deleteFile($values['fileId'], $media->getCurrentSection());
        $media->invalidateControl();
    }

}
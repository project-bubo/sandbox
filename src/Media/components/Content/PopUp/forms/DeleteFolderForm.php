<?php

namespace Bubo\Media\Components\Content\PopUp;

/**
 * Delete folder popup form
 */
class DeleteFolderForm extends BaseForm
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

        $folderId = $this->parent->getId();
        $this->addSubmit('send', 'OK');

        $this->addHidden('folderId', $folderId);
        $this->addHidden('section', $media->getCurrentSection());

        $this->onSuccess[] = array($this, 'formSubmited');
    }


    /**
     * Process form
     * @param DeleteFolderForm $form
     */
    public function formSubmited($form)
    {

        $values = $form->getValues();

        $this->presenter->mediaManagerService->deleteFolder($values['folderId'], $values['section']);

        $p = $this->lookup('Bubo\\Media');
        $p->invalidateControl();

    }

}
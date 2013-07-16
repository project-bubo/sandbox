<?php

namespace Bubo\Media\Components\Content\PopUp;

/**
 * Delete gallery popup form
 */
class DeleteGalleryForm extends BaseForm
{

    /**
     * Constructor
     * @param Nette\Application\UI\Control $parent
     * @param string $name
     */
    public function __construct($parent, $name)
    {
        parent::__construct($parent, $name);

        $galleryId = $this->parent->getId();
        $this->addSubmit('send', 'OK');

        $this->addHidden('galleryId', $galleryId);

        $this->onSuccess[] = array($this, 'formSubmited');
    }

    /**
     * Process form
     * @param DeleteGalleryForm $form
     */
    public function formSubmited($form)
    {
        $values = $form->getValues();

        $this->presenter->mediaManagerService->deleteGallery($values['galleryId']);

        $p = $this->lookup('Bubo\\Media');
        $p->invalidateControl();

    }

}
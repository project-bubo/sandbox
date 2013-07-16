<?php

namespace Bubo\Media\Components\Content\PopUp;

/**
 * Add gallery popup form
 */
class AddGalleryForm extends BaseForm
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

        $this->addText('folderName', 'Název galerie')
                        ->setRequired('Zadejte název galerie');

        $this->addSubmit('send', 'OK');

        $this->addHidden('parentFolderId', $parentFolderId);
        $this->addHidden('currentSection', $currentSection);

        $this->onSuccess[] = array($this, 'formSubmited');
    }

    /**
     * Process form
     * @param AddGalleryForm $form
     */
    public function formSubmited($form)
    {
        $values = $form->getValues();

        $this->presenter->mediaManagerService->createGallery($values);

        $p = $this->lookup('Bubo\\Media');
        $p->invalidateControl();
    }

}
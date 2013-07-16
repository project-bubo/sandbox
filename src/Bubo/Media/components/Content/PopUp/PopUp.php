<?php

namespace Bubo\Media\Components\Content;

use Bubo;

/**
 * Popup class
 */
class PopUp extends Bubo\Components\RegisteredControl
{

    /**
     * Template name
     * @var string
     */
    private $template;

    /**
     * Id of folder item the popup is binded to
     * @var int
     */
    private $id;

    /**
     * Text of the message in popup
     * @var string
     */
    private $message;

    /**
     * Generic factory for popup forms
     * @param string $name
     * @return mixed
     */
    public function createComponent($name)
    {

        if (preg_match('([a-zA-Z0-9]+Form)', $name)) {
            // detect section
            $classname = "Bubo\\Media\\Components\\Content\\PopUp\\" . ucfirst($name);
            if (class_exists($classname)) {
                $form = new $classname($this, $name);
                //$section->setTranslator($this->presenter->context->translator);
                return $form;
            }
        }

        return parent::createComponent($name);
    }

    /**
     * Returns folder id
     * @return int
     */
    public function getFolderId()
    {
        $media = $this->lookup('Bubo\\Media');
        return $media->getFolderId();
    }

    /**
     * Supplement method for retrieving current section
     * @return string
     */
    public function getCurrentSection()
    {
        $media = $this->lookup('Bubo\\Media');
        return $media->getCurrentSection();
    }

    /**
     * Sets ID
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Returns ID
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets message
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Returns message
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets template
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Renders the popup window based on selected template
     */
    public function render()
    {
        $template = $this->createNewTemplate(__DIR__ . '/templates/default.latte');

        switch ($this->template) {
            case 'confirmDeleteFolder':
                $template = $this->createNewTemplate(__DIR__ . '/templates/deleteFolder.latte');
                $template->title = 'Smazat složku?';
                $template->message = $this->message ?: 'Opravdu si přejete smazat tuto složku?';
                break;
            case 'renameFolder':
                $template = $this->createNewTemplate(__DIR__ . '/templates/renameFolder.latte');
                $template->title = 'Přejmenování složky';
                break;
            case 'confirmDeleteFile':
                $template = $this->createNewTemplate(__DIR__ . '/templates/deleteFile.latte');
                $template->title = 'Smazat soubor?';
                $template->message = $this->message ?: 'Opravdu si přejete smazat tento soubor?';
                break;
            case 'renameFile':
                $template = $this->createNewTemplate(__DIR__ . '/templates/renameFile.latte');
                $template->title = 'Přejmenování souboru';
                break;
            case 'renameGallery':
                $template = $this->createNewTemplate(__DIR__ . '/templates/renameGallery.latte');
                $template->title = 'Přejmenování galerie';
                break;
            case 'confirmDeleteGallery':
                $template = $this->createNewTemplate(__DIR__ . '/templates/deleteGallery.latte');
                $template->title = 'Smazat galerii?';
                $template->message = $this->message ?: 'Opravdu si přejete smazat tuto galerii?';
                break;
            case 'createGallery':
                $template = $this->createNewTemplate(__DIR__ . '/templates/createGallery.latte');
                $template->title = 'Vytvořit gallerii';
                break;
            case 'editTitles':
                $template = $this->createNewTemplate(__DIR__ . '/templates/editTitles.latte');
                $media = $this->lookup('Bubo\\Media');
                $langs = $this->presenter->langManagerService->getLangs();
                $config = $media->getConfig();

                $template->title = 'Popisek obrázku';
                $template->langs = $langs;
                $template->titleConfig = $config['titles'];

                break;
            case 'insertFile':
                $template = $this->createNewTemplate(__DIR__ . '/templates/insertFile.latte');
                $template->title = 'Generování náhledů';
                break;
            default:
                $template->title = 'Vytvořit novou složku';


        }

        $template->render();
     }

}
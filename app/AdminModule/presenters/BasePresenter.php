<?php

namespace BuboApp\AdminModule\Presenters;

use Bubo;

use Nette\Utils\Arrays;
use Nette\Security\User;

/**
 * Base presenter of admin module
 */
abstract class BasePresenter extends \BasePresenter
{
    /**
     * Base path
     * @var string
     */
    public $basePath;

    /**
     * List of activated admin sections
     * @var array
     */
    protected $adminSections = array(
        'labelSection',
        'virtualDriveSection',
    );

    /**
     * Startup
     */
    public function startup()
    {
        parent::startup();
        $this->basePath = $this->getBasePath();

        if ($this->name != 'Admin:Auth') {
            if (!$this->user->isLoggedIn()) {
                if ($this->user->getLogoutReason() === User::INACTIVITY) {
                    $this->flashMessage('Session timeout, you have been logged out');
                }

                $this->redirect('Auth:login', array(
                    'backlink' => $this->storeRequest()
                ));

            } else {
                if (!$this->user->isAllowed($this->name, $this->action)) {
                    $this->flashMessage('Access denied');
                    $this->redirect('Default:');
                }
            }
        }

        // configure native components
        $adminModuleControlMap = array(
            '~^pageMultiForm$~'  =>  'Components\\MultiForms', // REFACTOR !!
            '~^[[:alnum:]]+Form$~'  =>  'AdminModule\\Forms',
            '~^[[:alnum:]]+DataGrid$~'  =>  'AdminModule\\DataGrids',
            '~^[[:alnum:]]+ConfirmDialog$~'  =>  'AdminModule\\Dialogs',
            '~^[[:alnum:]]+Sorter$~' => 'AdminModule\\Sorters\\',
        );

        $this->nativeControlMap = Arrays::mergeTree($this->nativeControlMap, $adminModuleControlMap);
        $this->setupUiComponents();
    }


    /**
     * Logout user
     */
    public function handleLogout()
    {
        $this->user->logOut();
        $this->flashMessage('You were logged off.');
        $this->redirect('this');
    }


    /**
     * Setup UI components of admin dashboard
     */
    protected function setupUiComponents()
    {
        // admin menu sections
        $this->registerAdminMenuSections();
        // module switch
        $this->setupModuleSwitch();
    }

    /**
     * Returns base path
     * @return string
     */
    public function getBasePath()
    {
        return $this->getHttpRequest()->getUrl()->basePath;
    }

    /**
     * Registers admin menu sections
     */
    protected function registerAdminMenuSections()
    {
        foreach ($this->adminSections as $adminSection) {
            $this['adminMenu']->registerSection($adminSection);
        }
    }

    /**
     * Setups module switch by current module
     */
    protected function setupModuleSwitch()
    {
        $currentModule = $this->pageManagerService->getCurrentModule();
        $this['moduleSwitch']->getActualModule($currentModule);
    }

    public function createComponentAdminMenu($name)
    {
        return new Components\AdminMenu($this, $name);
    }

    public function createComponentSitemapIndexer($name)
    {
        return new Sitemap\SitemapIndexer($this, $name);
    }

    public function createComponentMedia($name)
    {
        return new Bubo\Media($this, $name);
    }

    public function beforeRender()
    {
        parent::beforeRender();
        // page manager id automatically injected into template
        $this->template->pageManager = $this->pageManagerService;
    }

//    /**
//     * Factory method for all
//     * - forms
//     * - datagrids
//     * - confirmdialogs
//     *
//     * @param type $name
//     * @return classname
//     */
//    public function createComponent($name)
//    {
//
//        if ($name == 'pageMultiForm') {
//            return new Components\MultiForms\PageMultiForm($this, $name);
//
//        } else if (preg_match('([a-zA-Z0-9]+Form)', $name)) {
//            // detect forms
//            $classname = "AdminModule\\Forms\\" . ucfirst($name);
//            if (class_exists($classname)) {
//                $form = new $classname($this, $name);
//                $form->setTranslator($this->context->translator);
//                return $form;
//            }
//        } else if (preg_match('([a-zA-Z0-9]+DataGrid)', $name)) {
//            // detect datagrids
//            $classname = "AdminModule\\DataGrids\\" . ucfirst($name);
//            if (class_exists($classname)) {
//                $datagrid = new $classname($this, $name);
//                $datagrid->setTranslator($this->context->translator);
//                return $datagrid;
//            }
//        } else if (preg_match('([a-zA-Z0-9]+ConfirmDialog)', $name)) {
//            // detect confrim dialogs
//            $classname = "AdminModule\\Dialogs\\" . ucfirst($name);
//            if (class_exists($classname)) {
//                $dialog = new $classname($this, $name);
//                //$dialog->setTranslator($this->context->translator);
//                return $dialog;
//            }
//        }  else if (preg_match('([a-zA-Z0-9]+Sorter)', $name)) {
//            // detect confrim dialogs
//            $classname = "AdminModule\\Sorters\\" . ucfirst($name);
//            if (class_exists($classname)) {
//                $sorter = new $classname($this, $name);
//                //$dialog->setTranslator($this->context->translator);
//                return $sorter;
//            }
//        }
//
//        return parent::createComponent($name);
//
//    }

    public function createComponentLanguageManager($name)
    {
        return new \Components\LanguageManager($this, $name);
    }

    public function createComponentModuleSwitch($name)
    {
        return new Components\ModuleSwitch($this, $name);
    }

    public function createComponentStructureManager($name)
    {
        return new Components\StructureManager($this, $name, $this->userId);
    }


}


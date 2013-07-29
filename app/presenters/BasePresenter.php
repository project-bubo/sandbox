<?php

/**
 * Base presenter for all application presenters.
 *
 * @property-read \Model\PageModel $pageModel
 * @property-read \Model\LabelModel $labelModel
 */
abstract class BasePresenter extends \Bubo\Application\UI\Presenter
{

    /** @persistent default language */
    public $lang;

    public $identity = NULL;
    public $userId = NULL;

    public $moduleName = '';

    public $plugins;

    public $domainName;

    public $showTime = FALSE;
    public $generateGraph = FALSE;

    public $baseUri;

    /** @var GettextTranslator\Gettext */
    protected $translator;


    /**
     * @param GettextTranslator\Gettext
     */
    public function injectTranslator(GettextTranslator\Gettext $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns "Admin" or "Front" or ...
     * @return type
     */
    private function detectModule()
    {
        $moduleName = '';

        $a = strrpos($this->name, ':');
        if ($a !== FALSE) {
            $moduleName = substr($this->name, 0, $a);
        }

        return $moduleName;
    }

    public function getFullLang() {
        return $this->lang ?: $this->langManagerService->getDefaultLanguage();
    }

    public function startup()
    {
        \Nette\Diagnostics\Debugger::timer(); // zapne stopky
        parent::startup();
        $this->moduleName = $this->detectModule();

        $this->domainName = 'http://'.$_SERVER['HTTP_HOST'];

        $this->addComponent(new \Bubo\Pages\CMSPage($this->context, 0, NULL, 'page'), 'pages');

        $baseUrl = rtrim($this->presenter->getHttpRequest()->getUrl()->getBaseUrl(), '/');
        $this->baseUri = preg_replace('#https?://[^/]+#A', '', $baseUrl);

        $resources = $this->context->resourceManager->getResources();

        if (isset($this->user->identity->data)) {
            //dump($this->user->identity->data['role']->getAcl());
        }

        $this->identity = $this->getUser()->getIdentity();
        $this->userId = $this->identity ? $this->identity->id : NULL;

        $message = 'Startup() of basePresenter took: '. sprintf('%01.1f ms', \Nette\Diagnostics\Debugger::timer() * 1000);

        if (!$this->isAjax() && $this->showTime) {
            dump($message);
        }
    }

    public function &__get($name)
    {
        if (preg_match('#([[:alnum:]]+Model)#', $name, $matches)) {
            $model = $this->context->modelLoader->loadModel(ucfirst($matches[1]));
            return $model;
        } else if (preg_match('#([[:alnum:]]+)Service#', $name, $matches)) {


            if ($this->context->hasService($matches[1])) {
                $service = $this->context->$matches[1];
                switch ($matches[1]) {
                    case 'pageManager':
                    case 'extManager':
                    case 'langManager':
                    case 'projectManager':
                    case 'mediaManager':
                        if (!$service->isPresenterSet()) {
                            $service->presenter = $this;
                        }
                        break;
                }
                return $service;
            } else {
                throw new Nette\MemberAccessException("Service with name '$matches[1]' does not exist");
            }
        }
        return parent::__get($name);
    }




    public function beforeRender()
    {
        \Nette\Diagnostics\Debugger::timer(); // zapne stopky
        parent::beforeRender();

//        dump($this->context->parameters);
//        die();
        $this->template->pageLoadingMethod = $this->pageManagerService->getPageLoadingMode();


//        // language detection
//        if (!isset($this->lang)) {
//            $this->lang = $this->getHttpRequest()->detectLanguage(array('cs', 'en'));
//            $this->canonicalize();
//        }
//
//        // lang priority: 1. from logged user, 2. from url, 3. default
//        $this->lang = $this->template->lang =
//                ($this->user->isLoggedIn() AND !empty($this->user->getIdentity()->lang)) ? $this->user->getIdentity()->lang : // is user logged? does he have "lang" defined? use it!
//                $this->getParam("lang") ? : // is parametr "lang" set in url? use it!
//                        $this->lang; // nothing above? use default
//        // translator activation
//
//        // if "cs" is default, we don't need that in url
//        if ($this->lang == "cs")
//            $this->lang = NULL;


        //dump($this->lang ?: $this->langManagerService->getDefaultLanguage());

        $this->context->translator->setLang($this->getFullLang());
        $this->template->setTranslator($this->context->translator);

        $this->template->registerHelper('timeAgoInWords', 'Bubo\Helpers\Helpers::timeAgoInWords');
        // session example
        /*
          $this->namespace = $this->context->session->getSection($this->namespace);
          $this->namespace->setExpiration("+6 hours");
          $this->namespace->valueName = "valueContent";
         */

        $this->template->viewName = $this->view;
        //$this->template->root = dirname(realpath(APP_DIR));
        //$this->template->path = "/www";
        $a = strrpos($this->name, ':');
        if ($a === FALSE) {
            $this->template->moduleName = '';
            $this->template->presenterName = $this->name;
        } else {
            $this->template->moduleName = substr($this->name, 0, $a + 1);
            $this->template->presenterName = substr($this->name, $a + 1);
        }


        // synchrnonize acl
//        $actualAcl = $this->context->resourceManager->getAcl();
//        $this->getModelAcl()->synchronize($actualAcl);


    }


//    public function __call($methodName, $args) {
//        if (preg_match('|.*getModel([a-zA-Z0-9]+).*|', $methodName, $mtch)) {
//            if (class_exists('Models\\' . $mtch[1] . 'Model')) {
//                return $this->context->modelLoader->loadModel($mtch[1] . 'Model');
//            }
//        }
//
//        return parent::__call($methodName, $args);
//
//    }


    public function getOrderedModuleNamespaces($module = NULL)
    {

    }

}

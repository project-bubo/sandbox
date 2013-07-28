<?php

namespace FrontModule;

abstract class BasePresenter extends \BasePresenter {

    public $url;

    public function createComponentGallery(){
        return new Components\Galleries\Gallery();
    }

    public function createComponentFile(){
        return new Components\Files\File();
    }

    public function startup() {
        parent::startup();
//        \PavelMaca\Captcha\CaptchaControl::register();
    }


    private function _getExistingClass($nsPrefixes, $cName) {
//        dump($nsPrefixes, $cName);
//        die();

        $class = NULL;

        foreach ($nsPrefixes as $nsPrefix) {
            $_className = $nsPrefix . $cName;

            if (class_exists($_className)) {
                $class = $_className;
                break;
            }
        }

        return $class;
    }

    // genericka tovarna
    public function createComponent($name) {


        //$templates = array();

        $chunks = explode('/', $this->pageManagerService->getCurrentModule());

        array_walk($chunks, function($val,$key) use(& $chunks) {
                                $chunks[$key] .= 'Module';
        });


        $nsPrefixes = array();

        for ($i = count($chunks); $i > 0; $i--) {
            $t = array_slice($chunks, 0, $i);
            $nsPrefixes[] = implode("\\", $t);
        }

        if (preg_match('([a-zA-Z0-9]+Form)', $name)) {
            $classname = $this->_getExistingClass($nsPrefixes, "\\Forms\\" . ucfirst($name));
            if (class_exists($classname)) {
                $control = new $classname($this, $name);
                return $control;
            }

        } else if (preg_match('([a-zA-Z0-9]+Menu)', $name)) {
            //dump($nsPrefixes);
            $classname = $this->_getExistingClass($nsPrefixes, "\\Components\\PageMenus\\" . ucfirst($name));
            //dump($classname);
            if (class_exists($classname)) {
                $control = new $classname($this, $name, $this->lang ?: $this->langManagerService->getDefaultLanguage());
                return $control;
            }
        } else if (preg_match('([a-zA-Z0-9]+Tabs)', $name)) {
            //$classname = $nsPrefix."\\Components\\PageTabs\\" . ucfirst($name);
            $classname = $this->_getExistingClass($nsPrefixes, "\\Components\\PageTabs\\" . ucfirst($name));
            if (class_exists($classname)) {
                $control = new $classname($this, $name, $this->lang ?: $this->langManagerService->getDefaultLanguage());
                return $control;
            }
        } else if (preg_match('([a-zA-Z0-9]+Gallery)', $name)) {

            //$classname = $nsPrefix."\\Components\\Galleries\\" . ucfirst($name);
            $classname = $this->_getExistingClass($nsPrefixes, "\\Components\\Galleries\\" . ucfirst($name));
            if (class_exists($classname)) {
                $control = new $classname();
                return $control;
            }

            $classname = "FrontModule\\Components\\Galleries\\" . ucfirst($name);
            if (class_exists($classname)) {

                $control = new $classname();
                return $control;
            }
        } else {

            // generic facotry for components with default constructor
            // public function __construct($parent, $name)

            $classname = $this->_getExistingClass($nsPrefixes, "\\Components\\" . ucfirst($name));

//            dump($classname);
//            die();

            if ($classname !== NULL) {
                $class = new \Nette\Reflection\ClassType($classname);
                $constructor = $class->getConstructor();
                $constructorParams = $constructor->getParameters();

                if (count($constructorParams) == 2 && $constructorParams[0]->name == 'parent' && $constructorParams[1]->name == 'name') {
                    $control = new $classname($this, $name);
                    return $control;
                }

            }

        }

        return parent::createComponent($name);

    }


}

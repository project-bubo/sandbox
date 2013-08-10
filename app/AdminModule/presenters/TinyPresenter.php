<?php
namespace BuboApp\AdminModule\Presenters;

/**
 * Description of TinyPresenter
 *
 */
class TinyPresenter  extends BasePresenter {

    /** @persistent */
    public $tree = array();

    public function startup(){
        parent::startup();
    }


    public function createComponentVirtualDrive($name){
        return new Components\VirtualDrive($this, $name);
    }


    public function actionThumbnail($fileId, $width, $height, $method = 'FIT'){
        $file = $this->presenter->context->virtualDrive->getFileInfo($fileId);
        $this->presenter->context->virtualDrive->setPathFromFileId($this->fileId);
        $this->presenter->context->virtualDrive->getImageThumbnail($file['filename'], $width, $height, $method);

    }

    public function renderPokus($cid = NULL, $mediaTrigger = NULL) {
        $this->template->cid = $cid;
    }

    public function actionGetPluginsTinyData() {
        $pluginsTinyData = array();
        $pluginsTinyData[] = array(
            'title' => 'plugin',
            'submenu' => array(
                            0 => array(
                                'title'   => 'Akce pluginu',
                                'command' => '{control Plugin}'
                            )
                        )
        );
        /*
        $plugins = $this->plugins;

        $pluginsTinyData = array();

        foreach($plugins as $plugin) {
            if ($plugin['instance']->isInstalled() && $plugin['instance']->hasTinyMenu()) {
                $pluginsTinyData[] = $plugin['instance']->getTinyMenu();
            }
        }
        */
        echo json_encode($pluginsTinyData);
        $this->terminate();
    }

}

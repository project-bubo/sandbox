<?php

namespace ContextMenu;

class StructureManagerContextMenu extends \BuboApp\AdminModule\Components\ContextMenu {

    public function setParams($params) {
        $this->treeNodeId = $params[0];
        if(isSet($params[1])) $this->menuId = $params[1];
        return $this;
    }

    public function render() {
        $template = parent::initTemplate(dirname(__FILE__) . '/structureManagerContextMenu.latte');

        $template->treeNodeId = $this->treeNodeId;

//        dump($template->treeNodeId);

//        $parentTreeNodeId = 1;

        $labels = $this->presenter->pageManagerService->getAllLabels($this->parent->getLanguage());

//        $assignedActiveLabels = array();
//
//        $passiveLabels = array();
//        $assignedPassiveLabels = array();

        if ($this->visible) {

            $params = array('treeNodeId' => $this->treeNodeId, 'lang' => $this->parent->getLanguage(), 'searchAllTimeZones'  =>  TRUE);
            $page = $this->presenter->pageManagerService->getPage($params);

            if (!empty($page)) {

                //$assignedActiveLabels = $page->labels['active'];


                //$passiveLabels = $page->labels['passive']['all'];
                //$assignedPassiveLabels = $page->labels['passive']['assigned'];

                $parentPage = $page->parent;

                // TODO: pokud $parentPage je NULL,
                // pak $parentTreeNodeId nastavit podle jazykove verze...1 je root of default language
//                $parentTreeNodeId = $parentPage->treeNodeId;

                $template->page = $page;


//        if ($this->presenter['clipboard']->containsKey($this->treeNodeId) && $this->presenter['clipboard']->isInCutMode()) {
//            $template->blockInsertSection = TRUE;
//        } else {
//            $template->blockInsertSection = FALSE;
//        }
                $template->blockInsertSection = FALSE;

//                $template->parentTreeNodeId = $parentTreeNodeId;

                $template->labels = $labels;
//                $template->assignedActiveLabels = $assignedActiveLabels;
//
//                $template->passiveLabels = $passiveLabels;
//                $template->assignedPassiveLabels = $assignedPassiveLabels;

                $template->labelAdminMode = $this->parent->sessionSection->labelMode == 'admin';

                $template->render();

            }
        }
    }

    public function handleShowHistory($treeNodeId) {
        $this->presenter->payload->redirect = $this->getPresenter()->link('Page:history', array('id' => $treeNodeId));
        $this->presenter->terminate();
    }

//    public function handleTogglePassiveLabel($labelId) {
//
//        $label = $this->getPresenter()->getModelLabel()->getLabel($labelId);
//
//        // assign or remove label?
//        // lets ask the page with $treeNodeId
//        $page = $this->presenter->context->pageManager->getPage($this->treeNodeId);
//        $assign = !$page->hasAssignedPassiveLabel($labelId);
//
//        $args = array(
//                    'treeNodeId'    =>  $this->treeNodeId,
//                    'label'         =>  $label,
//                    'assignLabel'   =>  $assign
//        );
//
//        $this->presenter->context->commandExecutor->executeCommand('togglePassiveLabelAtPage', $args);
//        $this->getPresenter()->getContext()->pageManager->reattachPages($this->getPresenter());
//
//        $flashMessage = "Pasivní štítek '$label->name' byl ";
//
//        if ($assign) {
//            $flashMessage .= 'přidán ke stránce';
//        } else {
//            $flashMessage .= 'odebrán ze stránky';
//        }
//
//        $this->_finalizeRequest($flashMessage);
//    }



    public function handleToggleActiveLabel($labelId) {

        $label = $this->presenter->labelModel->getLabel($labelId);

        // assign or remove label?
        // lets ask the page with $treeNodeId
        $params = array('treeNodeId' => $this->treeNodeId, 'lang' => $this->parent->getLanguage(), 'searchAllTimeZones'    =>  TRUE);
        // get page not ghost (ghost should not get there)
        $page = $this->presenter->pageManagerService->getPage($params);


        $assign = !$page->isActivelyLabelledBy($labelId);

        $this->presenter->commandExecutorService->toggleActiveLabelAtPageCommand($this->treeNodeId, $label, $assign, $this->parent->getLanguage());
        //$this->presenter->pageManagerService->reattachPages($this->presenter);

        $flashMessage = "Štítek '$label->name' byl ";

        if ($assign) {
            $flashMessage .= 'přidán ke stránce';
        } else {
            $flashMessage .= 'odebrán ze stránky';
        }

        $this->_finalizeRequest($flashMessage);
    }

    public function handleRemovePassiveLabel($labelId) {
        $label = $this->presenter->pageManagerService->getLabel($labelId);

        $this->presenter->commandExecutorService->removePassiveLabelAtPageCommand($this->treeNodeId, $label, $this->parent->getLanguage());

        $flashMessage = "P Štítek '".$label['name']."' byl odebrán ze stránky";
        $this->_finalizeRequest($flashMessage);
    }


    public function handleDelete($treeNodeId){
        $this->presenter->context->commandExecutor->executeCommand('moveTreeToTrash', array('treeNodeId' => $treeNodeId));
        $this->getPresenter()->getContext()->pageManager->reattachPages($this->getPresenter());

        $this->_finalizeRequest("Stránka přesunuta do koše");
    }




    private function _finalizeRequest($message) {
        $this->getPresenter()->flashMessage($message);
        $this->getPresenter()->invalidateControl('structureManager');
        $this->getPresenter()->invalidateControl('flashMessages');
    }

}

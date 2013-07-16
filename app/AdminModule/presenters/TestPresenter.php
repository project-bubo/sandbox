<?php

namespace AdminModule;

final class TestPresenter extends BasePresenter {

    public function createComponentTestForm(){
        $form = new \Nette\Application\UI\Form();
        $form->addText('pole','Nazev galerie');
        $form->addTextArea('text');
        $cnt = $form->addContainer('Container');
        $form->addMultipleFileUpload('upload','upload',99);
        $form->addSubmit('Odeslat');
        $form->onSuccess[] = array($this,'formSubmitted');
        return $form;        
    }
    
    public function startup(){
        parent::startup();
        
    }
        
    /**
     *
     * @param \Nette\Forms\Form $form 
     */
    public function formSubmitted($form){
        $formValues = $form->getValues();
        $this->presenter->context->virtualDrive->setPresenter($this->presenter);
        
        
        $this->presenter->context->virtualDrive->addGallery($formValues['upload'],$formValues['pole']);
        $this->redirect('this');
    }
    
    /**
    * @param \Components\VirtualDrive\Drive $vd  
    */
    public function renderTestDb(){
        
        /*$amount = 100;
        \Nette\Diagnostics\Debugger::timer();
        $this->getModelTest()->treeNodeRandomGeneration($amount);
        echo "Created in: ".\Nette\Diagnostics\Debugger::timer().' s<br>';flush();
        \Nette\Diagnostics\Debugger::timer();
        $this->getModelTest()->getAllElements();
        echo "Get all elements in: ".\Nette\Diagnostics\Debugger::timer().' s<br>';flush();
        \Nette\Diagnostics\Debugger::timer();
        $this->getModelTest()->getAllElementsOneByOne(20);
        echo "Get all elements one by one in: ".\Nette\Diagnostics\Debugger::timer().' s<br>';flush();
        die;*/
        /*

        \SimpleProfiler\Profiler::advancedTimer();
        $this->testModel->clearPagesAndLabels();
        \SimpleProfiler\Profiler::advancedTimer('Smazani stranek');

        \SimpleProfiler\Profiler::advancedTimer();
        $this->testModel->generatePages(5,3);
        \SimpleProfiler\Profiler::advancedTimer('Generovani stranek');
        
        \SimpleProfiler\Profiler::advancedTimer();
        $this->testModel->generateLabels(4);
        \SimpleProfiler\Profiler::advancedTimer('Generovani stitku');*/
        
die;
        
        $vd = $this->context->virtualDrive;
        $vd->setPresenter($this);
       
        $this->template->galleries = $this->virtualDriveService->getGalleriesByPageId(array(1,2));
        
    }

}

<?php

namespace AdminModule;


final class DrivePresenter extends SecuredPresenter {

    public function createComponentVirtualDrive($name){
        return new Components\VirtualDrive($this, $name);
    }
    
}

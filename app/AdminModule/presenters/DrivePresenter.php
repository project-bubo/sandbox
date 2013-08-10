<?php

namespace BuboApp\AdminModule;


final class DrivePresenter extends BasePresenter {

    public function createComponentVirtualDrive($name){
        return new Components\VirtualDrive($this, $name);
    }

}

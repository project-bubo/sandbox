<?php

namespace BuboApp\AdminModule\Presenters;


final class DrivePresenter extends BasePresenter {

    public function createComponentVirtualDrive($name){
        return new Components\VirtualDrive($this, $name);
    }

}

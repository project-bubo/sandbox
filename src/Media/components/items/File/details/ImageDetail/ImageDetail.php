<?php

namespace Bubo\Media\Components\Items\File\Details;

use Bubo;

final class ImageDetail extends AbstractFileDetail {   
    
    public function createComponentMediaImageSettingForm($name) {
        return new \AdminModule\Forms\MediaImageSettingForm($this, $name);
    }
    
    public function render() {
        $media = $this->lookup('Bubo\\Media');
        $content = $this->lookup('Bubo\\Media\\Components\\Content');
        
        $file = $this->presenter->mediaManagerService->loadFile($this->getId());
        $paths = $file->getPaths();
        
        $sizes = getimagesize($paths['dirPaths'][0]);
        
        $folderItem = $content->getFolderContentItem('files', $this->getId());
        
        $template = $this->createNewTemplate(__DIR__ . '/templates/'.$media->trigger.'.latte');
        $template->folderItem = $folderItem;
        $template->iconSrc = $this->presenter->mediaManagerService->getFileIconSrc($folderItem, 'detail');
        //$template->iconSrc = 
        
        $template->originalImage = $paths['urls'][0];        
        $template->dimensions = $sizes[0].'x'.$sizes[1];
        
        //$template->menu = $this->getMenu($folderItem);
        $template->render();
    }
    
}

<?php

namespace Services\VirtualDrive\Model;

final class DriveModel extends \Nette\Object {
        
    
    /**
     * @var \DibiConnection
     */
    private $connection;
        
    private $parent;
    
    public function __construct($connection, $parent) {
        $this->connection = $connection;
        $this->parent = $parent;
    }

    public function getFolderTree($fid){
        $tree = array();
        $r = true;
        while($r){
            $r = $this->getParentFolder($fid, FALSE);
            if($r){
                $tree[] = $r;
                $fid = $r['parent'];
            }
        }
        return array_reverse($tree);
    }
    
    public function getFolderPath($folderId){
        $tree = $this->getFolderTree($folderId);
        $ret = '';
        foreach($tree as $it){
            $ret .= '/'.$it['nicename'];
        }
        return $ret;
    }
    
    public function getFolders($parent){
        if($parent > 0){
            return $this->getFolderById($parent, TRUE);
        }else{
            $ret = array();
            $ret['folders'] = $this->connection->fetchAll("SELECT * FROM [:vd:folders] WHERE [parent]=%i",0);
            $ret['files'] = $this->connection->fetchAll("SELECT * FROM [:vd:files] WHERE [folder_id]=%i",0);
            return $ret;
        }
    }
    
    public function getFoldersByParent($fid){
        return $this->connection->fetch("SELECT * FROM [:vd:folders] WHERE [parent]=%i",$fid);
    }
    public function getParentFolder($fid, $onlyParentId = TRUE){
        if(!$onlyParentId){
            return $this->connection->fetch("SELECT * FROM [:vd:folders] WHERE [folder_id]=%i",$fid);
        }
        return $this->connection->fetchSingle("SELECT [parent] FROM [:vd:folders] WHERE [folder_id]=%i",$fid);
    }
    
    public function isFolderEmpty($fid){
        $ret = $this->connection->fetchSingle("SELECT count(*) FROM [:vd:folders] WHERE [parent]=%i",$fid);
        if($ret > 0){
            return false;
        }
        $ret = $this->connection->fetchSingle("SELECT count(*) FROM [:vd:files] WHERE [folder_id]=%i",$fid);
        if($ret > 0){
            return false;
        }
        return true;
    }
    
    public function getFolderId($path){
        $parts = explode("/",$path);
        $count = count($parts);
        $ret = 0;
        $parentId = 0;
        if($parts && $count > 0){
            foreach($parts as $part){
                $parentId = $this->connection->fetch("SELECT [folder_id] FROM [:vd:folders] WHERE [name]=%s AND [parent]=%i",$part, $parentId);
                
                if($parentId){
                    $ret = $parentId;
                }
            }
        }
        return $ret;
    }

    public function getFolderById($id, $loadContent = FALSE){
        $ret = $this->connection->fetch("SELECT * FROM [:vd:folders] WHERE [folder_id]=%i",$id);
        if(!$ret) return false;
        $ret->toArray();
        if($loadContent){
            $ret['folders'] = $this->connection->fetchAll("SELECT * FROM [:vd:folders] WHERE [parent]=%i",$id);
            $ret['files'] = $this->connection->fetchAll("SELECT * FROM [:vd:files] WHERE [folder_id]=%i",$id);
        }
        return $ret;
    }
    
    
    public function addFolder($name, $realname, $parent, $userId = 0, $force = false){
        $values = array(
            'name'=> $name,
            'nicename' => $realname,
            'parent' => $parent,
            'editor_id' => $userId ?: 0
        );
        return $this->connection->query("INSERT INTO [:vd:folders] ",$values);
    }
    
    public function editFolder($values, $id){
        return $this->connection->query("UPDATE [:vd:folders] SET ",$values," WHERE [folder_id]=%i",$id);
    }
    public function moveFolder($id, $dest){
        if($id == $dest) return false;
        $this->connection->query("UPDATE [:vd:folders] SET [parent]=%i WHERE [folder_id]=%i",$dest,$id);
        return $this->connection->query("UPDATE [:vd:folders] SET [has_child]=1 WHERE [folder_id]=%i",$dest);
    }

    
    public function addFile($file, $folderId = false, $galleryId = false, $ownerId = 0, $order = 0){
        $r = ((int) $this->connection->fetchSingle("SELECT MAX([file_id]) FROM [:vd:files]")) + 1;
        if(preg_match('|(.+)(\..+)$|',$file->name,$match)){
            $filename = \Nette\Utils\Strings::webalize($match[1]).'-'.$r.$match[2];
        }else{
            $filename = \Nette\Utils\Strings::webalize($file->name) .'-'. $r;
        }
        $value = array(
                'name' => $file->name,
                'filename' => $filename,
                'folder_id' => $folderId,
                'gallery_id' => $galleryId,
                'editor_id' => $ownerId?:0,
                'size' => $file->getSize(),
                'filetype' => $file->getContentType(),
                'image' => $file->isImage(),
                'sort' => $order
            /*'location' => */
                );
        $r = $this->connection->query("INSERT INTO [:vd:files] ",$value);
        if(!$r) return false;
        return $filename;
    }
    
    
    public function addGallery($name, $nicename, $owner = 0, $path = '', $config = NULL){
        $values = array(
            'name' => $name,
            'nicename' => $nicename,
            'editor_id' => $owner ?: 0,
            'path' => $path,
            'config' => $config
        );
        $this->connection->query("INSERT INTO [:core:galleries] ",$values);
        return $this->connection->getInsertId();
    }
    
    public function getGallery($id){
        return $this->connection->fetch("SELECT * FROM [:core:galleries] WHERE [gallery_id]=%i ",$id);
    }
    
    public function getGalleries($ids){
        if(count($ids) > 0){
            return $this->connection->fetchAll("SELECT * FROM [:core:galleries] WHERE [gallery_id] IN %in",$ids);
        }
        return $this->connection->fetchAll("SELECT * FROM [:core:galleries]");
    }
    
    public function getFilesByPageId($ids, $type = 'file'){
        if(!$ids) return false;
        return $this->connection->query("SELECT F.*,FL.*
                                            FROM 
                                                [:vd:pages_files] FL 
                                                LEFT JOIN [:vd:files] F ON F.[file_id] = FL.[id] 
                                            WHERE 
                                                FL.[storage] = %s AND FL.[page_id] IN %l ORDER BY F.[sort]",$type,(array) $ids)->fetchAssoc('page_id,property_name,#');
    }
    public function getGalleriesByPageId($ids){
        if(!$ids) return false;
        return $this->connection->query("SELECT F.*,FL.*,G.[path],G.[config],G.[nicename] as [gallery_url]
                                            FROM 
                                                [:vd:pages_files] FL
                                                LEFT JOIN [:core:galleries] G ON G.`gallery_id` = FL.`id`
                                                LEFT JOIN [:vd:files] F USING([gallery_id])
                                            WHERE 
                                                FL.[storage] = 'gallery' AND FL.[page_id] IN %l",(array) $ids)->fetchAssoc('page_id,property_name,#');
    }
    
    public function deleteFilesByGalleryId($ids){
        return $this->connection->query("DELETE FROM [:vd:files] WHERE [gallery_id] IN %l",(array) $ids);
    }
    
    public function getGalleryItems($id){
        if($id == 0) return false;
        return $this->connection->fetchAll("SELECT * FROM [:vd:files] WHERE [gallery_id]=%i ORDER BY [sort]",$id);
    }
    public function getGalleryThumbnail($id){
        if($id == 0) return false;
        return $this->connection->fetchSingle("SELECT filename FROM [:vd:files] WHERE [gallery_id]=%i ORDER BY [sort] LIMIT 1",$id);
    }
    
    
    public function sortGallery($sorted){
        foreach($sorted as $pos => $id){
            $this->connection->query("UPDATE [:vd:files] SET [sort] = %i WHERE [file_id]=%i",$pos, $id);
        }
    }
    
    public function getVirtualGallery(){
        $id = $this->connection->fetchSingle("SELECT [folder_id] FROM [:vd:folders] WHERE [name] LIKE 'Galerie'");
        if(!$id){
            $id = $this->addFolder(array('name'=>'Galerie','parent'=>0), true);
        }
        return $id;
    }
    
    public function moveFile($fileId, $id, $fromFileId = false){
        if($fromFileId){
            $folderId = $this->connection->fetchSingle("SELECT [folder_id] FROM [:vd:pages_files] WHERE [file_id]=%i", $fromFileId);
            if($folderId){
                return $this->connection->query("UPDATE [:vd:pages_files] SET [folder_id]=%i WHERE [file_id]=%i", $folderId, $fileId);
            }else{
                throw new \Exception('Destination folder not found.');
            }
        }
        return $this->connection->query("UPDATE [:vd:pages_files] SET [folder_id]=%i WHERE [file_id]=%i", $id, $fileId);
    }
    
    public function getFileInfo($id){
        return $this->connection->fetch("SELECT * FROM [:vd:files] WHERE [file_id] = %i",$id);
    }
    
   
    
    public function deleteFile($id){
        $this->connection->query("DELETE FROM [:vd:pages_files] WHERE [storage] = 'file' AND [id]=%i", $id);
        return $this->connection->query("DELETE FROM [:vd:files] WHERE [file_id]=%i", $id);
    }
    


    private function _recursiveDelete($parent){
        $folders = $this->connection->fetchAll("SELECT [folder_id] FROM [:vd:folders] WHERE [parent]=%i",$parent);
        if($folders && count($folders) > 0){
            $this->connection->query('DELETE FROM [:vd:folders] WHERE [folder_id] IN %in',$folders);
            $this->connection->query('DELETE FROM [:vd:files] WHERE [folder_id] IN %in',$folders);
        }
        return false;
    }
    
    public function deleteFolder($folder){
        $r = $this->_recursiveDelete($folder['folder_id']);
        return $r && $this->connection->query("DELETE FROM [:vd:folders] WHERE [folder_id]=%i", $folder['folder_id']);
    }
    
    public function deleteGallery($id){
        $this->connection->query("DELETE FROM [:vd:pages_files] WHERE [storage] = 'gallery' AND [id] IN %l", $id);
        return $this->connection->query("DELETE FROM [:core:galleries] WHERE [gallery_id] IN %l", (array) $id);
    }

    
    public function attachFileToPage($storage, $pageId, $id, $propertyName){
        $dbData = array(
                    'storage'        =>  $storage,
                    'page_id'        =>  $pageId,
                    'id'             =>  $id,
                    'property_name'  =>  $propertyName
        );
        
        return $this->connection->query("INSERT INTO [:vd:pages_files]", $dbData);
    }
    public function reattachFileToPage($pageId, $oldPageId){
        return $this->connection->query("UPDATE [:vd:pages_files] SET [page_id] = %i WHERE [page_id] = %i", $pageId, $oldPageId);
    }
    
    
    /**
     * funkce pro aktualizaci struktury
     * @param type $relativePath 
     */
    public function updateStructure($relativePath, $ownerId = 0) {
        if(empty($relativePath)) return true;
        $relativePath = \Nette\Utils\Strings::endsWith($relativePath, "/") ? substr($relativePath, 0, -1) : $relativePath;
        $parts = explode("/", $relativePath);
        $create = false;
        $count = count($parts);
        $c = 0;
        $parent = 0;
        if($parts && $count > 0){
            foreach($parts as $part){
                $folder = $this->connection->fetch("SELECT * FROM [:vd:folders] WHERE [name] = %s",$part);
                if(!$folder || $create){
                    $data = array(
                        'parent' => $parent,
                        'has_child' => $c < $count ? 1 : 0,
                        'name' => $part,
                        'nicename' => \Nette\Utils\Strings::webalize($part),
                        'editor_id' => $ownerId?:0
                    );
                    if(!empty($part)){
                        $this->connection->query('INSERT INTO [:vd:folders] ',$data);
                        $parent = $this->connection->getInsertId();
                    }
                }elseif($folder){
                    $parent = $folder['folder_id'];
                }
                if(!$folder || !$folder['has_child']) $create = true;
                $c++;
            }
        }
        return $parent;
    }

    public function createDuplicates($newPageId, $oldPageId) {
        //unset($pageIdsToDelete[$pageIdToUpdate]);
        $data = $this->connection->fetchAll("SELECT * FROM [:vd:pages_files] WHERE [page_id]=%i", $oldPageId);
        if($data){
            $inserts = array();
            foreach($data as $insert){
                $insert->toArray();
                $insert['page_id'] = $newPageId;
                $inserts[] = $insert;
            }
            $this->connection->query("INSERT INTO [:vd:pages_files] %ex", $inserts);
        }
       // $this->connection->query("DELETE FROM [:vd:pages_files] WHERE [page_id] IN %l",(array) $pageIdsToDelete);
        return true;
    }
    
        public function reconnectPages($newPageId, $oldPageId) {
        return $this->connection->query("UPDATE [:vd:pages_files] SET [page_id]=%i WHERE [page_id]=%i",$newPageId, $oldPageId);
    }
}

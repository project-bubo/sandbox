<?php

namespace Model;

final class AutosaverModel extends BaseModel {

    private function _isNewPage() {
        return empty($_POST['tree_node_id']);
    }
    
    private function _getAutosaveId() {
        return $_POST['autosave_id'] ?: NULL;
    }
    
    private function _findAutosaveId() {
        $editorId = $_POST['editor_id'] ?: NULL;
        $treeNodeId = $_POST['tree_node_id'] ?: NULL;
        
        $where = array(
                    'editor_id'     =>  $editorId,
                    'tree_node_id'  =>  $treeNodeId
        );  
        
        return $this->connection->fetchSingle('SELECT [autosave_id] FROM [:core:pages_autosave] WHERE %and', $where);
    }
    
    public function fetchAutosave($autosaveId) {
        return $this->connection->fetch('SELECT * FROM [:core:pages_autosave] WHERE [autosave_id] = %i', $autosaveId);
    }

    private function _createAutosave() {

        //unset($_POST['autosave_id']);
        $data = array(
                    'tree_node_id'  => $_POST['tree_node_id'] ?: NULL,
                    'editor_id'     => $_POST['editor_id'] ?: NULL,
                    'form_data'     => serialize($_POST)
        );
        
        $this->connection->query('INSERT INTO [:core:pages_autosave]', $data);
        $autosaveId = $this->getLastId();
        return $this->fetchAutosave($autosaveId);
    }
    
    private function _updateAutosave() {
        $autosaveId = $_POST['autosave_id'];    // must be set!
                
        // autosave_id is excluded from autosave
        unset($_POST['autosave_id']);
        $data = array(
                    'form_data' => serialize($_POST)
        );
        
        $this->connection->query('UPDATE [:core:pages_autosave] SET', $data, 'WHERE [autosave_id] = %i', $autosaveId);
        return $this->fetchAutosave($autosaveId);
    }

    /**
     * Autosave
     * --------
     * Saves serialized $_POST and returns autosave_id.
     * It is necessary to set autosave_id to page form!
     * 
     */
    public function autosave() {
       
        $autosavedItem = NULL;
        
        if (!empty($_POST)) {            
            
            if ($this->_isNewPage()) {
                // new page
                $autosaveId = $this->_getAutosaveId();
                
                if ($autosaveId !== NULL) {
                    // is autosaved --> update it
                    $autosavedItem = $this->_updateAutosave();
                } else {
                    // not autosaved --> create it
                    $autosavedItem = $this->_createAutosave();
                }
            } else {
                // saved page ($_POST[tree_node_id] is set)
                $autosaveId = $this->_findAutosaveId();
                
                if (!empty($autosaveId)) {
                    // is autosaved --> update it
                    $autosavedItem = $this->_updateAutosave();
                } else {
                    // not autosaved
                    $autosavedItem = $this->_createAutosave();
                }
            }
            
            
        }
        
        return $autosavedItem;
    }
    
    public function deleteAutosaveData($autosaveId) {     
        return $this->connection->query('DELETE FROM [:core:pages_autosave] WHERE [autosave_id] = %i', $autosaveId);
    }


    public function getUnsavedPages($userId) {
        $items = $this->connection->fetchAll('SELECT * FROM [:core:pages_autosave] WHERE [editor_id] = %i AND [tree_node_id] IS NULL', $userId);
        
        $counter = 1;
        
        $unsavedPages = array();
        
        foreach ($items as $i) {
            $unsavedPages[] = array(
                                'generated_name'  =>  'Untitled_'.$counter,
                                'autosave'      =>  $i
            );
            $counter++;
        }
        
        return $unsavedPages;
    }
    
    public function getNumberOfConcepts($userId) {
        return (int) $this->connection->fetchSingle('SELECT COUNT(*) FROM [:core:pages_autosave] WHERE [editor_id] = %i AND [tree_node_id] IS NULL', $userId);
    }
    
    public function getAutosavedPages($userId) {
        return $this->connection->query('SELECT * FROM [:core:pages_autosave] WHERE [editor_id] = %i AND [tree_node_id] IS NOT NULL', $userId)->fetchAssoc('tree_node_id');
    }
    
    /**
     * Returns autosaved page
     * ----------------------
     * This method cannot be used to retrieve concepts (when $treeNodeId is NULL)
     * Concepts are indetified by autosave_id
     * 
     * @param type $treeNodeId
     * @param type $editorId
     * @return type 
     */
    public function getLocalCopy($treeNodeId, $editorId) {
        if ($treeNodeId === NULL || $editorId === NULL) {
            return NULL;
        }
        
        $where = array(
                    'tree_node_id'  =>  $treeNodeId,
                    'editor_id'     =>  $editorId
        );
        return $this->connection->fetch('SELECT * FROM [:core:pages_autosave] WHERE %and', $where);
    }

     public function getConcept($autosaveId) {
        return $this->fetchAutosave($autosaveId);
    }
}
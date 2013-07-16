<?php

namespace Model;

final class TreeModel extends BaseModel {

    /**
     * Get page tree.
     * 
     * This huge select returns nodes from tree including the path.
     * BUT it returns the tree WITHOUT the root
     * 
     * When treeNodeId is NOT specified, then tree_node_id = 1, which is invisible root of all
     * But when tree_node_id IS specified, the root must be provided as well, so that's why UNION .o]
     * 
     * @param type $treeNodeId
     * @param type $maxLevel
     * @return type 
     */
//    public function getPageTree($treeNodeId = NULL, $maxLevel = 10) {
//        
//        //$startWith = ($treeNodeId === NULL) ? 1 : $treeNodeId;
//        
//        
//        
//        if ($treeNodeId == NULL) {
//            return $this->connection->query("SELECT  hi.tree_node_id AS tree_node_id,
//                                                          hierarchy_sys_connect_by_path('/', hi.tree_node_id) AS path,
//                                                          parent, lvl,
//                                                    CASE
//                                                        WHEN lvl >= @maxlevel THEN 1
//                                                        ELSE COALESCE(
//                                                        (
//                                                        SELECT  0
//                                                        FROM    [:core:page_tree] hl
//                                                        WHERE   hl.parent = ho.tree_node_id
//                                                        LIMIT 1
//                                                        ), 1)
//                                                    END AS is_leaf
//                                            FROM    (
//                                                    SELECT  hierarchy_connect_by_parent_eq_prior_id_with_level(tree_node_id, @maxlevel) AS tree_node_id,
//                                                                CAST(@level AS SIGNED) AS lvl
//                                                    FROM    (
//                                                            SELECT  @start_with := 1,
//                                                                    @tree_node_id := @start_with,
//                                                                    @level := 0,
//                                                                    @maxlevel := %i
//                                                            ) vars, cms_page_tree
//                                                    WHERE   @tree_node_id IS NOT NULL
//                                                    ) ho
//                                            JOIN    cms_page_tree hi
//                                            ON      hi.tree_node_id = ho.tree_node_id
//                                            ", $maxLevel)->fetchAssoc('tree_node_id');
//        } else {
//            
//            return $this->connection->query("SELECT  hi.tree_node_id AS tree_node_id,
//                                                          hierarchy_sys_connect_by_path('/', hi.tree_node_id) AS path,
//                                                          parent, lvl,
//                                                    CASE
//                                                        WHEN lvl >= @maxlevel THEN 1
//                                                        ELSE COALESCE(
//                                                        (
//                                                        SELECT  0
//                                                        FROM    [:core:page_tree] hl
//                                                        WHERE   hl.parent = ho.tree_node_id
//                                                        LIMIT 1
//                                                        ), 1)
//                                                    END AS is_leaf
//                                            FROM    (
//                                                    SELECT  hierarchy_connect_by_parent_eq_prior_id_with_level(tree_node_id, @maxlevel) AS tree_node_id,
//                                                                CAST(@level AS SIGNED) AS lvl
//                                                    FROM    (
//                                                            SELECT  @start_with := 1,
//                                                                    @tree_node_id := @start_with,
//                                                                    @level := 0,
//                                                                    @maxlevel := %i
//                                                            ) vars, cms_page_tree
//                                                    WHERE   @tree_node_id IS NOT NULL
//                                                    ) ho
//                                            JOIN    cms_page_tree hi
//                                            ON      hi.tree_node_id = ho.tree_node_id
//                                            WHERE hi.tree_node_id = %i
//
//                                            UNION
//
//                                            SELECT  hi.tree_node_id AS tree_node_id,
//                                                          hierarchy_sys_connect_by_path('/', hi.tree_node_id) AS path,
//                                                          parent, lvl,
//                                                    CASE
//                                                        WHEN lvl >= @maxlevel THEN 1
//                                                        ELSE COALESCE(
//                                                        (
//                                                        SELECT  0
//                                                        FROM    [:core:page_tree] hl
//                                                        WHERE   hl.parent = ho.tree_node_id
//                                                        LIMIT 1
//                                                        ), 1)
//                                                    END AS is_leaf
//                                            FROM    (
//                                                    SELECT  hierarchy_connect_by_parent_eq_prior_id_with_level(tree_node_id, @maxlevel) AS tree_node_id,
//                                                                CAST(@level AS SIGNED) AS lvl
//                                                    FROM    (
//                                                            SELECT  @start_with := %i,
//                                                                    @tree_node_id := @start_with,
//                                                                    @level := 0,
//                                                                    @maxlevel := %i
//                                                            ) vars, cms_page_tree
//                                                    WHERE   @tree_node_id IS NOT NULL
//                                                    ) ho
//                                            JOIN    cms_page_tree hi
//                                            ON      hi.tree_node_id = ho.tree_node_id
//                                            ", $maxLevel, $treeNodeId, $treeNodeId, $maxLevel)->fetchAssoc('tree_node_id');
//            
//        }
//        
//        
//        
//        
//    }
    
    
    public function getPageTreeItem($treeNodeId, $maxLevel = 10) {
        return $this->_getPageTreeItem($treeNodeId, TRUE, $maxLevel);
    }
    
    public function getPageTreeItemForCache($treeNodeId, $maxLevel = 10) {
        return $this->_getPageTreeItem($treeNodeId, FALSE, $maxLevel);
    }
    
    private function _getPageTreeItem($treeNodeId, $fetchAssoc, $maxLevel) {
        
        $query = $this->connection->query("SELECT  hi.tree_node_id AS tree_node_id,
                                                          hierarchy_sys_connect_by_path('/', hi.tree_node_id) AS path,
                                                          parent, lvl,
                                                    CASE
                                                        WHEN lvl >= @maxlevel THEN 1
                                                        ELSE COALESCE(
                                                        (
                                                        SELECT  0
                                                        FROM    cms_page_tree hl
                                                        WHERE   hl.parent = ho.tree_node_id
                                                        LIMIT 1
                                                        ), 1)
                                                    END AS is_leaf
                                            FROM    (
                                                    SELECT  hierarchy_connect_by_parent_eq_prior_id_with_level(tree_node_id, @maxlevel) AS tree_node_id,
                                                                CAST(@level AS SIGNED) AS lvl
                                                    FROM    (
                                                            SELECT  @start_with := 1,
                                                                    @tree_node_id := @start_with,
                                                                    @level := 0,
                                                                    @maxlevel := %i
                                                            ) vars, cms_page_tree
                                                    WHERE   @tree_node_id IS NOT NULL
                                                    ) ho
                                            JOIN    cms_page_tree hi
                                            ON      hi.tree_node_id = ho.tree_node_id
                                            WHERE hi.tree_node_id = %i", $maxLevel, $treeNodeId);
        
        return $fetchAssoc ? $query->fetchAssoc('tree_node_id') : $query->fetch();
        
    }
    
    
    public function isCacheEmpty() {
        $c = $this->connection->fetchSingle('SELECT COUNT(*) FROM [:core:page_tree_cache]');
        return ($c == 0);
    }
    
    
    public function fetchPageTreeFromCache() {
        return $this->connection->query('SELECT * FROM [:core:page_tree_cache]')->fetchAssoc('tree_node_id');
    }
    
    
    public function deleteTreeCache() {
        return $this->query('DELETE FROM [:core:page_tree_cache]');
    }
    
    public function createTreeCache($tree) {
        
        $data = array();
        
        foreach ($tree as $treeNodeId => $treeData) {
            
            
            $data[] = $treeData;
        }
        
        $this->connection->query('INSERT INTO [:core:page_tree_cache] %ex', $data);
        
     }
    
    /**
     * Duplicates whole subtree
     * ------------------------
     * 
     * Creates new page exemplars with given status.
     * Optionally version counter can be reset to 1.
     * 
     * 
     * And treeNodeIds can be remapped. 
     * 
     * The process is following:
     * 1) Get subtree with root $treeNodeId
     * 2) Select pages that will be duplicated
     *      - get actual pages (only from subtree)
     *      -> when $forceStatus is TRUE, then all statuses will be selected
     *      -> when $forceStatus is FALSE, then all statuses except $status will be selected
     *              (for example when duplicating tree to trash, some pages 
     *               in tree can be already trashed, so it is not necessary
     *               to trash then again, so $forceStatus = FALSE)
     * 3) Duplicated pages will be saved with 
     *      - status = $status
     *      - version number is incremeted or reset (depending on $resetVersionCounter)
     *      - newTreeNodeIdMapping is used if provided
     * 4) Copy urls // TODO
     * Returns treeNodeIds of new (duplicated) pages
     * 
     * @param type $treeNodeId
     * @param type $status
     * @param type $newTreeNodeIdMapping 
     */
    public function duplicateSubTree($treeNodeId, $status, $resetVersionCounter = FALSE, $forceStatus = FALSE, $newTreeNodeIdMapping = NULL) {
        
        $duplicatedPages = array();
        
        // Step 1: get subTree
        $subTree = $this->getPageTree($treeNodeId);
        
        // Step 2:
        $statuses = array('draft', 'published', 'trashed');
        if (!$forceStatus) {
            unset($statuses[array_search($status, $statuses)]);
        }
        
        $pagesToDuplicate = $this->getModelPage()->getActualPages($statuses, 'tree_node_id');
        
        // Step 3:
        $dbData = array();        
        foreach ($pagesToDuplicate as $pageToDuplicate) {
            // intersect $subTree and $pagesToDuplicate
            if (in_array($pageToDuplicate->tree_node_id, array_keys($subTree))) {
                $newTreeNodeId = NULL;
                if ($newTreeNodeIdMapping !== NULL && isset($newTreeNodeIdMapping[$pageToDuplicate->tree_node_id])) {
                    $newTreeNodeId = $newTreeNodeIdMapping[$pageToDuplicate->tree_node_id];
                }
                $dbData[] = $this->duplicatePage($pageToDuplicate, $status, $resetVersionCounter, $newTreeNodeId, TRUE);
                //$duplicatedTreeNodeIds[] = ($newTreeNodeId === NULL) ? $pageToDuplicate->tree_node_id : $newTreeNodeId;
                $duplicatedPages[] = $pageToDuplicate;
            }
        }
        
        
        
        if (!empty($dbData))
            $this->connection->query('INSERT INTO [:core:pages] %ex', $dbData);        

        if ($newTreeNodeIdMapping == NULL) {
            // update current urls
            $this->_updateUrls($duplicatedPages);
        }
        
    }
    
    /**
     * 
     * @param type $pageToDuplicate 
     */
    private function _updateUrls($duplicatedPages) {
        
        // key = from
        // value = to
        $pageIdMapping = array();

        $actualPages = $this->getModelPage()->getActualPages(NULL, 'tree_node_id');
        
        foreach ($duplicatedPages as $duplicatedPage) {
            $pageIdMapping[$duplicatedPage->page_id] = $actualPages[$duplicatedPage->tree_node_id]->page_id;
        }
        
        $oldUrlBinding = $this->_getUrlBinding(array_keys($pageIdMapping));
        
        $data = array();
        foreach ($oldUrlBinding as $oldPageId => $urlRecord) {
            $data[] = array(
                        'page_id'   =>  $pageIdMapping[$oldPageId],
                        'url'       =>  $urlRecord->url
            );
        }
        
        if (!empty($data))
            $this->connection->query('INSERT INTO [:core:urls] %ex', $data);
        
    }

    private function _getUrlBinding($pageIds) {
        return $this->connection->query('SELECT * FROM [:core:urls] WHERE [page_id] IN %in', $pageIds)->fetchAssoc('page_id');
    }
    
    /**
     * Create new page exemplar
     * 
     * @param type $treeNodeId
     * @param type $status 
     */
    public function duplicatePage($page, $status, $resetVersionCounter = FALSE, $newTreeNodeId = NULL, $onlyGetData = FALSE) {
        $actualPage = $this->getModelPage()->getActualPage($page->tree_node_id);
        $newPage = clone $page;

        
        unset($newPage['page_id']);
        unset($newPage['created']);
        $newPage['version'] = $resetVersionCounter ? 1 : $actualPage['version'] + 1;
        $newPage['tree_node_id'] = ($newTreeNodeId === NULL) ? $newPage['tree_node_id'] : $newTreeNodeId;
        $newPage['status'] = $status;
        
        
        return $onlyGetData ? $newPage : $this->connection->query("INSERT INTO [:core:pages]", $newPage);
    }
    
    /**
     * 
     * NEW APPROACH
     * 
     */
    
    public function __duplicatePage($page, $data, $withUrl = TRUE, $resetVersionCounter = FALSE) {

        $pageData = $page->getDbValues();
        
        $duplicate = $pageData;
        
                
        foreach ($data as $key => $value) {
            $duplicate[$key] = $value;
        }
        
        $duplicate['version'] = $resetVersionCounter ? 1 : $page->getProperty('version') + 1;
        $duplicate['class'] = $page->getReflection()->name;
        $duplicate['user_id'] = $page->presenter->userId;
        
        
        $result = $this->connection->query("INSERT INTO [:core:pages]", $duplicate);
        
//        dump($duplicate);
//        die();
        if ($withUrl) {
            // also create record in [:core:urls]
            $newPageId = $this->getLastId();
            
            // always reconstruct url
            
//            $args = array(
//                        'urlChunk'          =>  $duplicate['url_chunk'],
//                        'treeNodeId'        =>  $duplicate['tree_node_id'],
//                        'parentTreeNodeId'  =>  NULL
//            );
//            
//            
//            $pageIndex = $page->getPresenter()->context->pageManager->getPageIndex();
//            
//            $url = $this->getModelPage()->getUrl('self', $args, $pageIndex);
//            
//            
            $url = $page->getProperty('url');
            
            $urlData = array(
                            'page_id'   =>  $newPageId,
                            'url'       =>  $url
            );
            
            $result = $result & $this->connection->query("INSERT INTO [:core:urls]", $urlData);
        }
        
        
        
        return $result;
    }
    
    
    public function __createNewTreeNode($newParent) {
        
        $data = array(
                    'parent'    =>  $newParent
        );
        
        $this->connection->query('INSERT INTO [:core:page_tree]', $data);
        return $this->connection->getInsertId();
        
    }
    
    
    public function __relocate($source, $newParent) {
        return $this->connection->query('UPDATE [:core:page_tree] SET [parent] = %i WHERE [tree_node_id] = %i', $newParent, $source);
    }
    
}
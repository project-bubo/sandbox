<?php

namespace Model;

final class LabelModel extends BaseModel {

    
    public function getAllLabels($module) {
//        $labels = $this->connection->query('SELECT 
//                                                [l].*,
//                                                [def].[ext_id] as [extensions],
//                                                [def].[title] as [ext_title],
//                                                [def].[identifier] as [ext_identifier],
//                                                [def].[name] as [ext_name]
//                                                FROM 
//                                                    [:core:labels] [l] 
//                                                LEFT JOIN 
//                                                    [:core:label_ext_definitions] [def]
//                                                USING
//                                                    ([label_id])
//                                            ')->fetchAssoc('label_id,=,extensions');

        $labels = $this->connection->query('SELECT 
                                                [l].*,
                                                [def].[ext_id] as [extensions],
                                                [def].[title] as [ext_title],
                                                [def].[identifier] as [ext_identifier],
                                                [def].[name] as [ext_name]
                                                FROM 
                                                    [:core:labels] [l] 
                                                LEFT JOIN 
                                                    [:core:label_ext_definitions] [def]
                                                USING
                                                    ([label_id])
                                                WHERE
                                                    [l].[module] = %s
                                                ORDER BY [l].[create_button] DESC
                                            ', $module)->fetchAssoc('label_id,=,extensions');
        
        
//        dump($labels);
//        die();
        
        // unserialize
        $uLabels = array();
        
        
        foreach ($labels as $labelId => $label) {
            $uLabels[$labelId] = \Utils\MultiValues::unserializeArray($label);
            if ($uLabels[$labelId]['langs'] === NULL) $uLabels[$labelId]['langs'] = array();
            
            // remove empty extensions
            $keys = array_keys($label['extensions']);
            if (reset($keys) == "") {
                $uLabels[$labelId]['extensions'] = array();
            }
        }

        return $uLabels;
    }
    
//    
    public function getLabel($labelId) {
        return $this->connection->fetch('SELECT * FROM [:core:labels] WHERE [label_id] = %i', $labelId);
    }

    public function createLabel($data) {
        if ($data['depth_of_recursion'] == '')
            $data['depth_of_recursion'] = NULL;
        
        $this->connection->query('INSERT INTO [:core:labels]', $data);
        return $this->connection->getInsertId();
    }
//    
    public function editLabel($data) {
        
        $labelId = $data['label_id'];
        unset($data['label_id']);
        
        if ($data['depth_of_recursion'] == '')
            $data['depth_of_recursion'] = NULL;

        return $this->connection->query('UPDATE [:core:labels] SET', $data, 'WHERE [label_id] = %i', $labelId);
    }
    
    public function addExtension($data) {
        
        return $this->connection->query('INSERT INTO [:core:label_ext_definitions]', $data);
        
    }
    
    public function getExtension($extId) {
        return $this->connection->fetch('SELECT * FROM [:core:label_ext_definitions] WHERE [ext_id] = %i', $extId);
    }
    
    
    public function editExtension($data, $extId) {
        return $this->connection->query('UPDATE [:core:label_ext_definitions] SET',$data,' WHERE [ext_id] = %i', $extId);
    }
    
    /**
     * Remove extension definitions and all its values
     * @param type $extId
     * @return type
     */
    public function removeExtension($extId) {
        $ext = $this->getExtension($extId);
        if (!empty($ext)) {
            // remove all values
            $this->connection->query('DELETE FROM [:core:extended_values] WHERE [identifier] = %s', $ext['identifier']);
        }
        return $this->connection->query('DELETE FROM [:core:label_ext_definitions] WHERE [ext_id] = %i', $extId);
    }
    
    public function getLabelRootTreeNodeId($labelId, $lang) {
        $where = array(
                    'label_id'  =>  $labelId,
                    'active'    =>  'yes',
                    'p.lang'      =>  $lang
        );
        return $this->connection->fetchSingle('SELECT 
                                                    [p].[tree_node_id]
                                                    FROM 
                                                        [:core:pages_labels] [l]
                                                    JOIN
                                                        [:core:pages] [p]
                                                    ON
                                                        [l].[tree_node_id] = [p].[tree_node_id]
                                                    WHERE 
                                                        %and 
                                                    LIMIT 1', $where);
    }

    // TODO
    public function addPassiveLabelling($treeNodeId, $label) {
        
        $data = array();
        
        if ($label && is_array($label['langs'])) {
            foreach ($label['langs'] as $langCode => $bool) {
                if ($bool) {
                    $data[] = array(
                                    'label_id'      =>  $label['label_id'],
                                    'tree_node_id'  =>  $treeNodeId,
                                    'active'        =>  'no',
                                    'lang'          =>  $langCode
                                  );
                }
            }
            
        }
        
        return $this->connection->query('INSERT INTO [:core:pages_labels] %ex', $data);
    }
    
    public function getAllLabelsAssignedToPage($treeNodeId) {
        return $this->connection->query('SELECT 
                                            [l].* 
                                            FROM 
                                                [:core:labels] [l]
                                            JOIN
                                                [:core:pages_labels] [pl]
                                            USING
                                                ([label_id])
                                            WHERE
                                                [pl].[tree_node_id] = %i
                                         ', $treeNodeId)->fetchAssoc('nicename');
    }
    
    
    public function deleteLabel($labelId) {
        return $this->connection->query('DELETE FROM [:core:labels] WHERE [label_id] = %i', $labelId);
    }
    
    
    public function savePageSorting($labelId, $parentId, $items) {
        
        $res = $this->connection->fetchSingle('SELECT [page_order] FROM [:core:labels] WHERE [label_id] = %i', $labelId);
        
        $pageSort = array();
        
        if ($res && \Utils\MultiValues::unserialize($res)) {
            $pageSort = \Utils\MultiValues::unserialize($res);
        }

        $pageSort[$parentId] = $items;

//        print_r($items);
//        die();
        
        return $this->connection->query('UPDATE [:core:labels] SET [page_order] = %s WHERE [label_id] = %i', serialize($pageSort), $labelId);
        
    }
    
}
<?php

namespace Model;

final class ExtModel extends BaseModel {

    
    // meta utulities
    public function _loadMeta($key) {
        $res = $this->connection->query('SELECT [value], [key] FROM [:core:ext_meta] WHERE [key] IN %in',(array) $key);
        return is_array($key) ? $res->fetchAssoc('key') : $res->fetchSingle();
    }
    
    private function _deleteMeta($key) {
        return $this->connection->query('DELETE FROM [:core:ext_meta] WHERE [key] IN %in', (array) $key);
    }
    
    private function _insertMeta($key, $value) {
        $this->_deleteMeta($key);
        $data = array(
                'key'       =>  $key,
                'value'     =>  $value
        );
        return $this->connection->query('INSERT INTO [:core:ext_meta]', $data);
    }
    
    private function _updateMeta($key, $value) {
        $this->_insertMeta($key, $value);
    }
    
    private function _loadMetaByValue($search) {
        return $this->connection->query('SELECT [key] FROM [:core:ext_meta] WHERE [value] LIKE %~like~', $search)->fetchAssoc('key');
    }
    
    
    /**
     * Adding of ext param is dovoded into following parts
     * 
     * 1) creating params node (related to treeNodeId)
     * 2) creating param names in all langs (related to pages)
     * 3) creating tag assignment + ordering (related to page labels)
     * 
     * @param type $data
     * @param type $langs
     */
    public function addParam($data, $langs) {

        
        // create param node
        $extTreeData = array(
                        'parent'        =>  NULL,
                        'identifier'    =>  $data['identifier'],  // foreign key
                        'tree_node_id'  =>  $data['tree_node_id'] // foreign key
        );
        
        $this->connection->query('INSERT INTO [cms_ext_tree]', $extTreeData);
        $extTreeNodeId = $this->connection->getInsertId();
        
        
        $paramData = array();
        
        if (!empty($langs)) {
            foreach ($langs as $langCode => $lang) {
                
                if (isset($data[$langCode])) {
                    $paramData[] = array(
                                    'ext_tree_node_id'  =>  $extTreeNodeId,
                                    'lang'              =>  $langCode,
                                    'param_name'        =>  $data[$langCode]['param_name']
                    );
                }
                
            }
        }
        
        $this->connection->query('INSERT INTO [:core:ext_params] %ex', $paramData);
        
        // save tag assignment to meta
        $key = $extTreeNodeId. '-tagAssignment';
        $value = serialize($data['tags']);
        $this->_insertMeta($key, $value);
        
//        dump(serialize($data['tags']));
//        die();
//        dump('hotovo');
//        die();
    }
    
    
    public function addStructuredParam($data, $langs) {
        
//        dump($data);
//        die();
        
         // create param node
        $extTreeData = array(
                        'parent'        =>  $data['parent'] ?: NULL,
                        'identifier'    =>  $data['identifier'],  // foreign key
                        'tree_node_id'  =>  $data['tree_node_id'] // foreign key
        );
        
        $this->connection->query('INSERT INTO [cms_ext_tree]', $extTreeData);
        $extTreeNodeId = $this->connection->getInsertId();
        
        
        $paramData = array();
        
        if (!empty($langs)) {
            foreach ($langs as $langCode => $lang) {
                
                if (isset($data[$langCode])) {
                    $temp = array(
                                    'ext_tree_node_id'  =>  $extTreeNodeId,
                                    'lang'              =>  $langCode,
                                    'param_name'        =>  $data[$langCode]['param_name']
                    );
                    unset($data[$langCode]['param_name']);
                    $data[$langCode]['x_coord'] = $data['x_coord'];
                    $data[$langCode]['y_coord'] = $data['y_coord'];
                    $data[$langCode]['image'] = $data['image'];
                    $temp['param_value']  = serialize($data[$langCode]);
                    $paramData[] = $temp;
                }
                
            }
        }
        
//        dump($paramData);
//        die();
        
        $this->connection->query('INSERT INTO [:core:ext_params] %ex', $paramData);
        
        // save tag assignment to meta
//        $key = $extTreeNodeId. '-tagAssignment';
//        $value = serialize($data['tags']);
//        $this->_insertMeta($key, $value);
    }
    
    
    public function removeParam($extTreeNodeId) {
        $this->_deleteMeta($extTreeNodeId. '-tagAssignment');
        return $this->connection->query('DELETE FROM [cms_ext_tree] WHERE [ext_tree_node_id] = %i', $extTreeNodeId);
    }
    
    public function removeStructuredParam($extTreeNodeId) {
        return $this->connection->query('DELETE FROM [cms_ext_tree] WHERE [ext_tree_node_id] = %i', $extTreeNodeId);
    }
    
    
    public function getParam($extTreeNodeId, $tags) {
        
        $defaults = array();
        
        
        // load tags 
        $defaults['tags'] = $this->_loadMeta($extTreeNodeId.'-tagAssignment');

        
        
        $defaults += $this->connection->query('SELECT 
                                    [lang],
                                    [parent],
                                    [param_name],
                                    [param_value]
                                    FROM 
                                        [:core:ext_tree] 
                                    JOIN
                                        [:core:ext_params]
                                    USING
                                        ([ext_tree_node_id])
                                    WHERE 
                                        [ext_tree_node_id] = %i
                                ', $extTreeNodeId)->fetchAssoc('lang');
        
        return $defaults;
        
        
    }
    
    
    
    public function editParam($data, $extTreeNodeId, $langs) {
        
        // update tag assignment of this param and 
        // change param name for each mutation
        // and parent?????
    
        $allStoredMutations = $this->connection->query('SELECT [lang] FROM [:core:ext_params] WHERE [ext_tree_node_id] = %i', $extTreeNodeId)->fetchAssoc('lang');
        
        if (!empty($langs)) {
            foreach ($langs as $langCode => $lang) {
                if (isset($allStoredMutations[$langCode])) {
                    // UPDATE
                    $where = array(
                                'ext_tree_node_id%i'    =>  $extTreeNodeId,
                                'lang%s'                =>  $langCode
                    );
                    $this->connection->query('UPDATE [:core:ext_params] SET [param_name] = %s WHERE %and', $data[$langCode]['param_name'], $where);
                } else {
                    // INSERT
                    $dbData = array(
                                'ext_tree_node_id%i'  =>  $extTreeNodeId,
                                'lang%s'              =>  $langCode,
                                'param_name%s'        =>  $data[$langCode]['param_name']
                    );
                    $this->connection->query('INSERT INTO [:core:ext_params]', $dbData);
                    
                }
            }
        }
        
        // delete and add tag assigment
        $key = $extTreeNodeId. '-tagAssignment';
        $value = serialize($data['tags']);
        $this->_updateMeta($key, $value);
        
        
//        dump($extTreeNodeId, $data, $langs);
//        die();
        
    }
    
    
    public function editStructuredParam($data, $extTreeNodeId, $langs) {
        // update tag assignment of this param and 
        // change param name for each mutation
        // and parent
        
        $allStoredMutations = $this->connection->query('SELECT [lang] FROM [:core:ext_params] WHERE [ext_tree_node_id] = %i', $extTreeNodeId)->fetchAssoc('lang');
        
        if (!empty($langs)) {
            foreach ($langs as $langCode => $lang) {
                if (isset($allStoredMutations[$langCode])) {
                    // UPDATE
                    $where = array(
                                'ext_tree_node_id%i'    =>  $extTreeNodeId,
                                'lang%s'                =>  $langCode
                    );
                    $paramName = $data[$langCode]['param_name'];
                    unset($data[$langCode]['param_name']);
                    
                    $data[$langCode]['x_coord'] = $data['x_coord'];
                    $data[$langCode]['y_coord'] = $data['y_coord'];
                    $data[$langCode]['image'] = $data['image'];
                    
                    $dbData = array(
                                'param_name'    =>  $paramName,
                                'param_value'   =>  serialize($data[$langCode])
                    );
                    $this->connection->query('UPDATE [:core:ext_params] SET', $dbData, 'WHERE %and', $where);
                } else {
                    // INSERT
                    $temp = array(
                                    'ext_tree_node_id'  =>  $extTreeNodeId,
                                    'lang'              =>  $langCode,
                                    'param_name'        =>  $data[$langCode]['param_name']
                    );
                    unset($data[$langCode]['param_name']);
                    $data[$langCode]['x_coord'] = $data['x_coord'];
                    $data[$langCode]['y_coord'] = $data['y_coord'];
                    $data[$langCode]['image'] = $data['image'];
                    $temp['param_value']  = serialize($data[$langCode]);
                    $dbData[] = $temp;

                    $this->connection->query('INSERT INTO [:core:ext_params]', $dbData);
                    
                }
            }
        }
        
        
        // set parent
        $this->connection->query('UPDATE [:core:ext_tree] SET [parent] = %i WHERE [ext_tree_node_id] = %i', $data['parent'], $extTreeNodeId);
        
        
        // delete and add tag assigment
//        $key = $extTreeNodeId. '-tagAssignment';
//        $value = serialize($data['tags']);
//        $this->_updateMeta($key, $value);
    }
    
    
    public function getParams($treeNodeId, $identifier) {
        
        $where = array(
                    't.tree_node_id'  =>  $treeNodeId,
                    't.identifier'    =>  $identifier
        );
        
        return $this->connection->query('SELECT 
                                            [p].[lang],
                                            [p].[ext_param_id],
                                            [p].[ext_tree_node_id],
                                            [p].[param_name],
                                            [v].[param_value]
                                            FROM
                                                [:core:ext_tree] [t]
                                            JOIN
                                                [:core:ext_params] [p]
                                            USING
                                                ([ext_tree_node_id])
                                            LEFT JOIN
                                                [:core:ext_tree] [ch]
                                            ON
                                                [ch].[parent] = [t].[ext_tree_node_id]
                                            LEFT JOIN
                                                [:core:ext_values] [v]
                                            ON
                                                [v].[ext_tree_node_id] = [ch].[ext_tree_node_id]
                                                AND
                                                [v].[lang] = [p].[lang]
                                            WHERE
                                                %and
                                        ', $where)->fetchAssoc('ext_tree_node_id,lang');
        
    }
    
    
    public function getParamsByChild($treeNodeId, $identifier) {
        
        $where = array(
                    'ch.tree_node_id'  =>  $treeNodeId,
                    't.identifier'    =>  $identifier
        );
        
        return $this->connection->query('SELECT 
                                            [p].[lang],
                                            [p].[ext_param_id],
                                            [p].[ext_tree_node_id],
                                            [p].[param_name],
                                            [v].[param_value]
                                            FROM
                                                [:core:ext_tree] [t]
                                            JOIN
                                                [:core:ext_params] [p]
                                            USING
                                                ([ext_tree_node_id])
                                            LEFT JOIN
                                                [:core:ext_tree] [ch]
                                            ON
                                                [ch].[parent] = [t].[ext_tree_node_id]
                                            LEFT JOIN
                                                [:core:ext_values] [v]
                                            ON
                                                [v].[ext_tree_node_id] = [ch].[ext_tree_node_id]
                                                AND
                                                [v].[lang] = [p].[lang]
                                            WHERE
                                                %and
                                        ', $where)->fetchAssoc('ext_tree_node_id,lang');
        
    }
    
    
    public function editParamValues($formValues, $langs) {
        
//        dump($formValues);
//        die();
        
//        $insertData = array();
//        dump($formValues);
//        die();
        
        foreach ($formValues as $key => $v) {
            
            if (\Nette\Utils\Strings::startsWith($key, 'ext_tree_node_id_')) {
                // parse parent treeNodeId
                
                $parentExtTreeNodeId = substr($key, 17);
                // try to find ext_tree_node_id of this set of values
                $where = array(
                                'parent'        =>  $parentExtTreeNodeId,
                                'tree_node_id'  =>  $formValues['tree_node_id']
                    );
                $valueExtTreeNodeId = $this->connection->fetchSingle('SELECT [ext_tree_node_id] FROM [:core:ext_tree] WHERE %and', $where);
                
                if (!$valueExtTreeNodeId) {
                    // param node does not exists -> needs to bude created

                    $valueNodeData = array(
                                        'parent'        =>  $parentExtTreeNodeId,
                                        'tree_node_id'  =>  $formValues['tree_node_id'],
                                        'identifier'    =>  'val_'.$formValues['identifier']
                    );
                    $this->connection->query('INSERT INTO [:core:ext_tree]', $valueNodeData);
                    $valueExtTreeNodeId = $this->connection->getInsertId();
                    
                }
                
                // $valueExtTreeNodeId is SET!
                
                // delete all values and refill them
                $this->connection->query('DELETE FROM [:core:ext_values] WHERE [ext_tree_node_id] = %i', $valueExtTreeNodeId);
                
                // fill params
                $extValuesData = array();
                
                
                
                foreach ($langs as $langCode => $lang) {
                    
                    if (isset($v[$langCode])) {
                        
                        $arrValue = (array) $v[$langCode];                    
                        $dbValue = count($arrValue) > 1 ? serialize($arrValue) : $v[$langCode];


                        $extValuesData[] = array(
                                            'ext_tree_node_id'  =>  $valueExtTreeNodeId,
                                            'lang'              =>  $langCode,
                                            'param_value'       =>  $dbValue
                        );
                        
                    }
                    
                }
                
                
                if (!empty($extValuesData)) {
                    $this->connection->query('INSERT INTO [:core:ext_values] %ex', $extValuesData);
                }
                
                
                
//                dump($extValuesData);
//                die();
                
                
            }
            
            
        }

        
    }
    
    public function getParamValues($treeNodeId, $identifier) {

        $out = array();
        
        $p = $this->getParamsByChild($treeNodeId, $identifier);
        
        //dump($p);
        
        foreach ($p as $extTreeNodeId => $langData) {
            foreach ($langData as $lang => $data) {
                if (\Utils\MultiValues::unserialize($data['param_value']) !== FALSE) {
                    $out['ext_tree_node_id_'.$extTreeNodeId][$lang] = \Utils\MultiValues::unserialize($data['param_value']);
                } else {
                    $out['ext_tree_node_id_'.$extTreeNodeId][$lang] = $data['param_value'];
                }
                $out['ext_tree_node_id_'.$extTreeNodeId][$lang] = $out['ext_tree_node_id_'.$extTreeNodeId][$lang] ?: array();
            }
        }
        
        
        return $out;
    }
    
    
    public function getNamedStructuredParamValues($treeNodeId, $identifier, $lang) {
        
        $where = array(
                    't.tree_node_id'  => $treeNodeId,
                    't.identifier'    => $identifier,
                    'p.lang'          => $lang
        );
        
        $p = $this->connection->query('SELECT
                                            [ext_tree_node_id],
                                            [param_name],
                                            [param_value],
                                            [parent]
                                            FROM
                                                [:core:ext_tree] [t]
                                            JOIN
                                                [:core:ext_params] [p]
                                            USING
                                                ([ext_tree_node_id])
                                            WHERE
                                                %and
                                    ', $where)->fetchAssoc('ext_tree_node_id');
        
        $ret = array('parents' => array(), 'children' => array());
        
        
        
        foreach ($p as $extTreeNodeId => $_p) {
            if ($_p['parent'] !== NULL) {
                $ret['children'][$_p['parent']][$extTreeNodeId] = array(
                            'ext_tree_node_id' => $_p['ext_tree_node_id'],
                            'parent' => $_p['parent'],
                            'name'  => $_p['param_name'],
                            'value' => \Utils\MultiValues::unserialize($_p['param_value']) ? \Utils\MultiValues::unserialize($_p['param_value']) : $_p['param_value'] 
                ); 
            } else {
                $ret['parents'][$extTreeNodeId] = array(
                            'ext_tree_node_id' => $_p['ext_tree_node_id'],
                            'parent' => $_p['parent'],
                            'name'  => $_p['param_name'],
                            'value' => \Utils\MultiValues::unserialize($_p['param_value']) ? \Utils\MultiValues::unserialize($_p['param_value']) : $_p['param_value'] 
                );
            }
        }
        
        return $ret;
        
    }
    
    
    public function getNamedParamValues($parentTreeNodeId, $treeNodeId, $identifier, $lang, $tag) {
        
        $where = array(
                    'p.tree_node_id' =>  $parentTreeNodeId,
                    'ch.tree_node_id'   =>  $treeNodeId,
                    'p.identifier'      =>  $identifier,
                    'val.lang'          =>  $lang,
                    'param.lang'          =>  $lang
        );
        
     
        $key = $tag . '-' . $parentTreeNodeId . '-' . $identifier . '-paramSortOrder';
        $s = \Utils\MultiValues::unserialize($this->_loadMeta($key));
        //dump($s);
        
        $qq = '"'.$tag.'";b:1';
        
        $keys = $this->_loadMetaByValue($qq);
        
        $extTreeNodeIds = array();
        
        foreach ($keys as $k => $v) {
            if (preg_match('#([0-9]+)-tagAssignment#', $k, $matches)) {
                $extTreeNodeIds[] = $matches[1];
            }
        }
        
        $q = $this->connection->query("SELECT
                                        [p].[ext_tree_node_id],
                                        [param].[param_name],
                                        [val].[param_value]
                                        FROM 
                                            [:core:ext_tree] [p] 
                                        JOIN
                                            [:core:ext_params] [param]
                                        USING
                                            ([ext_tree_node_id])
                                        LEFT JOIN
                                            [:core:ext_tree] [ch]
                                        ON
                                            [ch].[parent] = [p].[ext_tree_node_id]
                                        LEFT JOIN
                                            [:core:ext_values] [val]
                                        ON
                                            [ch].[ext_tree_node_id] = [val].[ext_tree_node_id]
                                        WHERE 
                                            %and
                                            AND [p].[ext_tree_node_id] IN %in
                                        %if
                                            ORDER BY FIELD (p.ext_tree_node_id, %sql)
                                    ", $where, $extTreeNodeIds, $s, $s ? implode(',', $s) : 0);
        
        
        
        
        
        $ret = $q->fetchAssoc('ext_tree_node_id');
        
//        dump($ret);
        
        //get assignment meta
        $metaData = $this->_loadMeta(array_keys($ret));

        foreach ($metaData as $key => $v) {
            $tagAssignment[$key] = \Utils\MultiValues::unserialize($v['value']);
        }
        
        $out = array();
        
//        $tag = 'benefits';
        // order and filter params
        foreach($ret as $extTreeNodeId => $data) {
            if (isset($tagAssignment[$extTreeNodeId.'-tagAssignment']) && 
                isset($tagAssignment[$extTreeNodeId.'-tagAssignment'][$tag]) &&
                $tagAssignment[$extTreeNodeId.'-tagAssignment'][$tag]
                    ) {
                
                //foreach ($langs as $langCode => $paramData) {
                    $out[$data['param_name']] = \Utils\MultiValues::unserialize($data['param_value']);
                //}
                
                //$out[] = \Utils\MultiValues::unserialize($value['param_value'])
                
            }
        }
        
        //dump('jsem tu');
        
        return $out;
//        dump($out);
//        die();
        
        $paramValues = array();
        
        $extTreeNodeIds = array();
        $ta = array();
        foreach ($ret as $langCode => $values) {
            foreach ($values as $extParamId => $value) {
                $extTreeNodeIds[$value['ext_tree_node_id'].'-tagAssignment'] = TRUE;
                $ta[$value['ext_tree_node_id']] = TRUE;
                $paramValues[$langCode][$extParamId] = \Utils\MultiValues::unserialize($value['param_value']);
            }
        }

//        dump($extTreeNodeIds);
//        die();
        
        
        $tagAssignment = array();
        
        if ($tag !== NULL) {
            
        }
        
//        dump($ta, $tagAssignment, $paramValues);
//        die();
        
        return $paramValues;
        
    }

    public function getStructuredParamsParentSelectData($identifier, $treeNodeId, $defaultLang, $excludedExtTreeNodeId = NULL) {
        
        $where = array(
                    'identifier'    =>  $identifier,
                    'tree_node_id'  =>  $treeNodeId,
                    'lang'          =>  $defaultLang,
                    'parent'        =>  NULL
        );
        
        return $this->connection->query('SELECT 
                                            [ext_tree_node_id],
                                            [param_name]
                                            FROM 
                                                [:core:ext_tree]
                                            JOIN
                                                [:core:ext_params]
                                            USING
                                                ([ext_tree_node_id])
                                            WHERE 
                                                %and AND [ext_tree_node_id] != %i
                                            ', $where, (int) $excludedExtTreeNodeId)->fetchPairs('ext_tree_node_id','param_name');
        
    }
    
    
    
    public function loadParamsForSorting($tag, $identifier, $treeNodeId, $lang) {
        
        $where = array(
                    'identifier'    =>  $identifier,
                    'tree_node_id'  =>  $treeNodeId,
                    'lang'          =>  $lang
        );
        
        $key = $tag . '-' . $treeNodeId . '-' . $identifier . '-paramSortOrder';
        $s = \Utils\MultiValues::unserialize($this->_loadMeta($key));
        //dump($s);
        
        $q = '"'.$tag.'";b:1';
        
        $keys = $this->_loadMetaByValue($q);
        
        //dump($tag, $keys);
        
        $extTreeNodeIds = array();
        
        foreach ($keys as $k => $v) {
            if (preg_match('#([0-9]+)-tagAssignment#', $k, $matches)) {
                $extTreeNodeIds[] = $matches[1];
            }
        }
        
//        dump($extTreeNodeIds);
//        die();
        return $this->connection->query('SELECT
                                            [t].*,
                                            [p].*
                                            FROM
                                              [:core:ext_tree] [t]
                                            JOIN
                                              [:core:ext_params] [p]
                                            USING
                                              ([ext_tree_node_id])
                                            WHERE
                                              %and
                                              AND [ext_tree_node_id] IN %in
                                            %if
                                            ORDER BY FIELD (ext_tree_node_id, %sql)
                                        ', $where, $extTreeNodeIds, $s, $s ? implode(',', $s) : 0)->fetchAssoc('ext_tree_node_id');
        
    }
    
    public function sortParams($tag, $treeNodeId, $identifier, $params) {
        
        // save sorting to meta
        $key = $tag . '-' . $treeNodeId . '-' . $identifier . '-paramSortOrder';
        $value = serialize($params);
        
        $this->_insertMeta($key, $value);
//        dump($treeNodeId, $identifier, $params);
//        die();
        
        
    }
    
    
    public function saveExtSorting($labelId, $items) {
        
        $key = $labelId.'-extSortorder';
        if (!empty($items)) {
            $this->_updateMeta($key, serialize($items));
        }
    }
    

    public function loadExtSorting($labelId) {
        
        $key = $labelId.'-extSortorder';
        return $this->_loadMeta($key);
        
    }
    
    public function saveEntityParamFormData($data, $labelId) {
        
        $key = $labelId.'-entityParams';
        $value = serialize($data);
        
        $this->_insertMeta($key, $value);
        
    }
    
    public function getDefaultsForEntityParamForm($labelId, $entityConfig) {
        
        $entityParams = array();
        
        $key = $labelId.'-entityParams';
        $data = $this->_loadMeta($key);
        
        $defaults = array();
        
        if ($data !== FALSE) {
            $defaults = \Utils\MultiValues::unserialize($data);
        } else {
            if ($entityConfig && isset($entityConfig['properties'])) {
                foreach ($entityConfig['properties'] as $entityParamName => $entityParam) {
                    $defaults[$entityParamName]['label'] = $entityParam['label'];
                }
            }
        }
        
        return $defaults;
        
    }
    
    
    public function filterEntityProperties($properties, $labelId) {
        
        $key = $labelId.'-entityParams';
        $data = $this->_loadMeta($key);
        
        if ($data !== FALSE) {
            $defaults = \Utils\MultiValues::unserialize($data);
            
            if ($defaults !== FALSE) {
                $newProperties = array();
                foreach ($properties as $propertyName => $property) {
                    if (isset($defaults[$propertyName])) {
                        if (!$defaults[$propertyName]['exclude']) {
                            $_p = $property;
                            $_p['label'] = $defaults[$propertyName]['label'];
                            $newProperties[$propertyName] = $_p;
                        }
                    }
                }
            }
            
            $properties = $newProperties;
//            dump($properties, \Utils\MultiValues::unserialize($data));
//            die();
        } 
        
        return $properties;
        
    }
    
}
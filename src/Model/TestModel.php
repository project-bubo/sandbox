<?php

namespace Model;

/**
 * Description of TestModel
 *
 * @author toretak
 */
class TestModel extends BaseModel{

    
    
    
    public function treeNodeRandomGeneration($amount = 1000000, $parent = 0, $deep = 1, $url = 'root'){
        $values =  array();
        $pages = array();
        $urls = array();
        $alphabet = array_flip(range('a','z'));
        $this->connection->query('SET foreign_key_checks = 0');
        $allPages = $this->connection->fetchAll('SELECT T.[parent],P.[tree_node_id],P.[page_id],U.[url] FROM [cms_pages] P,[cms_page_tree] T,[cms_urls] U WHERE P.[page_id] = U.[page_id_] AND T.[tree_node_id] = P.[tree_node_id] AND P.[inherits_from] = %i',$deep-1);
        if($allPages && count($allPages) > 0){
            foreach($allPages as $page){
                $maxPageId = $this->connection->fetchSingle('SELECT MAX([page_id]) FROM [cms_pages]');
                $maxTreeNodeId = $this->connection->fetchSingle('SELECT MAX([tree_node_id]) FROM [cms_page_tree]');
                $values =  array();
                $pages = array();
                $urls = array();
                for($i=1;$i<=$amount;$i++){
                    $values[] = array(
                        'tree_node_id' => $maxTreeNodeId + $i,
                        'parent' => $page['tree_node_id'],
                        'sortorder' => $maxTreeNodeId + $i
                    );
                    $pages[] = array(
                        'page_id' => $maxPageId + $i,
                        'name' => 'BenQ'.($maxTreeNodeId + $i).'SaMSuNg',
                        'tree_node_id' => $maxTreeNodeId + $i,
                        'inherits_from' => $deep,
                        'lang' => 'cs',
                        'start_public%sql' => 'NOW()',
                        'entity' => 'page',
                        'version' => 1,
                    );
                    $urls[] = array(
                        'page_id_' => $maxPageId + $i,
                        'url' => $page['url'].'/'.implode('', array_rand($alphabet, 6))
                    );
                    
                }
                $this->connection->query("INSERT INTO [cms_page_tree] %ex",  $values);
                $this->connection->query("INSERT INTO [cms_pages] %ex",  $pages);
                $this->connection->query("INSERT INTO [cms_urls] %ex",  $urls);
            }
        }else{
            $maxPageId = $this->connection->fetchSingle('SELECT MAX([page_id]) FROM [cms_pages]');
            $maxTreeNodeId = $this->connection->fetchSingle('SELECT MAX([tree_node_id]) FROM [cms_page_tree]');

            for($i=1;$i<=$amount;$i++){
                $values[] = array(
                    'tree_node_id' => $maxTreeNodeId + $i,
                    'parent' => $parent,
                    'sortorder' => $maxTreeNodeId + $i
                );
                $pages[] = array(
                    'page_id' => $maxPageId + $i,
                    'name' => 'BenQ'.($maxTreeNodeId + $i).'SaMSuNg',
                    'tree_node_id' => $maxTreeNodeId + $i,
                    'inherits_from' => 0,
                    'lang' => 'cs',
                    'start_public%sql' => 'NOW()',
                    'entity' => 'page',
                    'version' => 1,
                );
                $urls[] = array(
                    'page_id_' => $maxPageId + $i,
                    'url' => $url.'/'.implode('',array_rand($alphabet, 6))
                );
            }
            $this->connection->query("INSERT INTO [cms_page_tree] %ex",  $values);
            $this->connection->query("INSERT INTO [cms_pages] %ex",  $pages);
            $this->connection->query("INSERT INTO [cms_urls] %ex",  $urls);
        }
        $this->connection->query('SET foreign_key_checks = 1');
    }
    
    
    
    public function getAllElementsOneByOne($stop = 1000000){
        for($i=1; $i <= $stop; $i++){
            $o = $this->connection->fetch("SELECT * FROM [cms_page_tree] WHERE [tree_node_id]=%i",$i);
        }
        return;
    }

    public function getAllElements(){
        $all = $this->connection->fetchAll("SELECT * FROM [cms_page_tree]");
        foreach($all as $val){
            $a = 1 + $val['parent'];
        }
        return;
    }

    
    ///
    
    public function generatePages($N = 2, $deep = 3){
        $deep--;
        //vytvori N stranek na nulte urovni
        $this->treeNodeRandomGeneration($N,0);
        //do hloubky $deep vytvori pro kazdu stranku $N stranek
        for($i = 1; $i <= $deep;$i++){
            $this->treeNodeRandomGeneration($N, NULL, $i);
        }
    }
    
    
    
    public function generateLabels($amount = 5){
        $values = array();
        $alphabet = array_flip(range('a','z'));
        $colors = array_flip(array('ff0000','00ff00','0000ff','f0f0f0','0f0f0f','ff00ff','00ffff','ffff00','ffff0','0ffff'));
        for($i = 1; $i <= $amount ; $i++){
            $values[] = array(
                'name' => implode('',array_rand($alphabet, 8)),
                'depth_of_recursion' => rand(0, 4),
                'is_global' => rand(0,1),
                'color' => array_rand($colors)
            );
        }
        $this->connection->query('INSERT INTO [cms_labels] %ex',$values);
        $this->applyLabels();
    }

    public function applyLabels(){
        $labels = $this->connection->fetchAll('SELECT * FROM [cms_labels]');
        foreach($labels as $label){
            //apply label
            $tid = $this->connection->fetchSingle('SELECT [tree_node_id] FROM [cms_pages] WHERE [inherits_from] = 0 ORDER BY RAND() LIMIT 1');
            $ins = $this->_applyLabel($label['label_id'], $tid, $label['depth_of_recursion']);
            $this->connection->query('INSERT INTO [cms_pages_labels] %ex', $ins);
        }
    }
    
    private function _applyLabel($labelId, $treeNodeId, $depth, $deep = 0){
        $vals = array();
        if($deep == 0){
            $vals[] = array(
                'tree_node_id' => $treeNodeId,
                'label_id' => $labelId,
                'active' => 'yes'
            );
        }
        if($deep < $depth){
            $children = $this->_getDescendants($treeNodeId);
            if($children){
                foreach($children as $child){
                    $vals[] = array(
                        'tree_node_id' => $child['tree_node_id'],
                        'label_id' => $labelId,
                        'active' => 'no'
                    );
                    $vals = array_merge($vals, $this->_applyLabel($labelId, $child['tree_node_id'], $depth + 1));
                }
            }
        }
        return $vals;
    }
    
    private function _getDescendants($treeNodeId){
        return $this->connection->fetchAll('SELECT [tree_node_id] FROM [cms_page_tree] WHERE [parent]=%i',$treeNodeId);
    }

    
    
    //cistic
    public function clearPagesAndLabels(){
        $this->connection->query('SET foreign_key_checks = 0');
        $this->connection->query('TRUNCATE TABLE [cms_pages]');
        $this->connection->query('TRUNCATE TABLE [cms_page_tree]');
        $this->connection->query('TRUNCATE TABLE [cms_urls]');
        $this->connection->query('TRUNCATE TABLE [cms_labels]');
        $this->connection->query('TRUNCATE TABLE [cms_pages_labels]');
        $this->connection->query('SET foreign_key_checks = 1');
    }
}
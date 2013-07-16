<?php

namespace Bubo\Services;

use Nette;

/**
 * Resource Manager
 *
 * @author Marek Juras
 */
class ResourceManager extends BaseService  {

    private $connection;
    private $modelLoader;
    
    private $resources = array();
    
    private $acl = array();
    private $aclTitles = array();
    
    public function __construct($connection, $modelLoader) {
        $this->connection = $connection;
        $this->modelLoader = $modelLoader;
    }
    
    /**
     * Add resource
     * ------------
     * 
     * Each resource is an array and must have following structure:
     * 
     * $resource = array(
     *                  'resource'	=>	array(
     *                                   'name'		=> 	'page',
     *                                   'title'	=>	'Page'
     *                                  ),
     * 
     *                  'privileges'	=>	array(
     *                                          'edit'	 	=>	'Edit page',
     *                                          'add'	 	=>	'Add page',
     *                                          'delete' 	=>	'Delete page'
	 *                                  )
     *              )
     * 
     * Resource in this format is then parsed to prepare names (into $this->acl)
     * and to prepare titles (into $this->aclTitles)
     * 
     * @param type $resource 
     */
    public function addResource($resource) {
        
        if (isset($resource['resource']['name'])) {
            $resourceName = $resource['resource']['name'];

            if (!in_array($resourceName, array_keys($this->resources))) {
                $this->resources[$resourceName] = $resource;

                $this->aclTitles[$resource['resource']['name']] = $resource['resource']['title'];
                foreach ($resource['privileges'] as $privilegeName => $privilegeTitle) {
                    $this->acl[$resourceName][$privilegeName] = FALSE;
                    $this->aclTitles[$resource['resource']['name'].':'.$privilegeName] = $privilegeTitle;
                }
            } else {
                throw new DuplicateResourceException("Resource with name '$resourceName' is already included in the resource list");
            }            
        } else {
            throw new BadResourceFormatException('Resource array has bad structure');
        }
        
    }
    
    
    public function getResources() {
        return $this->resources;
    }
    
    public function getAcl() {
        return $this->acl;
    }
        
    public function getAclTitles() {
        return $this->aclTitles;
    }
    
    public function detectResource($presenter) {
        $resource = NULL;
        $privilege = NULL;
        
        $presenterName = $presenter->getName();
        $action = $presenter->getAction();
        
        switch ($presenterName) {
            case 'Admin:Page':
                $treeNodeId = $presenter->getParam('id');
                
                $pageModel = $this->modelLoader->loadModel('PageModel');
                switch ($action) {
                    case 'add':
                        $resource = new \Models\Resources\Page(array('user_id' => 2, 'language_id' => '1', 'tree_node_id' => NULL));
                        $privilege = 'add';
                        break;
                    case 'default':                        
                        if (!empty($treeNodeId)) {                            
                            $resource = new \Model\Resources\Page($presenter->pageManagerService->getPage($treeNodeId));
                            $privilege = 'edit';                            
                        }
                        break;
                    case 'delete':
                        $privilege = 'delete';
                        break;
                }
                break;
            case 'Admin:Plugin':
                $plugin = $presenter->getParam('plugin');
                $view = $presenter->getParam('view');

                switch ($action) {
                    case 'interpret':
                        $resource = $presenter->plugins[$plugin]['instance'];
                        $privilege = 'view'.ucfirst($view);
                        break;
                }

        }
        
        $return = array(
                    'resource'  =>  $resource, 
                    'privilege' =>  $privilege
                    );

        
        return $resource ? $return : NULL;
    }

}

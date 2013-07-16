<?php

namespace AdminModule\Forms;

use Nette\Application\UI\Form;


class BaseForm extends Form {
    
    public $modelLoader;
    
    public function __construct($parent, $name){
        parent::__construct($parent, $name);
        $this->modelLoader = $this->getPresenter()->context->modelLoader;
        
        $this->getElementPrototype()
                            ->novalidate('novalidate')
                            ->class[] = 'form-with-smooth-buttons';
        
        $renderer = $this->getRenderer();
//        $renderer->wrappers['controls']['container'] = 'dl';
//        $renderer->wrappers['pair']['container'] = NULL;
//        $renderer->wrappers['label']['container'] = 'dt';
//        $renderer->wrappers['control']['container'] = 'dd';
    }
    
    
    /**
     * Check url uniqueness
     * 
     * $item   - url_chunk
     * $arg[0] - tree_node_id (form item)
     * $arg[1] - parent (form item)
     * $arg[2] - publish submit button (form item)
     * 
     * The goal is to check whether url (constructed from
     * url_chunk and url of the parent) is not among "published" urls.
     * 
     * Published url is such url, that is joined to "published" page.
     * 
     * Tree_node_id is identifying the page version thread:
     * D-->P-->D-->D-->D-->D-->P-->D
     * 
     * If tree_node_id is not null, then "published" urls from
     * page version thread are also ALLOWED urls
     * (must be excluded from uniqueness check)
     * 
     * @param type $item
     * @param type $arg 
     */
    public function urlUniqueValidator($item, $arg) { 
        
        if ($arg[2]->isSubmittedBy()) {
            $urlChunk = $item->value;
            $treeNodeId = $arg[0]->value;
            $parentTreeNodeId = $arg[1]->value;

            $mode = empty($treeNodeId) ? 'add' : 'edit';

            // construct the url:
            // - get url of the parent & append url_chunk
            $parentUrl = '';
            if ($parentTreeNodeId > 2)
                $parentUrl = $this->presenter->getModelPage()->getUrlByTreeNodeId($parentTreeNodeId);
            
            
            
            $url = $parentUrl . '/' . $urlChunk;

            $allowedPublishedIds = ($mode == 'edit') ?
                                        $this->presenter->getModelPage()->getPublishedPageIds($treeNodeId) : array();


            return $this->presenter->getModelPage()->isUrlUnique($url, $allowedPublishedIds);        
        } else {
            return TRUE;
        }
        
        
    }
    
    /**
     * Check url uniqueness for all pages
     * 
     * $item   - submit button (blind target)
     * $arg    - form
     * 
     * For each lang section, following tasks need to be done.
     * 
     * The goal is to check whether url (constructed from
     * url_chunk and url of the parent) is not among "published" urls.
     * 
     * Published url is such url, that is joined to "published" page.
     * 
     * Tree_node_id is identifying the page version thread:
     * D-->P-->D-->D-->D-->D-->P-->D
     * 
     * If tree_node_id is not null, then "published" urls from
     * page version thread are also ALLOWED urls
     * (must be excluded from uniqueness check)
     * 
     * @param type $item
     * @param type $arg 
     */
    public function newUrlUniqueValidator($item, $arg) { 
//        dump($arg->getValues());
        
        $validAllUrls = TRUE;
        
        $values = $arg->getValues();
        
        $langVersions = $values['lang_versions'];
        $whatToPublish = $values['what_to_publish'];
        
        
        foreach ($langVersions as $langCode => $publish) {
            
            // is this page marked as published??
            $markedAsPublished = FALSE;
            if ($whatToPublish == 'all' || ($whatToPublish == 'selected') &&  $publish) {
                $markedAsPublished = TRUE;
            }

            if ($markedAsPublished) {

                    $urlChunk = $values[$langCode]['url_chunk'];
                    $treeNodeId = $values[$langCode]['tree_node_id'];
                    $parentTreeNodeId = $values[$langCode]['parent'];

                    $mode = empty($treeNodeId) ? 'add' : 'edit';

                    // construct the url:
                    // - get url of the parent & append url_chunk
                    $parentUrl = $this->presenter->context->pageManager->getPage($parentTreeNodeId)->getUrl();
                    
                    //$parentUrl = $this->presenter->getModelPage()->getUrlByTreeNodeId($parentTreeNodeId);

                    $url = $parentUrl . '/' . $urlChunk;
                
                    $allowedPublishedIds = ($mode == 'edit') ?
                                        $this->presenter->getModelPage()->getPublishedPageIds($treeNodeId) : array();
                    
                    $validAllUrls &= $this->presenter->getModelPage()->isUrlUnique($url, $allowedPublishedIds);
                    
                    if (!$validAllUrls) {
                        $item->addError('Chybné URL ve stránce '.$langCode);
                        return FALSE;
                    }
                    
            }
            
        }
        
        
        return $validAllUrls;
        
        
    }
    
    
    public function atLeastOneCheckBoxChecked($item, $arg) {
        // only $arg is importat -> contains all elements
        $bool = FALSE;
        foreach ($arg->getComponents() as $componentName => $component) {
            $bool |= $component->value;
            if ($bool) break;
        }
        return $bool;
    }
    
    public function loginUniqueValidator($item, $args) {
        return $this->presenter->getModelUser()->isLoginUnique($item->value, $args['userId']);
        
    }
    
    
    /**
     * Set default overriden from Nette\Container
     * enhanced with unserialization feature
     * 
     * @param type $values
     * @param type $erase
     * @return \AdminModule\Components\Forms\BaseForm
     */
    public function setDefaults($values, $erase = FALSE) {
//        dump($values);
		$form = $this->getForm(FALSE);
		if (!$form || !$form->isAnchored() || !$form->isSubmitted()) {
            
            if (!empty($values)) {
                $values = \Utils\MultiValues::unserializeArray($values);
            }
//            dump($values);
            $myValues = $values;
            foreach ($values as $lang => $data) {
                if (is_array($data) && isset($data['ext_values'])) {
                    foreach ($data['ext_values'] as $k => $v) {
                        $myValues[$lang]['ext_'.$k] = $v;
                    }
                }
            }
//            dump($myValues);
//            die();
			$this->setValues($myValues, $erase);
		}
        
		return $this;
	}
    
    
    
    public function draftNotInFuture($item, $args) {
        $values = $args->getValues();
//        dump($values);
        $draftNotInFuture = TRUE;
        foreach ($values['lang_versions'] as $langCode => $selected) {
            $status = NULL;
            $name = $values[$langCode]['name'];
            switch ($values['what_to_publish']) {
                case 'none': 
                    $status = 'draft';
                    break;
                case 'selected':
                    $status = $selected ? 'published' : 'draft';
                    break;
                case 'all':
                    $status = 'published';
            }
            
//            dump($name);
            if ($status == 'draft' && !empty($name) && isset($values[$langCode]['start_public'])) {
                $draftNotInFuture &= $values[$langCode]['start_public'] ? strtotime($values[$langCode]['start_public']) <= time() : TRUE;
            } else {
                $draftNotInFuture &= TRUE;
            }
            
        }
//        dump($draftNotInFuture);
//        die();
        
        return $draftNotInFuture;        
    }
    
    public function atLeastOnePageFilled($item, $args) {
        $values = $args->getValues();

        $atLeastOnePageFilled = FALSE;
        foreach ($values['lang_versions'] as $langCode => $selected) {
            $name = $values[$langCode]['name'];
            
            $atLeastOnePageFilled |= !empty($name);
            
        }
//        dump($draftNotInFuture);
//        die();
        
        return $atLeastOnePageFilled;        
    }
}


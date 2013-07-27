<?php

namespace Bubo\Navigation;

use Bubo,
    Nette\Caching\Cache;

abstract class PageMenu extends Bubo\Components\RegisteredControl {

    private $renderer;
    private $labelName;

    private $parentPage;    //??
    private $lang;

    private $cacheTags;
    private $cachingEnabled;

    public function __construct($parent, $name, $lang = NULL) {
        parent::__construct($parent, $name);
        $this->lang = $lang;
        $this->renderer = new Rendering\PageMenuRenderer();
        $this->cachingEnabled = TRUE;
    }

    public function createLabelTraverser() {
        return $this->presenter->traverserFactoryService->createLabelTraverser($this);
    }

    public function setLabelName($labelName) {
        $this->labelName = $labelName;
        return $this;
    }

    public function addCacheTag($tag) {
        $this->cacheTags[] = $tag;
        return $this;
    }

    public function disableCaching() {
        $this->cachingEnabled = FALSE;
    }

    public function getLabelName() {
        return $this->labelName;
    }

    public function setLang($lang) {
        $this->lang = $lang;
    }

    public function getLang() {
        return $this->lang;
    }

    public function getTraverser() {
        return NULL;
    }

    public function getRenderer() {
        return $this->renderer;
    }

    public function setUpRenderer($renderer) {
        return $renderer;
    }

    public function getParentPage() {
        return $this->parentPage;
    }

    /**
     * Render page menu
     *
     * - $page determines the branch of labeled forest
     * - $useCurrentPageAsLabelRoot
     *      - if set to FALSE (default behaviour) the label root is used as a
     *        root of traversing
     *      - if is set to TRUE - the $page is used as the root of traversing
     *        even if the $page is labelled passivelly
     *
     * - $ignorePage (use case MaxPraga shopMenu)
     *
     * @param type $page
     * @param type $useCurrentPageAsLabelRoot
     * @param type $ignorePage
     */
    public function render($page = NULL, $useCurrentPageAsLabelRoot = FALSE, $ignorePage = FALSE) {

        //\SimpleProfiler\Profiler::advancedTimer();

        $this->parentPage = $page;

        $traverser = $this->getTraverser();
        //$traverser->preprareRoots();

        $doCaching = FALSE;




        if (isset($this->presenter->page)) {
            $doCaching = TRUE;
        }

        $doCaching = $doCaching && $this->cachingEnabled;


        $cacheKey = NULL;

        if ($doCaching) {
            $cacheKey = $this->presenter->page->getModuleCacheId($this->name);
            if ($traverser->isHighlighted()) {
                $cacheKey = $this->presenter->page->getPageCacheId($this->name);
            }
        }

        $cache = new \Nette\Caching\Cache($this->presenter->context->cacheStorage, 'Bubo.PageMenus');
        $val = $cache->load($cacheKey);

        if (!$doCaching) {
            $val = NULL;
        }

        if ($val === NULL) {

            $val = $traverser ? $traverser->setRenderer($this->setUpRenderer($this->renderer))
                                            ->setUpSpecifiedRoot($page, $useCurrentPageAsLabelRoot, $ignorePage)
                                            ->traverse() : '';


            if ($doCaching) {
                $this->cacheTags[] = 'labels/'.$traverser->label['nicename'];

                $dp = array(
                        Cache::TAGS => $this->cacheTags
                );

                $cache->save($cacheKey, $val->__toString(), $dp);
            }
         }

        echo $val;


        //\SimpleProfiler\Profiler::advancedTimer($this->reflection->shortName);


    }

}

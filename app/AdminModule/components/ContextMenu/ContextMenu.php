<?php

namespace BuboApp\AdminModule\Components;

use Nette\Application\UI\Control;

/**
 * Context menu component
 * ----------------------
 *
 * This components are maintained under the following naming conventions:
 *
 * <className>_<menuSelectorId>_<snippetName>
 *
 *
 */
abstract class ContextMenu extends \Bubo\Components\RegisteredControl {

    /** @persistent */
    public $treeNodeId = 0;


    protected $top = 0;
    protected $left = 0;
    protected $menuId = "vmenu";
    protected $snippetName = "";
    protected $visible = FALSE;

    public function __construct($parent, $name, $menuId, $snippetName) {
        parent::__construct($parent, $name);
        $this->menuId = $menuId;
        $this->snippetName = $snippetName;

    }

    public function setPosition($posX, $posY) {
        $this->top = $posY;
        $this->left = $posX;
        return $this;
    }

    public function setParams($params) {
        $this->treeNodeId = isset($params[0]) ? (int) $params[0] : 0;
        return $this;
    }

    public function show() {
        $this->visible = TRUE;
        return $this;
    }

    public function hide() {
        $this->visible = FALSE;
        return $this;
    }

    public function initTemplate($templateFile) {
        $template = parent::initTemplate($templateFile);

        $template->top = $this->top;
        $template->left = $this->left;
        $template->display = $this->visible ? 'block' : 'none';

        $template->menuId = $this->menuId;
        // nema cenu posilat do sablony snippetName,
        // protoze se musi v sablone vykresit staticky

        return $template;
    }




}

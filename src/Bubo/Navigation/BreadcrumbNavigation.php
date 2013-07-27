<?php

namespace Bubo\Navigation;

use Bubo;

abstract class BreadcrumbNavigation extends \Bubo\Components\RegisteredControl {
    
    public function __construct($parent, $name) {
        parent::__construct($parent, $name);
    }
    
}

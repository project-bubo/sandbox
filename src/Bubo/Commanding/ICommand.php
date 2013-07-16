<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Bubo\Commanding;
/**
 *
 * @author jurasm2
 */
interface ICommand {
    
    public function setUpCommander($commander);
    
    public function getTraverser();
    
    public function execute();
    
}

?>

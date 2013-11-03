<?php

namespace BuboApp\AdminModule\DataGrids;

abstract class BaseDataGrid extends \DataGrid\DataGrid {
    
    /** parent presenter */
    public $parentPresenter;
   
    /** connection */
    public $connection;
    
    /* model loader */
    public $modelLoader;
    
    public function __construct($parentPresenter) {
        parent::__construct();
        $this->parentPresenter = $parentPresenter;       
        $this->connection = $parentPresenter->context->database;
        
        // load model loader
        $this->modelLoader = $parentPresenter->context->modelLoader;
    }
    
 
    /** 
     * Some helpers
     */
    
    public static function convertBytes($bytes) {
        $size = $bytes / 1024;
        if ($size < 1024) {
            $size = number_format($size, 2);
            $size .= ' KB';
        } else {
            if ($size / 1024 < 1024) {
                $size = number_format($size / 1024, 2);
                $size .= ' MB';
            } else if ($size / 1024 / 1024 < 1024) {
                $size = number_format($size / 1024 / 1024, 2);
                $size .= ' GB';
            }
        }
        return $size;
    }
    
    public static function formatDateTime($value, $defaultValue = NULL, $customFormat = 'j.n.Y H:i') {
        $retValue = is_null($defaultValue) ? '-' : $defaultValue;
        if (!empty($value)) {
            $date = new \DateTime($value);
            $retValue = $date->format($customFormat);
        }
        return $retValue;
    }
    
    public static function formatDuration($duration) {
        $percent = 100;
        $time = $duration * $percent / 100;
        $time = intval($time / 3600) . ":" . intval(($time - (intval($time / 3600) * 3600)) / 60) . ":" . sprintf("%01.1f", ($time - (intval($time / 60) * 60)));
        
        return $time.'s';
    }
    
}
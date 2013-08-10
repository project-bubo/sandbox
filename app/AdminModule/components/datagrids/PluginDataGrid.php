<?php

namespace BuboApp\AdminModule\DataGrids;


final class PluginDataGrid extends BaseDataGrid {

    public function __construct($parentPresenter) {
        parent::__construct($parentPresenter);
        
      
        // Create a query
        $ds = $this->connection->dataSource("SELECT 
                                                *
                                                FROM
                                                 [:core:plugins] 
                                            ");


        // Create a data source
        $dataSource = new \DataGrid\DataSources\Dibi\DataSource($ds);

        // Configure data grid

        $this->setDataSource($dataSource);

        // Configure columns
        //$this->addNumericColumn('id', 'ID')->getHeaderPrototype()->style('width: 50px');
        
        $this->addColumn('plugin_id', 'ID');
        $this->addColumn('name', 'Název pluginu');
        $this->addColumn('status', 'Status');
        $this->addColumn('version', 'Verze');
        
        
        $this->keyName = 'plugin_id';
        $this->addActionColumn('Actions');

        $icon = \Nette\Utils\Html::el('span');
        
        $this->addAction('Nainstalovat', 'pluginConfirmDialog:confirmInstall!', clone $icon->class('icon icon-install')->setText('Nainstalovat'), TRUE);
        $this->addAction('Odinstalovat', 'pluginConfirmDialog:confirmUninstall!', clone $icon->class('icon icon-uninstall')->setText('Odinstalovat'), TRUE);

        //$this->addAction('Delete', 'movieConfirmDialog:confirmDelete!', clone $icon->class('icon icon-del')->setText('Smazat video'), TRUE);
    }

    
}
<?php

namespace BuboApp\AdminModule\DataGrids;


final class PageHistoryDataGrid extends BaseDataGrid {

    public function __construct($parentPresenter) {
        parent::__construct($parentPresenter);
        
      
        $treeNodeId = $parentPresenter->getParam('id');
        
        $actualPage = $parentPresenter->getModelPage()->getActualPage($treeNodeId);
        
        // Create a query
        $ds = $this->connection->dataSource("SELECT 
                                                * 
                                                FROM 
                                                    [:core:pages] 
                                                JOIN
                                                    [:core:users]
                                                USING 
                                                    ([user_id])
                                                WHERE 
                                                    [tree_node_id] = %i 
                                                ORDER BY 
                                                    [created] DESC 
                                            ", $treeNodeId);


        // Create a data source
        $dataSource = new \DataGrid\DataSources\Dibi\DataSource($ds);

        // Configure data grid

        $this->setDataSource($dataSource);

        // Configure columns
        //$this->addNumericColumn('id', 'ID')->getHeaderPrototype()->style('width: 50px');
        
        $this->addColumn('version', 'Číslo verze');
        $this->addColumn('status', 'Status');
        $this->addColumn('created', 'Datum vytvoření');
        $this->addColumn('login', 'Autor');
        
        $this->keyName = 'page_id';
        $this->addActionColumn('Actions');

        $icon = \Nette\Utils\Html::el('span');
        
        $this->addAction('Vrátit ze zálohy', 'backupConfirmDialog:confirmBackup!', clone $icon->class('icon icon-reload-from-backup')->setText('Vrátit ze zálohy'), TRUE);

        //$this->addAction('Delete', 'movieConfirmDialog:confirmDelete!', clone $icon->class('icon icon-del')->setText('Smazat video'), TRUE);
    }

    
}
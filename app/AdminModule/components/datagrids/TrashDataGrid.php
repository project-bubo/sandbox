<?php

namespace BuboApp\AdminModule\DataGrids;


final class TrashDataGrid extends BaseDataGrid {

    public function __construct($parentPresenter) {
        parent::__construct($parentPresenter);
        
                
        // Create a query
        $ds = $this->connection->dataSource('SELECT 
                                                p.*
                                                FROM
                                                    (
                                                        SELECT 
                                                            tree_node_id, MAX(version) version
                                                        FROM 
                                                            cms_pages
                                                        GROUP BY 
                                                            tree_node_id
                                                    ) m
                                                INNER JOIN 
                                                    cms_pages p 
                                                ON 
                                                    p.tree_node_id = m.tree_node_id 
                                                        AND
                                                    p.version=m.version
                                                    AND status = %s
                                                ', 'trashed');


        // Create a data source
        $dataSource = new \DataGrid\DataSources\Dibi\DataSource($ds);

        // Configure data grid

        $this->setDataSource($dataSource);

        // Configure columns
        //$this->addNumericColumn('id', 'ID')->getHeaderPrototype()->style('width: 50px');
        
        $this->addColumn('page_id', 'ID');
        $this->addColumn('name', 'Název');
        $this->addDateColumn('created', 'Datum', '%H:%M %d.%m.%Y');
      
        
        $this->keyName = 'page_id';
        $this->addActionColumn('Actions');

        $icon = \Nette\Utils\Html::el('span');
        
        
        $this->addAction('Obnovit', 'trashConfirmDialog:confirmRestore!', clone $icon->class('icon icon-reload-from-backup')->setText('Obnovit'), TRUE);
        $this->addAction('Delete', 'trashConfirmDialog:confirmDelete!', clone $icon->class('icon icon-del')->setText('Smazat stránku'), TRUE);
    }

    
}
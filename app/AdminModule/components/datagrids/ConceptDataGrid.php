<?php

namespace AdminModule\DataGrids;


final class ConceptDataGrid extends BaseDataGrid {

    public function __construct($parentPresenter) {
        parent::__construct($parentPresenter);
        
                
        // Create a query
        $ds = $this->connection->dataSource("SELECT 
                                                *
                                                FROM
                                                 [:core:pages_autosave]
                                                WHERE
                                                 [editor_id] = %i
                                                AND 
                                                 [tree_node_id] IS NULL
                                            ", $parentPresenter->userId);


        // Create a data source
        $dataSource = new \DataGrid\DataSources\Dibi\DataSource($ds);

        // Configure data grid

        $this->setDataSource($dataSource);

        // Configure columns
        //$this->addNumericColumn('id', 'ID')->getHeaderPrototype()->style('width: 50px');
        
        $this->addColumn('autosave_id', 'ID');
        $this->addDateColumn('saved', 'Datum', '%H:%M %d.%m.%Y');
      
        
        $this->keyName = 'autosave_id';
        $this->addActionColumn('Actions');

        $icon = \Nette\Utils\Html::el('span');
        
        
        $this->addAction('Pokračovat v práci', 'add', clone $icon->class('icon icon-pencil')->setText('Pokračovat v práci'));
        $this->addAction('Delete', 'conceptConfirmDialog:confirmDelete!', clone $icon->class('icon icon-del')->setText('Smazat koncept'), TRUE);
    }

    
}
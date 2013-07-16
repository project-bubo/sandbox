<?php

namespace AdminModule\DataGrids;


final class ExtDataGrid extends BaseDataGrid {

    public function __construct($parentPresenter) {
        parent::__construct($parentPresenter);
        
      
        $labelId = $parentPresenter->labelId;
        
        //$actualPage = $parentPresenter->pageManagerService->getLabel($labelId);
        
        // Create a query
        $ds = $this->connection->dataSource("SELECT 
                                                * 
                                                FROM 
                                                    [:core:label_ext_definitions] 
                                                WHERE 
                                                    [label_id] = %i 
                                            ", $labelId);


        // Create a data source
        $dataSource = new \DataGrid\DataSources\Dibi\DataSource($ds);

        // Configure data grid

        $this->setDataSource($dataSource);

        // Configure columns
        //$this->addNumericColumn('id', 'ID')->getHeaderPrototype()->style('width: 50px');
        
        $this->addColumn('title', 'Titulek');
        $this->addColumn('identifier', 'Identifikátor');
        $this->addColumn('name', 'Jméno (konfigurační)');
//        $this->addColumn('created', 'Datum vytvoření');
//        $this->addColumn('login', 'Autor');
        
        $this->keyName = 'ext_id';
        $this->addActionColumn('Actions');

        $icon = \Nette\Utils\Html::el('span');
        
        $this->addAction('Editovat', 'Label:editExt', clone $icon->class('icon icon-edit')->setText('Editovat'));

        $this->addAction('Delete', 'extConfirmDialog:confirmDelete!', clone $icon->class('icon icon-del')->setText('Smazat rozšíření'), TRUE);

    }

    
}
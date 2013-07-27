<?php

namespace AdminModule\DataGrids;


final class LanguageDataGrid extends BaseDataGrid {

    public function __construct($parentPresenter) {
        parent::__construct($parentPresenter);
                
        // Create a query
        $ds = $this->connection->dataSource("SELECT 
                                                * 
                                                FROM 
                                                    [:core:languages] 
                                            ");


        // Create a data source
        $dataSource = new \DataGrid\DataSources\Dibi\DataSource($ds);

        // Configure data grid

        $this->setDataSource($dataSource);

        // Configure columns
        //$this->addNumericColumn('id', 'ID')->getHeaderPrototype()->style('width: 50px');
        
        $this->addColumn('name', 'Jazyk');
        $this->addColumn('code', 'Kód');
        $this->addColumn('enabled', 'Povolen');
        
        $this->keyName = 'language_id';
        $this->addActionColumn('Actions');

        $icon = \Nette\Utils\Html::el('span');
        
        $this->addAction('Nainstalovat', 'languageConfirmDialog:confirmInstall!', clone $icon->class('icon icon-install')->setText('Nainstalovat'), TRUE);
        $this->addAction('Odinstalovat', 'languageConfirmDialog:confirmUninstall!', clone $icon->class('icon icon-uninstall')->setText('Odinstalovat'), TRUE);

        //$this->addAction('Delete', 'movieConfirmDialog:confirmDelete!', clone $icon->class('icon icon-del')->setText('Smazat video'), TRUE);
    }

    
}
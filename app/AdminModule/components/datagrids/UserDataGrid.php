<?php

namespace BuboApp\AdminModule\DataGrids;


final class UserDataGrid extends BaseDataGrid {

    public function __construct($parentPresenter) {
        parent::__construct($parentPresenter);
        
                
        // Create a query
        $ds = $this->connection->dataSource("SELECT 
                                                *
                                                FROM
                                                 [:core:users]
                                            ");


        // Create a data source
        $dataSource = new \DataGrid\DataSources\Dibi\DataSource($ds);

        // Configure data grid

        $this->setDataSource($dataSource);

        // Configure columns
        //$this->addNumericColumn('id', 'ID')->getHeaderPrototype()->style('width: 50px');
        
        $this->addColumn('user_id', 'ID');
        $this->addColumn('login', 'Login');
        $this->addColumn('email', 'E-mail');
        

        $this->keyName = 'user_id';
        $this->addActionColumn('Actions');

        $icon = \Nette\Utils\Html::el('span');

        $this->addAction('Nastavit přístupy', 'setAcl', clone $icon->class('icon icon-key')->setText('Nastavit přístupy'));
        $this->addAction('Upravit uživatele', 'edit', clone $icon->class('icon icon-edit')->setText('Upravit uživatele'));
        //$this->addAction('Delete', 'conceptConfirmDialog:confirmDelete!', clone $icon->class('icon icon-del')->setText('Smazat koncept'), TRUE);
    }

    
}
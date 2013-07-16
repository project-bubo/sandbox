<?php

namespace AdminModule\DataGrids;


final class ExtParamDataGrid extends BaseDataGrid {

    public function __construct($parentPresenter) {
        parent::__construct($parentPresenter);
        
      
        $treeNodeId = $parentPresenter->getParam('id');
        $identifier = $parentPresenter->getParam('identifier');
        $defaultLang = $parentPresenter->langManagerService->getDefaultLanguage();
      
        $where = array(
                    'tree_node_id'  =>  $treeNodeId,
                    'identifier'    =>  $identifier,
                    'lang'          =>  $defaultLang
        );
        

//        dump($defaultLang);
//        die();
        //$actualPage = $parentPresenter->pageManagerService->getLabel($labelId);
        
        // Create a query
        $ds = $this->connection->dataSource("SELECT 
                                                [t].[ext_tree_node_id],
                                                [t].[identifier],
                                                [t].[tree_node_id],
                                                [p].[param_name]
                                                FROM 
                                                    [:core:ext_tree] [t]
                                                JOIN
                                                    [:core:ext_params] [p]
                                                USING
                                                    ([ext_tree_node_id])
                                                WHERE 
                                                    %and
                                            ", $where);


        // Create a data source
        $dataSource = new \DataGrid\DataSources\Dibi\DataSource($ds);

        // Configure data grid

        $this->setDataSource($dataSource);

        // Configure columns
        //$this->addNumericColumn('id', 'ID')->getHeaderPrototype()->style('width: 50px');
        
        $this->addColumn('param_name', 'Parametr [' . $defaultLang . ']');
        $this->addColumn('identifier', 'Identifikátor');
//        $this->addColumn('created', 'Datum vytvoření');
//        $this->addColumn('login', 'Autor');
        
        $this->keyName = 'ext_tree_node_id';
        $this->addActionColumn('Actions');

        $icon = \Nette\Utils\Html::el('span');
        
        $this->addAction('Editovat', 'ExtParam:editParam', clone $icon->class('icon icon-edit')->setText('Editovat'));

        $this->addAction('Delete', 'extParamConfirmDialog:confirmDelete!', clone $icon->class('icon icon-del')->setText('Smazat parametr'), TRUE);

    }

    
}
<?php

namespace BuboApp\AdminModule;

class RepairPresenter extends BasePresenter {

    public function actionRepair() {

        // create cs lang root
        $data = array(
                    'parent'    =>  1
        );

        $this->context->database->query('INSERT INTO [:core:page_tree]', $data);

        $langRootId = $this->context->database->getInsertId();


        // relocate pages connected to master root

        if (!empty($langRootId)) {
            $this->context->database->query('UPDATE [:core:page_tree] SET [parent] = %i WHERE [parent] = 1 AND [tree_node_id] != %i', $langRootId, $langRootId);
        }

        die();
    }


}

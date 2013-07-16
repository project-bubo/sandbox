<?php

namespace Model;

final class PluginModel extends BaseModel {

    public function getPlugins() {
        return $this->connection->fetchAll('SELECT * FROM [:core:plugins]');
    }
    
   

}
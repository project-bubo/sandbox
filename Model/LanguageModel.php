<?php

namespace Model;

final class LanguageModel extends BaseModel {


    public function installLanguage($languageId) {
        $data = array(
                    'enabled' => 1
        );
        
        $this->connection->query('UPDATE [:core:languages] SET', $data, 'WHERE [language_id] = %i', $languageId);
    }
    
    
    public function uninstallLanguage($languageId) {
        $data = array(
                    'enabled' => 0
        );
        
        $this->connection->query('UPDATE [:core:languages] SET', $data, 'WHERE [language_id] = %i', $languageId);
    }
    
    public function getLanguage($languageId) {
        return $this->connection->fetch('SELECT * FROM [:core:languages] WHERE [language_id] = %i', $languageId);
    }
    
    public function getInstalledLanguages($assocBy = 'code') {
        return $this->connection->query('SELECT * FROM [:core:languages] WHERE [enabled] = 1')->fetchAssoc($assocBy);
    }
}
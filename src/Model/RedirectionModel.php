<?php

namespace Model;

final class RedirectionModel extends BaseModel {


    /**
     * Maintenance of tree of redirections
     * -----------------------------------
     * 
     * Management of table [:core:redirections]
     * Adding a redirection is done as follows:
     * 
     * 1) Find the page couple
     *    Page $toPageId always has a status 'published' but $fromPageId
     *    can be 'draft' or 'trashed'. Page version thread must be traversed
     *    backwards to find closest published page (Fig1)
     *                  
     *         /--> closest published page
     *        /       
     *   D-->P-->D-->D-->D-->D-->D-->P
     *                          /   /   
     *          $fromPageId <--´   /
     *           $toPageId  <-----´
     *   
     *   Fig1: Page versions thread 
     * 
     * 2) Url retrieval
     *    Both page id's are now keys to retrieve pages' urls from [:core:urls]
     * 
     * 3) Creating redirection
     *    When retrieved urls differ, new record is added to table [:core:redirections]
     *    Let's call fromUrls as HEADs and toUrls as TAILs.
     *    Then
     *      - find all records with TAIL = just inserted HEAD
     *        and update the TAILs with just inserted TAIL
     *      - is is possible that closed (single) loops may be created,
     *        but it is easy to detect then and delete them
     *        Closed (single) loops have HEAD = TAIL
     *    
     * 
     * @param type $fromUrl
     * @param type $toUrl 
     */
    public function addRedirection($fromPageId, $toPageId) {
        /* STEP 1 */
        $closestPage = $this->_findClosestPageVersion($fromPageId, 'published');

        /* STEP 2 */
        $sourceUrl = $this->getModelPage()->retrieveUrl($closestPage->page_id);
        $targetUrl = $this->getModelPage()->retrieveUrl($toPageId);

        /* STEP 3 */
        if ($sourceUrl != $targetUrl) {
            return $this->_insertRedirection($sourceUrl, $targetUrl);
        }
        
        return NULL;
    }
    
    private function _findClosestPageVersion($pageId, $status = 'draft') {
        $closestPage = $this->getModelPage()->getPage($pageId);
        
        if ($closestPage->status != $status) {
            $closestPage = $this->connection->fetch('SELECT * FROM [:core:pages] WHERE [status] = %s AND [created] > %s LIMIT 1', $status, $closestPage->created);
        }

        return $closestPage;        
    }
    
    private function _insertRedirection($sourceUrl, $targetUrl) {
        $skip = $this->connection->fetch('SELECT * FROM [:core:redirections] WHERE [from_url] = %s', $targetUrl);
        
        $skippingUrl = $targetUrl;
        if ($skip) {
            $skippingUrl = $skip['to_url'];
        }
        
        $data = array(
                    'from_url'  =>  $sourceUrl,
                    'to_url'    =>  $skippingUrl
        );
        
        $res = $this->connection->query('INSERT INTO [:core:redirections]', $data);
        $this->connection->query('UPDATE [:core:redirections] SET [to_url] = %s WHERE [to_url] = %s', $targetUrl, $sourceUrl);
        $this->connection->query('DELETE FROM [:core:redirections] WHERE [from_url] = [to_url]');
        
        return $res;
        
    }
    
}
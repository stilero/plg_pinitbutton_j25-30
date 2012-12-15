<?php
/**
 * JArticle_Class
 *
 * @version  1.0
 * @package Stilero
 * @subpackage JArticle_Class
 * @author Daniel Eliasson (joomla@stilero.com)
 * @copyright  (C) 2012-nov-29 Stilero Webdesign (www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class PIBK2JArticle extends PIBJArticle{
    
    public function __construct($article) {
        parent::__construct($article);
    }
    
    protected function buildQuery($select, $from, $whereCol, $whereVal){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($select);
        $query->from($db->nameQuote($from));
        $query->where($whereCol.'='.$db->quote($whereVal));
        return $query;
    }
    
    protected function fetchCategoryTitle($catid){
        $db = JFactory::getDbo();
        $query = $this->buildQuery('name', '#__k2_categories', 'id', $catid);
        $db->setQuery($query);
        $result = $db->loadObject();
        if(!$result){
            return;
        }
        return $result->name;
    }
    
    public function categoryTitle($article){
        if(isset($article->category->name)){
            return $article->category->name;
        }
        $catTitle = $this->fetchCategoryTitle($article->catid);
        if($catTitle){
            return $catTitle;
        }else{
            $this->setError(self::$ERROR_NO_CATEGORY, 'No Category specified');
            return '';
        }
    }
    
    public function isPublished($article){
        $isPublished = $article->published == $this->STATE_PUBLISHED ? true : false;
        if(!$isPublished){
            return FALSE;
        }
        $publishUp = isset($article->publish_up) ? $article->publish_up : '';
        $publishDown = isset($article->publish_down) ? $article->publish_down : '';
        if($publishUp == '' ){
            return false;
        }
        $now = $this->getCurrentDate();
        if ( ($publishUp > $now) ){
            return FALSE;
        }else if($publishDown < $now && $publishDown != '0000-00-00 00:00:00' && $publishDown!=""){
            return FALSE;
        }else {
            return TRUE;
        }
    }
    
    public function isArticle(){
        $id = JRequest::getInt('id');
        $cid = JRequest::getInt('cid');
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');
        $hasID = is_int($id) ? true : false;
        $hasCID = is_int($cid) ? true : false;
        $isK2 = $option == 'com_k2' ? true : false;
        $isView = $view == 'item' ? true : false;
        if( ($hasID || $hasCID) && $isK2 && $isView ){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    public function tags($article) {
        $db = &JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('name');
        $query->from('#__k2_tags AS t');
        $query->innerJoin('#__k2_tags_xref AS x ON x.tagID = t.id');
        $query->where('x.itemID = ' . $db->Quote($article->id));
        $db->setQuery($query);
        $tags=  $db->loadResultArray ();
        return $tags;
    }
}
<?php
/**
 * JArticle_Class
 * A class for errors
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

class PIBJArticleErrors{
    
    public $errors = array();
    public static $ERROR_NO_CATEGORY = '1';
    
    public function setError($errorCode, $errorMessage){
        $this->errors[] = array($errorCode => $errorMessage);
    }
    
    public function hasError(){
        if(!empty($this->errors)){
            return true;
        }
        return false;
    }
    
    public function __get($name) {
        return $this->$name;
    }
}

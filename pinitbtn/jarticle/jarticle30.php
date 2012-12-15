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

class PIBJArticle30 extends PIBJArticle{
    
    public static $JOOMLA_VERSION = '3.0';
    public static $ACCESS_GUEST = '5';
    
    public function __construct($article) {
        parent::__construct($article);
    }
}

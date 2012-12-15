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

class PIBJArticleUrl{
    
    protected $article;
    protected $routerPath = '/components/com_content/helpers/route.php';

    public function __construct($JArticle) {
        $this->article = $JArticle->getArticle();
    }
    
    protected function isExtensionInstalled($option){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__extensions');
        $query->where('type' . ' = ' . $db->quote('component'));
        $query->where('element'.' = '.$db->quote($option));
        $db->setQuery($query);
        $result = $db->loadObject();
        if(!$result){
            return FALSE;
        }
        return TRUE;
    }

    protected function _categoryAlias(){
        jimport( 'joomla.filter.output' );
        $db =& JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('alias');
        $query->from('#__categories');
        $query->where('id = '.$db->quote($this->article->catid));
        $db->setQuery($query);
        $result = $db->loadObject();
        $alias = JFilterOutput::stringURLSafe($result->alias);
        return $alias;
    }
    
    protected function _articleAlias(){
        jimport( 'joomla.filter.output' );
        $alias = $this->article->alias;
        if(empty($alias)) {
            $db =& JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('alias');
            $query->from('#__content');
            $query->where('id='.$db->quote($this->article->id));
            $db->setQuery($query);
            print $query;
            $result = $db->loadObject();
            $alias = $this->article->title;
            if(!empty($result->alias)){
                $alias = $result->alias;
            }
        }
        $filteredAlias = JFilterOutput::stringURLSafe($alias);
        return $filteredAlias;
    }
    
    protected function _articleSlug(){
        $slug = $this->article->id.':'.$this->_articleAlias();
        return $slug;
    }
    
    protected function _initSh404SefUrls(){
        $app = &JFactory::getApplication();
        require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sh404sef'.DS.'sh404sef.class.php');
        $sefConfig = & Sh404sefFactory::getConfig();

        // hook to be able to install other SEF extension plugins
        Sh404sefHelperExtplugins::loadInstallAdapters();

        // another hook to allow other SEF extensions language file to be loaded
        Sh404sefHelperExtplugins::loadLanguageFiles();

        if (!$sefConfig->Enabled) {
            // go away if not enabled
            return;
        }
        $joomlaRouter = $app->getRouter();
        $pageInfo = & Sh404sefFactory::getPageInfo();
        $pageInfo->router = new Sh404sefClassRouter();
        $joomlaRouter->attachParseRule( array( $pageInfo->router, 'parseRule'));
        $joomlaRouter->attachBuildRule( array( $pageInfo->router, 'buildRule'));
    }
    
    protected function _attachSh404SefRouting(){
         if(!$this->isExtensionInstalled('com_sh404sef')){
             return;
        }
        $isSh404SefExtensionEnabled = JComponentHelper::isEnabled('com_sh404sef', true);
        if(!$isSh404SefExtensionEnabled && JPATH_BASE != JPATH_ADMINISTRATOR){
            return;
        }
        $this->_initSh404SefUrls();
        
    }
    
    protected function noSlashes($str){
        $strippedBackSlashes = stripslashes($str);
        $strippedSlashes = str_replace('/', '', $strippedBackSlashes);
        return $strippedSlashes;
    }
    
    protected function isInBackend(){
        $inBackend = false;
        if($this->noSlashes(JPATH_BASE) == $this->noSlashes(JPATH_ADMINISTRATOR)) {
            $inBackend = true;
        }
        return $inBackend;
    }
    
    protected function articleRoute(){
        require_once(JPATH_SITE.$this->routerPath);
        $catAlias = $this->_categoryAlias();
        $articleSlug = $this->_articleSlug();
        $catSlug = $this->article->catid.':'.$catAlias;
        $articleRoute = JRoute::_( ContentHelperRoute::getArticleRoute($articleSlug, $catSlug) );
        return $articleRoute;
    }
    
    protected function _joomlaSefUrlFromRoute(){
        $isInBackend = $this->isInBackend();
        if($isInBackend) {
            // In the back end we need to set the application to the site app instead
            JFactory::$application = JApplication::getInstance('site');
        }  
        $this->_attachSh404SefRouting();
        $articleRoute = $this->articleRoute();
        $sefURI = str_replace(JURI::base(true), '', $articleRoute);
        $siteURL = substr(JURI::root(), 0, -1);
        if($isInBackend) {
            $siteURL = str_replace($siteURL.DS.'administrator', '', $siteURL);
            JFactory::$application = JApplication::getInstance('administrator');
        }
        $sefURL = $siteURL.$sefURI;
        return $sefURL;
    }
        
    public function url(){
        return $this->_joomlaSefUrlFromRoute();
    }
}

<?php

/**
 * PinitButton adds a Pinterest button to your website
 *
 * @version  1.0
 * @author Daniel Eliasson (joomla at stilero.com)
 * @copyright  (C) 2012-apr-27 Stilero Webdesign http://www.stilero.com
 * @category Plugins
 * @license    GPLv2
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

// import library dependencies
jimport('joomla.plugin.plugin');
define('PINIT_CLASSES', JPATH_PLUGINS.'content/pinitbutton/pinitbtn/');
JLoader::register('Pinit', PINIT_CLASSES.'pinit.php');
JLoader::register('PIBK2JArticleUrl', PINIT_CLASSES.'jarticle/k2jarticle-url.php');
JLoader::register('PIBK2JArticle', PINIT_CLASSES.'jarticle/k2jarticle.php');
JLoader::register('PIBJArticleErrors', PINIT_CLASSES.'jarticle/jarticle-errors.php');
JLoader::register('PIBJArticleImage', PINIT_CLASSES.'jarticle/jarticle-image.php');
JLoader::register('PIBJArticleUrl', PINIT_CLASSES.'jarticle/jarticle-url.php');
JLoader::register('PIBJArticle', PINIT_CLASSES.'jarticle/jarticle.php');
JLoader::register('PIBJArticle30', PINIT_CLASSES.'jarticle/jarticle30.php');

class plgContentPinitbutton extends JPlugin {

    private $Pinit;
    private $JArticle;
    private $JArticleUrl;
    private $JArticleImage;

    
   var $config;
   var $classNames;
    var $debug;
    
    
    public static $DESC_META = 1;
    public static $DESC_INTRO = 2;
    public static $DESC_SITE = 3;
    public static $DESC_CUSTOM = 4;

    function plgContentPinitbutton(&$subject, $config) {
        parent::__construct($subject, $config);
        $language = JFactory::getLanguage();
        $language->load('plg_content_pinitbutton', JPATH_ADMINISTRATOR, 'en-GB', true);
        $language->load('plg_content_pinitbutton', JPATH_ADMINISTRATOR, null, true);
        $this->errorOccured = FALSE;
        $this->classNames = array(
            'com_article'       =>  'PIBJArticle',
            'com_content'       =>  'PIBJArticle',
            'com_k2'            =>  'PIBK2JArticle'
        );
        
    }
    
    protected function generateButton(&$article, $isK2 = false){
        if($isK2){
            $this->loadK2Classes($article);
        }else{
            $this->loadJoomlaClasses($article);
        }
        $this->Pinit->insertButtonScriptDeclaration();
        $article->text = $this->replaceWildcardInContent($article->text);
    }


    public function onContentPrepare($context, &$article, &$params, $limitstart=0) {
        if( $context != 'com_content.article' && $context !='com_virtuemart.productdetails'){
            return;
        }
        $this->generateButton($article);
    }
    
    public function onK2PrepareContent(&$item, &$params, $limitstart=0) {
        if(JRequest::getVar('option')!='com_k2' || JRequest::getVar('view')!='item'){
            return;
        }
        $this->generateButton($item);
    }
    
    public function onContentAfterDisplay($context, &$article, &$params, $limitstart=0) {

        if( $context != 'com_content.article' && $context !='com_virtuemart.productdetails'){
            return;
        }
        if($this->params->def('placement')!='2' || ( !$this->isArticleContext() && !$this->isProductContext() ) ){
            return '';
        }
        return $this->Pinit->buttonScript();
    }

    public function onContentBeforeDisplay($context, &$article, &$params, $limitstart=0) {
        if( $context != 'com_content.article' && $context !='com_virtuemart.productdetails'){
            return;
        }
        if($this->params->def('placement')!='1' || ( !$this->isArticleContext() && !$this->isProductContext() ) ){
            return '';
        }
        return $this->Pinit->buttonScript();
    }
    
    // ---------------- K2 Methods ------------------------
    public function onK2AfterDisplayContent(& $item, &$params, $limitstart=0){
       if(JRequest::getVar('option')!='com_k2' || JRequest::getVar('view')!='item'){
            return;
        }
        if($this->params->def('placement')!='2' || !$this->isArticleContext() ){
            return '';
        }
        $this->loadK2Classes($item);
        return $this->Pinit->buttonScript();
   }
   
   public function onK2BeforeDisplayContent(& $item, &$params, $limitstart=0){
       if(JRequest::getVar('option')!='com_k2' || JRequest::getVar('view')!='item'){
            return;
        }
//        if($this->params->def('placement')!='1' || !$this->isArticleContext() ){
        if($this->params->def('placement')!='1' ){
            return '';
        }
        if(!$this->loadK2Classes($item)){
            return;
        }
        $this->insertButtonScriptDeclaration();
        return $item->text = $this->replaceWildcardInContent($item->text);
   }
   
//   //------------------ Custom methods ---------------------
//   private function loadClasses($article, $isK2 = false){
//       $component = JRequest::getVar('option');
//        if(array_key_exists($component, $this->classNames)){
//            $className = $this->classNames[$component];
//            JLoader::register( $className, dirname(__FILE__).DS.'pinitbtn'.DS.'jArticle.php');
//            $articleFactory = new $className($article);
//            $this->JArticle = $articleFactory->getArticleObj();
//            if($this->debug == true){
//                JError::raiseNotice('0', 'Class; '.$className);
//                JError::raiseNotice('0', var_dump($this->JArticle));
//            }
//            return TRUE;
//        }
//        return false;
//   }
   
   private function loadJoomlaClasses($article){
       $this->JArticle = new PIBJArticle($article);
       $this->JArticleUrl = new PIBJArticleUrl($this->JArticle);
       $this->JArticleImage = new PIBJArticleImage($this->JArticle);
       $url = $this->JArticleUrl->url();
       $image = $this->findBestImage();
       $desc = $this->JArticle->description($article);
       $this->Pinit = new Pinit($url, $image, $desc, $this->params->def('pincount'), $this->params->def('image'));
   }
   
   private function loadK2Classes($item){
       $this->JArticle = new PIBK2JArticle($item);
       $this->JArticleUrl = new PIBK2JArticleUrl($this->JArticle);
       $this->JArticleImage = new PIBJArticleImage($this->JArticle);
   }
   
    private function isArticleContext(){
        $isArticleView = JRequest::getVar('view') == 'article' ? true : false;
        $hasArticleID = JRequest::getVar('id') != '' ? true : false;
        if($isArticleView && $hasArticleID){
            return true;
        }else{
            return false;
        }
    }
    
    private function isProductContext(){
        $productID = JRequest::getVar('product_id');
        if($productID == ''){
            $productID = JRequest::getVar('virtuemart_product_id');
        }
        $isProduct = $productID != '' ? true : false;
        return $isProduct;
    }
    
    private function layoutAsAttr(){
        $layout = '';
        switch ($this->params->def('pincount')) {
            case 1:
                $layout = ' count-layout="horizontal"';
                break;
            case 2:
                $layout = ' count-layout="vertical"';
                break;
            default:
                $layout = ' count-layout="none"';
                break;
        }
        return $layout;
    }
    
    private function buttonImage(){
        $layout = '';
        switch ($this->params->def('image')) {
            case 1:
                $layout = '//assets.pinterest.com/images/PinExt.png';
                break;
            case 2:
                $layout = 'http://passets-cdn.pinterest.com/images/about/buttons/big-p-button.png';
                break;
            case 3:
                $layout = 'http://passets-cdn.pinterest.com/images/about/buttons/pinterest-button.png';
                break;
            default:
                $layout = '//assets.pinterest.com/images/PinExt.png';
                break;
        }
        return $layout;
    }
    
    private function buttonScript(){
        $url = urlencode($this->JArticle->url);
        $imageurl = urlencode($this->findBestImage());
        $desc = urlencode($this->description());
        $buttonImg = $this->buttonImage();
        $layout = $this->layoutAsAttr();
        $buttonScript = '<div class="pinitButton"><a href="http://pinterest.com/pin/create/button/?url='.$url.'&media='.$imageurl.'&description='.$desc.'" class="pin-it-button"'.$layout.'><img border="0" src="'.$buttonImg.'" title="Pin It" /></a></div>';
        //if($this->debug) JError::raiseNotice('0', htmlentities ($buttonScript));
        return $buttonScript;
    }
    
    public function insertButtonScriptDeclaration(){
        $document = JFactory::getDocument();
        $document->addScript('//assets.pinterest.com/js/pinit.js');
    }
    
    private function description(){
        $desc = $this->JArticle->description != '' ? $this->JArticle->description : $desc;
         if($this->debug){
                JError::raiseNotice('0', 'desc: '. $desc);
         }
        switch ($this->params->def('og-desc')) {
            case 1:
                $metadesc = htmlentities(strip_tags($this->JArticle->metadesc));
                $desc = $metadesc == '' ? $desc : $metadesc ;
                break;
            case 2:
                $introtext = htmlentities(strip_tags($this->JArticle->introtext));
                $desc = $introtext == '' ? $this->JArticle->description : $introtext ;
                break;
            case 3:
                $joomlaConfig = JFactory::getConfig();
                $joomlaSiteDesc = htmlentities(strip_tags($joomlaConfig->getValue( 'config.MetaDesc' )));
                $desc = $joomlaSiteDesc == '' ? $desc : $joomlaSiteDesc ;
                break;
            case 4:
                $desc = htmlentities(strip_tags($this->params->def('og-desc-custom')));
                break;
            default:
                break;
        }
        $desc = $desc=='' ? htmlentities(strip_tags($this->params->def('og-desc-custom'))) : $desc;
        return $desc;
    }
    
    private function image($option){
        $image = $this->JArticleImage->src();
        if($this->debug){
            JError::raiseNotice('0', 'image; '.$image);
            JError::raiseNotice('0', 'article; '.  var_dump($this->JArticle));
        }
        switch ($option) {
            case 1:
                $firstImageInContent = $this->JArticleImage->firstImageInContent();
                $image = ($firstImageInContent != '') ? $firstImageInContent : $image;
                break;
            case 2:
                $introImage = $this->JArticleImage->introTextImage();
                $image = ($introImage != '') ? $introImage : $image;
                break;
            case 3:
                $fulltextImage = $this->JArticleImage->fullTextImage();
                $image = ($fulltextImage != '') ? $fulltextImage : $image;
                break;
            case 4:
                $images = $this->JArticleImage->images();
                $cssClass = $this->params->def('og-img-class');
                $classImage = $this->imageWithClass($images, $cssClass);
                if(isset($classImage)){
                    $image = ($classImage != '') ? $classImage : $image;
                }
                break;
            case 5:
                if($image == '' && $this->params->def('og-img-custom') != ''){
                    $image = 'images/'.$this->params->def('og-img-custom');
                }
                break;
            default:
                return;
                break;
        }
        $image = $image == '' ? 'images/'.$this->params->def('og-img-custom') : $image;
        if($image != ""){
            $image = preg_match('/http/', $image)? $image : JURI::root().$image;
        }
        return $image;
    }
    
    private function findBestImage(){
        $image = $this->image($this->params->def('og-img-prio1'));
        if($image == "" ){
            $image = $this->image($this->params->def('og-img-prio2'));
        }
        if($image == "" ){
            $image = $this->image($this->params->def('og-img-prio3'));
        }
        if($image == "" ){
            $image = $this->image($this->JArticle->image);
        }
        if($image != ""){
            return htmlentities(strip_tags($image));
        }
    }
    
    
    
    private function imageWithClass($images, $cssClass){
        if( (!isset($images)) || (empty ($images))  ){
            return;
        }
        foreach ($images as $image) {
            if($image['class'] == $cssClass){
                return $image['src'];
            }
        }
    }
    
    protected function replaceWildcardInContent($text){
        $script = $this->Pinit->buttonScript();
        $newText = str_replace('{pinitbtn}', $script, $text);
        return $newText;
    }

}
//End Class
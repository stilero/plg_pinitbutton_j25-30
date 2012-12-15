<?php
/**
 * JArticle Image class
 * Class for fetching images from Joomla articles.
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

class PIBJArticleImage{
    
    protected $article;
    protected $imageFirstInContent;
    protected $imageFullText;
    protected $imageIntro;
    protected $image;
    protected $images;
    
    /**
     * Easily fetch images from Joomla articles
     * @param Object $JArticle A JArticle initiated Class object
     */
    public function __construct($JArticle) {
        $this->article = $JArticle->getArticle();
        $this->fetchAllImages();
    }
    
    /**
     * Extracts images from html and returns an array
     * @param String $text the HTML string
     * @param String $type (optional) to set an image attribute ('full', 'intro', 'text')
     * @return Array
     */
    protected function extractImageFromHTML($text, $type=''){
        if(!class_exists('DOMDocument') ){
            return;
        }
        $html = new DOMDocument();
        $html->recover = true;
        $html->strictErrorChecking = false;
        $html->loadHTML($text);
        $images = array();
        foreach($html->getElementsByTagName('img') as $image) {
            $imgSrc = preg_match('/http/', $image->getAttribute('src'))? $image->getAttribute('src') : JURI::root().$image->getAttribute('src');
            $images[] = array(
                'src' => $imgSrc,
                'class' => $image->getAttribute('class'),
                'type' => $type
            );
        }
        return $images;
    }
    
    /**
     * Fetches images in all the content parts, and saves it to the image attribute
     */
    protected function fetchImagesInContent(){
        if(isset($this->images)){
            return;
        }
        $texts = array(
            'text' => $this->article->text, 
            'full' => $this->article->fulltext, 
            'intro' => $this->article->introtext);
        $images = array();
        foreach ($texts as $key => $text) {
            if($text != ''){
                $fetchedImages = $this->extractImageFromHTML($text, $key);
                if($fetchedImages){
                    $images = array_merge($images, $fetchedImages);
                }
            }
        }
        if(!empty($images)){
            $this->images = $images;
        }
    }
    
    /**
     * Method to initiate all fetching. Automatically run by the constructor
     */
    protected function fetchAllImages(){
        $this->fetchImagesInContent();
        $this->fetchFullTextImage();
        $this->fetchIntroImage();
        $this->fetchFirstImageInContent();
    }
    
    /**
     * Method for getting specific image type from the images attr.
     * @param String $imgType Image type to fetch ('full', 'intro', 'text')
     * @return String image url src.
     */
    protected function getImageTypeFromImages($imgType){
        if(!isset($this->images)){
            return;
        }
        foreach ($this->images as $image) {
            if($image['type'] == $imgType){
                return $image['src'];
            }            
        }
    }
    /**
     * Method for storing introimage to introimage attr.
     */
    protected function fetchIntroImage(){
        $this->imageIntro = $this->getImageTypeFromImages('intro');
    }
    /**
     * Method for storing fulltext image to fulltextimage attr.
     */
    protected function fetchFullTextImage(){
        $this->imageFullText = $this->getImageTypeFromImages('full');
    }
    /**
     * Method for fetching first image in any of the content parts and storing to the first image attr.
     * @return Null on fail
     */
    protected function fetchFirstImageInContent(){
        if(!isset($this->images)){
            return;
        }
        $image = '';
        if(isset($this->images[0]['src'])){
            $image = $this->images[0]['src'];
        }
        $this->imageFirstInContent = $image;
    }
    /**
     * Method for getting the first image in content
     * @return String Absolute url image src
     */
    public function firstImageInContent(){
        if(isset($this->imageFirstInContent)){
            return $this->imageFirstInContent;
        }
        $this->fetchFirstImageInContent();
        return $this->imageFirstInContent;
    }
    
    /**
     * Method for getting the first image in content.
     * @return String Absolute image url
     */
    public function src(){
        return $this->imageFirstInContent;
    }
    /**
     * Method for getting first full text image found
     * @return String Absolute image url
     */
    public function fullTextImage(){
        if(isset($this->imageFullText)){
            return $this->imageFullText;
        }
        $this->fetchFullTextImage();
        return $this->imageFullText;
    }
    /**
     * Method for getting first intro image found
     * @return String Absolute image url
     */
    public function introTextImage(){
        if(isset($this->imageIntro)){
            return $this->imageIntro;
        }
        $this->fetchIntroImage();
        return $this->imageIntro;
    }
    
    /**
     * Returns all images in an array
     * @return array array with images
     */
    public function images(){
        return $this->images;
    }
}
<?php
/**
 * PinitButton_J25-30
 *
 * @version  1.0
 * @package Stilero
 * @subpackage PinitButton_J25-30
 * @author Daniel Eliasson (joomla@stilero.com)
 * @copyright  (C) 2012-dec-15 Stilero Webdesign (www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class Pinit{
    
    protected $url;
    protected $image;
    protected $description;
    protected $cssClass;
    protected $countLayoutType;
    protected $imgBtnType;
    private static $pinterestBtnURL = 'http://pinterest.com/pin/create/button/';
    private static $btnSrcDefault = '//assets.pinterest.com/images/PinExt.png';
    private static $btnSrcBig = 'http://passets-cdn.pinterest.com/images/about/buttons/big-p-button.png';
    private static $btnSrcRegular = 'http://passets-cdn.pinterest.com/images/about/buttons/pinterest-button.png';
    public static $btnTypeDefault = 1;
    public static $btnTypeBig = 2;
    public static $btnTypeRegular = 3;
    public static $countLayoutTypeHorizontal = 1;
    public static $countLayoutTypeVertical = 2;
    public static $countLayoutTypeNone = 3;
    private static $countLayoutHorizontal = ' count-layout="horizontal"';
    private static $countLayoutVertical = ' count-layout="vertical"';
    private static $countLayoutNone = ' count-layout="none"';
    
    /**
     * Class for adding Pinit Buttons to websites
     * @param String $url URL to the site promoting
     * @param String $image SRC for the image to be posted on Pinterest
     * @param String $description A short description of your pin
     * @param String $class 
     * @param integer $imgBtnType
     */
    public function __construct($url, $image, $description, $countLayoutType=3, $imgBtnType=1) {
        $this->description = $description;
        $this->image = $image;
        $this->url = $url;
        $this->countLayoutType = $countLayoutType;
        $this->imgBtnType = $imgBtnType;
    }
    
    /**
     * Returns an image src url depending on the settings
     * @return String image source url
     */
    private function buttonImage(){
        $imageSrc = '';
        switch ($this->imgBtnType) {
            case self::$btnSrcDefault:
                $imageSrc = self::$btnSrcDefault;
                break;
            case self::$btnTypeBig:
                $imageSrc = self::$btnSrcBig;
                break;
            case self::$btnTypeRegular :
                $imageSrc = self::$btnSrcRegular;
                break;
            default:
                $imageSrc = self::$btnSrcDefault;
                break;
        }
        return $imageSrc;
    }
    
    /**
     * Returns a CSS class according to the settings
     * @return String Layout CSS Class
     */
    private function countLayout(){
        $layout = '';
        switch ($this->countLayoutType) {
            case self::$countLayoutTypeHorizontal :
                $layout = self::$countLayoutHorizontal;
                break;
            case self::$countLayoutTypeVertical :
                $layout = self::$countLayoutVertical;
                break;
            default:
                $layout = self::$countLayoutNone;
                break;
        }
        return $layout;
    }
    
    /**
     * Returns the HTML code for the Pinit Button
     * @return string Div container with a linked image
     */
    public function buttonScript(){
        $url = urlencode($this->url);
        $imageurl = urlencode($this->image);
        $desc = urlencode($this->description);
        $buttonImg = $this->buttonImage();
        $layout = $this->countLayout();
        $buttonScript = 
            '<div class="pinitButton">
                <a href="'.self::$pinterestBtnURL.'?url='.$url.'&media='.$imageurl.'&description='.$desc.'" class="pin-it-button"'.$layout.'>
                    <img border="0" src="'.$buttonImg.'" title="Pin It" />
                </a>
            </div>';
        return $buttonScript;
    }
    
    /**
     * Inserts required JavaScript on the page
     */
    public function insertButtonScriptDeclaration(){
        $document = JFactory::getDocument();
        $document->addScript('//assets.pinterest.com/js/pinit.js');
    }
}

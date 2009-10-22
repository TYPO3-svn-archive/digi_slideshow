<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Digitage <d.voigt@digitage.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Digi Slideshow' for the 'digi_slideshow' extension.
 *
 * @author	Digitage <d.voigt@digitage.de>
 * @package	TYPO3
 * @subpackage	tx_digislideshow
 */
class tx_digislideshow_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_digislideshow_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_digislideshow_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'digi_slideshow';	// The extension key.
	var $pi_checkCHash = true;

  function initFF(){
    $this->pi_initPIflexForm();// Init and get the flexform data of the plugin
    $piFlexForm = $this->cObj->data['pi_flexform'];// Assign the flexform data to a local variable for easier access
    if($piFlexForm){
      foreach ( $piFlexForm['data'] as $sheet => $data ) {
        foreach ( $data as $lang => $value ) {
          foreach ( $value as $key => $val ) {
            $this->conf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
          }
        }
      }
      $this->conf['imagebase'] = 'uploads/tx_digislideshow/';// set imagebase for flexform use
    }
  }

	/**
	 * Main method of your PlugIn
	 *
	 * @param	string		$content: The content of the PlugIn
	 * @param	array		$conf: The PlugIn Configuration
	 * @return	The content that should be displayed on the website
	 */
	function main($content,$conf){ try {
    
    $this->conf = $conf; // Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL(); // Loading the LOCAL_LANG values
    $this->initFF();
    
    //$UID = intval($this->cObj->data['uid']);
    $UID = uniqid();
    
    //print('<pre style="text-align:left;position:absolute;">');var_dump( $this->conf );print('</pre>');/*DEBUG*/
    
    // apply TS image settings
    if(empty($this->conf["imagelist"])){
        $files = $this->files($this->conf['imagebase']);
    }else
        $files = explode(',',$this->conf["imagelist"]);
    
    $files = $this->filter($files);
    
    if( count($files) % $this->conf['number'] ) throw new Exception('Not enough images!');
    if(empty($this->conf['imagebase'])) $this->conf['imagebase'] = 'uploads/tx_digislideshow/';// if GIFBUILDER is used
    
    $tmp = array();
    foreach( $files as $image ){
      $this->conf['image.']['file'] = $this->conf['imagebase'].$image;
      $tmp[] = $this->cObj->IMG_RESOURCE($this->conf['image.']);
    }
    $files = array_chunk($tmp,$this->conf['number'],true);
    
    //if($this->conf['image.']['file.']) $this->conf['imagebase'] = 'typo3temp/pics/';// if GIFBUILDER is used
    
    // build javascript data array
    $jsdata = array( $UID, intval($this->conf['number']), intval($this->conf['interval']), intval($this->conf['duration']), $files );
    
    // build html content
    $content = '<img src="'.implode('" /><img src="',$jsdata[4][0]).'" />';
    $content = '<div id="tx-digislideshow-pi1-'.$UID.'-1" class="tx-digislideshow-pi1-box">'.$content.'</div><div id="tx-digislideshow-pi1-'.$UID.'-2" class="tx-digislideshow-pi1-box">'.$content.'</div>';
    
    // add css to page
    $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId.'0'] = '<link rel="stylesheet" href="typo3conf/ext/digi_slideshow/res/css/gallery.css" type="text/css" />';
    
    // check if t3mootools is loaded
    if(t3lib_extMgm::isLoaded('t3mootools'))require_once(t3lib_extMgm::extPath('t3mootools').'class.tx_t3mootools.php');
    if(defined('T3MOOTOOLS')){// if t3mootools is loaded and the custom Library had been created
      tx_t3mootools::addMooJS();
    } else {// else use TS defined mootools
      $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId.'1'] = '<script src="'.$this->getPath($this->conf['mootools']).'" type="text/javascript"></script>';
    }  
    // add js to page
    $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId.'2'] = '<script type="text/javascript" src="typo3conf/ext/digi_slideshow/res/js/gallery.js"></script>';
    $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId.$UID] = '<script type="text/javascript">window.addEvent("domready",function(){ var ds'.$UID.' = new digi_slideshow('.array_php2js($jsdata).'); });</script>';
    
    return $this->pi_wrapInBaseClass($content);
    
  } catch (Exception $e) {
    
    return $this->pi_wrapInBaseClass($e->getMessage());
    
  }}

	/**
	 * Generate link-parameter from IPTC data
	 *
	 * @param	string		$content: not used
	 * @param	array		$conf: not used
	 * @return	content for the link
	 */
  function imagelink($content,$conf){
    $image = $GLOBALS["TSFE"]->lastImageInfo;
    $iptc = getIPTC($image['origFile']);
    if( intval($iptc['2#103']) > 0 ) return sprintf('http://jalaggate.picturemaxx.com/?SEARCHTXT1=%d',$iptc['2#103']);
    return 0;
  }
  
	/**
	 * Get IPTC data
	 *
	 * @param	string		$content: not used
	 * @param	array		$conf: configuration
	 * @return	content of iptc field
	 */
  function iptc($content,$conf){
    $image = $GLOBALS["TSFE"]->lastImageInfo;
    $iptc = getIPTC($image['origFile']);
    if(!empty($conf['field']))return $iptc[$conf['field']];
    return null;
  }
  
  private function getPath($str){return str_replace('EXT:','typo3conf/ext/',$str);}
  
  private function files($path){
    $files = glob($path.'*');
    $files = array_map('basename',$files);
    return $files;
  }
  
  private function filter($files){
    foreach( $files as $n => $f )if(preg_match('/.*\.(png|gif|jpg|jpeg|tif)/',strtolower($f)))$tmp[] = $f;
    return $tmp;
  }
  
}

function array_php2js($a){
  switch(gettype($a)){
    case 'integer': return sprintf('%d',$a);
    case 'array': return '['.implode(',',array_map('array_php2js',$a)).']';
    default: return sprintf("'%s'",$a);
  }
}

function getIPTC($file){
  $size = getimagesize($file, $info);
  if(isset($info['APP13'])){
    $iptc = iptcparse($info['APP13']);
    foreach( $iptc as $key => $value ){
      $tmp[$key] = utf8_encode(implode('|',$value));
    }
    return $tmp;
  }
  return null;
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/digi_slideshow/pi1/class.tx_digislideshow_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/digi_slideshow/pi1/class.tx_digislideshow_pi1.php']);
}

?>
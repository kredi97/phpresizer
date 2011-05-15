<?php
/**
 * @version $Revision$
 * @category PhpResizer
 * @package PhpResizer
 * @subpackage Engine
 * @author $Author$ $Date$
 * @license New BSD license
 * @copyright http://code.google.com/p/phpresizer/
 */

/**
 *
 */
abstract class PhpResizer_Engine_EngineAbstract
{
	
	const EXC_BAD_PARAM ='param %s is bad';
	const EXC_ENGINE_IS_NOT_AVALIBLE ='engine %s is not avalible';
	const EXC_EXTENSION_IS_NOT_AVALIBLE ='extension  %s is not allowed. Allowed: %s';
    
    /**
     * @var array
     */
    protected $types = array();

    
    public function __construct()
    {
    	$this->_setCalculator(new PhpResizer_Calculator_Calculator());
        $this->checkEngine();
    }
    
    private function _setCalculator (PhpResizer_Calculator_Interface $calculator){
    	$this->calculator = $calculator;
    }
    

    /**
     * 
     * @param $params
     * @return unknown_type
     * @throws PhpResizer_Exception_IncorrectExtension
     * @throws PhpResizer_Exception_Basic
     */
    protected function checkExtOutputFormat(array $params){
    	
    	if (!is_string($params['path'])) {
            throw new PhpResizer_Exception_Basic(sprintf(self::EXC_BAD_PARAM,'path'));
        }

    	$ext = PhpResizer_PhpResizer::getExtension($params['path']);
        if (!in_array($ext, $this->types)) {
            throw new PhpResizer_Exception_IncorrectExtension(
            	sprintf(self::EXC_EXTENSION_IS_NOT_AVALIBLE, $ext, implode(',',$this->types)));
        }
        
        if (!$params['cacheFile']
            || !is_string($params['cacheFile']))
        {
            throw new PhpResizer_Exception_Basic(sprintf(self::EXC_BAD_PARAM,'cacheFile'));
        }
    }
//    
//    protected function checkResizeSmallImage(array $calculateParams){
//		list($srcImageWidth, $srcImageHeight) = $calculateParams['srcGetImageSize'];        
//		
//        if(!$calculateParams['zoomSmallImage'] && 
//        ($srcImageWidth <= $calculateParams['width'] || $srcImageHeight <= $calculateParams['height'])){        	
//        	return true;
//        }        
//        return false;
//    }
//    
//    protected function copySmallImageIntoCache($params) {    	
//    	return copy($params['path'],$params['cacheFile']);
    	
//    	$extSrc = PhpResizer_PhpResizer::getExtension($params['path']);
//    	$extDst = PhpResizer_PhpResizer::getExtension($params['cacheFile']);
//    	
//    	if($extSrc !== $extDst) {
//	    	$num = strrpos($params['cacheFile'], $extDst);
//	    	$params['cacheFile'] = substr($params['cacheFile'], 0, $num).$extSrc;
//    	}
//
//    	if(!file_exists($params['cacheFile'])) {
//    		copy($params['path'], $params['cacheFile']);
//    	}
//    	
//    	return $params['cacheFile'];
//    }
}
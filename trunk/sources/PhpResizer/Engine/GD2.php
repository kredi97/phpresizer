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
class PhpResizer_Engine_GD2 
	extends PhpResizer_Engine_EngineAbstract  
	implements PhpResizer_Engine_Interface
{
    protected $types=array(
    	IMAGETYPE_GIF => 'gif',
    	IMAGETYPE_JPEG => 'jpeg',    	
    	IMAGETYPE_PNG => 'png',
    	1000 => 'jpg');

    public function checkEngine () {
        if (!extension_loaded('gd')) {
            throw new PhpResizer_Exception_Basic(
            	sprintf(self::EXC_ENGINE_IS_NOT_AVALIBLE,
            		PhpResizer_PhpResizer::ENGINE_GD2));
        }
    }
    
    public function resize  (array $params=array()) {

    	$this->checkExtOutputFormat($params);        
        $path = $params['path'];
        $cacheFile = $params['cacheFile'];

        $this->calculator->setInputParams($params);
        extract($this->calculator->calculateParams());
        
		$srcImageType = $srcGetImageSize[2];
        $image = call_user_func('imagecreatefrom' . $this->types[$srcImageType], $path);        

        $temp = imagecreatetruecolor ($dstWidth, $dstHeight);

        // save transparent
		if($srcImageType == IMAGETYPE_GIF || $srcImageType == IMAGETYPE_PNG){			
            imagealphablending($temp, false);
			imagesavealpha($temp, true);
			$transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
			imagefilledrectangle($temp, $dstX, $dstY, $dstWidth, $dstHeight, $transparent);
		}
		
		if ($background){
			// imagefill($temp, 0, 0, 0xFFFF00);
		}
		
		imagecopyresampled ($temp, $image, $dstX, $dstY, $srcX, $srcY, $dstWidth, $dstHeight, $srcWidth, $srcHeight);

        call_user_func("image" . $this->types[$srcImageType], $temp, $cacheFile);
        imagedestroy($image);
        imagedestroy($temp);
        return true;
    }
}
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
class PhpResizer_Engine_GD2 extends PhpResizer_Engine_EngineAbstract  
{
    protected $types=array(
    	IMAGETYPE_GIF => 'gif',
    	IMAGETYPE_JPEG => 'jpeg',    	
    	IMAGETYPE_PNG => 'png',
    	1000 => 'jpg');
                    
    protected function _checkEngine () {
        if (!extension_loaded('gd')) {
            throw new PhpResizer_Exception_Basic(
            	sprintf(self::EXC_ENGINE_IS_NOT_AVALIBLE,
            		PhpResizer_PhpResizer::ENGINE_GD2));
        }
    }

    public function resize  (array $params=array()) {
        $this->getParams($params);
        $size = $this->params['size'];
        $path = $this->params['path'];
        $cacheFile = $this->params['cacheFile'];

        extract($this->calculateParams());

        $image = call_user_func('imagecreatefrom' . $this->types[$size[2]], $path);        

        $temp = imagecreatetruecolor ($dstWidth, $dstHeight);

        // save transparent
		if($size[2] == IMAGETYPE_GIF || $size[2] == IMAGETYPE_PNG){			
            imagealphablending($temp, false);
			imagesavealpha($temp, true);
			$transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
			imagefilledrectangle($temp, $dstX, $dstY, $dstWidth, $dstHeight, $transparent);
		}
		
		if ($background){
			// imagefill($temp, 0, 0, 0xFFFF00);
		}
		
		imagecopyresampled ($temp, $image, $dstX, $dstY, $srcX, $srcY, $dstWidth, $dstHeight, $srcWidth, $srcHeight);

        call_user_func("image" . $this->types[$size[2]], $temp, $cacheFile);
        imagedestroy($image);
        imagedestroy($temp);
        return true;
    }
}
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
 *
 */
class PhpResizer_Engine_ImageMagic 	
	extends PhpResizer_Engine_EngineAbstract 
	implements PhpResizer_Engine_Interface 
{ 
	   	
    protected $types=array(IMAGETYPE_GIF => 'gif', 
	    IMAGETYPE_JPEG=>'jpeg',
	    IMAGETYPE_PNG=>'png', 
	    1000 => 'jpg', 
	    'bmp', 
	    'tif',  
	    'tiff');

    // linux command to ImageMagick convert
    private $convertPath='convert';

    public function checkEngine () {
        $command = $this->convertPath.' -version';
       
        exec($command, $stringOutput);

        if (false === strpos($stringOutput[0],'ImageMagick')) {
            throw new PhpResizer_Exception_Basic(
            	sprintf(self::EXC_ENGINE_IS_NOT_AVALIBLE,
            	PhpResizer_PhpResizer::ENGINE_IMAGEMAGICK));
        }
    }

    public function resize  (array $params=array()) {
    	
    	$this->checkExtOutputFormat($params);        
        $path = $params['path'];
        $cacheFile = $params['cacheFile'];

        $this->calculator->setInputParams($params);
        extract($this->calculator->calculateParams());

             $command = $this->convertPath
                 . ' ' . escapeshellarg($path) . ' -crop'
                 . ' ' . $srcWidth.'x'.$srcHeight . '+' . $srcX . '+' . $srcY
                 . ' -resize ' . $dstWidth . 'x' . $dstHeight
                 . ' -sharpen 1x10'
                 //.' -colorspace GRAY'
                //.' -posterize 32'
                //.' -depth 8'
                //.' -contrast'
                //.' -equalize'
                //.' -normalize'
                //.' -gamma 1.2'
                 . ' -quality 85'
                 //.' -blur 2x4'
                 //.' -unsharp 0.2x0+300+0'
                //.' -font arial.ttf -fill white -box "#000000100" -pointsize 12 -annotate +0+10 "  '.$path.' "'
                //.' -charcoal 2'
                //.' -colorize 180'
                //.' -implode 4'
                //.' -solarize 10' ???
                //.' -spread 5'
                ;
                if ($background) {
                	 $command .= '  -background "'.$background.'" -gravity center -extent '.$width.'x'.$height;              	
                }
                $command .= ' ' . escapeshellarg($cacheFile);

			exec($command);
            return true;
    }
}

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
class PhpResizer_Engine_GraphicsMagick extends PhpResizer_Engine_EngineAbstract  {

	protected $types=array(IMAGETYPE_GIF => 'gif',
	    IMAGETYPE_JPEG=>'jpeg',
	    IMAGETYPE_PNG=>'png',
	    1000 => 'jpg',
	    'bmp',
	    'tif',
	    'tiff');

    // linux command to GraphicksMagick
    private $gmPath='gm';

    protected function _checkEngine () {
        $command = $this->gmPath.' version';
        
        exec($command, $stringOutput);

        if (false === strpos($stringOutput[0],'GraphicsMagick')) {
			throw new PhpResizer_Exception_Basic(
            	sprintf(self::EXC_ENGINE_IS_NOT_AVALIBLE,
            	PhpResizer_PhpResizer::ENGINE_GRAPHIKSMAGICK));
        }
    }

    public function resize  (array $params=array()) {
    	
        $this->getParams($params);

        $size = $this->params['size'];
        $path = $this->params['path'];
        $cacheFile = $this->params['cacheFile'];

        extract($this->calculateParams());
        
             $command = $this->gmPath.' convert'
                 . ' ' . escapeshellarg($path) . ' -crop'
                 . ' ' . $srcWidth . 'x' . $srcHeight . '+' . $srcX . '+' . $srcY
                 . ' -resize ' . $dstWidth . 'x' . $dstHeight
                 . ' -sharpen 1x10'
                 . ' -quality 85';
                 
                if ($background) {
                	// $command .= '  -background "'.$background.'" -gravity center -extent '.$width.'x'.$height;              	
                }
                
                $command .= ' ' . escapeshellarg($cacheFile);
            exec ($command);

        return true;
    }
}

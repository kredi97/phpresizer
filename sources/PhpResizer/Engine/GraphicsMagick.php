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
class PhpResizer_Engine_GraphicsMagick 
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

    // linux command to GraphicksMagick
    private $gmPath='gm';

    public function checkEngine () {
        $command = $this->gmPath.' version';
        
        exec($command, $stringOutput);

        if (!isset($stringOutput[0]) || false === stripos($stringOutput[0],'GraphicsMagick')) {
			throw new PhpResizer_Exception_Basic(
            	sprintf(self::EXC_ENGINE_IS_NOT_AVALIBLE,
            	PhpResizer_PhpResizer::ENGINE_GRAPHIKSMAGICK));
        }
    }

    public function resize  (array $params=array()) {

		$calculateParams = $this->calculator->checkAndCalculateParams($params);
        extract($calculateParams);
        
    	$this->checkExtOutputFormat($params);        
	
        $oldLocale = setlocale(LC_CTYPE, null);
        //need for use russian symbols in path escapeshellarg
        setlocale(LC_CTYPE, "en_US.UTF-8");
        
        if ($params['size'][2] === IMAGETYPE_PNG) {
        	$quality = $pngCompress;
        }
        
		$command = $this->gmPath.' convert'
			. ' ' . escapeshellarg($params['path']) . ' -crop'
            . ' ' . $srcWidth . 'x' . $srcHeight . '+' . $srcX . '+' . $srcY
            . ' -resize ' . $dstWidth . 'x' . $dstHeight
            . ' -sharpen 1x10'
            . ' -quality '.$quality;
        
		if ($background) {
			$command .= '  -background "#'.$background.'" -gravity center -extent '.$width.'x'.$height;              	
		}
		
		setlocale(LC_CTYPE, $oldLocale);
		
        $command .= ' ' . escapeshellarg($params['cacheFile']);
		exec ($command);

        return true;
    }
}

<?php
/**
 * @version $Revision$
 * @category PhpResizer
 * @package PhpResizer
 * @subpackage Engine
 * @author $Author$ $Date$
 * @license New BSD license
 * @copyright http://phpresizer.org/
 */

require_once ('abstract.php');

/**
 *
 *
 */
class imagemagic extends ImageEngine  {

    protected $types=array(1 => "gif", "png","jpg","bmp","tif");

    // linux command to ImageMagick convert
    private $convertPath='convert';

    protected function _checkEngine () {
        $command = $this->convertPath.' -version';
        ob_start();
            passthru($command);
        $stringOutput = ob_get_clean();

        $resultSearch = strpos($stringOutput,'ImageMagick');

        if ($resultSearch===false) {
            throw new PhpResizer_PhpResizerException('Engine '.__CLASS__.' is not avalible');
        }
    }

    public function resize  (array $params=array()) {
        $this->getParams($params);
        $size = $this->params['size'];
        $path = $this->params['path'];
        $cacheFile = $this->params['cacheFile'];
        extract($this->calculateParams());
             $command = $this->convertPath
                 .' '.$this->addSlashe($path).' -crop'
                 .' '.$srcWidth.'x'.$srcHeight.'+'.$srcX.'+'.$srcY
                 .' -resize '.$dstWidth.'x'.$dstHeight
                 .' -sharpen 1x10'
                 //.' -colorspace GRAY'
                //.' -posterize 32'
                //.' -depth 8'
                //.' -contrast'
                //.' -equalize'
                //.' -normalize'
                //.' -gamma 1.2'
                 .' -quality 75'
                 //.' -blur 2x4'
                 //.' -unsharp 0.2x0+300+0'
                //.' -font arial.ttf -fill white -box "#000000100" -pointsize 12 -annotate +0+10 "  '.$path.' "'
                //.' -charcoal 2'
                //.' -colorize 180'
                //.' -implode 4'
                //.' -solarize 10' ???
                //.' -spread 5'
                 .' '.$this->addSlashe($cacheFile);

            exec($command);
            return true;
    }
}

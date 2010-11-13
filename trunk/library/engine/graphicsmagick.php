<?php
/**
 * @version $Revision$
 * @category PhpResizer
 * @package PhpResizer
 * @subpackage Engine
 * @copyright  http://phpresizer.org/
 * @license New BSD license
 * @author $Author$ $Date$
 */

require_once ('abstract.php');

class graphicsmagick extends ImageEngine  {

    protected $types=array(1 => "gif", "png","jpg","bmp","tif");

    // linux command to GraphicksMagick
    private $gmPath='gm';

    protected function _checkEngine () {
        $command = $this->gmPath.' version';
        ob_start();
            passthru($command);
        $stringOutput = ob_get_clean();

        $resultSearch = strpos($stringOutput,'GraphicsMagick');

        if ($resultSearch===false) {
            throw new ResizerException('Engine '.__CLASS__.' is not avalible');
        }
    }

    public function resize  (array $params=array()) {
        $this->getParams($params);

        $size = $this->params['size'];
        $path = $this->params['path'];
        $cacheFile = $this->params['cacheFile'];

        extract($this->calculateParams());
             $command = $this->gmPath.' convert'
                 .' '.$this->addSlashe($path).' -crop'
                 .' '.$srcWidth.'x'.$srcHeight.'+'.$srcX.'+'.$srcY
                 .' -resize '.$dstWidth.'x'.$dstHeight
                 .' -sharpen 1x10'
                 .' -quality 75'
                 .' '.$this->addSlashe($cacheFile);

            exec($command);
        return true;
    }
}

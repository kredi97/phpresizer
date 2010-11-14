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

/**
 *
 */
abstract class PhpResizer_Engine_EngineAbstract {

    protected $params=null;

    private $maxHeight = 1500;
    private $maxWidth = 1500;

    protected $types=array();

    public function __construct() {
        $this->_checkEngine();
    }


    protected function getParams(array $params) {
        $defaultOptions=array(
            'width' => $params['size'][0],
            'height' => $params['size'][1],
            'aspect'=>true,
            'crop' =>100,
            'size'=>null, //array, result working function getimagesize()
            'cacheFile'=>null,
            'path'=>null
        );
        $this->params=array_merge($defaultOptions,$params);
        $this->params['crop']=(int)$this->params['crop'];
        $this->params['width']=(int)$this->params['width'];
        $this->params['height']=(int)$this->params['height'];
        $this->params['aspect']=(bool)$this->params['aspect'];
        $this->_checkParams();
    }

    protected function _checkParams (){

        if ($this->params['width']<1 OR $this->maxWidth<$this->params['width']) {
            $this->params['width']=$this->maxWidth;
        };
        if ($this->params['height']<1 OR $this->maxHeight<$this->params['height']) {
            $this->params['height']=$this->maxHeight;
        };

        if ($this->params['crop']<=0 OR 100<$this->params['crop']) {
            $this->params['crop']=100;
        }

        if (!is_string($this->params['path'])) {
            throw new PhpResizer_PhpResizerException('path is not string');
        }

        if (!$this->params['cacheFile'] OR !is_string($this->params['cacheFile'])) {
            throw new PhpResizer_PhpResizerException('cacheFile is not string');
        }

        if (!$this->params['size'] OR !is_array($this->params['size'])) {
            throw new PhpResizer_PhpResizerException('size is not array');
        }

        if (!in_array(strtolower(pathinfo($this->params['path'],PATHINFO_EXTENSION)),$this->types)) {
            throw new PhpResizer_PhpResizerException('extensin  '.pathinfo($this->params['path'],PATHINFO_EXTENSION).' is not avalible');
        }
    }


    protected function addSlashe($path){
        $search=array(' ','(',')');

        foreach($search as $val) {
            $path=str_replace($val, '\\'.$val, $path);
        }
        return $path;
    }

    /**
     * @return boolean
     */
    protected function _checkEngine () {}

    /**
     *
     * @todo rename variable
     */
    protected function calculateParams() {
        extract($this->params);
        $srcX=0; $srcY=0;
        if ($aspect and $aspect!="0") {
            if(($size[1]/$height) > ($size[0]/$width)) {
                $width = ceil(($size[0]/$size[1]) * $height);
                $height = $height;
            }else{
                $height = ceil($width / ($size[0]/$size[1]));
                $width = $width;
            }
        }else{
           if (($height/$width) <= ($size[1]/$size[0])) {
                $temp=$height*($size[0]/$width);
                   $srcY=ceil(($size[1]-$temp)/2);
                $size[1]=ceil($temp);
           }else{
                $temp=$width*($size[1]/$height);
                   $srcX=ceil(($size[0]-$temp)/2);
                $size[0]=ceil($temp);
           }
        }

        if ($crop!=100) {
            $crop=$this->params['crop'];
            $srcX+=ceil((100-$crop)/200*$size[0]);
            $srcY+=ceil((100-$crop)/200*$size[1]);
            $size[0]=ceil($size[0]*$crop/100);
            $size[1]=ceil($size[1]*$crop/100);
        }

        return array(
            'srcX'=>$srcX,
            'srcY'=>$srcY,
            'srcWidth'=>$size[0],
            'srcHeight'=>$size[1],
            'dstX'=>0,
            'dstY'=>0,
            'dstWidth'=>$width,
            'dstHeight'=>$height,
        );
    }

    /**
     *
     * @return boolean
     */
    public function resize () {}


}
<?php
class PhpResizer_Calculator_Calculator 
	implements PhpResizer_Calculator_Interface 
{

    /**
     * @var array
     */
    protected $types = array();
    
    /**
     * @var int
     */
    private $_defaultCropSize = 100;		
	
    /**
     * @var int
     */
    private $maxHeight = 1500;

    /**
     * @var int
     */
    private $maxWidth = 1500;
    
    public function setInputParams (array $inputParams) {
        $defaultOptions=array(
            'width' => $inputParams['size'][0],
            'height' => $inputParams['size'][1],
            'aspect' => true,
            'crop' => 100,
            'size' => null, //array, result working function getimagesize()
            'cacheFile' => null,
            'path' => null,
        	'background'=> null
        );
        $this->params = array_merge($defaultOptions, $inputParams);
        $this->params['crop'] = (int)$this->params['crop'];
        $this->params['width'] = (int)$this->params['width'];
        $this->params['height'] = (int)$this->params['height'];
        $this->params['aspect'] = (bool)$this->params['aspect'];

        $this->_checkParams();
    }

    /**
     * @throws PhpResizer_PhpResizerException
     */
    protected function _checkParams()
    {
        if ($this->params['width'] < 1 
            || $this->maxWidth < $this->params['width'])
        {
            $this->params['width'] = $this->maxWidth;
        }

        if ($this->params['height'] < 1 
            || $this->maxHeight < $this->params['height'])
        {
            $this->params['height'] = $this->maxHeight;
        }

        if ($this->params['crop'] <= 0  
            || $this->_defaultCropSize < $this->params['crop'])
        {
            $this->params['crop'] = $this->_defaultCropSize;
        }

        if (!$this->params['size']
            ||!is_array($this->params['size']))
        {
            throw new PhpResizer_Exception_Basic(sprintf(self::EXC_BAD_PARAM,'size'));
        }
        
        if($this->params['background']) {
        /*
         * @todo write check param fill
         */
        }        
        

    }

    public function calculateParams()
    {
    	
        extract($this->params);

        $srcX = 0; $srcY = 0;

        if ($aspect) {
            if (($size[1]/$height) > ($size[0]/$width)) {
                $dstWidth = ceil(($size[0]/$size[1]) * $height);
                $dstHeight = $height;
            } else {
                $dstHeight = ceil($width / ($size[0]/$size[1]));
                $dstWidth = $width;
            }
        } else {
			$dstHeight = $height;
			$dstWidth = $width;
			if (($height/$width) <= ($size[1]/$size[0])) {
				$temp=$height*($size[0]/$width);
				$srcY=ceil(($size[1]-$temp)/2);
                $size[1]=ceil($temp);
			} else {
				$temp=$width*($size[1]/$height);
				$srcX=ceil(($size[0]-$temp)/2);
                $size[0]=ceil($temp);
           }
        }

        if (100 != $crop) {
            $crop = $this->params['crop'];
            $srcX += ceil((100-$crop)/200*$size[0]);
            $srcY += ceil((100-$crop)/200*$size[1]);
            $size[0] = ceil($size[0]*$crop/100);
            $size[1] = ceil($size[1]*$crop/100);
        }

        return array(
        	'srcGetImageSize'=> $size,
        	'width' => (int) $width,
        	'height' => (int) $height,
            'srcX' => (int) $srcX,
            'srcY' => (int) $srcY,
            'srcWidth' => (int) $size[0],
            'srcHeight' => (int) $size[1],
            'dstX' => 0,
            'dstY' => 0,
            'dstWidth' => (int) $dstWidth,
            'dstHeight' => (int) $dstHeight,
        	'background'=> $background,        	
        );
    }
}
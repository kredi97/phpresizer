<?php
class PhpResizer_Calculator_Calculator 
	implements PhpResizer_Calculator_Interface
{

    const DEFAULT_CROP = 100; // 0..100
    const DEFAULT_ASPECT = true;
    const DEFAULT_QUALITY = 85;	// 0..100
    const DEFAULT_PNGCOMPRESS = 9;	//0..9
    const DEFAULT_ZOOM_SMALL_IAMGE = true; // bool

	/**
     * @var int
     */
    private $maxAvalibleWidth = 1500;
    
    /**
     * @var int
     */
    private $maxAvalibleHeight = 1500;
    
    
    public function checkAndCalculateParams (array $inputParams) {
        $defaultOptions=array(
            'width' => $inputParams['size'][0],
            'height' => $inputParams['size'][1],
            'aspect' => self::DEFAULT_ASPECT,
            'crop' => self::DEFAULT_CROP,
        	'quality' => self::DEFAULT_QUALITY,
        	'pngCompress'=> self::DEFAULT_PNGCOMPRESS,
            'size' => null, //array, result working function getimagesize()
            'cacheFile' => null,
            'path' => null,
        	'background'=> null,
        	'zoomSmallImage'=>self::DEFAULT_ZOOM_SMALL_IAMGE
        );
        $this->params = array_merge($defaultOptions, $inputParams);
        
        $this->params['crop'] = (int)$this->params['crop'];
        $this->params['width'] = (int)$this->params['width'];
        $this->params['height'] = (int)$this->params['height'];
        $this->params['aspect'] = (bool)$this->params['aspect'];
        $this->params['zoomSmallImage'] = (bool)$this->params['zoomSmallImage'];

        $this->_checkParams();

        return $this->_calculateParams();
    }

    /**
     * @throws PhpResizer_PhpResizerException
     */
    protected function _checkParams()
    {
        if ($this->params['width'] < 1
            || $this->params['width'] > $this->maxAvalibleWidth)
        {
            $this->params['width'] = $this->maxAvalibleWidth;
        }

        if ($this->params['height'] < 1
            ||  $this->params['height'] > $this->maxAvalibleHeight)
        {
            $this->params['height'] = $this->maxAvalibleHeight;
        }

        if ($this->params['crop'] <= 0
            || $this->params['crop'] > 100)
        {
            $this->params['crop'] = self::DEFAULT_CROP;
        }

        if (!$this->params['size']
            ||!is_array($this->params['size']))
        {
            throw new PhpResizer_Exception_Basic(sprintf(self::EXC_BAD_PARAM, 'size'));
        }
        
        if($this->params['background'] && !preg_match('/[0-9a-f]{6}/ui', $this->params['background'])) {
			$this->params['background'] = null;
        }
        
		if($this->params['quality'] < 1 || $this->params['quality'] > 100) {		
        	$this->params['quality'] = self::DEFAULT_QUALITY;
        }
         
        if($this->params['pngCompress'] < 1 || $this->params['pngCompress'] > 9) {
        	$this->params['pngComperss'] = self::DEFAULT_PNGCOMPRESS;
        }
    }

    /**
     * 
     *  @toda needRefacoring
     *  check paramName;
     *  
     */
    private function _calculateParams()
    {
        extract($this->params);
        $sizeCopy = $size;
        $srcX = 0; $srcY = 0;

        if ($aspect) {
            if (($sizeCopy[1]/$height) > ($sizeCopy[0]/$width)) {
                $dstWidth = ceil(($sizeCopy[0]/$sizeCopy[1]) * $height);
                $dstHeight = $height;
            } else {
                $dstHeight = ceil($width / ($sizeCopy[0]/$sizeCopy[1]));
                $dstWidth = $width;
            }
        } else {
			$dstHeight = $height;
			$dstWidth = $width;
			if (($height/$width) <= ($sizeCopy[1]/$sizeCopy[0])) {
				$temp=$height*($sizeCopy[0]/$width);
				$srcY=ceil(($sizeCopy[1]-$temp)/2);
                $sizeCopy[1]=ceil($temp);
			} else {
				$temp=$width*($sizeCopy[1]/$height);
				$srcX=ceil(($sizeCopy[0]-$temp)/2);
                $sizeCopy[0]=ceil($temp);
           }
        }

        if (100 != $crop) {
            $crop = $this->params['crop'];
            $srcX += ceil((100-$crop)/200*$size[0]);
            $srcY += ceil((100-$crop)/200*$size[1]);
            $sizeCopy[0] = ceil($sizeCopy[0]*$crop/100);
            $sizeCopy[1] = ceil($sizeCopy[1]*$crop/100);
        }

        $outputData = array(
        	'srcGetImageSize'=> $size,
        	'zoomSmallImage'=> $zoomSmallImage,
        	'width' => (int) $width,
        	'height' => (int) $height,
            'srcX' => (int) $srcX,
            'srcY' => (int) $srcY,
            'srcWidth' => (int) $sizeCopy[0],
            'srcHeight' => (int) $sizeCopy[1],
            'dstX' => 0,
            'dstY' => 0,
            'dstWidth' => (int) $dstWidth,
            'dstHeight' => (int) $dstHeight,
        	'background'=> $background,
        	'quality' => (int)$quality,
        	'pngCompress' => (int)$pngCompress
        );
        
	
        if(!$outputData['zoomSmallImage'] && 
        ($outputData['dstWidth'] >= $outputData['srcGetImageSize'][0] 
        || $outputData['dstHeight'] >= $outputData['srcGetImageSize'][1])){      
        	
        	$outputData['srcX'] = 0;
        	$outputData['srcY'] = 0;
        	$outputData['dstWidth'] = $outputData['srcWidth'] =  $outputData['width'] = $outputData['srcGetImageSize'][0];
        	$outputData['dstHeight'] = $outputData['srcHeight'] = $outputData['height'] = $outputData['srcGetImageSize'][1];
        	  
        }        
 
        return $outputData;
    }
}
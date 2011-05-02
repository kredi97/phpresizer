<?php
interface PhpResizer_Calculator_Interface {

	/**
     * Merge params and check 
     * @param array $params
     * @throws PhpResizer_Exception_Basic
     */
	public function setInputParams (array $inputParams);

	public function calculateParams ();
	
}
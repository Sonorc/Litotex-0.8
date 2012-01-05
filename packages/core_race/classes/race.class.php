<?php
/*
 * Copyright (c) 2010 Litotex
 * 
 * Permission is hereby granted, free of charge,
 * to any person obtaining a copy of this software and
 * associated documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy,
 * modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit
 * persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * 
 * The above copyright notice and this permission notice
 * shall be included in all copies or substantial portions
 * of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */
class race{
	private $_id;
	private $_name;
	private $_image;
	private $_description;
	public function __construct($raceID){
		self::_getRaceData($raceID);
	}
	public function getName(){
		return $this->_name;
	}
	public function getID(){
		return (int)$this->_id;
	}
	public function getImage(){
		return $this->_image;
	}
	public function getDescription(){
		return $this->_description;
	}
	
	public function setName($name){
		package::$db->AutoExecute('lttx_races', array('name' => $name), 'UPDATE', '`id` = ' . $this->getID());
		$this->_name = $name;
	}
	public function setImage($image){
		package::$db->AutoExecute('lttx_races', array('image' => $image), 'UPDATE', '`id` = ' . $this->getID());
		$this->_image = $image;
	}
	public function setDescription($description){
		package::$db->AutoExecute('lttx_races', array('description' => $description), 'UPDATE', '`id` = ' . $this->getID());
		$this->_description = $description;
	}
	private function _getRaceData($raceID){
		$result = package::$db->Execute("SELECT `id`, `name`, `image`, `description` FROM `lttx_races` WHERE `id` = ?", array($raceID));
		if(!isset($result->fields[0]))
			throw new lttxError('E_race_not_found', $raceID);
		$this->_id = $result->fields[0];
		$this->_name = $result->fields[1];
		$this->_image = $result->fields[2];
		$this->_description = $result->fields[3];
	}
}
<?php
class explorePluginHandler extends plugin_handler{
	protected $_name = "explores";
	protected $_location = "explores";
	protected $_cacheLocation = "../cache/explore.cache.php";
	protected $_currentFile = __FILE__;
}

class exploreDependencyPluginHandler extends plugin_handler{
	protected $_name = 'dependencies';
	protected $_location = 'dependencies';
	protected $_cacheLocation = "../cache/exploreDependencies.cache.php";
	protected $_currentFile = __FILE__;
}

class explore{
	private $_ID;
	private $_data;
	private $_changed = false;
	private $_initialized = false;
	private $_pluginHandler = false;
	private $_plugins = array();
	private $_timeFormula = '';
	private $_pointsFormula = '';
	private $_dependencyPluginHandler = false;
	public function __construct($exploreID){
		$data = package::$db->Execute("SELECT `name`, `race`, `plugin`, `pluginPreferences`, `timeFormula`, `pointsFormula` FROM `lttx_explores` WHERE `ID` = ?", array($exploreID));
		if(!isset($data->fields[0]))
			throw new Exception('Explore ' . $exploreID .' was not found');
		$plugin = $data->fields[2];
		$pluginPreferences = $data->fields[3];
		if(($plugin = unserialize($plugin)) === false)
			return;
		if(($pluginPreferences = unserialize($pluginPreferences)) === false)
			return;
		foreach($plugin as $i => $pluginName){
			if(!isset($pluginPreferences[$i]))
				return;
			$this->_plugins[$pluginName] = $pluginPreferences[$i];
		}
		$this->_initialized = true;
		$this->_data['name'] = $data->fields[0];
		$this->_data['race'] = $data->fields[1];
		$this->_ID = $exploreID;
		$this->_pluginHandler = new explorePluginHandler();
		$this->_dependencyPluginHandler = new exploreDependencyPluginHandler();
		$this->_timeFormula = $data->fields[4];
		$this->_pointsFormula = $data->fields[5];
	}
	public function __destruct(){
		$this->flush();
	}
	public function getName(){
		if(!$this->initialized())
			return false;
		return $this->_data['name'];
	}
	public function getCost($level){
		if(!$this->_initialized)
			return false;
		$resource = new ressource($this->_data['race'], 'explore', $this->_ID, false, true);
		$resource->useFormula($level);
		package::$packages->callHook('manipulateExploreCost', array(&$resource));
		return $resource;
	}
	public function initialized(){
		return (bool)$this->_initialized;
	}
	public function castFunction($function, $params){
		$return = true;
		$replaceKeys = array();
		foreach($params as $i => $param){
			if($param == '$preferences')
				$replaceKeys[] = $i;
		}
		foreach($this->_plugins as $pluginName => $pluginPreferences){
			foreach($replaceKeys as $replaceKey){
				$params[$replaceKey] = $pluginPreferences;
			}
			if(!$this->_pluginHandler->callPluginFunc($pluginName, $function, $params))
				$return = false;
		}
		return $return;
	}
	public function getBuildTime($level){
		if(!math::verifyFormula($this->_timeFormula))
			return false;
		$formula = math::replaceX($this->_timeFormula, (int)$level);
		return math::calculateString($formula);
	}
	public function getPoints($level){
		if(!math::verifyFormula($this->_pointsFormula))
			return false;
		$formula = math::replaceX($this->_pointsFormula, (int)$level);
		return math::calculateString($formula);
	}
	public function increaseExploreLevel($level, territory $territory){
		return $this->castFunction('increaseLevel', array($territory, $level, '$preferences', $this->_ID));
	}
	public function getDependencies($level){
		$dep = package::$db->Execute("SELECT `plugin`, `pluginPreferences` FROM `lttx_exploreDependencies` WHERE `sourceID` = ? AND `level` <= ?", array($this->_ID, (int)$level));
		$return = array();
		if(!$dep)
			return false;
		while(!$dep->EOF){
			$return[] = array($dep->fields[0], unserialize($dep->fields[1]));
			$dep->MoveNext();
		}
		return $return;
	}
	public function checkDependencies(territory $territory, $level){
		$depList = $this->getDependencies($level);
		$return = true;
		foreach($depList as $dep){
			if(!$this->_dependencyPluginHandler->callPluginFunc($dep[0], 'checkDependency', array($territory, $dep[1])))
				$return = false;
		}
		return $return;
	}
	public static function getAllByRace($race){
		$result = package::$db->Execute("SELECT `ID` FROM `lttx_explores` WHERE `race` = ?", array($race));
		$return = array();
		if(!$result)
			return false;
		while(!$result->EOF){
			$return[] = new explore($result->fields[0]);
			$result->MoveNext();
		}
		return $return;
		//TODO: Cache to make no extra Database connections
	}
	public function addExplore(){
		
	}
	public function flush(){
		
	}
}
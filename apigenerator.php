<?php
/**
 * Class managing api
 * This class will read some file and generate a rest api map for beeing used with a restfull api
 */
class ApiGenerator
{
	private $apiMap;
	private $config;
	private $config_file = "config/api_config.json";

	/**
	 * Simple constructor wich only initalise var
	 */
	function __construct($config_file) {
		$this->apiMap = [];
		$this->config_file = $config_file;

		$this->config->using_cache = false;
		$this->config->cache_dir = "cache";

	}

	/**
	 * Load the config file if exist
	 * try into config dir or at the root
	 */
	private function loadConfig(){
		if(file_exists($this->config_file)){
			$this->config = json_decode(file_get_contents($this->config_file),true);
		}

	}
	/**
	 * Return the name of the cache file for a class and function
	 */
	private function getNameFileCache($class,$function,$param)
	{
		$param = md5(json_encode($param));
		return $this->getDirFileCache($class,$function).DIRECTORY_SEPARATOR.$param.".cache";
	}

	/**
	 * Return the name of the cache directory for a class and function
	 */
	private function getDirFileCache($class,$function)
	{
		return $this->config->cache_dir.DIRECTORY_SEPARATOR.$class.DIRECTORY_SEPARATOR.$function;
	}

	/**
	 * Generate cache file
	 */
	private function genFileCache($class="",$function="",$param="main",$content)
	{
		if(!is_dir($this->config->cache_dir))
		{
			mkdir($this->config->cache_dir);
		}
		if(!is_dir($this->config->cache_dir.DIRECTORY_SEPARATOR.$class))
		{
			mkdir($this->config->cache_dir.DIRECTORY_SEPARATOR.$class);
		}
		if(!is_dir($this->getDirFileCache($class,$function)))
		{
			mkdir($this->getDirFileCache($class,$function));
		}
		file_put_contents($this->getNameFileCache($class,$function,$param), serialize($content));
	}

	/**
	 * Read File cache and return content
	 */
	private function getFileCache($class="",$function="",$param="main"){
		return unserialize(file_get_contents($this->getNameFileCache($class,$function,$param)));
	}

	/**
	 * Delete cache file
	 */
	private function delFileCache($class="",$function="",$param="main"){
		if($param == "main"){
			if(file_exists($this->getDirFileCache($class,$function)))
			{
				foreach (scandir($this->getDirFileCache($class,$function)) as $item) {
					if ($item == '.' || $item == '..') continue;
					unlink($this->getDirFileCache($class,$function).DIRECTORY_SEPARATOR.$item);
				}
				rmdir($this->getDirFileCache($class,$function));
			}
		}else{
			unlink($this->getNameFileCache($class,$function,$param));
		}
	}

	/**
	 * Set cache mode
	 */
	public function setCache($bool)
	{
		$this->config->using_cache = $bool;
	}

	/**
	 * List file and include only one that start by "api" like apimodulelog.php
	 * Do an eval of the source of this file
	 * Generate the api map
	 */
	public function load(){

		$file_cache_exists = file_exists($this->getNameFileCache("api","map","main"));

		$dir = opendir("./");
		$class;
		while($file = readdir($dir))
		{
			$class = null;
			if(substr_compare($file,"api", 0,3) == 0 && substr_compare($file,"apimanager.php",0) != 0)
			{
				require_once($file);
				if(!$this->config->using_cache || !$file_cache_exists)
				{
					$class = $this->get_php_classes($file);
					if(!is_null($class["apiname"])){
						$class["apielement"]["classname"] = $class["name"];
						$class["apielement"]["function"] = $class["apifunction"];
						$this->apiMap += [$class["apiname"] => $class["apielement"]];
					}
				}
			}
		}
		closedir($dir);
		if($this->config->using_cache && !$file_cache_exists)
		{
			$this->genFileCache("api","map", "main",$this->apiMap);
		}

		if($this->config->using_cache && $file_cache_exists)
		{
			$this->apiMap = $this->getFileCache("api","map");
		}
	}

 	/**
 	 * @link http://stackoverflow.com/questions/928928/determining-what-classes-are-defined-in-a-php-class-file?answertab=votes
 	 * Parse php_string code dans return classname and interface
 	 * @param string $php_code The php code to parse
 	 * @return string[] return ex : ["apiname"=>"foo",apifunction=>["functionname"]=>"bar","help"=>"help me"]
 	 */
	private function get_php_classes($file) {
		$php_code = file_get_contents($file);
		$classes = array();
		$parentclasse = array();
		$disablefunc = array();
		$include_filename = array();
		$classes["apiname"] = null;
		$classes["apifunction"] = [];
		$tokens = token_get_all($php_code);
		$count = count($tokens);
		for ($i = 2; $i < $count; $i++) {
			//get include file
			if(($tokens[$i - 2][0] == T_REQUIRE_ONCE || $tokens[$i - 2][0] == T_REQUIRE) && $tokens[$i][0] == T_CONSTANT_ENCAPSED_STRING){
				$name = trim($tokens[$i][1],"'");
				$namebase = substr($name, 0, -4);
				$include_filename[$namebase] = $name;
			}
			//get class name
			if ( $tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) {
				$class_name = $tokens[$i][1];
				$comment = $tokens[$i - 4][1];
				if(preg_match("/\*\s+@apiname\s+(\w+)/", $comment, $output_array) == 1){
					$classes["name"] = $class_name;
					$classes["apiname"] = $output_array[1];

					$disable_count = preg_match_all("/\*\s+@apifunctiondisable(?:\s+)?(\S+)/", $comment, $output_array_disable);
					for($j = 0; $j < $disable_count; $j++){
						array_push($disablefunc,$output_array_disable[1][$j]);
					}
				}
			}

			//get include file
			if($tokens[$i - 2][0] == T_EXTENDS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING){
				$name = strtolower($tokens[$i][1]);
				$parentclasse = $this->get_php_classes($include_filename[$name]);
			}

			//load files
			if ($tokens[$i - 2][0] == T_FUNCTION && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING && !is_null($classes["apiname"])) {
				$comment = null;
				if($tokens[$i - 6][0] == T_DOC_COMMENT || $tokens[$i - 4][0] == T_DOC_COMMENT){
					$comment = ($tokens[$i - 6][0] == T_DOC_COMMENT)?$tokens[$i - 6][1]:$tokens[$i - 4][1];
				}
				if(!is_null($comment)){
					$funcname = $tokens[$i][1];
					if(preg_match("/\*\s+@apifunction\s+(\w+)(?:\s+)?(default)?(?:\s+)?(auth)?(?:\s+)?(group:(\S+))?(?:\s+)?(cache)?/", $comment, $output_array) == 1){
						$param = $this->extractParams($tokens,$i);
						$auth = (isset($output_array[3]) && $output_array[3] == "auth")?true:false;
						$groups = ["user"];
						if(isset($output_array[4])){
							$groups = explode('|',$output_array[5]);
						}
						$output_array[1] = (isset($output_array[2]) && $output_array[2] == "default")?"default":$output_array[1];
						$cache = [];
						$cache['del'] = [];
						$cache['activate'] = (isset($output_array[6]) && $output_array[6] == "cache")?true:false;
						$del_count=0;
						$del_count = preg_match_all("/\*\s+@apicachedel(?:\s+)?(\S+)(?:\s+)?(\S+)/", $comment, $output_array_cache);
						for($j = 0; $j < $del_count; $j++){
							$output_array_cache[1][$j] = ($output_array_cache[1][$j] == "this")?$classes["name"]:$output_array_cache[1][$j];
							array_push($cache['del'],["classname"=>$output_array_cache[1][$j],"functionname"=>$output_array_cache[2][$j]]);
						}
						foreach($groups as $value){
							$classes["apifunction"][$value][$output_array[1]] = ["functionname"=>$funcname,
																				 "help"=>$this->generateHelpFunction($comment,$param),
																				 "auth"=>$auth,
																				 "cache"=>$cache,
																				 "disable"=>(in_array($funcname,$disablefunc))?true:false
																				];
							foreach($disablefunc as $funcnamedisable){
								if(isset($parentclasse["apifunction"][$value][$funcnamedisable])){
									$parentclasse["apifunction"][$value][$funcnamedisable]["disable"] = true;
								}
							}
							if(isset($parentclasse["apifunction"][$value]))
							{
								$classes["apifunction"][$value] = array_merge($classes["apifunction"][$value],$parentclasse["apifunction"][$value]);
							}
						}
					}
				}
			}
		}
		return $classes;
	}


	/**
	 * Generate help for a function
	 * $return
	 */
	private function generateHelpFunction($comment,$var){
		$return = [];
		$return["description"] = "";
		$return["params"] = [];

		$lines = preg_split ('/$\R?^/m', $comment);
		foreach($lines as $line)
		{
			if(preg_match("/\*\s+([^@].+)$/", $line, $output_array) == 1)
			{
				$return["description"] = $return["description"].$output_array[1]." ";
			}
			if(preg_match("/\*\s+@apiresponce\s+(.+)/", $line, $output_array) == 1){
				$return["return"] = $output_array[1];
			}
			else if(preg_match("/\*\s+@return\s+(.+)/", $line, $output_array) == 1){
				$return["return"] = $output_array[1];
			}
		}

		//parse variables and generate
		foreach($var as $key => $value)
		{
			$param_help = [];
			$value = ltrim ($value,'$');
			if(preg_match("/\*\s+@param\s+(\w+)?(\s+)?\\$$value(?:\s+)?(.+)?/", $comment, $output_array) == 1)
			{
				$param_help = ["type"=>$output_array[1],"help"=>$output_array[3]];
			}
			$return["params"][$value] = $param_help;
		}
		return $return;
	}

	/**
	 * Extract param for function
	 * @param $token string[] tokens of the source code
	 * @param $start position where we are in the tokens
	 */
	private function extractParams($tokens,$start){
		$pos = $start +1;
		$return = [];
		while($tokens[$pos] != ")"){
			if($tokens[$pos][0] == T_VARIABLE){
				$return = array_merge($return, [$tokens[$pos][1]]);
			}
			$pos++;
		}
		return $return;
	}

	/**
	 * Execute api
	 * @param string[] $url element
	 * @param bool $auth authentification status
	 * @return string[] responce from the api
	 */
	public function execute($elementsUrl,$auth=false,$grp="user"){
		if(isset($elementsUrl[0]) && array_key_exists($elementsUrl[0],$this->apiMap))
		{
			#get the class to execute
			$route = $this->apiMap[$elementsUrl[0]];

			#remove first api not need anymore
			array_splice($elementsUrl,0,1);

			#define return array
			$execute = true;
			$default = true;
			$return = [];
			$function = (isset($route['function'][$grp]["default"]))?$route['function'][$grp]["default"]:null;

			#find if exist into the api and if true switch with the function
			if(isset($elementsUrl[0]) && isset($route['function'][$grp]) && array_key_exists($elementsUrl[0],$route['function'][$grp])){
				$default = false;
				$function = $route['function'][$grp][$elementsUrl[0]];
			}
			else{
				foreach($route['function'] as $group){
					if(isset($elementsUrl[0]) && array_key_exists($elementsUrl[0],$group)){
						$return['error'] = "You aren't in the right group";
						$execute = false;
					}
				}
			}
			if($auth == $function['auth'] && !$function["disable"]){
				if($execute)
				{
					$param_cache = (isset($elementsUrl[0]))?$elementsUrl[0]:"main";
					$file_cache_exists = file_exists($this->getNameFileCache($route["classname"],$function["functionname"],$param_cache));

					if(!$default){
						array_splice($elementsUrl,0,1);
					}
					if(!$this->config->using_cache || !$function['cache']['activate'] || !$file_cache_exists || !$function["disable"])
					{
						$call = new ReflectionMethod( $route["classname"], $function["functionname"] );
						$return = $call->invokeArgs( new $route["classname"](), $elementsUrl);
					}

					if($this->config->using_cache && $function['cache']['activate'] && !$file_cache_exists)
					{
						$this->genFileCache($route["classname"], $function["functionname"],$param_cache, $return);
					}

					if($this->config->using_cache && $file_cache_exists && $function['cache']['activate'])
					{
						$return  = $this->getFileCache($route["classname"], $function["functionname"],$param_cache);
					}

					foreach($function['cache']['del'] as $delement){
						if(isset($return['param'])){
							$this->delFileCache($delement["classname"], $delement["functionname"],$return['param']);
						}else{
							$this->delFileCache($delement["classname"], $delement["functionname"]);
						}

					}
					unset($return['param']);
				}
			}
			else{
				$return['error'] = "you must be logged to access";
			}
		}
		else #if not present print element and print maping_api
		{
			$return =  $this->getHelp();
			$return['error'] = "your request is not into the api<br />see the api array for list";
		}
		return $return;
	}

	/**
	 * get help for the api
	 * @return help[]
	 */
	private function getHelp(){
		$return['api'] = $this->apiMap;
		foreach($return['api'] as $key => $value)
		{
			$help = [];
			foreach($value["function"] as $group => $list_func){
				$list = [];
				foreach($list_func as $key2 => $value2){
					if(!$value2['disable']){
						$list+=[$key2 => $value2['help']];
					}
				}
				$help+= [$group => $list];
			}
			$return['api'][$key] = $help;
		}
		return $return;
	}
}
?>

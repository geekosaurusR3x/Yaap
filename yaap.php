<?php
//seting up the auto load for parser
spl_autoload_register(function ($class) {
    include 'parser/' . $class . '.php';
});



//look to http://php.net/manual/fr/function.getallheaders.php#99814
if (!function_exists('apache_request_headers'))
{
	function apache_request_headers()
	{
		$headers = [];

		foreach ($_SERVER as $name => $value){
			if (substr($name, 0, 5) == 'HTTP_')
			{
				$headers[str_replace(' ', '-', (str_replace('_', ' ', substr($name, 5))))] = $value;
			}else{
				$headers[$name]=$value;
			}
		}

		return $headers;
	}
}

/**
 * Class managing api
 * This class will read some file and generate a rest api map for beeing used as a restfull api
 */

class Yaap
{
	private $apiMap;
	private $config;
	private $config_file;
	private $request;
	private $data_paser;

	/**
	 * Simple constructor wich only initalise var
	 * @param string $config_file alternate path for the config file
	 */
	function __construct($config_file = "api_config.json" ) {
		$this->apiMap = [];
		$this->config_file = $config_file;
		$this->loadConfig();
	}

	/**
	 * Load the config file if exist
	 * try into config dir or load default config
	 */
	private function loadConfig(){
		if(file_exists($this->config_file)){
			$this->config = json_decode(file_get_contents($this->config_file));
		}
		else{
			$this->config = new stdClass();
			$this->config->using_cache = false;
			$this->config->cache_dir = "cache";
			$this->config->default_usr_group = "usr";
			$this->config->base_of_header = "X_API_";
			$this->config->base_url = "api/";
			$this->config->data_type = "application/json";
			$this->config->help = true;
		}

	}

	/**
	 * Load the url request, request method, param and header
	 */
	function getRequest(){
		$headers = apache_request_headers();
		$this->request = new stdClass();

		$this->request->elements = explode("/",str_replace($this->config->base_url, '', $headers['REQUEST_URI']));
		$this->request->method = $headers['REQUEST_METHOD'];
		$this->request->content_type = (strcmp($headers['CONTENT_TYPE'],'') != 0)?$headers['CONTENT_TYPE']:$this->config->data_type;

		$data_type = explode(";",$this->request->content_type)[0];
		$data_type = explode("/",$data_type)[1];

		$class = $data_type."Parser";
		$this->parser_request = new $class();

		$data = file_get_contents('php://input');
		$this->request->post = $_POST;
		try {
			$this->request->data = $this->parser_request->decode($data);
		}catch (Exception $e){
			$this->send(['responce_code'=>400,
					   'message'=>$e->getMessage()]);
		}

	}

	/**
	 * Execute api
	 */
	public function execute(){
		return $this->request;
	}


	/**
	 * Execute the function and output the answer directly.
	 * Use it if you haven't thing doing after processing data before send it
	 */
	public function executeAndSend(){

		$return = $this->execute();
		$this->send($return);
	}

	/**
	 * Output the answer after passing by the parser
	 * set the content type from the request and after echo the parser encoded datas
	 * @param Array $data the data to encode
	 */
	public function send($data){

		if(isset($data['responce_code'])){
			http_response_code ($data['responce_code']);
			unset($data['responce_code']);
		}

		header("Content-Type: ".$this->request->content_type);
		echo($this->parser_request->encode($data));
		die();
	}
}
?>

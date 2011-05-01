<?

class Home implements Constants{

	public $error = "";

	public function __construct() {
		if ($_POST) {
			$this->doPost();
		} else {
			$this->doGet();
		}	
	}
	
	
	public function doGet() {
		if(isset($_GET['url'])){
			$sql = SQL::getInstance();
			$longurl = $sql->getLongURL($this->getIDFromShortenedURL($_GET['url']));
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' .  $longurl[0][0]);
			exit;
		}
		else{
			include_once('skin/default/home.html');
		}
	}
	
	
	public function doPost() {
		
		$url = $_POST['longurl'];	
		
		//Check url is valid and doesnt return a 404 error.
		if ($url == "") {
			$this->error = "Please enter a url";
		}
		else //check the url doesnt return a 404 error
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch,  CURLOPT_RETURNTRANSFER, TRUE);
			$response = curl_exec($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if($http_code == '404')
			{
				$this->error = "URL entered cannot be found";
			}
		}
		
		//Check url is valid
		if (!preg_match("/^(https?:\/\/+[\w\-]+\.[\w\-]+)/i",$url))
		{
		    $this->error = "URL address entered not valid, if you think this is an error please email the site admin";
		}
		
		//Check url has not been shortened before to eliminate database access for incorrect urls this is only on pre-validated urls.
		if ($this->error == "") {
			$sql = SQL::getInstance();
			$duplicate = $sql->checkDBforURL($url);
			if(!empty($duplicate)){
				$this->error = self::BASE_HREF . $this->getShortenedURLFromID($duplicate['0']['0']);
			}
			
		}
		
		// Was validation successful?
		if ($this->error != "") {
			include_once('skin/default/home.html');
			exit();
		} else {
			$insert_id = $sql->addURL($url);
			if ($insert_id < 1) {
				$this->error = "There was a problem generating your short URL, please try again";
				include_once('skin/default/home.html');
				exit();
			} else {
				$this->error = self::BASE_HREF . $this->getShortenedURLFromID($insert_id);
				include_once('skin/default/home.html');
			}
		}
	}

	public function getShortenedURLFromID ($integer, $base = self::ALLOWED_CHARS)
	{
		$out = '';
		$length = strlen($base);
		while($integer > $length - 1)
		{
			$out = $base[fmod($integer, $length)] . $out;
			$integer = floor( $integer / $length );
		}
		return $base[$integer] . $out;
	}
	
	public function getIDFromShortenedURL ($string, $base = self::ALLOWED_CHARS)
	{
		$length = strlen($base);
		$size = strlen($string) - 1;
		$string = str_split($string);
		$out = strpos($base, array_pop($string));
		foreach($string as $i => $char)
		{
			$out += strpos($base, $char) * pow($length, $size - $i);
		}
		return $out;
	}
	

}
?>
<?

class SQL implements Constants{

	private static $sql;
	private $dbh;

	private function __construct() {
		try {
		    $this->dbh = new PDO('mysql:host=' . self::DB_HOST . ';dbname=' . self::DB_NAME, self::DB_USER, self::DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
		} catch (PDOException $e) {
		    error_log('Database connection failed: ' . $e->getMessage());
		}
	}

	public static function getInstance() {
		if (! isset(self::$sql)) {
			$class = __CLASS__;
			self::$sql = new $class;
		} 
		return self::$sql;	
	}
	
	public function addURL($url) {
		$sth = $this->dbh->prepare('INSERT INTO shortenedurls (long_url, created, creator) VALUES (:long_url, :created, :creator)');
		$sth->bindParam(':long_url', $url, PDO::PARAM_STR);
		$sth->bindParam(':created', time(), PDO::PARAM_STR);
		$sth->bindParam(':creator', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
		$sth->execute();
		return $this->dbh->lastInsertId();
	}
	
	public function checkDBforURL($url) {
		$sth = $this->dbh->prepare('SELECT id FROM shortenedurls WHERE long_url= :url');
		$sth->bindParam(':url', $url, PDO::PARAM_STR);
		$sth->execute();
		return $sth->fetchAll();
	}
	
	public function getLongURL($id) {
		$sth = $this->dbh->prepare('SELECT long_url FROM shortenedurls WHERE id= :id');
		$sth->bindParam(':id', $id, PDO::PARAM_STR);
		$sth->execute();
		return $sth->fetchAll();
	}
} 

?>
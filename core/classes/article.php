<?php

// Class to handle articles

class Article
{
  // Properties  
  public $id = null; 			  // @var int The article ID from the database
  public $publicationDate = null; // @var int When the article is to be / was first published
  public $title = null; 		  // @var string Full title of the article
  public $summary = null; 		  // @var string A short summary of the article
  public $content = null; 		  // @var string The HTML content of the article
  public $tags = null; 		  	  // @var string Tags
  
	// Sets the object's properties using the values in the supplied array
	// @param assoc The property values
  public function __construct($data=array()){
    if (isset($data['id'])) 			 $this->id = (int) $data['id'];
    if (isset($data['publicationDate'])) $this->publicationDate = (int) $data['publicationDate'];
    if (isset($data['title'])) 			 $this->title = preg_replace ( "/[^\.\,\-\_\'\"\@\?\!\:\$ a-zA-Z0-9()]/", "", $data['title'] );
    if (isset($data['summary'])) 		 $this->summary = preg_replace ( "/[^\.\,\-\_\'\"\@\?\!\:\$ a-zA-Z0-9()]/", "", $data['summary'] );
    if (isset($data['content'])) 		 $this->content = $data['content'];
    if (isset($data['tags'])) 			 $this->tags = $data['tags'];
  }
  
// Sets the object's properties using the edit form post values in the supplied array
// @param assoc The form post values
public function storeFormValues($params){
	// Store all the parameters
	$this->__construct( $params );
	// Parse and store the publication date
	/*if ( isset($params['publicationDate']) ) {
		$publicationDate = explode ( '-', $params['publicationDate'] );
		if ( count($publicationDate) == 3 ) {
			list ( $y, $m, $d ) = $publicationDate;
			$this->publicationDate = mktime ( 0, 0, 0, $m, $d, $y );
		}
	} else {
	}*/
}

  
public static function getById( $id ) {
	$query = mysql_query('SELECT *, UNIX_TIMESTAMP(publicationDate) AS publicationDate FROM articles WHERE id = ' . $id);
	if (mysql_num_rows($query) > 0){
		$data = mysql_fetch_assoc($query);
		return new Article($data);
	} else {
		header('location:notFound');
		exit;
}
}
  
	// Returns all (or a range of) Article objects in the DB

	// @param int Optional The number of rows to return (default=all)
	// @param string Optional column by which to order the articles (default="publicationDate DESC")
	// @return Array|false A two-element array : results => array, a list of Article objects; totalRows => Total number of articles

  public static function getList( $numRows=1000000, $order="publicationDate DESC" ) {
	global $dbHost;
	global $dbName;
	global $dbUser;
	global $dbPass;
	
	$conn = new PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName, $dbUser, $dbPass);
    $sql = "SELECT SQL_CALC_FOUND_ROWS *, UNIX_TIMESTAMP(publicationDate) AS publicationDate FROM articles
            ORDER BY " . mysql_real_escape_string($order) . " LIMIT :numRows";

    $st = $conn->prepare( $sql );
    $st->bindValue( ":numRows", $numRows, PDO::PARAM_INT );
    $st->execute();
    $list = array();

    while ( $row = $st->fetch() ) {
      $article = new Article( $row );
      $list[] = $article;
    }

    // Now get the total number of articles that matched the criteria
    $sql = "SELECT FOUND_ROWS() AS totalRows";
    $totalRows = $conn->query( $sql )->fetch();
    $conn = null;
    return ( array ( "results" => $list, "totalRows" => $totalRows[0] ) );
  }

	// Inserts the current Article object into the database, and sets its ID property.
  public function insert() {

    // Does the Article object already have an ID?
    if ( !is_null( $this->id ) ) trigger_error ( "Article::insert(): Attempt to insert an Article object that already has its ID property set (to $this->id).", E_USER_ERROR );

    // Insert the Article
	global $dbHost;
	global $dbName;
	global $dbUser;
	global $dbPass;
	
	$conn = new PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName, $dbUser, $dbPass);
	
    $sql = "INSERT INTO articles ( title, summary, content, tags ) VALUES ( :title, :summary, :content, :tags )";
    $st = $conn->prepare ( $sql );
   // $st->bindValue( ":publicationDate", $this->publicationDate, PDO::PARAM_INT );
    $st->bindValue( ":title", $this->title, PDO::PARAM_STR );
    $st->bindValue( ":summary", $this->summary, PDO::PARAM_STR );
    $st->bindValue( ":content", $this->content, PDO::PARAM_STR );
    $st->bindValue( ":tags", $this->tags, PDO::PARAM_STR );
	$st->execute();
    $this->id = $conn->lastInsertId();
    $conn = null;
  }
  
	// Updates the current Article object in the database.

  public function update() {

    // Does the Article object have an ID?
    if ( is_null( $this->id ) ) trigger_error ( "Article::update(): Attempt to update an Article object that does not have its ID property set.", E_USER_ERROR );
   
    // Update the Article
	global $dbHost;
	global $dbName;
	global $dbUser;
	global $dbPass;
	
	$conn = new PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName, $dbUser, $dbPass);
    $sql = "UPDATE articles SET publicationDate=FROM_UNIXTIME(:publicationDate), title=:title, summary=:summary, content=:content WHERE id = :id";
    $st = $conn->prepare ( $sql );
    $st->bindValue( ":publicationDate", $this->publicationDate, PDO::PARAM_INT );
    $st->bindValue( ":title", $this->title, PDO::PARAM_STR );
    $st->bindValue( ":summary", $this->summary, PDO::PARAM_STR );
    $st->bindValue( ":content", $this->content, PDO::PARAM_STR );
    $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
    $st->execute();
    $conn = null;
  }

	// Deletes the current Article object from the database.

  public function delete() {

    // Does the Article object have an ID?
    if ( is_null( $this->id ) ) trigger_error ( "Article::delete(): Attempt to delete an Article object that does not have its ID property set.", E_USER_ERROR );

    // Delete the Article
	global $dbHost;
	global $dbName;
	global $dbUser;
	global $dbPass;
	
	$conn = new PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName, $dbUser, $dbPass);
    $st = $conn->prepare ( "DELETE FROM articles WHERE id = :id LIMIT 1" );
    $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
    $st->execute();
    $conn = null;
  }

}

?>

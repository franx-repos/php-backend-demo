<?php
#*******************************************************************************************#
				
				
				#******************************************#
				#********** ENABLE STRICT TYPING **********#
				#******************************************#

				declare(strict_types=1);
				
				
#*******************************************************************************************#


				#***********************************#
				#********** CLASS BLOG **********#
				#***********************************#

				
#*******************************************************************************************#


				class Blog {
					
					#*******************************#
					#********** ATTRIBUTE **********#
					#*******************************#
					
					private $blogID;
					private $blogHeadline;
					private $blogImagePath;
					private $blogImageAlignment;
					private $blogContent;
					private $blogDate;
					
					// Embedded Objects
					private $user;
					private $category;
					
					
					#***********************************************************#
					
					
					#*********************************#
					#********** CONSTRUCTOR **********#
					#*********************************#
					
					public function __construct( 	$user 				= new User(),
													$category 			= new Category(),
													$blogID 			= NULL, 
													$blogHeadline 		= NULL, 
													$blogImagePath 		= NULL,
													$blogImageAlignment = NULL, 
													$blogContent 		= NULL, 
													$blogDate 			= NULL )
					{
// if(DEBUG_CC)			echo "<p class='debug class'>🛠 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "()  (<i>" . basename(__FILE__) . "</i>)</p>\n";						
						
						/*
							Entweder wird beim Constructor-Aufruf ein einzubettendes Objekt übergeben, oder es wird bei der 
							Parameterübernahme ein leeres Objekt erzeugt. In beiden Fällen wird das einzubettende Objekt
							IMMER in das einbettende Objekt geschachtelt.
						*/
						$this->setUser($user);
						$this->setCategory($category);
						
						// Setter nur aufrufen, wenn der jeweilige Parameter keinen Leerstring und nicht NULL enthält
						if($blogID 				!== NULL AND $blogID 				!== '') $this->setBlogID($blogID);
						if($blogHeadline 		!== NULL AND $blogHeadline 			!== '') $this->setBlogHeadline($blogHeadline);
						if($blogImagePath 		!== NULL AND $blogImagePath 		!== '') $this->setBlogImagePath($blogImagePath);
						if($blogImageAlignment 	!== NULL AND $blogImageAlignment 	!== '') $this->setBlogImageAlignment($blogImageAlignment);
						if($blogContent 		!== NULL AND $blogContent 			!== '') $this->setBlogContent($blogContent);
						if($blogDate 			!== NULL AND $blogDate 				!== '') $this->setBlogDate($blogDate);						
						
// if(DEBUG_CC)			echo "<pre class='debug class value'><b>Line " . __LINE__ . "</b>: \$this<br>". print_r($this, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";					
					}
					
					
					#********** DESTRUCTOR **********#
					public function __destruct() {
// if(DEBUG_CC)			echo "<p class='debug class'>☠️  <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "()  (<i>" . basename(__FILE__) . "</i>)</p>\n";						
					}					
					
					
					#***********************************************************#

					
					#*************************************#
					#********** GETTER & SETTER **********#
					#*************************************#
				
					#********** BLOG ID **********#
					public function getBlogID():NULL|int {
						return $this->blogID;
					}
					public function setBlogID(int|string $value):void {
						
						#********** VALIDATE DATA FORMAT **********#
						if( ($value = filter_var($value, FILTER_VALIDATE_INT)) === false ) {
							// Fehlerfall (nicht erlaubtes Datenformat)
if(DEBUG_C)					echo "<p class='debug class err'><b>Line " . __LINE__ . "</b>: " . __METHOD__ . "():  Muss dem Format 'Integer' entsprechen! <i>(" . basename(__FILE__) . ")</i></p>\n";
							
						} else {
							// Erfolgsfall (erlaubtes Datenformat)
							$this->blogID = $value;
						}						
					}
					
					#********** BLOG HEADLINE **********#
					public function getBlogHeadline():NULL|string {
						return $this->blogHeadline;
					}
					public function setBlogHeadline(string $value):void {						
						$this->blogHeadline = sanitizeString($value);	
					}
					
					#********** BLOG IMAGE PATH **********#
					public function getBlogImagePath():NULL|string {
						return $this->blogImagePath;
					}
					public function setBlogImagePath(string $value):void {						
						$this->blogImagePath = sanitizeString($value);	
					}

					#********** BLOG IMAGE ALIGNMENT **********#
					public function getBlogImageAlignment():NULL|string {
						return $this->blogImageAlignment;
					}
					public function setBlogImageAlignment(string $value):void {						
						$this->blogImageAlignment = sanitizeString($value);	
					}
					
					#********** BLOG CONTENT **********#
					public function getBlogContent():NULL|string {
						return $this->blogContent;
					}
					public function setBlogContent(string $value):void {						
						$this->blogContent = sanitizeString($value);	
					}
					
					#********** BLOG DATE **********#
					public function getBlogDate():NULL|string {
						return $this->blogDate;
					}
					public function setBlogDate(string $value):void {						
						$this->blogDate = sanitizeString($value);	
					}
					
					#********** USER OBJECT **********#
					public function getUser():User {
						return $this->user;
					}
					public function setUser(User $value):void {						
						$this->user = $value;	
					}

					#********** CATEGORY OBJECT **********#
					public function getCategory():Category {
						return $this->category;
					}
					public function setCategory(Category $value):void {						
						$this->category = $value;	
					}
					
					#********** DELEGATIONS **********#
					public function getFullUserNameWithCity():string {
						return $this->getUser()->getFullNameWithCity();
					}

					#********** VIRTUAL ATTRIBUTES **********#
					public function getBlogImage():string {
						return "<img class='blog-img {$this->getBlogImageAlignment()}' src='{$this->getBlogImagePath()}'
									alt='{$this->getBlogHeadline()}' title='{$this->getBlogHeadline()}'>";
					}

					#***********************************************************#


					#******************************#
					#********** METHODEN **********#
					#******************************#


					#********** CREATES BLOG OBJECTS FROM DB RESULTS **********#
					/**
					 * Erstelle Blog-Objekte aus den Datenbankergebnissen.
					 *
					 * @param array $data Das Array mit den Ergebnissen aus der Datenbank.
					 * @return array Ein Array mit erstellten Blog-Objekten.
					 */
					public static function createBlogObjects(array $data): array {
						$blogObjectsArray = [];

						foreach ($data as $row) {
							// Erstelle das User-Objekt
							$user = new User(
								userID: $row['userID'],
								userFirstName: $row['userFirstName'],
								userLastName: $row['userLastName'],
								userCity: $row['userCity']
							);

							// Erstelle das Category-Objekt
							$category = new Category(
								catID: $row['catID'],
								catLabel: $row['catLabel']
							);

							// Erstelle das Blog-Objekt
							$blogObjectsArray[$row['blogID']] = new Blog(
								user: $user,
								category: $category,
								blogID: $row['blogID'],
								blogHeadline: $row['blogHeadline'],
								blogImagePath: $row['blogImagePath'],
								blogImageAlignment: $row['blogImageAlignment'],
								blogContent: $row['blogContent'],
								blogDate: $row['blogDate']
							);
							
						}

						return $blogObjectsArray;
					}

					#***********************************************************#

					#********** FETCH ALL BLOGS DATA FROM DB **********#
					/**
					*
					* FETCH ALL BLOGS DATA FROM DB AND RETURNS ARRAY WITH BLOGOBJECTS
					*
					* @param PDO $PDO DB-Connection object
					*
					* @param $categoryFilterID filters by catID
					*
					* @return array An array containing all Blogs as blog objects
					*
					*/
					public static function fetchBlogs(PDO $PDO, ?int $categoryFilterID = null): array {
						
if(DEBUG_C) 			echo "<p class='debug class'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";

						$filters 	= $categoryFilterID !== null ? ['catID' => $categoryFilterID] : [];
						$joins 		= ['User', 'Category'];
						$orderBy 	= 'blogDate';
				
						$data 		= Db::fetchFromDb($PDO, 'Blog', $joins, $filters, $orderBy);
				
						// Blog-Objekte erstellen und zurückgeben
    					return self::createBlogObjects($data);
					}

					#***********************************************************#

					#********** FETCH BLOGS DATA BY USER ID **********#
					/**
					*
					* FETCH BLOGS DATA FROM DB SELECTED BY USER ID AND RETURNS ARRAY WITH BLOGOBJECTS
					*
					* @param PDO $PDO DB-Connection object
					*
					* @param $userID filters by userID
					*
					* @return array An array containing all Blogs as blog objects
					*
					*/
					public static function fetchBlogsByUserID(PDO $PDO, ?int $userID = null):array {
			
if(DEBUG_C) 			echo "<p class='debug class'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";

						$filters 	= $userID !== null ? ['userID' => $userID] : [];
						$joins 		= ['User', 'Category'];
						$orderBy 	= 'blogDate';
				
						$data 		= Db::fetchFromDb($PDO, 'Blog', $joins, $filters, $orderBy);

						// Blog-Objekte erstellen und zurückgeben
    					return self::createBlogObjects($data);

					}
					#***********************************************************#

					#********** SAVES OBJECTDATA TO DB **********#
					/**
					*
					* SAVES BLOG-OBJECTDATA TO DB
					* WRITES LAST INSERT ID INTO BLOG-OBJECT
					*
					* @param PDO $PDO DB-Connection object
					*
					* @return boolean true if writing was successful, else false
					*
					*/
					public function saveNewBlogToDB(PDO $PDO): int {
						return Db::saveToDb($PDO, 'Blog', [
							'blogHeadline'       => $this->getBlogHeadline(),
							'blogImagePath'      => $this->getBlogImagePath(),
							'blogImageAlignment' => $this->getBlogImageAlignment(),
							'blogContent'        => $this->getBlogContent(),
							'blogDate'           => $this->getBlogDate(),
							'catID'              => $this->getCategory()->getCatID(),
							'userID'             => $this->getUser()->getUserID()
						]);
					}

					#***********************************************************#

					public function updateBlog(PDO $PDO): int {
						return Db::updateToDb($PDO, 'Blog', [
							'catID'              => $this->getCategory()->getCatID(),
							'blogHeadline'       => $this->getBlogHeadline(),
							'blogImagePath'      => $this->getBlogImagePath(),
							'blogImageAlignment' => $this->getBlogImageAlignment(),
							'blogContent'        => $this->getBlogContent()
						], 'blogID', $this->getBlogID());
					}					
					
					#***********************************************************#

					#********** DELETE BLOG POST FROM DB **********#
					/**
					*
					*	DELETES A BLOG POST-DATASET FROM DB
					*	VIA BLOG-ID-ATTRIBUTE
					*
					*	@param	PDO $PDO		DB-Connection object
					*
					*	@return	boolean			true if dataset was deleted, else false
					*
					*/
					public function deleteBlog(PDO $PDO):int {
if(DEBUG_C)				echo "<p class='debug class'>🌀 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";
						
						$sql 				= "DELETE FROM Blog
												WHERE blogID = ?";
						
						$placeholders		= array( $this->getBlogID() );
						
						// Schritt 3 DB: Prepared Statements
						try {
							// Prepare: SQL-Statement vorbereiten
							$PDOStatement = $PDO->prepare($sql);
							
							// Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
							$PDOStatement->execute($placeholders);
							
						} catch(PDOException $error) {
if(DEBUG_C) 				echo "<p class='debug class db err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
						}
						
						// Schritt 4 DB: Daten weiterverarbeiten und DB-Verbindung schließen
						$rowCount = $PDOStatement->rowCount();
if(DEBUG_C)				echo "<p class='debug class value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						return $rowCount;
					}
										
					#***********************************************************#
					
				}

#*******************************************************************************************#
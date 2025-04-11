<?php
#*******************************************************************************************#
				
				
				#******************************************#
				#********** ENABLE STRICT TYPING **********#
				#******************************************#

				declare(strict_types=1);
				
				
#*******************************************************************************************#


				#*******************************************#
				#********** CLASS BLOG CONTROLLER **********#
				#*******************************************#

				
#*******************************************************************************************#


				class BlogController {
					
					#*******************************#
					#********** ATTRIBUTE **********#
					#*******************************#
					
					private PDO $db;
					
					#***********************************************************#
					
					
					#*********************************#
					#********** CONSTRUCTOR **********#
					#*********************************#
					
					public function __construct() {
						$this->db = dbConnect();
					}
					
					#********** DESTRUCTOR **********#
					
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
					public function saveNewBlogToDB(PDO $PDO, $blog): int {
if(DEBUG_C) 			echo "<p class='debug class'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";

						return Db::saveToDb($PDO, 'Blog', [
							'blogHeadline'       => $blog->getBlogHeadline(),
							'blogImagePath'      => $blog->getBlogImagePath(),
							'blogImageAlignment' => $blog->getBlogImageAlignment(),
							'blogContent'        => $blog->getBlogContent(),
							'blogDate'           => $blog->getBlogDate(),
							'catID'              => $blog->getCategory()->getCatID(),
							'userID'             => $blog->getUser()->getUserID()
						]);
					}

					#***********************************************************#

					public function updateBlog(PDO $PDO, $blog): int {
						return Db::updateToDb($PDO, 'Blog', [
							'catID'              => $blog->getCategory()->getCatID(),
							'blogHeadline'       => $blog->getBlogHeadline(),
							'blogImagePath'      => $blog->getBlogImagePath(),
							'blogImageAlignment' => $blog->getBlogImageAlignment(),
							'blogContent'        => $blog->getBlogContent()
						], 'blogID', $blog->getBlogID());
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
					public function deleteBlog(PDO $PDO, Blog $blog):int {
if(DEBUG_C)				echo "<p class='debug class'>🌀 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";
						
						$sql 				= "DELETE FROM Blog
												WHERE blogID = ?";
						
						$placeholders		= array( $blog->getBlogID() );
						
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
<?php
#*******************************************************************************************#


				#****************************************#
				#********** PAGE CONFIGURATION **********#
				#****************************************#
				
				require_once 'include/config.inc.php';
				require_once 'include/db.inc.php';
				require_once 'include/form.inc.php';
				include_once 'include/dateTime.inc.php';

				#********** INCLUDE CLASSES **********#
				require_once 'class/Db.class.php';
				require_once 'class/User.class.php';
				require_once 'class/Blog.class.php';
				require_once 'class/Category.class.php';

				#********** INCLUDE CONTROLLERS **********#
				require_once 'controllers/UserController.class.php';
				require_once 'controllers/BlogController.class.php';
				require_once 'controllers/FormController.class.php';


#***************************************************************************************#


				#****************************************#
				#********** SECURE PAGE ACCESS **********#
				#****************************************#
				
				#********** PREPARE SESSION **********#
				session_name('oop_blogproject');
		
				#********** START/CONTINUE SESSION **********#
				session_start();
				
				#*******************************************#
				#********** CHECK FOR VALID LOGIN **********#
				#*******************************************#
				
				if( isset( $_SESSION['ID'] ) === false OR $_SESSION['IPAddress'] !== $_SERVER['REMOTE_ADDR'] ) {
					// Fehlerfall (Seitenaufrufer ist nicht eingeloggt)
					
					#********** DENY PAGE ACCESS **********#
					session_destroy();
					header('LOCATION: ./');					
					exit();
					
				} else {
					// Erfolgsfall (Seitenaufrufer ist eingeloggt)					
					session_regenerate_id(true);
					
					#********** FETCH USER DATA BY USER EMAIL **********#

					$PDO = dbConnect();
				
if(DEBUG)			echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Fetching user data from db by user email... <i>(" . basename(__FILE__) . ")</i></p>\r\n";
					
					$userController = new UserController();
					$user = $userController->fetchUserData($PDO, $_SESSION['userEmail']);

					// DB-Verbindung schließen
if(DEBUG_DB)		echo "<p class='debug db'><b>Line " . __LINE__ . "</b>: DB-Verbindung geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";
				
					dbClose($PDO, $PDOStatement);
					
				} // SECURE PAGE ACCESS END

#***************************************************************************************#	

			
				#******************************************#
				#********** INITIALIZE VARIABLES **********#
				#******************************************#
				
				$blog 						= NULL;
				$category 					= NULL;

				$editPost					= false;
				
				$errorCatLabel				= NULL;
				$errorHeadline 				= NULL;
				$errorImageUpload 			= NULL;
				$errorBlogImageAlignment 	= NULL;
				$errorContent 				= NULL;

				// $errorUserFirstName			= NULL;
				// $errorUserLastName			= NULL;
				// $errorUserCity				= NULL;
				// $errorUserEmail				= getSessionValue('errors', 'errorUserEmail');;
				// $errorUserPassword			= NULL;
				
				$error						= NULL;
				$success					= NULL;
				$info						= NULL;

#*******************************************************************************************#

                #********** STORE SESSION ERRORS AND USER INPUT LOCALLY **********#
                $errors = $_SESSION['errors'] ?? [];
                unset($_SESSION['errors']);

                $tempBlogData = $_SESSION['tempBlogData'] ?? [];
                unset($_SESSION['tempBlogData']);

#***************************************************************************************#

				#***********************************************************#
				#********** FETCH BLOG ENTRIES FROM DB BY USER ID **********#
				#***********************************************************#			
				
				// Schritt 1 DB: DB-Verbindung herstellen
				$PDO = dbConnect();
				
if(DEBUG)		echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Fetching blog posts from database by user ID... <i>(" . basename(__FILE__) . ")</i></p>\r\n";				
				$blogObjectsArray = Blog::fetchBlogsByUserID($PDO, $user->getUserID());

				// DB-Verbindung schließen
if(DEBUG_DB)	echo "<p class='debug db'><b>Line " . __LINE__ . "</b>: DB-Verbindung geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";
				
				dbClose($PDO, $PDOStatement);
				
#***************************************************************************************#

	
				#********************************************#
				#********** PROCESS URL PARAMETERS **********#
				#********************************************#
				
				// Schritt 1 URL: Prüfen, ob Parameter übergeben wurde
				if( isset($_GET['action']) ) {
if(DEBUG)			echo "<p class='debug'>🧻 Line <b>" . __LINE__ . "</b>: URL-Parameter 'action' wurde übergeben... <i>(" . basename(__FILE__) . ")</i></p>";	
			
					// Schritt 2 URL: Werte auslesen, entschärfen, DEBUG-Ausgabe
					$action = sanitizeString($_GET['action']);
if(DEBUG_V)			echo "<p class='debug value'>Line <b>" . __LINE__ . "</b>: \$action = $action <i>(" . basename(__FILE__) . ")</i></p>";
		
					// Schritt 3 URL: ggf. Verzweigung
					
					#********** LOGOUT **********#
					if( $_GET['action'] === 'logout' ) {
						UserController::logout();

					#********** EDIT BLOG POST **********#
					} elseif( $_GET['action'] === 'editPost' ) {
if(DEBUG)				echo "<p class='debug'>📑 Line <b>" . __LINE__ . "</b>: 'Blogpost wird zum Bearbeiten geladen... <i>(" . basename(__FILE__) . ")</i></p>";	
						
						#********** FETCH ID **********'
						if( isset($_GET['blogID']) === true ) {
							$blogID = sanitizeString( $_GET['blogID'] );

							#********** WHITELISTING: CHECK IF BLOG ID IS VALID **********#
							if( array_key_exists($blogID, $blogObjectsArray) === false ) {
								// Fehlerfall
if(DEBUG)						echo "<p class='debug err'>📑 <b>Line " . __LINE__ . "</b>: Invalid blog ID! <i>(" . basename(__FILE__) . ")</i></p>\r\n";
								
							} else {
								// Erfolgsfall
if(DEBUG)						echo "<p class='debug ok'>📑 <b>Line " . __LINE__ . "</b>: Blog ID is valid. <i>(" . basename(__FILE__) . ")</i></p>\r\n";
												
								#********** FETCH BLOG FROM BLOG OBJECTS ARRAY **********#
if(DEBUG)						echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Fetching blog data from array... <i>(" . basename(__FILE__) . ")</i></p>\r\n";
								$blog = $blogObjectsArray[$blogID];
								
								// set flag for edit post form
								$editPost = true;
			
							} // WHITELISTING: CHECK IF BLOG ID IS VALID END
							
						} // FETCH ID END
					
					#********** DELETE BLOG POST **********#
					} elseif( $_GET['action'] === 'deletePost' ) {
if(DEBUG)				echo "<p class='debug'>📑 Line <b>" . __LINE__ . "</b>: 'Blogpost wird gelöscht... <i>(" . basename(__FILE__) . ")</i></p>";	

						#********** PROCESS URL PARAMETER BLOG ID **********#
						// Check if blog id was sent
						if( isset($_GET['blogID']) === false ) {
							// Fehlerfall
if(DEBUG)					echo "<p class='debug err'>📑 <b>Line " . __LINE__ . "</b>: No blog id received! <i>(" . basename(__FILE__) . ")</i></p>\r\n";
						
						} else {
							// Erfolgsfall
if(DEBUG)					echo "<p class='debug ok'>📑 <b>Line " . __LINE__ . "</b>: Blog id received. <i>(" . basename(__FILE__) . ")</i></p>\r\n";		
							
							// zusätzlichen Parameter auslesen und ins Objekt schreiben							
							$blog = new Blog( blogID: $_GET['blogID'] );
								
							#********** WHITELISTING: CHECK IF BLOG ID IS VALID **********#
							if( array_key_exists($blog->getBlogID(), $blogObjectsArray) === false ) {
								// Fehlerfall
if(DEBUG)						echo "<p class='debug err'>📑 <b>Line " . __LINE__ . "</b>: Invalid blog id! <i>(" . basename(__FILE__) . ")</i></p>\r\n";
							
							} else {
								// Erfolgsfall
if(DEBUG)						echo "<p class='debug ok'>📑 <b>Line " . __LINE__ . "</b>: Blog id is valid. <i>(" . basename(__FILE__) . ")</i></p>\r\n";

								#********** FETCH BLOG FROM BLOG OBJECTS ARRAY **********#
if(DEBUG)						echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Fetching blog data from array... <i>(" . basename(__FILE__) . ")</i></p>\r\n";
								$blog = $blogObjectsArray[$blog->getBlogID()];
if(DEBUG_V)						echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$blog<br>". print_r($blog, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";
				
								
								#*****************************************#
								#********** DELETE CONFIRMATION **********#
								#*****************************************#
							
// 								#********** IF DELETE BLOG POST NOT YET CONFIRMED **********#
// 								if( isset($_GET['deleteConfirmed']) === false ) {
// if(DEBUG)						echo "<p class='debug'><b>Line " . __LINE__ . "</b>: Activating confirmation popup... <i>(" . basename(__FILE__) . ")</i></p>\n";
																
// 									// Initialize flag for activating confirmation popup
// 									$deleteCheckMessage = true;
									
															
// 								#********** IF DELETE BLOG POST CONFIRMED **********#
// 								} elseif( isset($_GET['deleteConfirmed']) === true ) {
// if(DEBUG)						echo "<p class='debug ok'>📑 <b>Line " . __LINE__ . "</b>: Delete was confirmed. <i>(" . basename(__FILE__) . ")</i></p>\n";
									
									
									#********** DELETE BLOG FROM DB **********#	
									// Schritt 1 DB: DB-Verbindung herstellen
									$PDO = dbConnect();
									
									$blogController = new BlogController();
									if( $blogController->deleteBlog($PDO, $blog) !== 1 ) {
										// Fehlerfall
if(DEBUG)								echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: ERROR deleting blog with ID: {$blog->getBlogID()} from database! <i>(" . basename(__FILE__) . ")</i></p>\r\n";				
										$error = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
										
									} else {
										// Erfolgsfall
if(DEBUG)								echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Blog with ID: {$blog->getBlogID()} successfully deleted from database. <i>(" . basename(__FILE__) . ")</i></p>\r\n";				
										
										#********** DELETE IMAGE FROM SERVER **********#

										if( @unlink($blog->getBlogImagePath()) === false ) {
											// Fehlerfall
if(DEBUG)										echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Löschen des Bildes unter <i>'{$blog->getBlogImagePath()}'</i>! <i>(" . basename(__FILE__) . ")</i></p>\n";				
										
										} else {
											// Erfolgsfall
if(DEBUG)										echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Bild unter <i>'{$blog->getBlogImagePath()}'</i> erfolgreich gelöscht. <i>(" . basename(__FILE__) . ")</i></p>\n";				
										
										} // DELETE IMAGE FROM SERVER END		
										
										#********** REMOVE BLOG FROM BLOG OBJECTS ARRAY **********#
										unset($blogObjectsArray[$blog->getBlogID()]);										

										$success = "Der Blog Post'{$blog->getBlogHeadline()}' wurde erfolgreich aus der Datenbank gelöscht.";
										
										// Blogobjekt leeren
										$blog = NULL;	
											 
									} // DELETE BLOG POST FROM DB END
									
									// DB-Verbindung schließen
if(DEBUG_DB)						echo "<p class='debug db'><b>Line " . __LINE__ . "</b>: DB-Verbindung geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";
									dbClose($PDO, $PDOStatement);
									
								// } // DELETE CONFIRMATION END
							
							} // WHITELISTING: CHECK IF BLOG ID IS VALID END
						
						}
					} // BRANCHING END
					
				} // PROCESS URL PARAMETERS END

#***************************************************************************************#			

	
				#*************************************************#
				#********** PROCESS FORM 'NEW CATEGORY' **********#
				#*************************************************#
				
				// Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
				if( isset($_POST['formNewCategory']) === true ) {
			
					// Schritt 2 FORM: Werte auslesen, entschärfen, DEBUG-Ausgabe
					
					#********** GENERATE CATEGORY OBJECT **********#
					$category = new Category( catLabel: $_POST['catLabel'] );
if(DEBUG_V)			echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$category<br>". print_r($category, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";

					// Schritt 3 FORM: Werte ggf. validieren
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";
					$errorCatLabel = validateInputString($category->getCatLabel(), maxLength: 50);
					
					
					#********** FINAL FORM VALIDATION **********#
					if( $errorCatLabel !== NULL ) {
						// Fehlerfall
if(DEBUG)				echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>";						
						
					} else {
						// Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: Das Formular ist formal fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>";						
						
						// Schritt 4 FORM: Daten weiterverarbeiten
						
						#***********************************#
						#********** DB OPERATIONS **********#
						#***********************************#
						
						// Schritt 1 DB: DB-Verbindung herstellen
						$PDO = dbConnect();

						#********** CHECK IF CATEGORY NAME ALREADY EXISTS **********#
if(DEBUG)				echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Prüfe, ob Kategorie bereits existiert. <i>(" . basename(__FILE__) . ")</i></p>\n";

						$count = Db::checkIfExists($PDO, 'Category', 'catLabel', $category->getCatLabel());
						
						if( $count !== 0 ) {
							// Fehlerfall
if(DEBUG)					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Die Kategorie '{$category->getCatLabel()}' existiert bereits! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							$errorCatLabel = "Die Kategorie '{$category->getCatLabel()}' existiert bereits!";
	
						} else {
							// Erfolgsfall
if(DEBUG)					echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>:  Die Kategorie '{$category->getCatLabel()}' ist noch nicht in der DB angelegt. <i>(" . basename(__FILE__) . ")</i></p>\n";

							#********** SAVE CATEGORY INTO DB **********#
if(DEBUG)					echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Speichere Kategorie in die DB... <i>(" . basename(__FILE__) . ")</i></p>\n";
							
							if( ($category->saveNewCategoryToDB(PDO: $PDO)) !== 1 ) {
								// Fehlerfall
if(DEBUG)						echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Speichern der Kategorie! <i>(" . basename(__FILE__) . ")</i></p>\n";				

								// Fehlermeldung für User generieren
								$error = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
	
							} else {
								// Erfolgsfall
								$category->setCatID( $PDO->lastInsertID() );
if(DEBUG)						echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Kategorie erfolgreich unter ID{$category->getCatID()} gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";				

								// Erfolgsmeldung für User
								$success = "Die Kategorie '{$category->getCatLabel()}' wurde erfolgreich angelegt.";
							
								// Categoryobjekt leeren
								$category = NULL;

							} // SAVE CATEGORY INTO DB END
						} // CHECK IF CATEGORY NAME ALREADY EXISTS END
						
						// DB-Verbindung schließen
						dbClose($PDO, $PDOStatement);
					} // FINAL FORM VALIDATION END
				} // PROCESS FORM 'NEW CATEGORY' END

#***************************************************************************************#


				#***************************************************#
				#********** PROCESS FORM 'NEW BLOG ENTRY' **********#
				#***************************************************#
				
				// Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
				if( isset($_POST['formNewBlogEntry']) === true ) {			
if(DEBUG)			echo "<p class='debug'>🧻 Line <b>" . __LINE__ . "</b>: Formular 'New Blog Entry' wurde abgeschickt... <i>(" . basename(__FILE__) . ")</i></p>";	

					$blog = new Blog(
						user:     new User(userID: $user->getUserID()),
						category: new Category(catID: $_POST['catID']),
						blogHeadline: $_POST['blogHeadline'],
						blogImageAlignment: $_POST['blogImageAlignment'],
						blogContent: $_POST['blogContent']
					);

					$formController = new FormController(PDO: $PDO);
					$formController->processFormNewBlogEntry( blog: $blog );
							
					// DB-Verbindung schließen
					dbClose($PDO);
					
					// Redirect after POST to prevent form resubmission
					// header("Location: dashboard.php");
					// exit;

				} // PROCESS FORM 'NEW BLOG ENTRY' END

#*******************************************************************************************#


				#**************************************************#
				#********** PROCESS FORM EDIT BLOG ENTRY **********#
				#**************************************************#
				
				// Schritt 1 FORM: prüfen, ob Formular abgesendet wurde
				if( isset($_POST['formEditBlogEntry']) ) {
if(DEBUG)			echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Form 'edit blog entry' was sent. <i>(" . basename(__FILE__) . ")</i></p>\r\n";
					
					$editPost = true;
						
					// Schritt 2 FORM: Werte auslesen, entschärfen, DEBUG-Ausgabe
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Fetching form data... <i>(" . basename(__FILE__) . ")</i></p>\n";
										
					$blog->getCategory()->setCatID( $_POST['catID'] );
					$blog->setBlogHeadline( $_POST['blogHeadline'] );
					$blog->setBlogImageAlignment( $_POST['blogImageAlignment'] );
					$blog->setBlogContent( $_POST['blogContent'] );
										
					$formController = new FormController(PDO: $PDO);
					$formController->processFormNewBlogEntry( blog: $blog, editBlog: true, blogObjectsArray: $blogObjectsArray );
							
					// DB-Verbindung schließen
					dbClose($PDO);
					
					// Redirect after POST to prevent form resubmission
					// header("Location: dashboard.php");
					// exit;
				}

					// Schritt 3 FORM: Werte validieren
// if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Validating field values... <i>(" . basename(__FILE__) . ")</i></p>\n";

// 					$errorHeadline 	= validateInputString($blog->getBlogHeadline() );
// 					$errorContent 	= validateInputString($blog->getBlogContent(), minLength:5, maxLength:20000 );
									
					#********** FINAL FORM VALIDATION **********#					
// 					if( $errorHeadline !== NULL OR $errorContent !== NULL ) {
// 						// Fehlerfall
// if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Form contains errors! <i>(" . basename(__FILE__) . ")</i></p>\r\n";
						
// 					} else {
// 						// Erfolgsfall
// if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Form contains no errors. Processing form data... <i>(" . basename(__FILE__) . ")</i></p>\r\n";					
						
						#********** WHITELISTING: CHECK IF BLOG ID IS VALID **********#
// 						if( array_key_exists($blog->getBlogID(), $blogObjectsArray) === false ) {
// 							// Fehlerfall
// if(DEBUG)					echo "<p class='debug err'>📑 <b>Line " . __LINE__ . "</b>: Invalid blog ID! <i>(" . basename(__FILE__) . ")</i></p>\r\n";
							
// 						} else {
// 							// Erfolgsfall
// if(DEBUG)					echo "<p class='debug ok'>📑 <b>Line " . __LINE__ . "</b>: Blog ID is valid. <i>(" . basename(__FILE__) . ")</i></p>\r\n";

							#****************************************#
							#********** IMAGE UPLOAD START **********#
							#****************************************#
														
							#********** CHECK IF IMAGE UPLOAD IS ACTIVE **********#
// 							if( $_FILES['blogImage']['tmp_name'] === '' ) {
// 								// Image Upload is inactive
// if(DEBUG)						echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Image upload inactive. <i>(" . basename(__FILE__) . ")</i></p>\n";				
								
// 							} else {
// 								// Image Upload is active
// if(DEBUG)						echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Image upload active. <i>(" . basename(__FILE__) . ")</i></p>\n";				
									
// 								$validateImageUploadResultArray = validateImageUpload( $_FILES['blogImage']['tmp_name'] );
							
// 								#********** VALIDATE IMAGE UPLOAD **********#
// 								if( $validateImageUploadResultArray['imageError'] !== NULL ) {
									
// 									// Fehlerfall
// if(DEBUG)							echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Bildupload: $validateImageUploadResultArray[imageError] <i>(" . basename(__FILE__) . ")</i></p>\n";				
// 									$errorImageUpload = $validateImageUploadResultArray['imageError'];
									
// 								} else {
// 									// Erfolgsfall
// if(DEBUG)							echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Bild erfolgreich unter <i>'$validateImageUploadResultArray[imagePath]'</i> auf den Server geladen. <i>(" . basename(__FILE__) . ")</i></p>\n";												
									
// 									#********** DELETE OLD IMAGE FROM SERVER **********#
// 									if( @unlink($blog->getBlogImagePath()) === false ) {
// 										// Fehlerfall
// if(DEBUG)								echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Löschen des Vorgängerbildes unter <i>'{$blog->getBlogImagePath()}'</i>! <i>(" . basename(__FILE__) . ")</i></p>\n";				
										
// 									} else {
// 										// Erfolgsfall
// if(DEBUG)								echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Vorgängerbild unter <i>'{$blog->getBlogImagePath()}'</i> erfolgreich gelöscht. <i>(" . basename(__FILE__) . ")</i></p>\n";				
										
// 									} // DELETE OLD IMAGE FROM SERVER END					
									
// 									#*************************************************#
// if(DEBUG_V)							echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$validateImageUploadResultArray<br>". print_r($validateImageUploadResultArray, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";
									
// 									// Im Erfolgfall den neuen Bildpfad in die Bildpfadvariable schreiben
// 									$blog->setBlogImagePath($validateImageUploadResultArray['imagePath']);

// 								} // VALIDATE IMAGE UPLOAD END

// 							} // IMAGE UPLOAD END
							#*********************************************************#
							
							#********** FINAL FORM VALIDATION II (IMAGE UPLOAD VALIDATION) **********#
// 							if( $errorImageUpload !== NULL ) {
// 								// Fehlerfall
// if(DEBUG)						echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FINAL FORM VALIDATION II: Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";				
								
// 							} else {
// 								// Erfolgsfall
// if(DEBUG)						echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: FINAL FORM VALIDATION II: Das Formular ist komplett fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>\n";
																		
// 								#********** UPDATE BLOG TO DATABASE **********#
// if(DEBUG)						echo "<p class='debug ok'>📑 <b>Line " . __LINE__ . "</b>: Updating blog data to database...<i>(" . basename(__FILE__) . ")</i></p>\r\n";
								
// 								// Schritt 1 DB: DB-Verbindung herstellen
// 								$PDO = dbConnect();
// 								$blogController = new BlogController();
// 								// Schritt 4 DB: Schreiberfolg prüfen
// 								if( $blogController->updateBlog($PDO, $blog) === 0 ) {
// 									// 'Fehlerfall'
// if(DEBUG)							echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: No data was changed. <i>(" . basename(__FILE__) . ")</i></p>\r\n";				
// 									$info = 'Es wurden keine Daten verändert.';
								
// 								} else {
// 									// Erfolgsfall
// if(DEBUG)							echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Blog Post '{$blog->getBlogHeadline()}' updated successfully in database. <i>(" . basename(__FILE__) . ")</i></p>\r\n";				
// 									$success = "Der Blog Post '{$blog->getBlogHeadline()}' wurde erfolgreich upgedatet.";
										
// 									#********** ALTER BLOG POST IN ALL BLOGS ARRAY **********#
// 									$blogObjectsArray[$blog->getBlogID()] = $blog;
									
// 									// Bearbeitungsmodus verlassen
// 									$editPost = false;
									
// 									// Blog-Objekt leeren
// 									$blog = NULL;
									
// 								} // UPDATE BLOG TO DATABASE END
// 							} // FINAL FORM VALIDATION II (IMAGE UPLOAD VALIDATION) END
				
// 							// DB-Verbindung schließen
// if(DEBUG_DB)				echo "<p class='debug db'><b>Line " . __LINE__ . "</b>: DB-Verbindung geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";
// 							dbClose($PDO, $PDOStatement);

// 						// } // WHITELISTING: CHECK IF BLOG ID IS VALID END
// 					} // FINAL FORM VALIDATION END					
				// } // PROCESS FORM EDIT CUSTOMER END
			
#***************************************************************************************#			

	
				#***************************************************#
				#********** PROCESS FORM 'EDIT USER DATA' **********#
				#***************************************************#
				
				if( isset($_POST['formEditUserData']) === true ) {
if(DEBUG)			echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Formular 'Edit User Data' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";													
										
					$formController = new FormController($PDO);
					$formController->processEditUserForm($_POST, $user);
					
if(DEBUG_V)			echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_SESSION['errors']<br>". print_r($_SESSION['errors'], true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";
					// unset($_SESSION['errors']);

					#********** CLOSE DB CONNECTION **********#
					dbClose($PDO, $PDOStatement);
				} // PROCESS FORM EDIT PROFILE DATA END
				
#***************************************************************************************#
						

				#**********************************************#
				#********** FETCH CATEGORIES FROM DB **********#
				#**********************************************#
				
				// Schritt 1 DB: DB-Verbindung herstellen
				$PDO = dbConnect();
				
if(DEBUG)		echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Fetching categories from database... <i>(" . basename(__FILE__) . ")</i></p>\r\n";				
				$categoryObjectsArray = Category::fetchCategories(PDO: $PDO);
				
				// DB-Verbindung schließen
if(DEBUG_DB)	echo "<p class='debug db'><b>Line " . __LINE__ . "</b>: DB-Verbindung geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";
				
				dbClose($PDO, $PDOStatement);
				
#***************************************************************************************#

				// Überprüfen, ob eine Fehlermeldung in der Session vorhanden ist
				function getSessionValue($key, $subKey = null) {
					if ($subKey === null) {
						if (isset($_SESSION[$key])) {
							$value = $_SESSION[$key];
							unset($_SESSION[$key]);
							return $value;
						}
					} else {
						if (isset($_SESSION[$key][$subKey])) {
							$value = $_SESSION[$key][$subKey];
							unset($_SESSION[$key][$subKey]);
							return $value;
						}
					}
					return null;
				}

				$error		= getSessionValue('error');
				$success	= getSessionValue('success');
				$info		= getSessionValue('info');

#***************************************************************************************#

?>

<?php include 'views/head-section.view.php' ?>

<body class="dashboard">

    <!-- ---------- PAGE HEADER START ---------- -->
    <header>
        <section class="page-title">
            <img src="img/logo-black.png" alt="Blog-Logo" class="logo">
            <h1>PHP-Blog Projekt - Dashboard</h1>
        </section>
        <section class="menu">
            <a href="./">
                << zum Frontend</a>
                    <a href="?action=logout">Logout</a>
        </section>
    </header>

    <!-- ---------- PAGE HEADER END ---------- -->

    <!-- ---------- POPUP MESSAGE START ---------- -->
    <?php if( $error OR $success OR $info): ?>
    <popupBox>
        <?php if($error): ?>
        <h3 class="error"><?= $error ?></h3>
        <?php elseif($success): ?>
        <h3 class="success"><?= $success ?></h3>
        <?php elseif($info): ?>
        <h3 class="info"><?= $info ?></h3>
        <?php endif ?>
        <a class="button" onclick="document.getElementsByTagName('popupBox')[0].style.display = 'none'">Schließen</a>
    </popupBox>
    <?php endif ?>
    <!-- ---------- POPUP MESSAGE END ---------- -->

    <!-- ---------- LEFT PAGE COLUMN START ---------- -->
    <section class="container">
        <main class="forms">
            <p>Aktiver Benutzer: <?= $user->getFullName() ?></p>

            <!-- ---------- FORM 'NEW BLOG ENTRY' START ---------- -->
            <?php include 'views/new-blog-form.view.php' ?>

        </main>
        <!-- ---------- LEFT PAGE COLUMN END ---------- -->

        <!-- ---------- RIGHT PAGE COLUMN START ---------- -->
        <aside class="sidebar">

            <!-- ---------- FORM 'NEW CATEGORY' START ---------- -->
            <?php include 'views/new-category-form.view.php' ?>

            <!-- ---------- USER BLOGPOSTS -------- -->
            <?php include 'views/user-blogposts.view.php' ?>

            <!-- -------- USER DATA -------- -->
            <?php include 'views/edit-user-data.view.php' ?>

        </aside>
        <!-- ---------- RIGHT PAGE COLUMN END ---------- -->
    </section>
    </section>

</body>

</html>
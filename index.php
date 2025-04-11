<?php

#*******************************************************************************************#
				
				
				#******************************************#
				#********** ENABLE STRICT TYPING **********#
				#******************************************#
				
				declare(strict_types=1);

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

#*******************************************************************************************#


				#**************************************#
				#********** OUTPUT BUFFERING **********#
				#**************************************#
				
				if( ob_start() === false ) {
					// Fehlerfall
if(DEBUG)		echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Starten des Output Bufferings! <i>(" . basename(__FILE__) . ")</i></p>\r\n";				
					
				} else {
					// Erfolgsfall
if(DEBUG)		echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Output Buffering erfolgreich gestartet. <i>(" . basename(__FILE__) . ")</i></p>\r\n";									
				}

#*******************************************************************************************#


				#************************************#
				#********** VALIDATE LOGIN **********#
				#************************************#
				
				session_name("oop_blogproject");
				
				#********** START/CONTINUE SESSION **********#
				if( session_start() === false ) {
					// Fehlerfall
if(DEBUG)		echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Starten der Session! <i>(" . basename(__FILE__) . ")</i></p>\n";				
									
				} else {
					// Erfolgsfall
if(DEBUG)		echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Session <i>'oop_blogproject'</i> erfolgreich gestartet. <i>(" . basename(__FILE__) . ")</i></p>\n";

					
					#*******************************************#
					#********** CHECK FOR VALID LOGIN **********#
					#*******************************************#
					
					#********** A) NO VALID LOGIN **********#				
					if( isset($_SESSION['ID']) === false OR $_SESSION['IPAddress'] !== $_SERVER['REMOTE_ADDR'] ) {
						// Fehlerfall | User ist nicht eingeloggt
if(DEBUG)				echo "<p class='debug auth'><b>Line " . __LINE__ . "</b>: User ist nicht eingeloggt. <i>(" . basename(__FILE__) . ")</i></p>\n";				

						session_destroy();
						
						$loggedIn = false;
					
					#********** B) VALID LOGIN **********#
					} else {
						// Erfolgsfall | User ist eingeloggt
if(DEBUG)			echo "<p class='debug auth'><b>Line " . __LINE__ . "</b>: User ist eingeloggt. <i>(" . basename(__FILE__) . ")</i></p>\n";				
					
						session_regenerate_id(true);
												
						$loggedIn = true;
						
					} // CHECK FOR VALID LOGIN END
					
				} // VALIDATE LOGIN END

#*******************************************************************************************#


				#******************************************#
				#********** INITIALIZE VARIABLES **********#
				#******************************************#
				
				$loginError 		= NULL;
				$categoryFilterID	= NULL;

#*******************************************************************************************#


				#**********************************************#
				#********** FETCH CATEGORIES FROM DB **********#
				#**********************************************#
				
				// Schritt 1 DB: DB-Verbindung herstellen
				$PDO = dbConnect();
				
if(DEBUG)		echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Fetching categories from database... <i>(" . basename(__FILE__) . ")</i></p>\r\n";				
				$categoryObjectsArray = Category::fetchCategories(PDO: $PDO);
				
				// DB-Verbindung schließen
if(DEBUG_DB)	echo "<p class='debug db'><b>Line " . __LINE__ . "</b>: DB-Verbindung geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";
				unset($PDO, $PDOStatement);
				
				dbClose($PDO, $PDOStatement);
							
#***************************************************************************************#

				#****************************************#
				#********** PROCESS FORM LOGIN **********#
				#****************************************#				
						
				// Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
				if( isset($_POST['formLogin']) === true ) {
if(DEBUG)			echo "<p class='debug'>🧻 Line <b>" . __LINE__ . "</b>: Formular 'Login' wurde abgeschickt... <i>(" . basename(__FILE__) . ")</i></p>";	

					// Schritt 2 FORM: Auslesen, entschärfen und Debug-Ausgabe der übergebenen Formularwerte
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";

					#********** GENERATE HELPER VARIABLES **********#
					$userEmail = sanitizeString($_POST['f1']);
					$password = sanitizeString($_POST['f2']);
if(DEBUG_V)			echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$password: $password <i>(" . basename(__FILE__) . ")</i></p>\n";

					// Schritt 3 FORM: Feldvalidierung
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";

					$errorUserEmail = validateEmail($userEmail);
					$errorPassword 	= validateInputString($password, minLength:4);

					#********** FINAL FORM VALIDATION (FIELDS VALIDATION) **********#
					if( $errorUserEmail !== NULL OR $errorPassword !== NULL ) {
						// Fehlerfall
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";				
	
						// NEUTRALE Fehlermeldung für User
						$loginError = 'Diese Logindaten sind ungültig!';
	
					} else {
						// Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Formular ist formal fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>\n";				
	
						// Schritt 4 FORM: Verarbeitung der Formularwerte
if(DEBUG)				echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Formularwerte werden verarbeitet... <i>(" . basename(__FILE__) . ")</i></p>\n";
	
	
						#***********************************#
						#********** DB OPERATIONS **********#
						#***********************************#					
						
						#********** FETCH USER DATA BY USER EMAIL **********#
if(DEBUG)				echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese Userdaten aus DB aus... <i>(" . basename(__FILE__) . ")</i></p>\n";
	
						$userController = new UserController();
						$userController->handleLogin($userEmail, $password);					

						#********** CLOSE DB CONNECTION **********#
						dbClose($PDO);

					} // FINAL FORM VALIDATION (FIELDS VALIDATION) END

				} // PROCESS FORM LOGIN END


#***************************************************************************************#

			
				#********************************************#
				#********** PROCESS URL PARAMETERS **********#
				#********************************************#
				
				// Schritt 1 URL: Prüfen, ob Parameter übergeben wurde
				if( isset($_GET['action']) === true ) {
			
					// Schritt 2 URL: Werte auslesen, entschärfen, DEBUG-Ausgabe
					$action = sanitizeString($_GET['action']);
		
					// Schritt 3 URL: ggf. Verzweigung		
							
					#********** LOGOUT **********#					
					if( $_GET['action'] === 'logout' ) {
						UserController::logout();
						
					#********** FILTER BY CATEGORY **********#
					} elseif( $action === 'filterByCategory' ) {
						
						#********** FETCH SECOND URL PARAMETER **********#
						if( isset($_GET['catID']) === true ) {
							
							#********** CHECK IF CATEGORY ID IS OF TYPE INT **********#
							if( ($_GET['catID'] = filter_var($_GET['catID'], FILTER_VALIDATE_INT)) === false ) {
								// Fehlerfall (nicht erlaubtes Datenformat)
if(DEBUG)						echo "<p class='debug err'>📑 <b>Line " . __LINE__ . "</b>: Muss dem Format 'Integer' entsprechen! <i>(" . basename(__FILE__) . ")</i></p>\r\n";
	
							} else {
								// Erfolgsfall (erlaubtes Datenformat)		

								#********** WHITELISTING: CHECK IF CATEGORY ID IS VALID **********#
								if( array_key_exists($_GET['catID'], $categoryObjectsArray) === false ) {
									// Fehlerfall
if(DEBUG)							echo "<p class='debug err'>📑 <b>Line " . __LINE__ . "</b>: Ungültige Filter ID! <i>(" . basename(__FILE__) . ")</i></p>\r\n";
							
								} else {
									// Erfolgsfall
if(DEBUG)							echo "<p class='debug ok'>📑 <b>Line " . __LINE__ . "</b>: Filter ID existiert. <i>(" . basename(__FILE__) . ")</i></p>\r\n";
								
									// use $categoryFilterID as flag
									$categoryFilterID = intval($_GET['catID']);
if(DEBUG_V)							echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$categoryFilterID: $categoryFilterID <i>(" . basename(__FILE__) . ")</i></p>\n";			

								} // WHITELISTING: CHECK IF CATEGORY ID IS VALID END
							} // CHECK IF CATEGORY ID IS OF TYPE INT END								
						} // FETCH SECOND URL PARAMETER END
					} // BRANCHING END
				} // PROCESS URL PARAMETERS END
			
#***************************************************************************************#


				#************************************************#
				#********** FETCH BLOG ENTRIES FROM DB **********#
				#************************************************#			
				
				// Schritt 1 DB: DB-Verbindung herstellen
				$PDO = dbConnect();
				
if(DEBUG)		echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Fetching blog posts from database... <i>(" . basename(__FILE__) . ")</i></p>\r\n";				
				$blogObjectsArray = Blog::fetchBlogs($PDO,$categoryFilterID);

				// DB-Verbindung schließen
if(DEBUG_DB)	echo "<p class='debug db'><b>Line " . __LINE__ . "</b>: DB-Verbindung geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";
				
				dbClose($PDO, $PDOStatement);
				
// if(DEBUG_V)		echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$blogObjectsArray<br>". print_r($blogObjectsArray, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";

#***************************************************************************************#

				// Überprüfen, ob eine Login-Fehlermeldung in der Session vorhanden ist
				if (isset($_SESSION['login_error'])) {
					$loginError = $_SESSION['login_error'];
					// Fehlermeldung nach der Anzeige löschen, damit sie nicht bei der nächsten Anfrage angezeigt wird
					unset($_SESSION['login_error']);
				}
				
#***************************************************************************************#

?>

<!doctype html>
<html>

<?php include 'views/head-section.view.php' ?>

<body>

    <!-- ---------- PAGE HEADER START ---------- -->
    <?php include 'views/header.view.php' ?>
    <!-- ---------- PAGE HEADER END ---------- -->

    <section class="container">
        <main>
            <!-- ---------- BLOG ENTRIES START ---------- -->
            <?php include 'views/blog-entries.view.php' ?>
            <!-- ---------- BLOG ENTRIES END ------------ -->
        </main>

        <aside class="category-filter">
            <!-- ---------- CATEGORY FILTER LINKS START ---------- -->
            <?php include 'views/categoryFilter.view.php' ?>
            <!-- ---------- CATEGORY FILTER LINKS END ---------- -->
        </aside>
    </section>
</body>

</html>
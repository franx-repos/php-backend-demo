<?php
#*******************************************************************************************#
				
				
				#******************************************#
				#********** ENABLE STRICT TYPING **********#
				#******************************************#

				declare(strict_types=1);
							
#*******************************************************************************************#


				#*******************************************#
				#********** CLASS USER CONTROLLER **********#
				#*******************************************#

				
#*******************************************************************************************#


				class UserController {
					
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

					#********** HANDLE USER LOGIN **********#
					public function handleLogin(string $email, string $password): ?User {

						// Schritt 1 DB: DB-Verbindung herstellen
						$PDO = $this->db;

						#********** VALIDATE USER EMAIL **********#
if(DEBUG)				echo "<p class='debug'><b>Line " . __LINE__ . "</b>: 1. Validiere User Email... <i>(" . basename(__FILE__) . ")</i></p>\n";						
				
						if( ($user = $this->fetchUserData($PDO, $email)) === null ) {
							// Fehlerfall (ungültige User EMAIL)
if(DEBUG)					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Die User Email '{$email}' wurde nicht in der DB gefunden! <i>(" . basename(__FILE__) . ")</i></p>\n";				
		
							// NEUTRALE Fehlermeldung für User
							$_SESSION['login_error'] = 'Diese Logindaten sind ungültig!';
							return null;
							
						} else {// Erfolgsfall
if(DEBUG)					echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Die User Email '{$user->getUserEmail()}' wurde in der DB gefunden. <i>(" . basename(__FILE__) . ")</i></p>\n";				

							#********** VALIDATE PASSWORD **********#
if(DEBUG)					echo "<p class='debug'><b>Line " . __LINE__ . "</b>: 2. Validiere Passwort... <i>(" . basename(__FILE__) . ")</i></p>\n";
		
							if( password_verify( $password, $user->getUserPassword() ) === false ) {
								// Fehlerfall (ungültiges Passwort)
if(DEBUG)						echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Passwort aus dem Formular stimmt nicht mit dem Passwort aus der DB überein! <i>(" . basename(__FILE__) . ")</i></p>\n";				
			
								// NEUTRALE Fehlermeldung für User in Session schreiben
								$_SESSION['login_error'] = 'Diese Logindaten sind ungültig!';
								return null;

							} else {
								// Erfolgsfall (gültiges Passwort)
if(DEBUG)						echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Passwort aus dem Formular stimmt mit dem Passwort aus der DB überein. <i>(" . basename(__FILE__) . ")</i></p>\n";					
								
								#********** START SESSION **********#
								$this->startUserSession($user);
								header('LOCATION: ./dashboard.php');
								return $user; // Erfolgreicher Login
								
							} // START SESSION END
		
						} // VALIDATE PASSWORD END
		
					} // VALIDATE USER EMAIL END

					#********** FETCH USER FROM DB BY EMAIL **********#
					/**
					*
					*	FETCHES A SINGLE USER-DATASET FROM DB
					*	VIA THE USER_EMAIL ATTRIBUTE 
					*
					*	@param	PDO $PDO	DB-Connection object
					*
					*	@return	User $user	returns User Object
					*
					*/
					public static function fetchUserData(PDO $PDO, string $email): ?User {
if(DEBUG_C)             echo "<p class='debug class'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";
						
						// Filter für die E-Mail-Adresse setzen
						$filters = ['userEmail' => $email];
					
						// Benutzerdaten aus der DB abrufen
						$userDataArray = Db::fetchFromDb(PDO: $PDO, table: 'User', filters: $filters);
					
						// Prüfen, ob ein Benutzer mit dieser E-Mail gefunden wurde
						if (empty($userDataArray)) {
							// Fehlerfall (ungültige User Email)
if(DEBUG_C)                 echo "<p class='debug class err'><b>Line " . __LINE__ . "</b>: Kein Benutzer gefunden mit Email: $email <i>(" . basename(__FILE__) . ")</i></p>\n";
							return null; // Kein Benutzer gefunden

						} else {
							// Erfolgsfall (gültige User Email)
							$userData = $userDataArray[0];  // Es wird nur ein Benutzer erwartet
							
							#********** CREATE NEW USER OBJECT **********#					
							$user = new User(
                                userID:         !empty($userData['userID'])         ? $userData['userID']         : null,
                                userFirstName:  !empty($userData['userFirstName'])  ? $userData['userFirstName']  : null,
                                userLastName:   !empty($userData['userLastName'])   ? $userData['userLastName']   : null,
                                userEmail:      !empty($userData['userEmail'])      ? $userData['userEmail']      : null,
                                userCity:       !empty($userData['userCity'])       ? $userData['userCity']       : null,
                                userPassword:   !empty($userData['userPassword'])   ? $userData['userPassword']   : null
                            );
                    
                            return $user;
						}
					}					
					
					#***********************************************************#

					#********** SAVES NEW USER TO DB **********#
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
					public function saveNewUserToDB(PDO $PDO, $user): int {
if(DEBUG_C) 			echo "<p class='debug class'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";
						
						return Db::saveToDb($PDO, 'User', [
							'userFirstName' 	=> $user->getUserFirstName(),
							'userLastName'      => $user->getUserLastName(),
							'userEmail' 		=> $user->getUserEmail(),
							'userPassword'      => $user->getUserPassword()
						]);
					}

					#***********************************************************#

					#********** UPDATE USER DATA **********#
					public function updateUserData(PDO $PDO, $user):int {
						
						$data = [
							'userFirstName' => $user->getUserFirstName(),
							'userLastName'  => $user->getUserLastName(),
							'userEmail'     => $user->getUserEmail(),
							'userCity'      => $user->getUserCity()
						];
						
						if (!empty($user->getUserPassword())) {
							$data['userPassword'] = $user->getUserPassword();
						}
						
						return Db::updateToDb($PDO, 'User', $data, 'userID', $user->getUserID());						
					}		
					
					#***********************************************************#

					#********** START USER SESSION **********#

					private function startUserSession(User $user): void {
						session_name('oop_blogproject');
						
						if( session_start() === false ) {
							// Fehlerfall
if(DEBUG)					echo "<p class='debug auth err'><b>Line " . __LINE__ . "</b>: FEHLER beim Starten der Session! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							$_SESSION['login_error'] = 'Der Login kann nicht durchgeführt werden!<br>Bitte überprüfen Sie, ob in Ihrem Browser die Annahme von Cookies aktiviert ist.';

						} else {
							// Erfolgsfall
if(DEBUG)					echo "<p class='debug auth ok'><b>Line " . __LINE__ . "</b>: Session erfolgreich gestartet. <i>(" . basename(__FILE__) . ")</i></p>\n";				
			
							#********** SAVE USER DATA INTO SESSION FILE **********#						
							$_SESSION['ID']            	= $user->getUserID();
							$_SESSION['userEmail']		= $user->getUserEmail();
							$_SESSION['IPAddress']     	= $_SERVER['REMOTE_ADDR'];
						}
					}

					#***********************************************************#
					
					#********** LOGOUT USER **********#
					/**
					 * Logs out the current user by destroying the session and redirecting.
					 */
					public static function logout(): void {
// if(DEBUG)				echo "<p class='debug'>📑 Line <b>" . __LINE__ . "</b>: 'Logout' wird durchgeführt... <i>(" . basename(__FILE__) . ")</i></p>";	
						if (session_status() === PHP_SESSION_NONE) {
							session_start();
						} // Sicherstellen, dass die Session gestartet wurde
						session_destroy(); // Session löschen
						$_SESSION = []; // Sicherstellen, dass alle Session-Daten entfernt werden
						
						header("Location: ./");
						exit();
					}
					#***********************************************************#
					
				}
				
				
#*******************************************************************************************#
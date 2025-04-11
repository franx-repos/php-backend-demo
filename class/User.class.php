<?php
#*******************************************************************************************#
				
				
				#******************************************#
				#********** ENABLE STRICT TYPING **********#
				#******************************************#

				declare(strict_types=1);
							
#*******************************************************************************************#


				#********************************#
				#********** CLASS USER **********#
				#********************************#

				
#*******************************************************************************************#


				class User {
					
					#*******************************#
					#********** ATTRIBUTE **********#
					#*******************************#
					
					private $userID;
					private $userFirstName;
					private $userLastName;
					private $userEmail;
					private $userCity;
					private $userPassword;
					
					
					#***********************************************************#
					
					
					#*********************************#
					#********** CONSTRUCTOR **********#
					#*********************************#
					
					public function __construct( 	$userID 		= NULL, 
													$userFirstName	= NULL, 
													$userLastName 	= NULL,
													$userEmail 		= NULL, 
													$userCity 		= NULL, 
													$userPassword 	= NULL  )
					{
// if(DEBUG_CC)		echo "<p class='debug class'>🛠 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "()  (<i>" . basename(__FILE__) . "</i>)</p>\n";						
						
						// Setter nur aufrufen, wenn der jeweilige Parameter keinen Leerstring und nicht NULL enthält
						if($userID 			!== NULL AND $userID 		!== '') $this->setUserID($userID);
						if($userFirstName 	!== NULL AND $userFirstName !== '') $this->setUserFirstName($userFirstName);
						if($userLastName 	!== NULL AND $userLastName 	!== '') $this->setUserLastName($userLastName);
						if($userEmail 		!== NULL AND $userEmail 	!== '') $this->setUserEmail($userEmail);
						if($userCity 		!== NULL AND $userCity 		!== '') $this->setUserCity($userCity);
						if($userPassword 	!== NULL AND $userPassword 	!== '') $this->setUserPassword($userPassword);
						
// if(DEBUG_CC)		echo "<pre class='debug class value'><b>Line " . __LINE__ . "</b>: \$this<br>". print_r($this, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";					
					}
					
					#********** DESTRUCTOR **********#
					public function __destruct() {
// if(DEBUG_CC)		echo "<p class='debug class'>☠️  <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "()  (<i>" . basename(__FILE__) . "</i>)</p>\n";						
					}					
					
					#***********************************************************#

					
					#*************************************#
					#********** GETTER & SETTER **********#
					#*************************************#
				
					#********** USER ID **********#
					public function getUserID():NULL|int {
						return $this->userID;
					}
					public function setUserID(int|string $value):void {
						
						#********** VALIDATE DATA FORMAT **********#
						if( ($value = filter_var($value, FILTER_VALIDATE_INT)) === false ) {
							// Fehlerfall (nicht erlaubtes Datenformat)
if(DEBUG_C)				echo "<p class='debug class err'><b>Line " . __LINE__ . "</b>: " . __METHOD__ . "():  Muss dem Format 'Integer' entsprechen! <i>(" . basename(__FILE__) . ")</i></p>\n";
							
						} else {
							// Erfolgsfall (erlaubtes Datenformat)
							$this->userID = $value;
						}						
					}
					
					#********** USER FIRST NAME **********#
					public function getUserFirstName():NULL|string {
						return $this->userFirstName;
					}
					public function setUserFirstName(string $value):void {						
						$this->userFirstName = sanitizeString($value);
					}

					#********** USER LAST NAME **********#
					public function getUserLastName():NULL|string {
						return $this->userLastName;
					}
					public function setUserLastName(string $value):void {						
						$this->userLastName = sanitizeString($value);
					}
					
					#********** USER EMAIL **********#
					public function getUserEmail():NULL|string {
						return $this->userEmail;
					}
					public function setUserEmail(string $value):void {						
						$this->userEmail = sanitizeString($value);
					}
					
					#********** USER CITY **********#
					public function getUserCity():NULL|string {
						return $this->userCity;
					}
					public function setUserCity(string $value):void {						
						$this->userCity = sanitizeString($value);
					}
					
					#********** USER PASSWORD **********#
					public function getUserPassword():NULL|string {
						return $this->userPassword;
					}
					public function setUserPassword(string $value):void {						
						$this->userPassword = sanitizeString($value);
					}
					
					#********** VIRTUAL ATTRIBUTES **********#
					public function getFullName():string {
						return "{$this->getUserFirstName()} {$this->getUserLastName()}";
					}

					public function getFullNameWithCity():string {
						return "{$this->getUserFirstName()} {$this->getUserLastName()} ({$this->getUserCity()})";
					}

					#***********************************************************#

					public function checkIfExists(PDO $PDO):int {
if(DEBUG_C)				echo "<p class='debug class'>🌀 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";
						
						// Schritt 2 DB: SQL-Statement und Placeholders-Array erstellen
						$sql 			= 'SELECT COUNT(userEmail) FROM User
											WHERE userEmail = ?';
						
						$placeholders 	= array( $this->getUserEmail() );
						
						// Schritt 3 DB: Prepared Statements
						try {
							// Prepare: SQL-Statement vorbereiten
							$PDOStatement = $PDO->prepare($sql);
							
							// Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
							$PDOStatement->execute($placeholders);
							// showQuery($PDOStatement);
							
						} catch(PDOException $error) {
if(DEBUG_C) 				echo "<p class='debug class db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
						}
						
						// Schritt 4 DB: Ergebnis der DB-Operation auswerten
						$count = $PDOStatement->fetchColumn();
if(DEBUG_V)				echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$count: $count <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						return $count;						
					}

				}
				
#*******************************************************************************************#
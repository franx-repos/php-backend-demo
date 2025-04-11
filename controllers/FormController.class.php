<?php
#*******************************************************************************************#
				
				
				#******************************************#
				#********** ENABLE STRICT TYPING **********#
				#******************************************#

				declare(strict_types=1);
							
#*******************************************************************************************#


				#*******************************************#
				#********** CLASS FORM CONTROLLER **********#
				#*******************************************#

				
#*******************************************************************************************#


				class FormController {
					
					#*******************************#
					#********** ATTRIBUTE **********#
					#*******************************#
					
					private $PDO;
                    private $errors = [];
                    private $messages = [];
					
					#***********************************************************#
					
					
					#*********************************#
					#********** CONSTRUCTOR **********#
					#*********************************#
					
					public function __construct($PDO) {
						$this->PDO = $PDO;
					}

					#********** DESTRUCTOR **********#					
					
					
					#***********************************************************#
					

					#******************************#
					#********** METHODEN **********#
					#******************************#

                    #********** PROCESS REGISTRATION FORM **********#
					public function processRegistrationForm($postData, User $user) {
                        #********** PREVIEW POST ARRAY **********#
// if(DEBUG_V)	            echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_POST<br>". print_r($_POST, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";

                        $password       = sanitizeString( $postData['f4']);
                        $passWordCheck  = sanitizeString( $postData['f5']);

                        $_SESSION['old'] = [
                            'userFirstName' => $postData['f1'],
                            'userLastName'  => $postData['f2'],
                            'userEmail'     => $postData['f3']
                        ];

                        $this->validateFields(  user:           $user, 
                                                newPassword:    $password, 
                                                passwordCheck:  $passWordCheck, 
                                                passwordOrigin: null, 
                                                isRegistration: true);

                        // Falls Fehler vorhanden sind, abbrechen
                        if (!empty($this->errors)) { 
                            $_SESSION['errors'] = $this->errors;
                            return;
                        }
                        
                        if (!empty($password)) {
                            $user->setUserPassword(password_hash($password, PASSWORD_DEFAULT));
                        }

                        $PDO = $this->PDO;

						#********** CHECK IF EMAIL ADDRESS IS ALREADY REGISTERED **********#
if(DEBUG)			    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Prüfe, ob Email-Adresse bereits registriert ist... <i>(" . basename(__FILE__) . ")</i></p>\n";

                        $count = Db::checkIfExists($PDO, 'User', 'userEmail', $user->getUserEmail());

                        if( $count !== 0 ) {
							// Fehlerfall
if(DEBUG)				    echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Die Email-Adresse '{$user->getUserEmail()}' ist bereits in der DB registriert! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							$_SESSION['errors']['userEmail'] = 'Es existiert bereits eine gültige Registrierung zu dieser Email-Adresse.';

						} else {
							// Erfolgsfall
if(DEBUG)					echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Die Email-Adresse '{$user->getUserEmail()}' ist noch nicht in der DB registriert. <i>(" . basename(__FILE__) . ")</i></p>\n";

                            $userController = new UserController();
                            if( ($userController->saveNewUserToDB($PDO, $user)) !== 1 ) {
                                // Fehlerfall
    if(DEBUG)					echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: FEHLER beim Speichern des Users! <i>(" . basename(__FILE__) . ")</i></p>";
                                $_SESSION['error'] = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
                                                        
                            } else {
                                // Erfolgsfall
                                $user->setUserID( $PDO->lastInsertID() );
                            
    if(DEBUG)					echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: User erfolgreich mit der ID: {$user->getUserID()} gespeichert. <i>(" . basename(__FILE__) . ")</i></p>";
    if(DEBUG_V)					echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$user<br>". print_r($user, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";
                                
                                $_SESSION['success'] = 'Der User wurde erfolgreich gespeichert.';
                                
                            } // SAVE USER INTO DB END  
                        } // CHECK IF EMAIL ADDRESS IS ALREADY REGISTERED END
                    } // PROCESS REGISTRATION FORM END
                    
					#***********************************************************#

                    #********** PROCESS EDIT USER FORM **********#

					public function processEditUserForm( array $postData, User $user ): void {
                        
                        // Schritt 2 FORM: Auslesen, entschärfen und Debug-Ausgabe der übergebenen Formularwerte

                        $user->setUserFirstName( $postData['f1'] );
                        $user->setUserLastName( $postData['f2'] );
                        $user->setUserCity( $postData['f3'] );
                        $user->setUserEmail( $postData['f4'] );

                        #*** HELPER VARIABLES ***#
                        $newPassword 	= sanitizeString($postData['f5']);
                        $passwordCheck 	= sanitizeString($postData['f6']);
                        $passwordOrigin = sanitizeString($postData['f7']);

                        // Schritt 3 FORM: Feldvalidierung
                        $this->validateFields(  user:           $user, 
                                                newPassword:    $newPassword, 
                                                passwordCheck:  $passwordCheck, 
                                                passwordOrigin: $passwordOrigin );

                        // Falls Fehler vorhanden sind, abbrechen
                        if (!empty($this->errors)) { 
                            $_SESSION['errors'] = $this->errors;
                            return;
                        }
                        
                        if (!empty($newPassword)) {
                            $user->setUserPassword(password_hash($newPassword, PASSWORD_DEFAULT));
                        }

                        $PDO = dbConnect();

                        $userController = new UserController();
                        
                        if( ($rowCount = $userController->updateUserData($PDO, $user)) === 0 ) {
                            
                            // 'Fehlerfall' (Es wurden keine Daten verändert)
if(DEBUG)					echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Es wurden keine Daten verändert. <i>(" . basename(__FILE__) . ")</i></p>\n";				
                                    
                            // Rückmeldung für User generieren
                            $_SESSION['info'] = 'Ihre Nutzerdaten wurden nicht verändert.';
                            
                        } else {
                            // Erfolgsfall
if(DEBUG)					echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: $rowCount Datensätze erfolgreich geändert. <i>(" . basename(__FILE__) . ")</i></p>\n";				
                            
                            // Rückmeldung für User generieren
                            $_SESSION['success'] = 'Ihre Nutzerdaten wurden erfolgreich geändert.';
                        }
                    }

                    #***********************************************************#

                    #********** PROCESS FORM NEW BLOG ENTRY **********#
                    public function processFormNewBlogEntry( Blog $blog, bool $editBlog = false, ?array $blogObjectsArray = NULL ): void {
if(DEBUG_C)             echo "<p class='debug class'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";
                    
                        $_SESSION['tempBlogData'] = [
                            'category'              => $blog->getCategory()->getCatID(),
                            'blogHeadline'          => $blog->getBlogHeadline(),
                            'blockImageAlignment'   => $blog->getBlogImageAlignment(),
                            'blogContent'           => $blog->getBlogContent(),
                        ];

                        // Validierung
if(DEBUG)               echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";
                        $this->validateFields(blog: $blog);
                    
                        if (!empty($this->errors)) {
                            $_SESSION['errors'] = $this->errors;
                            return;
                        }
                    
                        if( $editBlog === true ) {
                            #********** WHITELISTING: CHECK IF BLOG ID IS VALID **********#
                            if( array_key_exists($blog->getBlogID(), $blogObjectsArray) === false ) {
							// Fehlerfall
if(DEBUG)					echo "<p class='debug err'>📑 <b>Line " . __LINE__ . "</b>: Invalid blog ID! <i>(" . basename(__FILE__) . ")</i></p>\r\n";
                            return;
                            }
                        }
                        
                        // Optionaler Bildupload
                        $errorImageUpload = $this->handleImageUpload($blog, $editBlog);
                        if ($errorImageUpload !== null) {
                            $_SESSION['errors']['imageUpload'] = $errorImageUpload;
                            return;
                        }
                    
                        // DB speichern
if(DEBUG)               echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Speichere Blogeintrag in DB... <i>(" . basename(__FILE__) . ")</i></p>\n";
                    
                        $PDO = dbConnect();
                        $blogController = new BlogController();
                        
                        if( $editBlog === true ) {
                            if( $blogController->updateBlog($PDO, $blog) === 0 ) {
                                // 'Fehlerfall'
if(DEBUG)						echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: No data was changed. <i>(" . basename(__FILE__) . ")</i></p>\r\n";				
                                $_SESSION['info'] = 'Es wurden keine Daten verändert.';
                            
                            } else {
                                // Erfolgsfall
if(DEBUG)						echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Blog Post '{$blog->getBlogHeadline()}' updated successfully in database. <i>(" . basename(__FILE__) . ")</i></p>\r\n";				
                                $_SESSION['success'] = "Der Blog Post '{$blog->getBlogHeadline()}' wurde erfolgreich upgedatet.";
                                    
                                #********** ALTER BLOG POST IN ALL BLOGS ARRAY **********#
                                $blogObjectsArray[$blog->getBlogID()] = $blog;
                                
                                // Bearbeitungsmodus verlassen
                                $editPost = false;
                                
                                // Blog-Objekt leeren
                                $blog = NULL;
                            }
                        } else {
                            if (($blogController->saveNewBlogToDB(PDO: $PDO, blog: $blog)) !== 1) {
if(DEBUG)                       echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: FEHLER beim Speichern! <i>(" . basename(__FILE__) . ")</i></p>";
                                $_SESSION['error'] = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
                            } else {
                                $blog->setBlogID($PDO->lastInsertID());
if(DEBUG)                       echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: Blog erfolgreich gespeichert. ID: {$blog->getBlogID()} <i>(" . basename(__FILE__) . ")</i></p>";
                                $_SESSION['success'] = 'Der Blogbeitrag wurde erfolgreich gespeichert.';
                                $blog = NULL;
                            }
                        }                    
                    }
                    #***********************************************************#

                    #********** VALIDATE FIELDS **********#
                    private function validateFields(    ?User $user             = NULL,
                                                        ?Blog $blog             = NULL, 
                                                        ?string $newPassword    = NULL, 
                                                        ?string $passwordCheck  = NULL, 
                                                        ?string $passwordOrigin = NULL, 
                                                        bool $isRegistration    = false ): void 
                        {
                        if ($user !== null) {
                            if ($error = validateInputString($user->getUserFirstName())) {
                                $this->errors['userFirstName'] = $error;
                            }
                            if ($error = validateInputString($user->getUserLastName())) {
                                $this->errors['userLastName'] = $error;
                            }
                            if ($error = validateEmail($user->getUserEmail())) {
                                $this->errors['userEmail'] = $error;
                            }
                        }
                
                        if ($isRegistration || !empty($newPassword) || !empty($passwordCheck) || !empty($passwordOrigin)) {
                            #********** 1. CHECK IF PASSWORD MATCHES REQUIREMENTS **********#
                            if( validateInputString($newPassword, minLength:4) !== NULL ) {
                                $this->errors['userPassword'] = 'Das neue Passwort entspricht nicht den Anforderungen!';
                            }
                            #********** 2. CHECK IF PASSWORD AND PASSWORD CHECK MATCH **********#
                            if ($newPassword !== $passwordCheck) {
                                $this->errors['userPassword'] = 'Passwörter stimmen nicht überein!';
                            }
                            // Nur bei Benutzerbearbeitung: Überprüfung des alten Passworts
                            #********** 3. CHECK IF PASSWORD ORIGIN MATCHES ACCOUNT PASSWORD FROM DB **********#
                            if (!$isRegistration && !password_verify($passwordOrigin, $user->getUserPassword())) {
                                $this->errors['passwordOrigin'] = 'Altes Passwort ist falsch!';
                            }
                        }

                        if ($blog !== null) {
                            if ($error = validateInputString($blog->getBlogHeadline())) {
                                $this->errors['errorHeadline'] = $error;
                            }
                            if ($error = validateInputString($blog->getBlogContent(), minLength: 5, maxLength: 20000)) {
                                $this->errors['errorContent'] = $error;
                            }
                        }
                    }

                    #***********************************************************#

                    private function handleImageUpload(Blog $blog, bool $editMode): ?string {
                        if (!isset($_FILES['blogImage']) || $_FILES['blogImage']['tmp_name'] === '') {
                            return null;
                        }
                    
                        $uploadResult = validateImageUpload($_FILES['blogImage']['tmp_name']);
                    
                        if (!empty($uploadResult['imageError'])) {
                            return $uploadResult['imageError'];
                        }
                    
                        if (!empty($uploadResult['imagePath'])) {
                            if ($editMode && $blog->getBlogImagePath()) {
                                @unlink($blog->getBlogImagePath());
                            }
                            $blog->setBlogImagePath($uploadResult['imagePath']);
                        }
                    
                        return null;
                    }
                    
                    #***********************************************************#

				}
                
#*******************************************************************************************#
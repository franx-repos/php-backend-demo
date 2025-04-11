<?php
#**************************************************************************************#

				
				#*************************************#
				#********** SANITIZE STRING **********#
				#*************************************#				
				
				/**
				*
				*	Ersetzt potentiell gefährliche Steuerzeichen durch HTML-Entities
				*	Entfernt vor und nach einem String Whitespaces
				*
				*	@params		string	$value		Die zu bereinigende Zeichenkette
				*
				*	@return		string					Die bereinigte Zeichenkette
				*
				*/
				function sanitizeString($value) {
					#********** LOCAL SCOPE START **********#
// if(DEBUG_F)		echo "<p class='debug sanitizeString'>🌀<b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$value') <i>(" . basename(__FILE__) . ")</i></p>\n";
										
					/*
						SCHUTZ GEGEN EINSCHLEUSUNG UNERWÜNSCHTEN CODES:
						Damit so etwas nicht passiert: <script>alert("HACK!")</script>
						muss der empfangene String ZWINGEND entschärft werden!
						htmlspecialchars() wandelt potentiell gefährliche Steuerzeichen wie
						< > " & in HTML-Code um (&lt; &gt; &quot; &amp;).
						
						Der Parameter ENT_QUOTES wandelt zusätzlich einfache ' in &apos; um.
						Der Parameter ENT_HTML5 sorgt dafür, dass der generierte HTML-Code HTML5-konform ist.
						
						Der 1. optionale Parameter regelt die zugrundeliegende Zeichencodierung 
						(NULL=Zeichencodierung wird vom Webserver übernommen)
						
						Der 2. optionale Parameter bestimmt die Zeichenkodierung
						
						Der 3. optionale Parameter regelt, ob bereits vorhandene HTML-Entities erneut entschärft werden
						(false=keine doppelte Entschärfung)
					*/
					$value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
										
					/*
						trim() entfernt VOR und NACH einem String (aber nicht mitten drin) 
						sämtliche sog. Whitespaces (Leerzeichen, Tabs, Zeilenumbrüche)
					*/
					$value = trim($value);
					
					// Entschärften und getrimmten String zurückgeben
					
					/*
						Leerstrings aus dem Formular in NULL umwandeln, damit in der DB vorhandene
						NULL-Werte nicht mit Leerstrings überschrieben werden.
					*/
					if($value === '') $value = NULL;
					
					return $value;
					#********** LOCAL SCOPE END **********#
				}
				
				
#**************************************************************************************#


				#*******************************************#
				#********** VALIDATE INPUT STRING **********#
				#*******************************************#
				
				/**
				*
				*	Prüft einen übergebenen String auf Maximallänge sowie optional 
				* 	auf Mindestlänge und Pflichtangabe.
				*	Generiert Fehlermeldung bei Leerstring und gleichzeitiger Pflichtangabe 
				*	oder bei ungültiger Länge.
				*
				*	@param	string		$value											Der zu validierende String
				*	@param	boolean		$mandatory=INPUT_STRING_MANDATORY		Angabe zu Pflichteingabe
				*	@param	integer		$minLength=INPUT_STRING_MIN_LENGTH		Die zu prüfende Mindestlänge
				*	@param	integer		$maxLength=INPUT_STRING_MAX_LENGTH		Die zu prüfende Maximallänge
				*
				*	@return	string|null														Fehlermeldung | ansonsten NULL
				*
				*/
				function validateInputString(	$value,
														$mandatory=INPUT_STRING_MANDATORY, 
														$minLength=INPUT_STRING_MIN_LENGTH, 
														$maxLength=INPUT_STRING_MAX_LENGTH )
				{
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debug validateInputString'>🌀<b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$value' [$minLength|$maxLength] mandatory:$mandatory ) <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					#********** MANDATORY CHECK **********#
					if( $mandatory === true AND $value === NULL ) {
						// Fehlerfall
						return 'Dies ist ein Pflichtfeld!';
					}
					
					
					#********** MAXIMUM LENGTH CHECK **********#
					/*
						Da die Felder in der Datenbank oftmals eine Längenbegrenzung besitzen,
						die Datenbank aber bei Überschreiten dieser Grenze keine Fehlermeldung
						ausgibt, sondern alles, das über diese Grenze hinausgeht, stillschweigend 
						abschneidet, muss vorher eine Prüfung auf diese Maximallänge durchgeführt 
						werden. Nur so kann dem User auch eine entsprechende Fehlermeldung ausgegeben
						werden.
					*/
					/*
						mb_strlen() erwartet als Datentyp einen String. Wenn (später bei der OOP)
						jedoch ein anderer Datentyp wie Integer oder Float übergeben wird, wirft
						mb_strlen() einen Fehler. Da es ohnehin keinen Sinn macht, einen Zahlenwert
						auf seine Länge (Anzahl der Zeichen) zu prüfen, wird diese Prüfung nur für
						den Datentyp 'String' durchgeführt.
					*/
					/*
						Wenn man Werte bereits bei der Entgegennahme sanitized, muss man allerdings darauf achten, 
						dass ab nun ggf. HTML-Entitäten (also bspw. &amp; &quot; etc.) in den Werten vorhanden sind. 
						Diese müssen bei eventuell nachfolgenden Stringoperationen wie Positionen suchen oder Längen 
						ermitteln ggf. berücksichtigt werden.
						Hierzu verwendet man die PHP-Funktion htmlspecialchars_decode().
						
						Beispiel: $feldwert = sanitizeString($value);
						Prüfen auf Maximallänge: mb_strlen( htmlspecialchars_decode($feldwert, ENT_QUOTES) );
					*/
					/*
						Da die Übergabe von NULL an PHP-eigene Funktionen in künftigen PHP-Versionen 
						nicht mehr erlaubt ist, muss vor jedem Aufruf einer PHP-Funktion sichergestellt 
						werden, dass der zu übergebende Wert nicht NULL ist.
					*/
					if( $value !== NULL AND mb_strlen($value) > $maxLength  ) {
						// Fehlerfall
						return "Darf maximal $maxLength Zeichen lang sein!";
					}
					
					
					#********** MINIMUM LENGTH CHECK **********#
					/*
						Es gibt Sonderfälle, bei denen eine Mindestlänge für einen Userinput
						vorgegeben ist, beispielsweise bei der Erstellung von Passwörtern.
						Damit nicht-Pflichtfelder aber auch weiterhin leer sein dürfen, muss
						die Mindestlänge als Standardwert mit 0 vorbelegt sein.
						
						Bei einem optionalen Feldwert, der gleichzeitig eine Mindestlänge
						einhalten muss, darf die Prüfung keine Leersrtings validieren, da 
						diese nie die Mindestlänge erfüllen und somit der Wert nicht mehr 
						optional wäre.
					*/
					/*
						mb_strlen() erwartet als Datentyp einen String. Wenn (später bei der OOP)
						jedoch ein anderer Datentyp wie Integer oder Float übergeben wird, wirft
						mb_strlen() einen Fehler. Da es ohnehin keinen Sinn macht, einen Zahlenwert
						auf seine Länge (Anzahl der Zeichen) zu prüfen, wird diese Prüfung nur für
						den Datentyp 'String' durchgeführt.
					*/
					/*
						Da die Übergabe von NULL an PHP-eigene Funktionen in künftigen PHP-Versionen 
						nicht mehr erlaubt ist, muss vor jedem Aufruf einer PHP-Funktion sichergestellt 
						werden, dass der zu übergebende Wert nicht NULL ist.
					*/
					if( $value !== NULL AND mb_strlen($value) < $minLength  ) {
						// Fehlerfall
						return "Muss mindestens $minLength Zeichen lang sein!";
					}
					
					
					#********** NO ERROR **********#
					return NULL;
					#********** LOCAL SCOPE END **********#
				}


#**************************************************************************************#


				#*******************************************#
				#********** VALIDATE EMAIL FORMAT **********#
				#*******************************************#				
				
				/**
				*
				*	Prüft einen übergebenen String auf eine valide Email-Adresse und auf Leerstring.
				*	Generiert Fehlermeldung bei ungültiger Email-Adresse oder Leerstring
				*
				*	@param	string	$value							Der zu übergebende String
				*
				*	@return	string|null									Fehlermeldung | ansonsten NULL
				*
				*/
				function validateEmail($value) {
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debug validateEmail'>🌀<b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$value') <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					#********** MANDATORY CHECK **********#
					if( $value === NULL ) {
						// Fehlerfall
						return 'Dies ist ein Pflichtfeld!';
					}
					
					
					#********** VALIDATE EMAIL ADDRESS FORMAT **********#
					if( filter_var( $value, FILTER_VALIDATE_EMAIL ) === false ) {
						// Fehlerfall
						return 'Dies ist keine gültige Email-Adresse!';
					}
					
					
					#********** NO ERROR **********#
					return NULL;
					#********** LOCAL SCOPE END **********#
				}


#**************************************************************************************#


				#*******************************************#
				#********** VALIDATE IMAGE UPLOAD **********#
				#*******************************************#
				
				/**
				*
				*	Validiert ein auf den Server hochgeladenes Bild auf korrekten und erlaubten MIME-Type, 
				*	auf Bildtyp, Bildgröße in Pixeln, Dateigröße in Bytes sowie den Header auf Plausibilität.
				*	Generiert einen unique Dateinamen sowie eine sichere Dateiendung und verschiebt das Bild 
				*	in das Zielverzeichnis.
				*
				*	@param	string	$fileTemp															Der temporäre Pfad zum hochgeladenen Bild im Quarantäneverzeichnis
				*	@param	integer	$imageMaxWidth				=IMAGE_MAX_WIDTH					Die maximal erlaubte Bildbreite in Pixeln				
				*	@param	integer	$imageMaxHeight			=IMAGE_MAX_HEIGHT					Die maximal erlaubte Bildhöhe in Pixeln
				*	@param	integer	$imageMinSize				=IMAGE_MIN_SIZE					Die minimal erlaubte Dateigröße in Bytes
				*	@param	integer	$imageMaxSize				=IMAGE_MAX_SIZE					Die maximal erlaubte Dateigröße in Bytes
				*	@param	array		$imageAllowedMimeTypes	=IMAGE_ALLOWED_MIME_TYPES		Whitelist der zulässigen MIME-Types mit den zugehörigen Dateiendungen
				*	@param	string	$imageUploadPath			=IMAGE_UPLOAD_PATH				Das Zielverzeichnis
				*
				*	@return	array		{'imagePath'	=>	String|NULL, 								Bei Erfolg der Speicherpfad zur Datei im Zielverzeichnis | bei Fehler NULL
				*							 'imageError'	=>	String|NULL}								Bei Erfolg NULL | Bei Fehler Fehlermeldung
				*
				*/
				function validateImageUpload( $fileTemp,
														$imageMaxWidth 			= IMAGE_MAX_WIDTH,
														$imageMaxHeight 			= IMAGE_MAX_HEIGHT,
														$imageMinSize 				= IMAGE_MIN_SIZE,
														$imageMaxSize 				= IMAGE_MAX_SIZE,
														$imageAllowedMimeTypes 	= IMAGE_ALLOWED_MIME_TYPES,
														$imageUploadPath 			= IMAGE_UPLOAD_PATH )
				{
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debug validateImageUpload'>🌀<b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$fileTemp') <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					#**************************************************************************#
					#********** I. GATHER INFORMATION FOR IMAGE FILE VIA FILE HEADER **********#
					#**************************************************************************#
					
					/*
						FILE HEADER
						
						Die Informationen, die immer in jedem Bildheader oder Dateiheader eines Bildes vorhanden sind, können 
						je nach dem spezifischen Bildformat variieren. Es gibt jedoch einige grundlegende Informationen, die in 
						den meisten gängigen Bildformaten vorkommen und als Pflichtangaben angesehen werden. 
						Zu den typischen Pflichtangaben gehören:

						- Dateisignatur (MIME TYPE): Jedes Bildformat hat eine eindeutige Dateisignatur, die am Anfang der Datei steht und 
						  auf das Format hinweist. Die Dateisignatur ist entscheidend, um das Dateiformat zu identifizieren.

						- Dateigröße: Die Größe der Bilddatei in Bytes oder Kilobytes ist in den meisten Dateiheadern enthalten. 
						  Dies ist wichtig für die Speicherplatzverwaltung und das Einlesen der Datei.

						- Bildabmessungen: Informationen über die Breite und Höhe des Bildes in Pixeln sind entscheidend, um die 
						  richtige Darstellung des Bildes zu gewährleisten. Diese Informationen sind nahezu immer im Dateiheader vorhanden.

						- Farbtiefe: Die Farbtiefe gibt an, wie viele Farben pro Pixel im Bild dargestellt werden können. 
						  Bei RGB-Bildern beträgt die übliche Farbtiefe 24 Bit (8 Bit pro Kanal), was 16,7 Millionen Farben entspricht. 
						  Dies ist eine grundlegende Information im Header.
											  
						  Diese Angaben sind in den meisten gängigen Bildformaten zu finden und gelten als grundlegende Pflichtangaben im 
						  Dateiheader. 
					*/
					/*
						Die Funktion getimagesize() liest den Dateiheader einer Bilddatei aus und 
						liefert bei gültigem MIME Type ('image/...') ein gemischtes Array zurück:
						
						[0] 				Bildbreite in PX (Bildabmessungen)
						[1] 				Bildhöhe in PX  (Bildabmessungen)
						[3] 				Einen für das HTML <img>-Tag vorbereiteten String (width="480" height="532") 
						['bits']			Anzahl der Bits pro Kanal (Farbtiefe)
						['channels']	Anzahl der Farbkanäle (somit auch das Farbmodell: RGB=3, CMYK=4) 
						['mime'] 		MIME Type
						
						Bei ungültigem MIME Type (also nicht 'image/...') liefert getimagesize() false zurück
					*/	
					
					$imageDataArray = getimagesize($fileTemp);
					
// if(DEBUG_F)		echo "<pre class='debug value validateImageUpload'><b>Line " . __LINE__ . "</b>: \$imageDataArray<br>". print_r($imageDataArray, true) . "<i>(" . basename(__FILE__) . ")</i>:</pre>\n";
					
					
					#********** 1. CHECK FOR VALID MIME TYPE **********#
					if( $imageDataArray === false ) {
						// 1. Fehlerfall: MIME TYPE IS NO VALID IMAGE MIME TYPE
						return array( 'imagePath' => NULL, 'imageError' => 'Dies ist keine gültige Bilddatei!' );	
						
					} elseif( is_array($imageDataArray) === true ) {
						// Erfolgsfall: MIME TYPE IS A VALID IMAGE MIME TYPE
						
						/*
							SONDERFALL NUMBER (NUMERIC STRINGS):
							Da wir aus Formularen und anderen Usereingaben alle Werte immer
							als Datentyp String erhalten, macht eine Prüfung auf einen konkreten
							numerischen Datentyp in PHP nur selten Sinn.
							
							Anstatt mittels is_int() direkt auf den Datentyp Integer zu prüfen,
							ist es besser, einen empfangenen String auf sein inhaltliches Format 
							zu prüfen: Ist der String numerisch und entspricht sein Wert einem Integer?

							Die Funktion filter_var() kann mittels eines regulären Ausdrucks, der über
							eine Konstante gesteuert wird, auch einen String auf den Inhalt 'Integer' oder
							'Float' überprüfen.

							Entspricht der mittels filter_var() geprüfte Wert dem zu prüfenden Datenformat,
							nimmt filter_var automatisch eine Typumwandlung vor und liefert den umgewandelten 
							Wert zurück.
						*/						
						$imageWidth 	= filter_var($imageDataArray[0], FILTER_VALIDATE_INT);
						$imageHeight 	= filter_var($imageDataArray[1], FILTER_VALIDATE_INT);
						$imageMimeType = sanitizeString($imageDataArray['mime']);
						$fileSize 		= fileSize($fileTemp);
						/*
							Das Auslesen der Header-Einträger 'bits' und 'channels' sollte überlegt sein,
							da diese beiden Angaben im Header auch bei validen Bildtypen durchaus fehlen könnten.
							In diesem Fall gäbe es diese Indizes nicht, und wir bekämen an dieser Stelle eine
							Fehlermeldung 'Undefined array key 'bits' oder 'channels'.
							
							$imageColorBits		= filter_var($imageDataArray['bits'], FILTER_VALIDATE_INT);
							$imageColorChannels	= filter_var($imageDataArray['channels'], FILTER_VALIDATE_INT);
						*/
						
if(DEBUG_F)			echo "<p class='debug value validateImageUpload'><b>Line " . __LINE__ . "</b>: \$imageWidth: $imageWidth px <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)			echo "<p class='debug value validateImageUpload'><b>Line " . __LINE__ . "</b>: \$imageHeight: $imageHeight px <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)			echo "<p class='debug value validateImageUpload'><b>Line " . __LINE__ . "</b>: \$imageMimeType: $imageMimeType <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)			echo "<p class='debug value validateImageUpload'><b>Line " . __LINE__ . "</b>: \$fileSize: $fileSize Byte <i>(" . basename(__FILE__) . ")</i></p>\n";
						
					} // I. GATHER INFORMATION FOR IMAGE FILE VIA FILE HEADER END
					#*********************************************************************************#
					
					
					#******************************************#
					#********** II. IMAGE VALIDATION **********#
					#******************************************#
					
					
					#********** 2. VALIDATE PLAUSIBILITY OF FILE HEADER **********#
					/*
						Diese Prüfung setzt darauf, dass ein maniplulierter Dateiheader nicht konsequent
						gefälscht wurde:
						Ein Hacker ändert den MimeType einer Textdatei mit Schadcode aud 'image/jpg', vergisst
						aber beispielsweise, zusätzlich weitere Einträge wie 'imageWidth' oder 'imageHeight' 
						hinzuzufügen.
						
						Da wir den Datentyp eines im Dateiheader fehlenden Wertes nicht kennen (NULL, '', 0), 
						wird an dieser Stelle ausdrücklich nicht typsicher, sondern auf 'falsy' geprüft.
						Ein ! ('NOT') vor einem Wert oder einer Funktion negiert die Auswertung: Die Bedingung 
						ist erfüllt, wenn die Auswertung false ergibt.
					*/
					if( !$imageWidth OR !$imageHeight OR !$imageMimeType OR $fileSize < $imageMinSize ) {
						// 2. Fehlerfall: NON PLAUSIBLE HEADER
						return array( 'imagePath' => NULL, 'imageError' => 'Verdächtiger Dateiheader!' );
					}
					
					
					#********** 3. VALIDATE IMAGE MIME TYPES **********#
					// WHITELIST mit erlaubten MIME TYPES
					// $imageAllowedMimeTypes = array('image/jpg' => '.jpg', 'image/jpeg' => '.jpg', 'image/png' => '.png', 'image/gif' => '.gif');
					/*
						- Die Funktion in_array() prüft, ob eine übergebene Needle einem Wert (value) innerhalb 
						  eines zu übergebenden Arrays entspricht.
						  
						- Die Funktion array_key_exists() prüft, ob eine übergebene Needle einem Index (key) innerhalb 
						  eines zu übergebenden Arrays entspricht.
					*/
					if( array_key_exists( $imageMimeType, $imageAllowedMimeTypes ) === false ) {
						// 3. Fehlerfall: MIME TYPE IS NOT ALLOWED
						return array( 'imagePath' => NULL, 'imageError' => 'Dies ist kein erlaubter Bildtyp!' );
					}
					
					
					#********** 4. VALIDATE IMAGE WIDTH **********#
					if( $imageWidth > $imageMaxWidth ) {
						// 4. Fehlerfall: IMAGE WIDTH TOO BIG
						return array( 'imagePath' => NULL, 'imageError' => "Die Bildbreite darf maximal $imageMaxWidth Pixel betragen!" );
					}					
					
					
					#********** 5. VALIDATE IMAGE HEIGHT **********#
					if( $imageHeight > $imageMaxHeight ) {
						// 5. Fehlerfall: IMAGE HEIGHT TOO BIG
						return array( 'imagePath' => NULL, 'imageError' => "Die Bildhöhe darf maximal $imageMaxHeight Pixel betragen!" );
					}					
					
					
					#********** 6. VALIDATE FILE SIZE **********#
					if( $fileSize > $imageMaxSize ) {
						// 6. Fehlerfall: FILE SIZE TOO BIG
						return array( 'imagePath' => NULL, 'imageError' => 'Die Dateigöße darf maximal ' . $imageMaxSize/1024 . 'Kb betragen!' );
					
					} // II. IMAGE VALIDATION END
					#*********************************************************************************#
					
					
					#***************************************************************#
					#********** III. PREPARE IMAGE FOR PERSISTANT STORAGE **********#
					#***************************************************************#
					
					#********** 1. GENERATE UNIQUE FILE NAME **********#
					/*
						Da der Dateiname selbst Schadcode in Form von ungültigen oder versteckten Zeichen,
						doppelte Dateiendungen (dateiname.exe.jpg) etc. beinhalten kann, darüberhinaus ohnehin 
						sämtliche, nicht in einer URL erlaubten Sonderzeichen und Umlaute entfernt werden müssten 
						sollte der Dateiname aus Sicherheitsgründen komplett neu generiert werden.
						
						Hierbei muss außerdem bedacht werden, dass die jeweils generierten Dateinamen unique
						sein müssen, damit die Dateien sich bei gleichem Dateinamen nicht gegenseitig überschreiben.
					*/
					/*
						- 	mt_rand() stellt die verbesserte Version der Funktion rand() dar und generiert 
							Zufallszahlen mit einer gleichmäßigeren Verteilung über das Wertesprektrum. Ohne zusätzliche
							Parameter werden Zahlenwerte zwischen 0 und dem höchstmöglichem von mt_rand() verarbeitbaren 
							Zahlenwert erzeugt.
							
						- 	str_shuffle() mischt die Zeichen eines übergebenen Strings zufällig durcheinander.
						
						- 	microtime() liefert einen Timestamp mit Millionstel Sekunden zurück (z.B. '0.57914300 163433596'),
							aus dem für eine URL-konforme Darstellung der Dezimaltrenner und das Leerzeichen entfernt werden.
					*/
					$fileName = mt_rand() . str_shuffle('0123456789_-abcdefghikbgnegknrewkfjbklnjklmnopqrstuvwxyz-_0123456789') . str_replace(array('.', ' '), '', microtime());


					#********** 2. GENERATE FILE EXTENSION **********#
					/*
						Aus Sicherheitsgründen wird nicht die ursprüngliche Dateinamenerweiterung aus dem
						Dateinamen verwendet, sondern eine vorgenerierte Dateiendung aus dem Array der 
						erlaubten MIME Types.
						Die Dateiendung wird anhand des ausgelesenen MIME Types [key] ausgewählt.
					*/
					$fileExtension = $imageAllowedMimeTypes[$imageMimeType];
// if(DEBUG_F)		echo "<p class='debug value validateImageUpload'><b>Line " . __LINE__ . "</b>: \$fileExtension: '<i>$fileExtension</i>' <i>(" . basename(__FILE__) . ")</i></p>\n";

					
					#********** 3. GENERATE FILE TARGET **********#
					/*
						Endgültigen Speicherpfad auf dem Server generieren:
						'destinationPath/fileName.fileExtension'
					*/
					$fileTarget = $imageUploadPath . $fileName . $fileExtension;

if(DEBUG_F)		echo "<p class='debug value validateImageUpload'><b>Line " . __LINE__ . "</b>: Länge: " . mb_strlen($fileTarget) . " <i>(" . basename(__FILE__) . ")</i></p>\n";				
if(DEBUG_F)		echo "<p class='debug value validateImageUpload'><b>Line " . __LINE__ . "</b>: \$fileTarget: '<i>$fileTarget</i>' <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					// III. PREPARE IMAGE FOR PERSISTANT STORAGE END
					#*****************************************************************#
					
					
					#*********************************************************#
					#********** IV. MOVE IMAGE TO FINAL DESTINATION **********#
					#*********************************************************#
					/*
						move_uploaded_file() verschiebt eine hochgeladene Datei an einen 
						neuen Speicherort und benennt die Datei um
					*/
					/*
						Ein @ vor einem Funktionsaufruf unterdrückt die Ausgabe der von dieser
						Funktion generierten Fehlermeldung im Frontend
					*/
					if( @move_uploaded_file( $fileTemp, $fileTarget ) === false ) {
						// 7. Fehlerfall: IMAGE CANNOT BE MOVED
if(DEBUG_F)			echo "<p class='debug err validateImageUpload'><b>Line " . __LINE__ . "</b>: FEHLER beim verschieben der Datei von '<i>$fileTemp</i>' nach '<i>$fileTarget</i>'! <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						// TODO: Eintrag in ErrorLog | Email an SysAdmin						
						return array( 'imagePath' => NULL, 'imageError' => 'Es ist ein Fehler aufgetreten! Bitte kontaktieren Sie unseren Support.' );
					
					} else {
						// Erfolgsfall
if(DEBUG_F)			echo "<p class='debug ok validateImageUpload'><b>Line " . __LINE__ . "</b>: Datei erfolgreich von '<i>$fileTemp</i>' nach '<i>$fileTarget</i>' verschoben. <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						return array( 'imagePath' => $fileTarget, 'imageError' => NULL );
					}
					// IV. MOVE IMAGE TO FINAL DESTINATION END
					#*****************************************************************#
					
					#********** LOCAL SCOPE END **********#
				}


#**************************************************************************************#
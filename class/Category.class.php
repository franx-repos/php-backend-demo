<?php
#*******************************************************************************************#
				
				
				#******************************************#
				#********** ENABLE STRICT TYPING **********#
				#******************************************#
				
				declare(strict_types=1);
				
				
#*******************************************************************************************#


				#************************************#
				#********** CLASS CATEGORY **********#
				#************************************#

				
#*******************************************************************************************#


				class Category {
					
					#*******************************#
					#********** ATTRIBUTE **********#
					#*******************************#
					
					private ?int $catID;
					private ?string $catLabel;
					
					
					#***********************************************************#
					
					
					#*********************************#
					#********** CONSTRUCTOR **********#
					#*********************************#
					
					public function __construct( $catID = NULL, $catLabel = NULL ) {
						
// if(DEBUG_CC)		echo "<p class='debug class'>🛠 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "()  (<i>" . basename(__FILE__) . "</i>)</p>\n";						
						
						// Setter nur aufrufen, wenn der jeweilige Parameter keinen Leerstring und nicht NULL enthält
						if( $catID 		!== NULL AND $catID 	!== '') $this->setCatID($catID );
						if( $catLabel 	!== NULL AND $catLabel 	!== '') $this->setCatLabel($catLabel );
						
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
				
					#********** CAT ID **********#
					public function getCatID():NULL|int {
						return $this->catID;
					}
					public function setCatID(int|string $value):void {
						
						#********** VALIDATE DATA FORMAT **********#
						if( ($value = filter_var($value, FILTER_VALIDATE_INT)) === false ) {
							// Fehlerfall (nicht erlaubtes Datenformat)
if(DEBUG_C)					echo "<p class='debug class err'><b>Line " . __LINE__ . "</b>: " . __METHOD__ . "():  Muss dem Format 'Integer' entsprechen! <i>(" . basename(__FILE__) . ")</i></p>\n";
							
						} else {
							// Erfolgsfall (erlaubtes Datenformat)
							$this->catID = $value;
						}						
					}
					
					#********** CAT LABEL **********#
					public function getCatLabel():NULL|string {
						return $this->catLabel;
					}
					public function setCatLabel(string $value):void {						
						$this->catLabel = sanitizeString($value);	
					}					
					
					#***********************************************************#
					

					#******************************#
					#********** METHODEN **********#
					#******************************#
					
					#********** FETCH ALL CATEGORIES DATA FROM DB **********#
					/**
					 * 
					 * Fetches all categories from the database and returns an array of Category objects.
					 *
					 * @param PDO $PDO The database connection object.
					 * @return array An associative array of Category objects indexed by catID.
					 * 
					 */
					public static function fetchCategories(PDO $PDO): array {
						$data = Db::fetchFromDb($PDO, 'Category');
				
						$categoryObjectsArray = [];
						foreach ($data as $row) {
							$categoryObjectsArray[$row['catID']] = new Category(
								catID: $row['catID'],
								catLabel: $row['catLabel']
							);
						}
						return $categoryObjectsArray;
					} 

					#***********************************************************#

					#********** SAVES OBJECTDATA TO DB **********#
					/**
					*
					*	SAVES CATEGORY-OBJECTDATA TO DB
					*	WRITES LAST INSERT ID INTO CATEGORY-OBJECT
					*
					*	@param	PDO $PDO	DB-Connection object
					*
					*	@return	boolean		true if writing was successful, else false
					*
					*/
					public function saveNewCategoryToDB(PDO $PDO): int {
						return Db::saveToDb($PDO, 'Category', [
							'catLabel' => $this->getCatLabel()
						]);
					}
					
					#***********************************************************#

				}
				
				
#*******************************************************************************************#
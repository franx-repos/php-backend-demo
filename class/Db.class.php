<?php
#*******************************************************************************************#
				
				
				#******************************************#
				#********** ENABLE STRICT TYPING **********#
				#******************************************#
				
				declare(strict_types=1);
				
				
#*******************************************************************************************#


				#******************************#
				#********** CLASS Db **********#
				#******************************#

				
#*******************************************************************************************#


				class Db {

					#******************************#
					#********** METHODEN **********#
					#******************************#

					#********** FETCH DATA FROM DB **********#
					/**
                     * Fetches data from the specified table with optional filtering, joins, and ordering.
                     *
                     * @param PDO $PDO The database connection object.
                     * @param string $table The name of the table to fetch data from.
                     * @param array $filters Optional associative array of column => value for WHERE conditions.
                     * @param array|null $joins Optional SQL JOIN clause.
                     * @param string|null $orderBy Optional ORDER BY clause.
                     * @return array Returns an array of fetched data as associative arrays.
                     * @throws Exception If the table name is not whitelisted.
                     */
					public static function fetchFromDb( PDO $PDO, 
                                                        string $table, 
                                                        array $joins = [], 
                                                        array $filters = [], 
                                                        ?string $orderBy = null ):array {

if(DEBUG_C)				echo "<p class='debug class'>🌀 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";

                        // 🔒 Whitelist erlaubter Tabellen und Joins
                        $allowedTables  = ['Category', 'Blog', 'User'];
                        $allowedJoins   = [
                            'User'      => 'INNER JOIN User USING(userID)',
                            'Category'  => 'INNER JOIN Category USING(catID)'
                        ];
                        $allowedOrderByColumns = ['blogDate', 'catLabel'];


                        // 🔎 Überprüfen, ob die angefragte Tabelle erlaubt ist
                        if (!in_array($table, $allowedTables, true)) {
                            throw new Exception("Unauthorized table access: $table");
                        }

                        // Array leer vorinitialisieren
                        $dataObjectsArray = [];

						// Schritt 2 DB: SQL-Statement und Placeholders-Array erstellen
						
                        // 🛡️ Joins überprüfen und einfügen
                        $sql = "SELECT * FROM $table";
                        
                        if (!empty($joins)) {
                            foreach ($joins as $joinTable) {
                                if (!isset($allowedJoins[$joinTable])) {
                                    throw new Exception("Unauthorized JOIN access: $joinTable");
                                }
                                $sql .= " " . $allowedJoins[$joinTable];
                            }
                        }

                        // 🛡️ Filter hinzufügen
						$placeholders = array();
										
                        if (!empty($filters)) {
                            $conditions = [];
                            foreach ($filters as $column => $value) {
                                $conditions[] = "$column = :$column";
                                $placeholders[$column] = $value;
                            }
                            $sql .= " WHERE " . implode(" AND ", $conditions);
                        }

                        // 🛡️ Sortierung prüfen
                        if ($orderBy !== null) {
                            if (!in_array($orderBy, $allowedOrderByColumns, true)) {
                                throw new Exception("Unauthorized ORDER BY column: $orderBy");
                            }
                            $sql .= " ORDER BY $orderBy DESC";
                        }

						// Schritt 3 DB: Prepared Statements
						try {
							// Prepare: SQL-Statement vorbereiten
							$PDOStatement = $PDO->prepare($sql);
							
							// Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
							$PDOStatement->execute($placeholders);
							// showQuery($PDOStatement);
							
						} catch(PDOException $error) {
if(DEBUG_C) 				echo "<p class='debug class db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
                            return array();
                        }
												
						// Schritt 4 DB: Datenbankoperation auswerten (und DB-Verbindung schließen: NICHT INNERHALB DER METHODE!)
						
						while( $row = $PDOStatement->fetch(PDO::FETCH_ASSOC) ) {
						
							$dataObjectsArray[] = $row;
						}
						
						return $dataObjectsArray;						
					
					}	
					
					#***********************************************************#

                    public static function saveToDb(PDO $PDO, string $table, array $data): int {
if(DEBUG_C)             echo "<p class='debug class'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";
                    
                        // 🔒 Whitelist erlaubter Tabellen
                        $allowedTables = ['Category', 'Blog', 'User'];
                        
                        if (!in_array($table, $allowedTables, true)) {
                            throw new Exception("Unauthorized table access: $table");
                        }
                    
                        // Spaltennamen und Platzhalter generieren
                        $columns = array_keys($data);
                        $placeholders = array_map(fn($col) => ":$col", $columns);
                        
                        $sql = "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
                    
                        try {
                            $PDOStatement = $PDO->prepare($sql);
                            $PDOStatement->execute($data);
                        } catch(PDOException $error) {
if(DEBUG_C)                 echo "<p class='debug class db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->getMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                            return 0;
                        }
                    
                        return $PDOStatement->rowCount();
                    }
                    
					#***********************************************************#

                    public static function updateToDb(PDO $PDO, string $table, array $data, string $primaryKey, int $id): int {
if(DEBUG_C)             echo "<p class='debug class'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";
                    
                        // 🔒 Whitelist erlaubter Tabellen
                        $allowedTables = ['Category', 'Blog', 'User'];
                        
                        if (!in_array($table, $allowedTables, true)) {
                            throw new Exception("Unauthorized table access: $table");
                        }
                    
                        // SQL-SET-Teil generieren
                        $columns = array_keys($data);
                        $setClauses = array_map(fn($col) => "$col = :$col", $columns);
                        
                        $sql = "UPDATE $table SET " . implode(", ", $setClauses) . " WHERE $primaryKey = :id";
                        
                        // Platzhalter-Werte hinzufügen
                        $data['id'] = $id;
                    
                        try {
                            $PDOStatement = $PDO->prepare($sql);
                            $PDOStatement->execute($data);
                        } catch(PDOException $error) {
if(DEBUG_C)                 echo "<p class='debug class db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->getMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                            return 0;
                        }
                    
                        return $PDOStatement->rowCount();
                    }
                    
					#***********************************************************#

                    #********** CHECK IF ENTRY ALREADY EXISTS IN DB **********#
					/**
					*
					*	Checks if entry already exists in Database
					*
					*	@param	PDO	$PDO		DB-connection object
					*
					*	@return	boolean			String if exists, else false
					*
					*/
					public static function checkIfExists(PDO $PDO, string $table, string $column, mixed $value): int {
                        if(DEBUG_C)             echo "<p class='debug class'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";
                        
                            // 🔒 Whitelist erlaubter Tabellen
                            $allowedTables = ['Category', 'Blog', 'User'];
                        
                            if (!in_array($table, $allowedTables, true)) {
                                throw new Exception("Unauthorized table access: $table");
                            }
                        
                            // SQL-Statement mit Platzhalter
                            $sql = "SELECT COUNT(*) FROM $table WHERE $column = :value";
                        
                            try {
                                // Prepare: SQL-Statement vorbereiten
                                $PDOStatement = $PDO->prepare($sql);
                        
                                // Execute: SQL-Statement ausführen
                                $PDOStatement->execute(['value' => $value]);
							

						} catch(PDOException $error) {
if(DEBUG_C)                 echo "<p class='debug class db err'><b>Line " . __LINE__ . "</b>: ERROR: " . $error->getMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                            return 0;  // Falls Fehler auftritt, direkt 0 zurückgeben
                        }

                        // Falls `$PDOStatement` null ist (z. B. falls `prepare` fehlschlägt), return 0
                        if (!$PDOStatement) {
                            return 0;
                        }

                        // Schritt 4 DB: Ergebnis der DB-Operation auswerten
                        $count = (int) $PDOStatement->fetchColumn();

if(DEBUG_V)             echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$count: $count <i>(" . basename(__FILE__) . ")</i></p>\n";

                        return $count;
                    }

				}
				
#*******************************************************************************************#
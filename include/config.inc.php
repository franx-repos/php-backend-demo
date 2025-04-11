<?php
#**************************************************************************************#

				
				#******************************************#
				#********** GLOBAL CONFIGURATION **********#
				#******************************************#

				#********** DATABASE CONFIGURATION LOCAL **********#
				require_once __DIR__ . '/../vendor/autoload.php';

				// Standardmäßig auf 'production' setzen
				$env = 'production';

				// Pfad zur .env Datei (nur für lokale Umgebung)
				$envPath = __DIR__ . '/../.env';

				if (file_exists($envPath)) {
					// .env existiert → lokal
					$env = 'local';

					try {
						// Dotenv laden
						$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
						$dotenv->load();
					} catch (Exception $e) {
						die('Fehler beim Laden der .env-Datei: ' . $e->getMessage());
					}
				} else {
					// Server-Umgebung (Render), hier sollte APP_ENV bereits gesetzt sein
					if (isset($_ENV['APP_ENV'])) {
						$env = $_ENV['APP_ENV'];
					}
				}
				
				#********** DATABASE CONFIGURATION PRODUCTION **********#
				define('DB_SYSTEM', 'mysql');
				define('DB_HOST', $_ENV['DB_HOST']);
				define('DB_NAME', $_ENV['DB_NAME']);
				define('DB_USER', $_ENV['DB_USER']);
				define('DB_PWD', $_ENV['DB_PWD']);
				
				#********** EXTERNAL STRING VALIDATION CONFIGURATION **********#
				define('INPUT_STRING_MANDATORY',		true);
				define('INPUT_STRING_MAX_LENGTH',	255);
				define('INPUT_STRING_MIN_LENGTH',	0);
				
				
				#********** IMAGE UPLOAD CONFIGURATION **********#
				define('IMAGE_MAX_WIDTH',			800);
				define('IMAGE_MAX_HEIGHT',			800);
				define('IMAGE_MIN_SIZE',				1024);
				define('IMAGE_MAX_SIZE',				128*1024);
				define('IMAGE_ALLOWED_MIME_TYPES',	array('image/jpg' => '.jpg', 'image/jpeg' => '.jpg', 'image/png' => '.png', 'image/gif' => '.gif') );
				
				
				#********** STANDARD PATHS CONFIGURATION **********#
				define('IMAGE_UPLOAD_PATH',			'./uploaded_images/');
				define('AVATAR_DUMMY_PATH',			'../css/images/avatar_dummy.png');
				define('CLASS_PATH',					'./class/');
				define('INTERFACE_PATH',				'./class/');
				define('TRAIT_PATH',					'../trait/');
				
				
				#********** STANDARD FILE EXTENSIONS CONFIGURATION **********#
				define('CLASS_EXTENSION',			'.class.php');
				define('INTERFACE_EXTENSION',		'.class.php');
				define('TRAIT_EXTENSION',			'.trait.php');

				
				#********** DEBUGGING CONFIGURATION **********#
				define('DEBUG',						false);		// DEBUGGING FOR MAIN DOCUMENTS
				define('DEBUG_V',					false);		// DEBUGGING FOR VALUES
				define('DEBUG_F',					false);		// DEBUGGING FOR FUNCTIONS
				define('DEBUG_DB',					false);		// DEBUGGING FOR DB-OPERATIONS
				define('DEBUG_C',					false);		// DEBUGGING FOR CLASSES
				define('DEBUG_CC',					false);		// DEBUGGING FOR CLASS CONSTRUCTORS
				define('DEBUG_T',					false);		// DEBUGGING FOR TRAITS


#**************************************************************************************#
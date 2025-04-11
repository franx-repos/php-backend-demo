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
				// require_once 'class/Blog.class.php';
				// require_once 'class/Category.class.php';

				#********** INCLUDE CONTROLLERS **********#
				require_once 'controllers/UserController.class.php';
				require_once 'controllers/FormController.class.php';

#*******************************************************************************************#

                #********** PREPARE SESSION **********#
				session_name('oop_blogproject');
		
				#********** START/CONTINUE SESSION **********#
				session_start();

#*******************************************************************************************#

                #******************************************#
				#********** INITIALIZE VARIABLES **********#
				#******************************************#
				
                $user                   = NULL;
                $loggedIn               = false;

#*******************************************************************************************#

                #********** STORE SESSION ERRORS AND USER INPUT LOCALLY **********#
                $errors = $_SESSION['errors'] ?? [];
                unset($_SESSION['errors']);

                $oldValues = $_SESSION['old'] ?? [];
                unset($_SESSION['old']);

#*******************************************************************************************#

                #***********************************************#
				#********** PROCESS FORM REGISTRATION **********#
				#***********************************************#

                if( isset($_POST['registrationForm']) === true ) {
if(DEBUG)			echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Formular 'Registration' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";													
					
                    $PDO = dbConnect();

    	            $user           = new User( userFirstName: $_POST['f1'],
                                                userLastName: $_POST['f2'],
                                                userEmail: $_POST['f3']);

					$formController = new FormController($PDO);
					$formController->processRegistrationForm($_POST, $user);

					#********** CLOSE DB CONNECTION **********#
					dbClose($PDO);

                    // Redirect after POST to prevent form resubmission
                    header("Location: registration.php");
                    exit;

				} // PROCESS FORM REGISTRATION END
                
#*******************************************************************************************#
 
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

#*******************************************************************************************#

?>

<!doctype html>
<html>

<?php include 'views/head-section.view.php' ?>

<body>

    <!-- ---------- PAGE HEADER START ---------- -->
    <header>
        <section class="page-title">
            <img src="img/logo-black.png" alt="Blog-Logo" class="logo">
            <h1>PHP-Blog Projekt - Registration</h1>
        </section>
        <section class="menu">
            <a href="./">
                << zum Frontend</a>
        </section>
    </header>

    <!-- ---------- PAGE HEADER END ---------- -->

    <main class="registration-page">
        <form action="" method="POST">

            <!-- -------- HIDDEN FIELD -------- -->
            <input type="hidden" name="registrationForm">

            <!-- ---------- USER DATA ---------- -->
            <fieldset>
                <label>Vorname</label>
                <span class="error"><?= $errors['userFirstName'] ?? '' ?></span>
                <input type="text" name="f1" value="<?= $oldValues['userFirstName'] ?? '' ?>"
                    placeholder="Bitte tragen Sie hier Ihren Vornamen ein..."><span class="marker">*</span>

                <label>Nachname</label>
                <span class="error"><?= $errors['userLastName'] ?? '' ?></span>
                <input type="text" name="f2" value="<?= $oldValues['userLastName'] ?? '' ?>"
                    placeholder="Bitte tragen Sie hier Ihren Nachnamen ein..."><span class="marker">*</span>

                <label>Email-Adresse</label>
                <span class="error"><?= $errors['userEmail'] ?? '' ?></span>
                <input type="text" name="f3" value="<?= $oldValues['userEmail'] ?? '' ?>"
                    placeholder="Bitte tragen Sie hier Ihre aktuelle Email-Adresse ein..."><span class="marker">*</span>

                <label>Passwort</label>
                <span class="error"><?= $errors['userPassword'] ?? '' ?></span><br>
                <small><i>
                        Geben Sie das gewünschte Passwort bitte im ersten Eingabefeld ein und
                        wiederholen Sie das Passwort im zweiten Eingabefeld.<br>
                        <i><strong>Das Passwort muss mindestens 4 Zeichen lang sein.</strong></i>
                    </i></small><br>
                <input type="password" name="f4" placeholder="Bitte wählen Sie ein Passwort..."><span
                    class="marker">*</span><br>
                <input type="password" name="f5" placeholder="Bitte wiederholen Sie das gewählte Passwort..."><span
                    class="marker">*</span><br>

            </fieldset>

            <!-- -------- SUBMIT BUTTON -------- -->
            <input type="submit" value="Jetzt registrieren">

        </form>
        <!-- -------- FORM REGISTRATION END -------- -->
    </main>
</body>

</html>
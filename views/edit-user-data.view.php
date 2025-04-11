<section class="user-data">
    <h2>Nutzerdaten</h2>

    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="formEditUserData">
        <section>
            <article>
                <label>Vorname: </label><span class="error"><?= getSessionValue('errors', 'userFirstName') ?></span>
                <input type="text" name="f1" value="<?= $user->getUserFirstName() ?>">
            </article>
            <article>
                <label>Nachname:</label><span class="error"><?= getSessionValue('errors', 'userLastName') ?></span>
                <input type="text" name="f2" value="<?= $user->getUserLastName() ?>">
            </article>
        </section>
        <section>
            <article>
                <label>Stadt: </label><span class="error"><?= getSessionValue('errors', 'userCity') ?></span>
                <input type="text" name="f3" value="<?= $user->getUserCity() ?>">
            </article>
            <article>
                <label>Email:</label><span class="error"><?= getSessionValue('errors', 'userEmail') ?></span>
                <input type="text" name="f4" value="<?= $user->getUserEmail() ?>">
            </article>
        </section>
        <h3 style="margin-top: 1.5rem">Passwort ändern:</h3>
        <section class="change-password">
            <article>
                <!-- -------- PASSWORD CHANGE START -------- -->
                <span class="error"><?= getSessionValue('errors', 'userPassword') ?></span>
                <input type="password" name="f5" placeholder="Neues Passwort">
            </article>
            <article>
                <span class="error"><?= getSessionValue('errors', 'userPassword') ?></span>
                <input type="password" name="f6" placeholder="Neues Passwort wiederholen">
            </article>

            <!-- -------- PASSWORD CHANGE END -------- -->
        </section>
        <section>

            <article>
                <span class="error"><?= getSessionValue('errors', 'passwordOrigin') ?></span>
                <input type="password" name="f7" placeholder="Mit altem Passwort bestätigen">
            </article>
        </section>
        <input type="submit" value="Änderungen speichern">
    </form>
</section>
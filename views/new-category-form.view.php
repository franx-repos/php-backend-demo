<section class="new-category">
    <form action="" method="POST">

        <input class="dashboard" type="hidden" name="formNewCategory">
        <fieldset>
            <h2 class="dashboard">Neue Kategorie anlegen</h2>
            <label>Name der neuen Kategorie:</label>
            <span class="error"><?= $errorCatLabel ?></span>
            <input class="dashboard" type="text" name="catLabel" placeholder="..."
                value="<?= $category?->getCatLabel() ?>">

            <input class="dashboard" type="submit" value="Neue Kategorie anlegen">
        </fieldset>
    </form>
</section>
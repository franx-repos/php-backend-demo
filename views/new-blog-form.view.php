<section class="new-blog-form">
    <form action="" method="POST" enctype="multipart/form-data">
        <fieldset>
            <!-- ---------- EDIT BLOG POST -------- -->
            <?php if($editPost === true): ?>
            <input type="hidden" name="formEditBlogEntry">
            <input type="hidden" name="blogID" value="<?= $blog->getBlogID() ?>">
            <p><a href="./dashboard.php#dashboard">
                    << Bearbeitungsmodus verlassen</a>
            </p>
            <h2 class="dashboard">Blog-Eintrag bearbeiten</h2>

            <!-- ---------- NEW BLOG POST -------- -->
            <?php else: ?>
            <input class="dashboard" type="hidden" name="formNewBlogEntry">
            <h2 class="dashboard">Neuen Blog-Eintrag verfassen</h2>
            <?php endif ?>
            <section>
                <article>
                    <label>Überschrift:</label>
                    <span class="error"><?= $errors['errorHeadline'] ?? '' ?></span>
                    <input class="dashboard" type="text" name="blogHeadline" placeholder="..."
                        value="<?= $blog?->getBlogHeadline() ?>">
                </article>
                <article>
                    <label>Kategorie:</label>
                    <select class="dashboard bold" name="catID">
                        <?php if( empty($categoryObjectsArray) === false ): ?>
                        <?php foreach($categoryObjectsArray AS $categoryObject): ?>
                        <option value='<?= $categoryObject->getCatID() ?>'
                            <?php if($blog?->getCategory()->getCatID() == $categoryObject->getCatID() ) echo 'selected'?>>
                            <?= $categoryObject->getCatLabel() ?></option>
                        <?php endforeach ?>
                        <?php else: ?>
                        <option value='' style='color: darkred'>Bitte zuerst eine Kategorie anlegen!
                        </option>
                        <?php endif ?>
                    </select>
                </article>
            </section>
            <!-- ---------- IMAGE UPLOAD START ---------- -->
            <fieldset name="image-upload">
                <label>[Optional] Bild veröffentlichen:</label>
                <span class="error"><?= $errorImageUpload ?></span>
                <imageUpload>

                    <!-- -------- INFOTEXT FOR IMAGE UPLOAD START -------- -->
                    <p class="small">
                        Erlaubt sind Bilder des Typs
                        <?php $allowedMimetypes = implode( ', ', array_keys(IMAGE_ALLOWED_MIME_TYPES) ) ?>
                        <?= strtoupper( str_replace( array(', image/jpeg', 'image/'), '', $allowedMimetypes) ) ?>.
                        <br>
                        Die Bildbreite darf <?= IMAGE_MAX_WIDTH ?> Pixel nicht übersteigen.<br>
                        Die Bildhöhe darf <?= IMAGE_MAX_HEIGHT ?> Pixel nicht übersteigen.<br>
                        Die Dateigröße darf <?= IMAGE_MAX_SIZE/1024 ?>kB nicht übersteigen.
                    </p>
                    <!-- -------- INFOTEXT FOR IMAGE UPLOAD END -------- -->
                    <input type="file" name="blogImage">

                    <select class="alignment" name="blogImageAlignment">
                        <option value="img-fleft"
                            <?php if($blog?->getBlogImageAlignment() == 'img-fleft') echo 'selected'?>>
                            align left
                        </option>
                        <option value="img-fright"
                            <?php if($blog?->getBlogImageAlignment() == 'img-fright') echo 'selected'?>>
                            align right
                        </option>
                    </select>

                    <!-- ---------- IMAGE PREVIEW ---------- -->
                    <?php if($editPost === true): ?>
                    <br>
                    <h3>Aktuelles Bild:</h3>
                    <img class="preview-img" src=<?= $blog->getBlogImagePath() ?> alt="Blog Post Preview Image">
                    <?php endif ?>
                </imageUpload>
            </fieldset>
            <!-- ---------- IMAGE UPLOAD END ---------- -->

            <label>Inhalt des Blogeintrags:</label>
            <span class="error"><?= $errors['errorContent'] ?? '' ?></span>
            <textarea class="dashboard" name="blogContent" placeholder="..."><?= $blog?->getBlogContent() ?></textarea>
            <input class="dashboard" type="submit"
                value="<?= $editPost === true ? 'Änderungen speichern' : 'Veröffentlichen' ?>">
        </fieldset>
    </form>
</section>
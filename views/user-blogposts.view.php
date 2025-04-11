<section class="user-blogposts">
    <fieldset>
        <h2>Posts</h2>
        <?php if( $blogObjectsArray === false ): ?>
        <p class="info">Noch keine Blogeinträge vorhanden.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>Kategorie</th>
                    <th>Erstellungsdatum</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php foreach( $blogObjectsArray AS $blogObject ): ?>
                    <td><?= $blogObject->getBlogHeadline() ?></td>
                    <td><?= $blogObject->getCategory()->getCatLabel() ?></td>
                    <td><?= isoToEuDateTime($blogObject->getBlogDate())['date'] ?></td>
                    <td>
                        <a href="?action=editPost&blogID=<?= $blogObject->getBlogID() ?>">
                            <img class="blog-list-icon" src="./img/edit-icon.png" title="Post bearbeiten"
                                alt="Bearbeiten">
                        </a>
                    </td>
                    <td>
                        <a href="?action=deletePost&blogID=<?= $blogObject->getBlogID() ?>">
                            <img class="blog-list-icon" src="./img/cross-icon.png" title="Post löschen" alt="Löschen">
                        </a>
                    </td>
                </tr>
            </tbody>
            <?php endforeach ?>
        </table>
        <?php endif ?>
    </fieldset>
</section>
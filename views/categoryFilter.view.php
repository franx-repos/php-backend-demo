<?php if( $categoryObjectsArray === false ): ?>
<p class="info">Noch keine Kategorien vorhanden.</p>

<?php else: ?>
<ul>
    <?php foreach( $categoryObjectsArray AS $categoryObject ): ?>

    <li><a href="?action=filterByCategory&catID=<?= $categoryObject->getCatID() ?>"
            <?php if( $categoryObject->getCatID() == $categoryFilterID ) echo 'class="active"' ?>><?= $categoryObject->getCatLabel() ?></a>
    </li>

    <?php endforeach ?>

</ul>
<section class="filter-reset">
    <a href="<?php echo $_SERVER['SCRIPT_NAME'] ?>">Filter zurücksetzen</a>
</section>
<?php endif ?>
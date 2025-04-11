<section class="blog-content fleft">
    <?php if( $blogObjectsArray === false ): ?>
    <p class="info">Noch keine Blogeinträge vorhanden.</p>

    <?php elseif( empty( $blogObjectsArray ) === true ): ?>
    <p class="info">In dieser Kategorie sind noch keine Blogeinträge vorhanden.</p>

    <?php else: ?>
    <?php foreach( $blogObjectsArray AS $singleBlogItemArray ): ?>
    <?php $dateTimeArray = isoToEuDateTime($singleBlogItemArray->getBlogDate()) ?>

    <article class='blog-post'>

        <a name='entry<?= $singleBlogItemArray->getBlogID() ?>'></a>

        <p class='category'><a
                href='?action=filterByCategory&catID=<?= $singleBlogItemArray->getCategory()->getCatID() ?>'>Kategorie:
                <?= $singleBlogItemArray->getCategory()->getCatLabel() ?></a></p>
        <h2><?= $singleBlogItemArray->getBlogHeadline() ?></h2>

        <p class='author'><?= $singleBlogItemArray->getFullUserNameWithCity() ?>
            schrieb am <?= $dateTimeArray['date'] ?> um <?= $dateTimeArray['time'] ?> Uhr:</p>

        <p class='blogContent'>

            <?php if( $singleBlogItemArray->getBlogImagePath() !== NULL ):  ?>
            <?= $singleBlogItemArray->getBlogImage() ?>
            <?php endif ?>

            <?= nl2br( $singleBlogItemArray->getBlogContent(), false ) ?>
        </p>
        <div class='clearer'></div>
    </article>

    <?php endforeach ?>
    <?php endif ?>
</section>
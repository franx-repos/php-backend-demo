<header>
    <section class="page-title">
        <img src="img/logo-black.png" alt="Blog-Logo" class="logo">
        <h1>PHP-Blog Projekt</h1>
    </section>
    <?php if( $loggedIn === false ): ?>
    <?php if($loginError): ?>
    <p class="error"><b><?= $loginError ?></b></p>
    <?php endif ?>

    <!-- -------- Login Form START -------- -->
    <form action="" method="POST" class="login-form">
        <input type="hidden" name="formLogin">

        <input type="text" name="f1" placeholder="Email">
        <input type="password" name="f2" placeholder="Password">
        <input type="submit" value="Login" class="login-btn">
    </form>
    <section class="menu">
        <a href='registration.php'>Registrieren</a>
    </section>
    <!-- -------- Login Form END -------- -->

    <?php else: ?>
    <!-- -------- PAGE LINKS START -------- -->
    <section class="menu">
        <a href='dashboard.php'>zum Dashboard >></a>
        <a href="?action=logout">Logout</a>
    </section>
    <!-- -------- PAGE LINKS END -------- -->
    <?php endif ?>

</header>
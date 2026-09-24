    </section>
    <!-- /.content -->

<?php if (!empty($footer_admin)): ?>

    <!-- FOOTER HALAMAN -->
    <footer class="footer">

        © <?= date("Y") ?>

        <?= NAMA_SEKOLAH_PANJANG ?>.

        Panel Administrator.

    </footer>

<?php endif; ?>

</main>
<!-- /.main -->


<!-- =========================================================
     JAVASCRIPT MENU HP (dipakai semua halaman admin)
========================================================= -->

<script>

const sidebar =
    document.getElementById("sidebar");

const overlay =
    document.getElementById("overlay");

const mobileMenuBtn =
    document.getElementById("mobileMenuBtn");


function bukaMenu() {

    if (!sidebar || !overlay) {
        return;
    }

    sidebar.classList.add("show");
    overlay.classList.add("show");

}


function tutupMenu() {

    if (!sidebar || !overlay) {
        return;
    }

    sidebar.classList.remove("show");
    overlay.classList.remove("show");

}


if (mobileMenuBtn) {

    mobileMenuBtn?.addEventListener(
        "click",
        function () {

            if (!sidebar || !overlay) {
                return;
            }

            if (
                sidebar.classList.contains("show")
            ) {

                tutupMenu();

            } else {

                bukaMenu();

            }

        }
    );

}


if (overlay) {

    overlay?.addEventListener(
        "click",
        function () {

            tutupMenu();

        }
    );

}


document
    .querySelectorAll(".menu a")
    .forEach(function (link) {

        link.addEventListener(
            "click",
            function () {

                if (window.innerWidth <= 1000) {

                    tutupMenu();

                }

            }
        );

    });

</script>


</body>

</html>

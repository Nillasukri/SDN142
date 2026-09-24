<?php
/* =====================================================================
   KAKI HALAMAN PENGUNJUNG         layouts/publik/kaki.php
   ---------------------------------------------------------------------
   Dipakai semua halaman pengunjung: menampilkan footer, menjalankan
   menu HP, lalu menutup halaman.

   FOOTER (nama sekolah, alamat, tautan cepat, dst) ADA DI FILE INI.
   ===================================================================== */
?>
<!-- =====================================================
     FOOTER
===================================================== -->

<footer>


    <div class="footer">

        <div class="container">


            <div class="footer-grid">


                <div>

                    <h3>

                        <?= e(
                            $dataProfil["nama_sekolah"]
                        ); ?>

                    </h3>


                    <p>

                        Website resmi sekolah sebagai
                        media informasi dan komunikasi
                        sekolah.

                    </p>

                </div>


                <div>

                    <h3>
                        Alamat
                    </h3>


                    <p>

                        <i class="fa-solid fa-location-dot"></i>


                        <?= e(
                            $dataProfil["alamat"]
                        ); ?>

                    </p>

                </div>


            </div>

        </div>

    </div>


    <div class="footer-bottom">

        &copy;

        <?= date("Y"); ?>


        <?= e(
            $dataProfil["nama_sekolah"]
        ); ?>


        — Semua Hak Dilindungi.

    </div>

</footer>

<!-- =====================================================
     JAVASCRIPT MENU MOBILE
===================================================== -->

<script>

const menuButton =
    document.getElementById("menuButton");

const menu =
    document.getElementById("menu");


if (menuButton && menu) {

    menuButton.addEventListener(
        "click",
        function () {

            menu.classList.toggle("active");

        }
    );


    const links =
        menu.querySelectorAll("a");


    links.forEach(
        function (link) {

            link.addEventListener(
                "click",
                function () {

                    menu.classList.remove("active");

                }
            );

        }
    );

}

</script>


</body>

</html>

/* =====================================================================
   GESER KARTU GURU — TOMBOL + SWIPE      assets/js/geser-guru.js
   ---------------------------------------------------------------------
   Dipakai oleh bagian "Guru & Tenaga Kependidikan" di index.php.

   Ada DUA cara menggeser kartu guru:

     1. TOMBOL PANAH
        Tombol kiri dan kanan di sisi kartu. Satu klik = geser
        satu kartu. Tombol akan menghilang kalau sudah mentok
        ke ujung, atau kalau semua kartu muat di layar.

     2. SWIPE / SERET
        Kartu bisa diseret pakai jari (HP) atau mouse (komputer).

   ---------------------------------------------------------------------
   CARA PAKAI (lihat index.php):

       <div class="geser-wrap">

           <button
               type="button"
               class="geser-btn geser-kiri"
               data-geser="#guru-slider"
               data-arah="-1"
           >
               <i class="fa-solid fa-chevron-left"></i>
           </button>

           <div class="guru-slider" id="guru-slider">
               ... kartu-kartunya ...
           </div>

           <button
               type="button"
               class="geser-btn geser-kanan"
               data-geser="#guru-slider"
               data-arah="1"
           >
               <i class="fa-solid fa-chevron-right"></i>
           </button>

       </div>

   data-geser -> id kotak yang digeser
   data-arah  -> -1 untuk ke kiri, 1 untuk ke kanan

   Gaya CSS-nya ada di assets/css/publik/index.css
   (bagian "TOMBOL & SWIPE GESER GURU").
   ===================================================================== */


document.addEventListener("DOMContentLoaded", function () {


    const daftarTombol = document.querySelectorAll(".geser-btn");


    if (daftarTombol.length === 0) {

        return;
    }


    /* Setiap kotak yang digeser disimpan sekali saja,
       beserta daftar tombol yang mengaturnya. */

    const kotakDanTombol = new Map();


    daftarTombol.forEach(function (tombol) {

        const kotak = document.querySelector(
            tombol.getAttribute("data-geser") || ""
        );


        if (!kotak) {

            return;
        }


        if (!kotakDanTombol.has(kotak)) {

            kotakDanTombol.set(kotak, []);

            pasangSeret(kotak);

            kotak?.addEventListener(
                "scroll",
                function () {

                    perbaruiTombol(kotak);

                }
            );

        }


        kotakDanTombol.get(kotak).push(tombol);


        tombol?.addEventListener(
            "click",
            function () {

                const arah = parseInt(
                    tombol.getAttribute("data-arah"),
                    10
                ) || 1;


                kotak.scrollBy({

                    left: arah * satuLangkah(kotak),

                    behavior: "smooth"

                });

            }
        );

    });


    window.addEventListener("resize", perbaruiSemua);

    window.addEventListener("load", perbaruiSemua);


    perbaruiSemua();



    /* -----------------------------------------------------------------
       1. UKURAN SATU LANGKAH

       Satu klik tombol = selebar satu kartu + jarak antar kartu.
       Kalau kartunya tidak ditemukan, pakai 80% lebar kotak.
    ----------------------------------------------------------------- */

    function satuLangkah(kotak) {

        const kartu = kotak.querySelector(".guru-card");


        return kartu
            ? kartu.offsetWidth + 20
            : kotak.clientWidth * 0.8;
    }



    /* -----------------------------------------------------------------
       2. TOMBOL MENTOK

       Tombol disembunyikan kalau sudah tidak ada lagi yang bisa
       digeser ke arah itu (biar tidak ada tombol yang "nganggur").
    ----------------------------------------------------------------- */

    function perbaruiTombol(kotak) {

        const tombol = kotakDanTombol.get(kotak) || [];

        const sisa = kotak.scrollWidth - kotak.clientWidth;

        const posisi = Math.round(kotak.scrollLeft);


        tombol.forEach(function (satu) {

            const arah = parseInt(
                satu.getAttribute("data-arah"),
                10
            ) || 1;


            const mentok =
                sisa <= 2 ||
                (arah < 0 ? posisi <= 2 : posisi >= sisa - 2);


            satu.classList.toggle("mentok", mentok);

        });
    }


    function perbaruiSemua() {

        kotakDanTombol.forEach(
            function (tombol, kotak) {

                perbaruiTombol(kotak);

            }
        );
    }



    /* -----------------------------------------------------------------
       3. SWIPE / SERET

       Menyeret kartu dengan jari (HP) atau mouse (komputer).
       Gulir halaman ke atas-bawah tetap normal, karena yang
       ditangani hanya seretan mendatar (lihat touch-action
       di CSS: pan-y).
    ----------------------------------------------------------------- */

    function pasangSeret(kotak) {

        let sedangDiseret = false;

        let mulaiX = 0;

        let mulaiGeser = 0;

        let sudahJauh = false;


        /* Gambar jangan ikut "terangkat" waktu diseret */

        kotak.querySelectorAll("img").forEach(
            function (gambar) {

                gambar.setAttribute("draggable", "false");

            }
        );


        kotak?.addEventListener(
            "pointerdown",
            function (kejadian) {

                /* Tombol mouse selain klik kiri diabaikan */

                if (
                    kejadian.pointerType === "mouse" &&
                    kejadian.button !== 0
                ) {

                    return;
                }


                sedangDiseret = true;

                sudahJauh = false;

                mulaiX = kejadian.clientX;

                mulaiGeser = kotak.scrollLeft;

                kotak.classList.add("seret-aktif");


                /* Waktu diseret, gerakan harus langsung mengikuti
                   jari / mouse (tanpa animasi halus). */

                kotak.style.scrollBehavior = "auto";


                if (kotak.setPointerCapture) {

                    kotak.setPointerCapture(kejadian.pointerId);

                }

            }
        );


        kotak?.addEventListener(
            "pointermove",
            function (kejadian) {

                if (!sedangDiseret) {

                    return;
                }


                const geseran =
                    kejadian.clientX - mulaiX;


                if (Math.abs(geseran) > 6) {

                    sudahJauh = true;

                }


                kotak.scrollLeft = mulaiGeser - geseran;

            }
        );


        function lepaskanSeret() {

            if (!sedangDiseret) {

                return;
            }


            sedangDiseret = false;

            kotak.classList.remove("seret-aktif");


            /* Kembalikan animasi halus untuk tombol */

            kotak.style.scrollBehavior = "";

        }


        kotak?.addEventListener("pointerup", lepaskanSeret);

        kotak?.addEventListener("pointercancel", lepaskanSeret);

        kotak?.addEventListener("lostpointercapture", lepaskanSeret);


        /* Klik yang tidak disengaja setelah menyeret diabaikan */

        kotak?.addEventListener(
            "click",
            function (kejadian) {

                if (sudahJauh) {

                    kejadian.preventDefault();

                    kejadian.stopPropagation();

                    sudahJauh = false;

                }

            },
            true
        );

    }

});

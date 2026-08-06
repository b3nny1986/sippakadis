<?php

/*
|--------------------------------------------------------------------------
| Entry point Vercel (vercel-php runtime)
|--------------------------------------------------------------------------
| Vercel memanggil fungsi PHP dari folder /api. Di sini kita cukup
| meneruskan seluruh request ke bootstrap Laravel (public/index.php).
| Statis assets (build CSS/JS) tetap dilayani langsung oleh Vercel.
*/

require __DIR__.'/../public/index.php';

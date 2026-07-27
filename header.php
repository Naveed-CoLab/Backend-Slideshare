<!DOCTYPE html>
<?php if (!isset($htmlLang) || trim($htmlLang) === '') { $htmlLang = '<html lang="en">'; } ?>
<?=$htmlLang?>
<head>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <title><?=$title?></title>
   <?php if (!isset($decription)) { $decription = ''; } ?>
   <?php if (!isset($canonical_url) || trim($canonical_url) === '') { $canonical_url = $site_url . '/'; } ?>
   <meta name="description" content="<?=$decription?>">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
   <meta property="og:title" content="<?=$title?>">
   <meta property="og:description" content="<?=$decription?>">
   <meta property="og:type" content="website">
   <meta property="og:url" content="<?=$canonical_url?>">
   <meta property="og:site_name" content="SlidesDownloader.pro">
   <meta property="og:image" content="<?=$site_url?>/images/logo.webp">
   <meta property="og:locale" content="en_US">
   <meta name="twitter:card" content="summary_large_image">
   <meta name="twitter:title" content="<?=$title?>">
   <meta name="twitter:description" content="<?=$decription?>">
   <meta name="twitter:image" content="<?=$site_url?>/images/logo.webp">
   <meta name="theme-color" content="#ffaa55">
   <link rel="canonical" href="<?=$canonical_url?>" />
   <link rel="alternate" href="<?=$canonical_url?>" hreflang="x-default" />
   <link rel="alternate" href="<?=$canonical_url?>" hreflang="en" />
   <link rel="sitemap" type="application/xml" title="Sitemap" href="<?=$site_url?>/sitemap.xml" />
   <link rel="preload" href="/css/style.default.css?v=5.4" as="style">
   <link rel="stylesheet" href="/css/style.default.css?v=5.4" id="theme-stylesheet">
   <link rel="apple-touch-icon" sizes="180x180" href="/favicon/favicon.svg">
   <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon.svg">
   <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon.svg">
   <?php if (isset($jsonLdScripts) && is_array($jsonLdScripts)): ?>
   <?php foreach ($jsonLdScripts as $jsonLdScript): ?>
   <script type="application/ld+json"><?=$jsonLdScript?></script>
   <?php endforeach; ?>
   <?php endif; ?>
   <!-- Google tag (gtag.js) -->
   <script async src="https://www.googletagmanager.com/gtag/js?id=G-KLPF6EE8TL"></script>
   <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
   <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-KLPF6EE8TL');
   </script>
</head>
<body>
   <header class="header">
      <nav class="navbar navbar-expand-lg navbar-dark">
         <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?=$site_url?>/">
               <img
                  src="/images/logo-180.webp"
                  srcset="/images/logo-180.webp 180w, /images/logo-300.webp 300w"
                  sizes="(max-width: 991px) 150px, 180px"
                  alt="SlideShare Downloader logo - SlidesDownloader.pro"
                  width="150"
                  height="40"
                  loading="eager"
                  fetchpriority="high"
                  decoding="async"
                  onerror="this.onerror=null;this.src='/images/logo.webp';">
            </a>
            <div class="d-flex align-items-center gap-3">
               <a class="reset-anchor fw-semibold text-white" style="color:#fff !important;" href="<?=$site_url?>/">SlideShare Downloader</a>
               <a class="reset-anchor fw-semibold text-white" style="color:#fff !important;" href="<?=$site_url?>/scribd-downloader">Scribd Downloader</a>
            </div>
         </div>
      </nav>
   </header>

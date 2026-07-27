<?php
include 'function.php';
$query = isset($_GET['slug']) ? $_GET['slug'] : '';

$pageMeta = array(
    'privacy' => array(
        'h1' => 'Privacy Policy',
        'title' => 'Privacy Policy - SlidesDownloader.pro',
        'description' => 'Read the privacy policy for SlidesDownloader.pro. Learn what data we collect, how we use it, and your rights.',
        'file' => 'pages/pp.php'
    ),
    'terms-and-conditions' => array(
        'h1' => 'Terms and Conditions',
        'title' => 'Terms and Conditions - SlidesDownloader.pro',
        'description' => 'Review the terms and conditions for using SlidesDownloader.pro. Understand acceptable use, restrictions, and disclaimers.',
        'file' => 'pages/tos.php'
    ),
    'dmca' => array(
        'h1' => 'DMCA Notice',
        'title' => 'DMCA Copyright Policy - SlidesDownloader.pro',
        'description' => 'Submit DMCA takedown notices for copyrighted content. Read the DMCA policy of SlidesDownloader.pro.',
        'file' => 'pages/dmca.php'
    ),
    'contact-us' => array(
        'h1' => 'Contact Us',
        'title' => 'Contact Us - SlidesDownloader.pro',
        'description' => 'Get in touch with SlidesDownloader.pro for support, feedback, or copyright queries. Email: info@slidesdownloader.pro.',
        'file' => 'pages/contact.php'
    ),
);

if (!isset($pageMeta[$query])) {
    header('HTTP/1.1 404 Not Found');
    $title = 'Page Not Found - SlidesDownloader.pro';
    $decription = 'The page you are looking for is not available on SlidesDownloader.pro.';
    $canonical_url = $site_url . '/';
    include 'header.php';
    echo '<main><div class="container py-5 text-center"><h1 class="h2">404 - Page Not Found</h1><p class="mb-4">The page you requested could not be found.</p><p><a class="reset-anchor" href="' . htmlspecialchars($site_url, ENT_QUOTES, 'UTF-8') . '/">Return to Home</a></p></div></main>';
    include 'footer.php';
    exit;
}

$meta = $pageMeta[$query];
$title = $meta['title'];
$decription = $meta['description'];
$canonical_url = $site_url . '/page/' . $query;
include 'header.php';
?>
<main>
   <div class="col-lg-8 mx-auto py-4">
      <h1 class="mt-4 px-2 text-center h2"><?=htmlspecialchars($meta['h1'], ENT_QUOTES, 'UTF-8')?></h1>
      <hr>
      <div class="bg-white p-3 m-2 border rounded">
         <?php include $meta['file']; ?>
      </div>
   </div>
</main>
<?php include 'footer.php'; ?>

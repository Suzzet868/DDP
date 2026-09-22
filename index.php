
<?php
require_once __DIR__ . '/conexion.php';

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function format_date($date) {
    $timestamp = strtotime($date);
    if (!$timestamp) {
        return 'Sin fecha';
    }

    $months = [
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
        9 => 'Set', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
    ];

    return $months[(int) date('n', $timestamp)] . ' ' . date('d, Y', $timestamp);
}

function format_day_month($date) {
    $timestamp = strtotime($date);
    if (!$timestamp) {
        return 'Sin fecha';
    }

    $months = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
    ];

    return date('d', $timestamp) . ' ' . $months[(int) date('n', $timestamp)];
}

function youtube_embed_url($url) {
    $parts = parse_url(trim((string) $url));
    $host = strtolower($parts['host'] ?? '');
    $videoId = '';

    if ($host === 'youtu.be' && !empty($parts['path'])) {
        $videoId = trim($parts['path'], '/');
    } elseif (strpos($host, 'youtube.com') !== false) {
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
            $videoId = $query['v'] ?? '';
        }
        if (!$videoId && !empty($parts['path']) && preg_match('#/(?:embed|shorts|live)/([^/]+)#', $parts['path'], $matches)) {
            $videoId = $matches[1];
        }
    }

    return $videoId ? 'https://www.youtube.com/embed/' . rawurlencode($videoId) : trim((string) $url);
}

function video_title_with_highlights($title) {
    $safeTitle = e($title);
    return preg_replace('/\(([^()]*)\)/', '<span class="video-title-highlight">$1</span>', $safeTitle);
}

function video_title_plain($title) {
    return e(preg_replace('/\(([^()]*)\)/', '$1', (string) $title));
}

$podcasts = mysqli_query(
    $conexion,
    'SELECT id, titulo, url_embed, fecha_publicacion FROM podcasts ORDER BY fecha_publicacion DESC, id DESC LIMIT 4'
);

$noticias = mysqli_query(
    $conexion,
    'SELECT titulo, foto, link_externo, fecha_publicacion FROM noticias ORDER BY fecha_publicacion DESC, id DESC LIMIT 6'
);

$boletines = mysqli_query(
    $conexion,
    'SELECT numero_boletin, resumen, foto_portada, archivo_pdf, fecha_publicacion FROM boletines ORDER BY fecha_publicacion DESC, id DESC LIMIT 1'
);

$videos = mysqli_query(
    $conexion,
    'SELECT titulo, url_embed, fecha_publicacion FROM videos ORDER BY fecha_publicacion DESC, id DESC LIMIT 6'
);

$reportajes = mysqli_query(
    $conexion,
    'SELECT id, titulo, resumen_corto, desarrollo, foto_principal, fecha_publicacion, es_destacado FROM reportajes ORDER BY id DESC LIMIT 4'
);

if (!$podcasts || !$noticias || !$boletines || !$videos || !$reportajes) {
    die('Error al consultar el contenido: ' . e(mysqli_error($conexion)));
}
?>
<!--
Author: W3layouts
Author URL: http://w3layouts.com
-->
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>DDP Noticias - Diálogo y Desarrollo Perú</title>

    <!-- Google fonts -->
    
	<link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&amp;subset=latin-ext,vietnamese" rel="stylesheet">
    
    <!-- Template CSS -->
    <link rel="stylesheet" href="assets/css/style-starter.css?v=20260910">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  </head>
  <body>
<!-- header -->
<header id="site-header" class="fixed-top">
  <div class="container">
      <nav class="navbar navbar-expand-lg stroke">
          <!--<a class="navbar-brand" href="index.html">
              <span class="fa fa-video-camera"></span> V-Conference
          </a>
           if logo is image enable this   -->
      <a class="navbar-brand" href="#index.html">
          <img src="assets/images/logo.png" alt="Your logo" title="Your logo" style="height:75px;" />
      </a> 
          <button class="navbar-toggler  collapsed bg-gradient" type="button" data-toggle="collapse"
              data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false"
              aria-label="Toggle navigation">
              <span class="navbar-toggler-icon fa icon-expand fa-bars"></span>
              <span class="navbar-toggler-icon fa icon-close fa-times"></span>
              </span>
          </button>

          <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
              <ul class="navbar-nav ml-auto">
                  <li class="nav-item active">
                      <a class="nav-link" href="index.php">Inicio <span class="sr-only">(current)</span></a>
                  </li>
                  <li class="nav-item @@about__active">
                      <a class="nav-link" href="#actualidad">Actualidad</a>
                  </li>
				  <li class="nav-item @@about__active">
                      <a class="nav-link" href="reportajes.php">Reportajes</a>
                  </li>
				  <li class="nav-item @@about__active">
                      <a class="nav-link" href="index.php#podcast">Podcast</a>
                  </li>
				  <li class="nav-item @@about__active">
                      <a class="nav-link" href="boletines.php">Boletín NTEP</a>
                  </li>
				  <li class="nav-item @@about__active">
                      <a class="nav-link" href="about.html">Alianzas</a>
                  </li>
                  <li class="nav-item @@contact__active">
                      <a class="nav-link" href="contact.html">Sobre D&D</a>
                  </li>				  
                  <li class="ml-2">
                      <a href="#btn" class="btn btn-style btn-outline-secondary">Contacto</a>
                  </li>
              </ul>
          </div>
          <!-- toggle switch for light and dark theme --
          <div class="mobile-position">
              <nav class="navigation">
                  <div class="theme-switch-wrapper">
                      <label class="theme-switch" for="checkbox">
                          <input type="checkbox" id="checkbox">
                          <div class="mode-container">
                              <i class="gg-sun"></i>
                              <i class="gg-moon"></i>
                          </div>
                      </label>
                  </div>
              </nav>
          </div>
          <!-- //toggle switch for light and dark theme -->
      </nav>
  </div>
</header>
<!-- //header -->
<section class="breadcrumb-area py-sm-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="breadcrumb-contents">
                    <h2 class="title-big">Reportajes</h2>
                    <!--<div class="breadcrumb">
                        <ul>
                            <li>
                                <a href="index.html">Home</a>
                            </li>
                            <li class="active">
                                 Blog posts
                            </li>
                        </ul>
                    </div>-->
                </div>
            </div><!-- end .col-md-12 -->
        </div><!-- end .row -->
    </div><!-- end .container -->
</section>
<section class="w3l-video w3l-homeblock3 reportajes-principal" id="video">
    <!-- /video-6-->
    <?php if (mysqli_num_rows($reportajes) > 0): ?>
        <?php
            $principalReportaje = mysqli_fetch_assoc($reportajes);
            $principalImage = $principalReportaje['foto_principal'] ?: 'assets/images/video.jpg';
            $principalSummary = $principalReportaje['resumen_corto'] ?: mb_substr(strip_tags($principalReportaje['desarrollo']), 0, 220) . '...';
        ?>
        <div class="container reportajes-principal-inner">
            <div class="video-grids-info row">
                <div class="video-gd-right col-lg-6 p-0">
                    <a href="reportaje.php?id=<?php echo (int) $principalReportaje['id']; ?>"><img src="<?php echo e($principalImage); ?>" onerror="this.onerror=null;this.src='assets/images/video.jpg';" alt="<?php echo e($principalReportaje['titulo']); ?>" class="img-fluid"></a>
                </div>
                <div class="video-gd-left col-lg-6 p-lg-5 p-4 align-self">
                    <div class="p-xl-4 p-0 video-wrap">
                        <h5><?php echo e(format_date($principalReportaje['fecha_publicacion'])); ?></h5>
                        <?php if ($principalReportaje['es_destacado']): ?><span class="badge badge-danger mb-2">Destacado</span><?php endif; ?>
                        <h3 class="title-big text-left mb-4"><a href="reportaje.php?id=<?php echo (int) $principalReportaje['id']; ?>"><?php echo e($principalReportaje['titulo']); ?></a></h3>
                        <p><?php echo e($principalSummary); ?></p>
                        <a href="reportaje.php?id=<?php echo (int) $principalReportaje['id']; ?>" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span></a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

<div class="grids-block-5 py-1">
    <!-- grids block 5 -->
    <section class="py-lg-4 py-md-3">
        <div class="container">
            <div class="row">
                <?php while ($reportaje = mysqli_fetch_assoc($reportajes)): ?>
                    <?php $image = $reportaje['foto_principal'] ?: 'assets/images/video.jpg'; ?>
                    <div class="col-lg-4 col-md-6 grids5-info mt-5">
                        <a href="reportaje.php?id=<?php echo (int) $reportaje['id']; ?>" class="d-block"><img src="<?php echo e($image); ?>" alt="<?php echo e($reportaje['titulo']); ?>" class="img-fluid" /></a>
                        <div class="blog-info">
                            <h5><?php echo e(format_date($reportaje['fecha_publicacion'])); ?></h5>
                            <h4><a href="reportaje.php?id=<?php echo (int) $reportaje['id']; ?>" class="d-block"><?php echo e($reportaje['titulo']); ?></a></h4>
                            <a href="reportaje.php?id=<?php echo (int) $reportaje['id']; ?>" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="pagination">
                <ul>
                    <li><a href="reportajes-1.html">Ver todos</a></li>
                </ul>
            </div>
        </div>
</div>
<!--<section class="w3l-homeblock5 py-0">
    <div class="container py-lg-5 py-4">
        <div class="row">
            <div class="col-lg-8 align-self">
                <h5 class="title-small mb-2">DDP Noticias</h5>
                    <h3 class="title-banner">La minería ilegal no genera desarrollo para los territorios donde opera</h3>
                    <p class="mt-4">Informe del instituto VIDENZA analiza y compara el Índice de Desarrollo Humano en distritos del país, con presencia de minería informal e ilegal y las localidades sin presencia de actividad minera y los que tienen presencia de minería formal...</p>
                        <a href="mapa.html" class="btn btn-style btn-primary mt-md-5 mt-4">Ver Mapa</a>
            </div>
            <div class="col-lg-4 mt-lg-0 mt-4">
                <img src="assets/images/mapa-interactivo.png" class="img-fluid radius-image" alt="">
            </div>
        </div>
    </div>
</section>-->
<section class="breadcrumb-area py-sm-5 py-1">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="breadcrumb-contents">
                    <h2 class="title-big">Noticias Recientes</h2><a class="anchor" id="actualidad"></a>
                    <!--<div class="breadcrumb">
                        <ul>
                            <li>
                                <a href="index.html">Home</a>
                            </li>
                            <li class="active">
                                 Blog posts
                            </li>
                        </ul>
                    </div>-->
                </div>
            </div><!-- end .col-md-12 -->
        </div><!-- end .row -->
    </div><!-- end .container -->
</section>
<div class="grids-block-5 py-5">
    <!-- grids block 5 -->
    <section class="py-lg-4 py-md-3">
        <div class="container">
            <div class="row">
                <?php if (mysqli_num_rows($noticias) > 0): ?>
                    <?php while ($noticia = mysqli_fetch_assoc($noticias)): ?>
                        <div class="col-lg-4 col-md-6 grids5-info mt-5">
                            <?php if ($noticia['link_externo']): ?><a target="_blank" rel="noopener noreferrer" href="<?php echo e($noticia['link_externo']); ?>" class="d-block"><?php endif; ?>
                            <img src="<?php echo e($noticia['foto'] ?: 'assets/images/video.jpg'); ?>" alt="<?php echo e($noticia['titulo']); ?>" class="img-fluid" />
                            <?php if ($noticia['link_externo']): ?></a><?php endif; ?>
                            <div class="blog-info">
                                <h5><?php echo e(format_date($noticia['fecha_publicacion'])); ?></h5>
                                <h4><?php if ($noticia['link_externo']): ?><a target="_blank" rel="noopener noreferrer" href="<?php echo e($noticia['link_externo']); ?>" class="d-block"><?php endif; ?><?php echo e($noticia['titulo']); ?><?php if ($noticia['link_externo']): ?></a><?php endif; ?></h4>
                                <?php if ($noticia['link_externo']): ?><a target="_blank" rel="noopener noreferrer" href="<?php echo e($noticia['link_externo']); ?>" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span></a><?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                <div class="col-lg-4 col-md-6 grids5-info">
                    <a target="_blank" href="https://minart.pe/2025/11/07/gold-fields-y-empresas-locales-apuestan-por-el-talento-hualgayoquino-capacitando-a-pobladores-en-manejo-de-camiones-mineros-en-hualgayoc/?fbclid=IwY2xjawON3L1leHRuA2FlbQIxMABicmlkETFjbkNQNVZ0NVN4WVhmekpIc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHpzyjGhuFRl3v4LIaB0ks6cftrL-zGT73DnNcsALEsgAQZRUufUc4iTcrLEc_aem_hYAx1MhXJflxvlIajOunzg" class="d-block"><img src="assets/images/nota-facebook-21-11-25.png" alt=""class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Noviembre 21</h5>
                        <!--<ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>-->
                        <h4><a target="_blank" href="https://minart.pe/2025/11/07/gold-fields-y-empresas-locales-apuestan-por-el-talento-hualgayoquino-capacitando-a-pobladores-en-manejo-de-camiones-mineros-en-hualgayoc/?fbclid=IwY2xjawON3L1leHRuA2FlbQIxMABicmlkETFjbkNQNVZ0NVN4WVhmekpIc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHpzyjGhuFRl3v4LIaB0ks6cftrL-zGT73DnNcsALEsgAQZRUufUc4iTcrLEc_aem_hYAx1MhXJflxvlIajOunzg" class="d-block">Impulsan talento local en Hualgayoc</a></h4>
                        <a target="_blank" href="https://minart.pe/2025/11/07/gold-fields-y-empresas-locales-apuestan-por-el-talento-hualgayoquino-capacitando-a-pobladores-en-manejo-de-camiones-mineros-en-hualgayoc/?fbclid=IwY2xjawON3L1leHRuA2FlbQIxMABicmlkETFjbkNQNVZ0NVN4WVhmekpIc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHpzyjGhuFRl3v4LIaB0ks6cftrL-zGT73DnNcsALEsgAQZRUufUc4iTcrLEc_aem_hYAx1MhXJflxvlIajOunzg" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 grids5-info mt-lg-0 mt-5">
                    <a target="_blank" href="https://andina.pe/agencia/noticia-canete-inauguran-moderno-local-colegio-construido-inversion-s30-millones-1051936.aspx" class="d-block"><img src="assets/images/nota-facebook-21-11-25b.png" alt="" class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Noviembre 21</h5>
                        <!--<ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>-->
                        <h4><a target="_blank" href="https://andina.pe/agencia/noticia-canete-inauguran-moderno-local-colegio-construido-inversion-s30-millones-1051936.aspx" class="d-block">Inauguran moderno colegio en Cerro Azul</a></h4>
                        <a target="_blank" href="https://andina.pe/agencia/noticia-canete-inauguran-moderno-local-colegio-construido-inversion-s30-millones-1051936.aspx" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
				<div class="col-lg-4 col-md-6 grids5-info mt-md-0 mt-5">
                    <a target="_blank" href="https://diarioelnoticiero.com/ministerio-de-vivienda-llego-a-juliaca-para-reafirmar-que-el-proyecto-de-agua-potable-y-alcantarillado-no-se-detiene-2/?fbclid=IwY2xjawON3QpleHRuA2FlbQIxMABicmlkETFjbkNQNVZ0NVN4WVhmekpIc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHneyK2etohVG6KyvDXFJM_GtKA_gWI85gaZ5yLBuK66R0SW80sBUUdbwRCjt_aem_ByzW0vi0q7VetPB26LXGBg" class="d-block"><img src="assets/images/nota-facebook-20-11-25.png" alt="" class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Noviembre 20</h5>
                        <!--<ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>-->
                        <h4><a target="_blank" href="https://diarioelnoticiero.com/ministerio-de-vivienda-llego-a-juliaca-para-reafirmar-que-el-proyecto-de-agua-potable-y-alcantarillado-no-se-detiene-2/?fbclid=IwY2xjawON3QpleHRuA2FlbQIxMABicmlkETFjbkNQNVZ0NVN4WVhmekpIc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHneyK2etohVG6KyvDXFJM_GtKA_gWI85gaZ5yLBuK66R0SW80sBUUdbwRCjt_aem_ByzW0vi0q7VetPB26LXGBg" class="d-block">Megaproyecto de saneamiento en Juliaca</a></h4>
                        <a target="_blank" href="https://diarioelnoticiero.com/ministerio-de-vivienda-llego-a-juliaca-para-reafirmar-que-el-proyecto-de-agua-potable-y-alcantarillado-no-se-detiene-2/?fbclid=IwY2xjawON3QpleHRuA2FlbQIxMABicmlkETFjbkNQNVZ0NVN4WVhmekpIc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHneyK2etohVG6KyvDXFJM_GtKA_gWI85gaZ5yLBuK66R0SW80sBUUdbwRCjt_aem_ByzW0vi0q7VetPB26LXGBg" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <!--<div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a href="blog-single.html" class="d-block"><img src="assets/images/blog8.jpg" alt=""
                            class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Posted on May 20, 2022</h5>
                        <ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>
                        <h4><a href="blog-single.html" class="d-block">Experience the breathtaking views and perspectives</a>
                        </h4>
                        <a href="blog-single.html" class="btn mt-4 p-0">Read More <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a href="blog-single.html" class="d-block"><img src="assets/images/blog9.jpg" alt=""
                            class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Posted on May 20, 2022</h5>
                        <ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>
                        <h4><a href="blog-single.html" class="d-block">The absolute best foods for getting that youthful glow</a>
                        </h4>
                        <a href="blog-single.html" class="btn mt-4 p-0">Read More <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a href="blog-single.html" class="d-block"><img src="assets/images/blog.jpg" alt=""
                            class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Posted on May 20, 2022</h5>
                        <ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>
                        <h4><a href="blog-single.html" class="d-block">Our quiet not heart along scale sense timed practice</a>
                        </h4>
                        <a href="blog-single.html" class="btn mt-4 p-0">Read More <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a href="blog-single.html" class="d-block"><img src="assets/images/blog1.jpg" alt=""
                            class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Posted on May 20, 2022</h5>
                        <ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>
                        <h4><a href="blog-single.html" class="d-block">Increasing your advantage by aligning strategy.</a>
                        </h4>
                        <a href="blog-single.html" class="btn mt-4 p-0">Read More <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a href="blog-single.html" class="d-block"><img src="assets/images/blog2.jpg" alt=""
                            class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Posted on May 20, 2022</h5>
                        <ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>
                        <h4><a href="blog-single.html" class="d-block">Business performance, Design incubator </a>
                        </h4>
                        <a href="blog-single.html" class="btn mt-4 p-0">Read More <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a href="blog-single.html" class="d-block"><img src="assets/images/blog4.jpg" alt=""
                            class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Posted on May 20, 2022</h5>
                        <ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>
                        <h4><a href="blog-single.html" class="d-block">Preparing for a new global economy</a>
                        </h4>
                        <a href="blog-single.html" class="btn mt-4 p-0">Read More <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>-->
                <?php endif; ?>
            </div>
            <div class="pagination">
                <ul>
                    <li><a href="noticias.php">Ver todos</a></li>
                </ul>
            </div>
        </div>
</div>
<!-- // grids block 5 --

<!-- middle grid -->
<section class="w3l-homeblock5 py-0">
    <div class="container py-lg-5 py-4">
        <?php if (mysqli_num_rows($boletines) > 0): $boletin = mysqli_fetch_assoc($boletines); ?>
        <div class="row">
            <div class="col-lg-8 align-self">
                <h3 class="title-big mb-4">Boletín NTEP Año <?php echo e(date('Y', strtotime($boletin['fecha_publicacion']))); ?></h3>
                <?php if (!empty($boletin['resumen'])): ?><p class="mb-4"><?php echo nl2br(e($boletin['resumen'])); ?></p><?php endif; ?>
                <div class="row mt-sm-4 mt-2 px-3">
                    <div class="col-6 p-0"><span>N° <?php echo e($boletin['numero_boletin']); ?></span><h4><?php echo e(format_day_month($boletin['fecha_publicacion'])); ?></h4></div>
                    <div class="col-6 p-0"><span><a target="_blank" rel="noopener noreferrer" href="<?php echo e($boletin['archivo_pdf']); ?>" class="facebook"><span class="fa fa-download"></span></a></span><h4>Ver boletín</h4></div>
                    <center><a href="boletines.php" class="btn btn-style btn-primary mt-md-5 mt-4">Ver todos</a></center>
                </div>
            </div>
            <div class="col-lg-4 mt-lg-0 mt-4 px-lg-5"><img src="<?php echo e($boletin['foto_portada'] ?: 'assets/images/boletin-ntep-45.png'); ?>" onerror="this.onerror=null;this.src='assets/images/boletin-ntep-45.png';" class="img-fluid radius-image d-block mx-auto" style="width:100%; max-width:370px;" alt="Boletín <?php echo e($boletin['numero_boletin']); ?>"></div>
        </div>
        <?php else: ?>
        <div class="row">
            <div class="col-lg-8 align-self">
                <h3 class="title-big mb-4">Boletín NTEP Año 2025</h3>
                <p class="">-Promueven megaproyectos turísticos por S/ 2,400 mllns.</p>
				<p class="">-Invertirán S/ 9 millones en zonas rurales de Cusco.</p>
				<p class="">-Producción láctea se duplica en Cajamarca.</p>
                <div class="row mt-sm-4 mt-2 px-3">
                    <div class="col-6 p-0">
                        <span>N° 45</span>
                        <h4>28 agosto</h4>
                    </div>
                    <div class="col-6 p-0">
                        <span><a target="_blank" href="boletines/boletin-NTEP-edicion-N45-2808.pdf" class="facebook"><span class="fa fa-download"></span></a></span>
                        <h4>Ver Boletin</h4>
                    </div>
                    <center><a href="boletines.php" class="btn btn-style btn-primary mt-md-5 mt-4">Ver todos</a></center>
                </div>
            </div>
            <div class="col-lg-4 mt-lg-0 mt-4 px-lg-5">
                <img src="assets/images/boletin-ntep-45.png" class="img-fluid radius-image d-block mx-auto" style="width:100%; max-width:370px;" alt="">
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<!-- //middle grid -->
<section class="w3l-homeblock3 py-5" id="podcast">
    <div class="container py-lg-5 py-md-4">
        <!--<h5 class="title-small mb-1 text-center">12 speakers and 20 fun events.</h5>-->
        <h3 class="title-big mb-5 text-center">Podcast</h3>
        <div class="row">
            <?php if (mysqli_num_rows($podcasts) === 0): ?>
                <div class="col-12"><p class="text-center">Todavía no hay podcasts publicados.</p></div>
            <?php endif; ?>
            <?php while ($podcast = mysqli_fetch_assoc($podcasts)): ?>
                <div class="col-lg-3 col-sm-6 mt-lg-0 mt-5">
                    <div class="area-box">
                        <a target="_blank" rel="noopener noreferrer" href="<?php echo e($podcast['url_embed']); ?>" aria-label="Reproducir podcast: <?php echo e($podcast['titulo']); ?>">
                            <img src="assets/images/podcast.png" alt="Podcast" class="img-fluid">
                        </a>
                        <p><?php echo e($podcast['titulo']); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>


<!-- logos Section --
<section class="w3l-logos w3l-homeblock3 py-5">
    <div class="container py-lg-3">
        <h5 class="title-small mb-1 text-center">DyD Perú</h5>
        <h3 class="title-big mb-md-5 mb-4 text-center">Alianzas</h3>
        <div class="row">
            <div class="col-lg-12 mx-auto">
                <div class="owl-logos owl-carousel owl-theme logo-view">
                    <div class="item">
                        <img src="assets/images/logo1.png" alt="company-logo radius-image" class="img-fluid">
                    </div>
                    <div class="item">
                        <img src="assets/images/logo2.png" alt="company-logo radius-image" class="img-fluid">
                    </div>
                    <div class="item">
                        <img src="assets/images/logo3.png" alt="company-logo radius-image" class="img-fluid">
                    </div>
                    <div class="item">
                        <img src="assets/images/logo4.png" alt="company-logo radius-image" class="img-fluid">

                    </div>
                    <div class="item">
                        <img src="assets/images/logo5.png" alt="company-logo radius-image" class="img-fluid">

                    </div>
                    <div class="item">
                        <img src="assets/images/logo6.png" alt="company-logo radius-image" class="img-fluid">

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- //logos Section -->

<section class="w3l-team" id="team">
	<div class="teams1 py-5 mb-3">
		<div class="container py-lg-3 pb-lg-5 pb-4">
			<div class="teams1-content">
                <!--<h5 class="title-small text-center">Amazing speakers</h5>-->
                <h3 class="title-big text-center mb-5">Especiales</h3>
                    <?php if (mysqli_num_rows($videos) > 0): ?>
                    <div class="especiales-slider-wrap">
                        <button type="button" class="especiales-slider-control especiales-slider-prev" aria-label="Videos anteriores"><span class="fa fa-chevron-left"></span></button>
                        <div class="owl-carousel owl-theme text-center especiales-base-datos">
                        <?php while ($video = mysqli_fetch_assoc($videos)): ?>
                            <div class="item">
                                <a class="video-card" target="_blank" rel="noopener noreferrer" href="<?php echo e($video['url_embed']); ?>" aria-label="Ver en YouTube: <?php echo e($video['titulo']); ?>">
                                    <span class="video-card__box"><span class="video-card__box-text"><?php echo video_title_with_highlights($video['titulo']); ?></span></span>
                                    <span class="video-card__title"><?php echo video_title_plain($video['titulo']); ?></span>
                                </a>
                            </div>
                        <?php endwhile; ?>
                        </div>
                        <button type="button" class="especiales-slider-control especiales-slider-next" aria-label="Más videos"><span class="fa fa-chevron-right"></span></button>
                    </div>
                    <?php else: ?>
                        <p class="text-center">Todavía no hay videos publicados.</p>
                    <?php endif; ?>
                    <?php if (false): ?>
                    <div class="row especiales-originales">
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="team-info">
                                <div class="column position-relative">
                                    <a href="#url"><img src="assets/images/team2.jpg" alt="" class="img-fluid rounded team-image" /></a>
								</div>
								<div class="column">
									<!--<h3 class="name-pos"><a href="#url">Anthony</a></h3>-->
									<p>Por una mineria artesanal segura para todos</p>
									<!--<div class="social">
										<a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
										<a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
										<a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
									</div>-->
								</div>
							</div>
						</div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="team-info">
								<div class="column position-relative">
                                    <a href="#url"><img src="assets/images/team3.jpg" onerror="this.onerror=null;this.src='assets/images/team2.jpg';" alt="" class="img-fluid rounded team-image" /></a>
								</div>
								<div class="column">
									<!--<h3 class="name-pos"><a href="#url">Sara grant</a></h3>-->
									<p>REINFO Días decisivos en el Congreso</p>
									<!--<div class="social">
										<a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
										<a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
										<a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
									</div>-->
								</div>
							</div>
						</div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="team-info">
								<div class="column position-relative">
                                    <a href="#url"><img src="assets/images/team4.jpg" onerror="this.onerror=null;this.src='assets/images/team2.jpg';" alt="" class="img-fluid rounded team-image" /></a>
								</div>
								<div class="column">
									<!--<h3 class="name-pos"><a href="#url">Claire Olson</a></h3>-->
									<p>La minería ilegal: un negocio rentable para bandas criminales</p>
									<!--<div class="social">
										<a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
										<a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
										<a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
									</div>-->
								</div>
							</div>
						</div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="team-info">
								<div class="column position-relative">
                                    <a href="#url"><img src="assets/images/team5.jpg" onerror="this.onerror=null;this.src='assets/images/team2.jpg';" alt="" class="img-fluid rounded team-image" /></a>
								</div>
								<div class="column">
									<!--<h3 class="name-pos"><a href="#url">Paula cross</a></h3>-->
									<p>El problema del REINFO y la minería ilegal en 50 segundos</p>
									<!--<div class="social">
										<a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
										<a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
										<a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
									</div>-->
								</div>
							</div>
						</div>
						<!--<div class="item">
							<div class="d-grid team-info">
								<div class="column position-relative">
									<a href="#url"><img src="assets/images/team6.jpg" alt="" class="img-fluid rounded team-image" /></a>
								</div>
								<div class="column">
									<h3 class="name-pos"><a href="#url">Amber kinsa</a></h3>
									<p>CEO of company</p>
									<div class="social">
										<a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
										<a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
										<a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
									</div>
								</div>
							</div>
						</div>
						<div class="item">
							<div class="d-grid team-info">
								<div class="column position-relative">
									<a href="#url"><img src="assets/images/team7.jpg" alt="" class="img-fluid rounded team-image" /></a>
								</div>
								<div class="column">
									<h3 class="name-pos"><a href="#url">Edward wood</a></h3>
									<p>Manager & Chief</p>
									<div class="social">
										<a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
										<a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
										<a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
									</div>
								</div>
							</div>
						</div>
						<div class="item">
							<div class="d-grid team-info">
								<div class="column position-relative">
									<a href="#url"><img src="assets/images/team8.jpg" alt="" class="img-fluid rounded team-image" /></a>
								</div>
								<div class="column">
									<h3 class="name-pos"><a href="#url">Jonarthan parks</a></h3>
									<p>Manager and Officer</p>
									<div class="social">
										<a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
										<a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
										<a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
									</div>
								</div>
							</div>
						</div>
						<div class="item">
							<div class="d-grid team-info">
								<div class="column position-relative">
									<a href="#url"><img src="assets/images/s1.jpg" alt="" class="img-fluid rounded team-image" /></a>
								</div>
								<div class="column">
									<h3 class="name-pos"><a href="#url">Leroy bell</a></h3>
									<p>CEO of company</p>
									<div class="social">
										<a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
										<a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
										<a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
									</div>
								</div>
							</div>
						</div>
						<div class="item">
							<div class="d-grid team-info">
								<div class="column position-relative">
									<a href="#url"><img src="assets/images/team1.jpg" alt="" class="img-fluid rounded team-image" /></a>
								</div>
								<div class="column">
									<h3 class="name-pos"><a href="#url">Bradley</a></h3>
									<p>Founder of Company</p>
									<div class="social">
										<a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
										<a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
										<a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
									</div>
								</div>
							</div>
						</div>-->
                    </div>
                    <?php endif; ?>
			</div>
		</div>
	</div>
</section>
<section class="w3l-banner py-0" id="work">
    <div class="midd-w3 py-lg-4 py-md-3">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mt-lg-0 mt-lg-5 about-right-faq align-self">
                    <h5 class="title-small mb-2">DDP Noticias</h5>
                    <h3 class="title-banner">Diálogo y Desarrollo Perú</h3>
                    <p class="mt-4">Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.</p>
                        <a href="#btn" class="btn btn-style btn-primary mt-md-5 mt-4">Nosotros</a>
                 </div>
                <div class="col-md-6 left-wthree-img mt-lg-0 mt-4">
                    <div class="position-relative">
                        <img src="assets/images/bannerimg.jpg" alt="" class="img-fluid">
                        <!--<a href="#small-dialog" class="popup-with-zoom-anim play-view text-center position-absolute">
                            <span class="video-play-icon">
                                <span class="fa fa-play"></span>
                            </span>
                        </a>
                         dialog itself, mfp-hide class is required to make dialog hidden -->
                        <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
                            <iframe src="https://www.youtube.com/embed/2jI6fHBtRJU" allow="autoplay; fullscreen" allowfullscreen=""></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- middle grid --
<section class="w3l-homeblock5 py-5">
    <div class="container py-lg-5 py-4">
        <div class="row">
            <div class="col-lg-6 align-self">
                <h3 class="title-big mb-4"> Don’t miss out on the fun and join the community! </h3>
                <p class="">Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates maiores ipsum quos
                    voluptate, cumque perspiciatis dolorem tempora fugit facere ducimus?.</p>
                <div class="row mt-sm-4 mt-2 px-3">
                    <div class="col-6 p-0">
                        <span>80+</span>
                        <h4>Speakers</h4>
                    </div>
                    <div class="col-6 p-0">
                        <span>50+</span>
                        <h4>Workshops</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mt-lg-0 mt-4">
                <img src="assets/images/stats.jpg" class="img-fluid radius-image" alt="">
            </div>
        </div>
    </div>
</section>
<!-- //middle grid -->
<!-- middle -->
<div class="middle py-5">
    <div class="container py-xl-5 py-lg-3">
        <div class="welcome-left text-center py-md-5 py-3">
            <h3 class="title-big">Síguenos en nuestras Redes Sociales</h3>
            <div class="main-social-footer-29">
            <a target="_blank" href="https://www.facebook.com/DialogoyDesarrolloPeru" class="facebook"><span class="fa fa-facebook-square fa-2x"></span></a>
            <a target="_blank" href="https://www.tiktok.com/@dialogo.y.desarrollo" class="twitter"><img src="assets/images/tiktokg.png"></a>
            <a target="_blank" href="https://www.instagram.com/dialogo.y.desarrollo/" class="instagram"><span class="fa fa-instagram fa-2x"></span></a>
            <!--<a href="#youtube" class="youtube"><span class="fa fa-youtube fa-2x"></span></a>
            <a href="#linkedin" class="linkedin"><span class="fa fa-linkedin fa-2x"></span></a>-->
          </div>
        </div>
    </div>
</div>
<!-- //middle -->


<!-- footer block -->
<section class="w3l-footer-29-main py-5" id="footer">
  <div class="footer-29 py-md-3">
    <div class="container">
      <div class="row footer-top-29">
        <div class="col-lg-6 col-md-6 footer-list-29 footer-1">
          <h6 class="footer-title-29">Quiénes Somos</h6>
          <p>Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.</p>
          <div class="main-social-footer-29">
            <a target="_blank" href="https://www.facebook.com/DialogoyDesarrolloPeru" class="facebook"><span class="fa fa-facebook-square"></span></a>
            <a target="_blank" href="https://www.tiktok.com/@dialogo.y.desarrollo" class="twitter"><img src="assets/images/tiktokp.png"></a>
            <a target="_blank" href="https://www.instagram.com/dialogo.y.desarrollo/" class="instagram"><span class="fa fa-instagram"></span></a>
            <!--<a href="#youtube" class="youtube"><span class="fa fa-youtube"></span></a>
            <a href="#linkedin" class="linkedin"><span class="fa fa-linkedin"></span></a>-->
          </div>
        </div>
        <div class="col-lg-3 col-md-6 footer-list-29 footer-2 mt-md-0 mt-5">
          <ul>
            <h6 class="footer-title-29">Contenido</h6>
            <li><a href="#url">Noticias</a></li>
            <li><a href="#url">Videos</a></li>
            <li><a href="#url">Posdcast.</a></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-6 mt-lg-0 mt-5 footer-list-29 footer-3">
          <div class="properties">
            <h6 class="footer-title-29">Contacto</h6>
            <ul>
            <!--<!--<li><a href="#url">Celulares</a></li>-->
            <!--<li><a href="#url">Celulares</a></li>-->
            <li><a href="#url">info@dialogoydesarrollo.com.pe</a></li>
          </ul>
          </div>
        </div>
      </div>
      <div class="bottom-copies text-center">
			<p class="copy-footer-29">© 2026 Diálogo y Desarrollo Perú. All rights reserved | Designed by <a target="_blank" href="https://www.wsperu.info">WebSolutions</a></p>
		</div>
    </div>
  </div>
  <!-- move top -->
  <button onclick="topFunction()" id="movetop" title="Go to top">
    <span class="fa fa-angle-up"></span>
  </button>
  <script>
    // When the user scrolls down 20px from the top of the document, show the button
    window.onscroll = function () {
      scrollFunction()
    };

    function scrollFunction() {
      if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        document.getElementById("movetop").style.display = "block";
      } else {
        document.getElementById("movetop").style.display = "none";
      }
    }

    // When the user clicks on the button, scroll to the top of the document
    function topFunction() {
      document.body.scrollTop = 0;
      document.documentElement.scrollTop = 0;
    }
  </script>
  <!-- /move top -->
</section>
<!-- //footer block -->

<!-- Template JavaScript -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

<script src="assets/js/theme-change.js"></script><!-- theme switch js (light and dark)-->

<!-- responsive tabs -->
<script src="assets/js/easyResponsiveTabs.js"></script>
<!--Plug-in Initialisation-->
<script type="text/javascript">
  $(document).ready(function () {
    //Horizontal Tab
    $('#parentHorizontalTab').easyResponsiveTabs({
      type: 'default', //Types: default, vertical, accordion
      width: 'auto', //auto or any width like 600px
      fit: true, // 100% fit in a container
      tabidentify: 'hor_1', // The tab groups identifier
      activate: function (event) { // Callback function if tab is switched
        var $tab = $(this);
        var $info = $('#nested-tabInfo');
        var $name = $('span', $info);
        $name.text($tab.text());
        $info.show();
      }
    });
  });
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<!-- logos for customers -->
<script>
  $(document).ready(function () {
    $('.owl-logos').owlCarousel({
      loop: true,
      margin: 0,
      nav: false,
      responsiveClass: true,
      autoplay: true,
      autoplayTimeout: 5000,
      autoplaySpeed: 1000,
      autoplayHoverPause: false,
      responsive: {
        0: {
          items: 2,
          nav: false
        },
        480: {
          items: 2,
          nav: false
        },
        568: {
          items: 3,
          nav: false
        },
        1000: {
          items: 5,
          nav: false
        }
      }
    })
  })
</script>
<!-- //logos owlcarousel -->

<!-- for tesimonials carousel slider -->
<script>
  $(document).ready(function () {
    $("#owl-demo1").owlCarousel({
      loop: true,
      margin: 20,
      responsiveClass: true,
      responsive: {
        0: {
          items: 1,
          nav: true
        },
        768: {
          items: 2,
          nav: false
        },
        1000: {
          items: 3,
          nav: true,
          loop: false
        }
      }
    })
  })
</script>
<!-- //script -->

<!-- script for teams -->
<script>
  $(document).ready(function () {
        $('.owl-carousel:not(.especiales-base-datos)').owlCarousel({
      loop: true,
      margin: 0,
      responsiveClass: true,
      responsive: {
        0: {
          items: 1,
          nav: true
        },
        400: {
          items: 2,

    <script>
        $(document).ready(function () {
            $('.especiales-base-datos').each(function () {
                var $carousel = $(this);
                var videoCount = $carousel.children('.item').length;

                $carousel.owlCarousel({
                    loop: videoCount > 4,
                    nav: videoCount > 1,
                    dots: videoCount > 1,
                    margin: 25,
                    responsive: {
                        0: { items: 1 },
                        400: { items: Math.min(videoCount, 2) },
                        768: { items: Math.min(videoCount, 3) },
                        1000: { items: Math.min(videoCount, 4) }
                    }
                });
            });
        });
    </script>
          nav: true,
          margin: 20
        },
        768: {
          items: 3,
          nav: true,
          margin: 20
        },
        1000: {
          items: 4,
          nav: true,
          loop: true,
          margin: 25
        }
      }
    })
  })
</script>
<!-- //script for teams-->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.especiales-slider-wrap').forEach(function (slider) {
            var track = slider.querySelector('.especiales-base-datos');
            var previous = slider.querySelector('.especiales-slider-prev');
            var next = slider.querySelector('.especiales-slider-next');
            var step = function () { return track.clientWidth / (window.innerWidth < 576 ? 1 : window.innerWidth < 992 ? 2 : 4); };

            previous.addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: 'smooth' }); });
            next.addEventListener('click', function () { track.scrollBy({ left: step(), behavior: 'smooth' }); });
        });
    });
</script>

<!-- Script for counter -->
<script>
  (() => {
    // Specify the deadline date
    const deadlineDate = new Date('January 27, 2025 23:59:59').getTime();

    // Cache all countdown boxes into consts
    const countdownDays = document.querySelector('.countdown__days .number');
    const countdownHours = document.querySelector('.countdown__hours .number');
    const countdownMinutes = document.querySelector('.countdown__minutes .number');
    const countdownSeconds = document.querySelector('.countdown__seconds .number');

    // Update the count down every 1 second (1000 milliseconds)
    setInterval(() => {
      // Get current date and time
      const currentDate = new Date().getTime();

      // Calculate the distance between current date and time and the deadline date and time
      const distance = deadlineDate - currentDate;

      // Calculations the data for remaining days, hours, minutes and seconds
      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      // Insert the result data into individual countdown boxes
      countdownDays.innerHTML = days;
      countdownHours.innerHTML = hours;
      countdownMinutes.innerHTML = minutes;
      countdownSeconds.innerHTML = seconds;
    }, 1000);
  })();
</script>
<!-- //Script for counter -->

<script src="assets/js/jquery.magnific-popup.min.js"></script>
<script>
  $(document).ready(function () {
    $('.popup-with-zoom-anim').magnificPopup({
      type: 'inline',

      fixedContentPos: false,
      fixedBgPos: true,

      overflowY: 'auto',

      closeBtnInside: true,
      preloader: false,

      midClick: true,
      removalDelay: 300,
      mainClass: 'my-mfp-zoom-in'
    });

    $('.popup-with-move-anim').magnificPopup({
      type: 'inline',

      fixedContentPos: false,
      fixedBgPos: true,

      overflowY: 'auto',

      closeBtnInside: true,
      preloader: false,

      midClick: true,
      removalDelay: 300,
      mainClass: 'my-mfp-slide-bottom'
    });
  });
</script>

<!-- disable body scroll which navbar is in active -->
<script>
  $(function () {
    $('.navbar-toggler').click(function () {
      $('body').toggleClass('noscroll');
    })
  });
</script>
<!-- disable body scroll which navbar is in active -->

<!--/MENU-JS-->
<script>
  $(window).on("scroll", function () {
    var scroll = $(window).scrollTop();

    if (scroll >= 80) {
      $("#site-header").addClass("nav-fixed");
    } else {
      $("#site-header").removeClass("nav-fixed");
    }
  });

  //Main navigation Active Class Add Remove
  $(".navbar-toggler").on("click", function () {
    $("header").toggleClass("active");
  });
  $(document).on("ready", function () {
    if ($(window).width() > 991) {
      $("header").removeClass("active");
    }
    $(window).on("resize", function () {
      if ($(window).width() > 991) {
        $("header").removeClass("active");
      }
    });
  });
</script>
<!--//MENU-JS-->

<script src="assets/js/bootstrap.min.js"></script>

</body>

</html>
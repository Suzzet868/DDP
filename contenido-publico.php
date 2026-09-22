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

$config = [
  'noticias' => [
    'label' => 'Noticias',
    'query' => 'SELECT titulo, foto, link_externo, fecha_publicacion FROM noticias ORDER BY fecha_publicacion DESC, id DESC',
    'empty' => 'Todavía no hay noticias publicadas.',
  ],
  'podcast' => [
    'label' => 'Podcast',
    'query' => 'SELECT titulo, url_embed, fecha_publicacion FROM podcasts ORDER BY fecha_publicacion DESC, id DESC',
    'empty' => 'Todavía no hay podcasts publicados.',
  ],
  'boletines' => [
    'label' => 'Boletines NTEP',
    'query' => 'SELECT numero_boletin, foto_portada, archivo_pdf, fecha_publicacion FROM boletines ORDER BY fecha_publicacion DESC, id DESC',
    'empty' => 'Todavía no hay boletines publicados.',
  ],
  'videos' => [
    'label' => 'Videos',
    'query' => 'SELECT titulo, url_embed, fecha_publicacion FROM videos ORDER BY fecha_publicacion DESC, id DESC',
    'empty' => 'Todavía no hay videos publicados.',
  ],
];

$tipo = $_GET['tipo'] ?? '';
if (!isset($config[$tipo])) {
  http_response_code(404);
  exit('Módulo no válido.');
}

$section = $config[$tipo];
$items = mysqli_query($conexion, $section['query']);
if (!$items) {
  die('Error al consultar ' . e($section['label']) . ': ' . e(mysqli_error($conexion)));
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?php echo e($section['label']); ?> | DDP Noticias</title>
  <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&amp;subset=latin-ext,vietnamese" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style-starter.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
<header id="site-header" class="fixed-top"><div class="container"><nav class="navbar navbar-expand-lg stroke">
  <a class="navbar-brand" href="index.php"><img src="assets/images/logo.png" alt="DDP Noticias" style="height:75px;"></a>
  <button class="navbar-toggler collapsed bg-gradient" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo02" aria-label="Abrir menú"><span class="navbar-toggler-icon fa icon-expand fa-bars"></span><span class="navbar-toggler-icon fa icon-close fa-times"></span></button>
  <div class="collapse navbar-collapse" id="navbarTogglerDemo02"><ul class="navbar-nav ml-auto">
    <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
    <li class="nav-item"><a class="nav-link" href="index.php#actualidad">Actualidad</a></li>
    <li class="nav-item"><a class="nav-link" href="reportajes.php">Reportajes</a></li>
    <li class="nav-item"><a class="nav-link" href="index.php#podcast">Podcast</a></li>
    <li class="nav-item <?php echo $tipo === 'boletines' ? 'active' : ''; ?>"><a class="nav-link" href="boletines.php">Boletín NTEP</a></li>
    <li class="nav-item"><a class="nav-link" href="about.html">Alianzas</a></li>
    <li class="nav-item"><a class="nav-link" href="contact.html">Sobre D&amp;D</a></li>
    <li class="ml-2"><a href="#btn" class="btn btn-style btn-outline-secondary">Contacto</a></li>
  </ul></div>
</nav></div></header>
<section class="breadcrumb-area py-sm-5 py-4"><div class="container"><div class="breadcrumb-contents"><h2 class="title-big"><?php echo e($section['label']); ?></h2><div class="breadcrumb"><ul><li><a href="index.php">Inicio</a></li><li class="active"><?php echo e($section['label']); ?></li></ul></div></div></div></section>
<div class="grids-block-5 py-5"><section class="py-lg-4 py-md-3"><div class="container"><div class="row">
<?php if (mysqli_num_rows($items) === 0): ?><div class="col-12"><p class="text-center"><?php echo e($section['empty']); ?></p></div><?php endif; ?>
<?php while ($item = mysqli_fetch_assoc($items)): ?>
  <?php if ($tipo === 'noticias'): ?>
    <div class="col-lg-4 col-md-6 grids5-info mb-5"><img src="<?php echo e($item['foto'] ?: 'assets/images/video.jpg'); ?>" alt="<?php echo e($item['titulo']); ?>" class="img-fluid"><div class="blog-info"><h5><?php echo e(format_date($item['fecha_publicacion'])); ?></h5><h4><?php echo e($item['titulo']); ?></h4><?php if ($item['link_externo']): ?><a target="_blank" rel="noopener noreferrer" href="<?php echo e($item['link_externo']); ?>" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span></a><?php endif; ?></div></div>
  <?php elseif ($tipo === 'boletines'): ?>
    <div class="col-lg-4 col-md-6 grids5-info mb-5"><img src="<?php echo e($item['foto_portada'] ?: 'assets/images/video.jpg'); ?>" onerror="this.onerror=null;this.src='assets/images/video.jpg';" alt="Boletín" class="img-fluid"><div class="blog-info"><h5><?php echo e(format_date($item['fecha_publicacion'])); ?></h5><a target="_blank" rel="noopener noreferrer" href="<?php echo e($item['archivo_pdf']); ?>" class="btn mt-4 p-0">Ver boletín <span class="fa fa-download"></span></a></div></div>
  <?php else: ?>
    <div class="col-lg-6 col-md-6 grids5-info mb-5"><div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="<?php echo e(youtube_embed_url($item['url_embed'])); ?>" title="<?php echo e($item['titulo']); ?>" loading="lazy" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe></div><div class="blog-info"><h5><?php echo e(format_date($item['fecha_publicacion'])); ?></h5><h4><a target="_blank" rel="noopener noreferrer" href="<?php echo e($item['url_embed']); ?>"><?php echo e($item['titulo']); ?></a></h4></div></div>
  <?php endif; ?>
<?php endwhile; ?>
</div></div></section></div>
<section class="w3l-footer-29-main py-5"><div class="container"><h6 class="footer-title-29">Quiénes Somos</h6><p>Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.</p></div></section>
<script src="assets/js/jquery-3.3.1.min.js"></script><script src="assets/js/bootstrap.min.js"></script>
</body>
</html>

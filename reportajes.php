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

$reportajes = mysqli_query(
  $conexion,
  'SELECT id, titulo, resumen_corto, desarrollo, foto_principal, fecha_publicacion, es_destacado FROM reportajes ORDER BY es_destacado DESC, fecha_publicacion DESC, id DESC'
);

if (!$reportajes) {
  die('Error al consultar los reportajes: ' . e(mysqli_error($conexion)));
}
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Reportajes | DDP Noticias</title>
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&amp;subset=latin-ext,vietnamese" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-starter.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  </head>
  <body>
    <header id="site-header" class="fixed-top">
      <div class="container">
        <nav class="navbar navbar-expand-lg stroke">
          <a class="navbar-brand" href="index.php"><img src="assets/images/logo.png" alt="DDP Noticias" title="DDP Noticias" style="height:75px;" /></a>
          <button class="navbar-toggler collapsed bg-gradient" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon fa icon-expand fa-bars"></span>
            <span class="navbar-toggler-icon fa icon-close fa-times"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
            <ul class="navbar-nav ml-auto">
              <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
              <li class="nav-item"><a class="nav-link" href="index.php#actualidad">Actualidad</a></li>
              <li class="nav-item active"><a class="nav-link" href="reportajes.php">Reportajes</a></li>
              <li class="nav-item"><a class="nav-link" href="index.php#podcast">Podcast</a></li>
              <li class="nav-item"><a class="nav-link" href="boletines.php">Boletín NTEP</a></li>
              <li class="nav-item"><a class="nav-link" href="about.html">Alianzas</a></li>
              <li class="nav-item"><a class="nav-link" href="contact.html">Sobre D&amp;D</a></li>
              <li class="ml-2"><a href="#btn" class="btn btn-style btn-outline-secondary">Contacto</a></li>
            </ul>
          </div>
        </nav>
      </div>
    </header>

    <section class="breadcrumb-area py-sm-5 py-4">
      <div class="container"><div class="row"><div class="col-md-12"><div class="breadcrumb-contents">
        <h2 class="title-big">Reportajes</h2>
        <div class="breadcrumb"><ul><li><a href="index.php">Inicio</a></li><li class="active">Reportajes</li></ul></div>
      </div></div></div></div>
    </section>

    <div class="grids-block-5 py-5">
      <section class="py-lg-4 py-md-3"><div class="container"><div class="row">
        <?php if (mysqli_num_rows($reportajes) === 0): ?>
          <div class="col-12"><p class="text-center">Todavía no hay reportajes publicados.</p></div>
        <?php endif; ?>

        <?php $reportajeIndex = 0; ?>
        <?php while ($reportaje = mysqli_fetch_assoc($reportajes)): ?>
          <?php
            $image = $reportaje['foto_principal'] ?: 'assets/images/video.jpg';
            $reportajeUrl = 'reportaje.php?id=' . (int) $reportaje['id'];
            $cardSpacing = $reportajeIndex < 3 ? '' : ' mt-5';
            $reportajeIndex++;
          ?>
          <div class="col-lg-4 col-md-6 grids5-info<?php echo $cardSpacing; ?>">
            <a href="<?php echo e($reportajeUrl); ?>" class="d-block"><img src="<?php echo e($image); ?>" onerror="this.onerror=null;this.src='assets/images/video.jpg';" alt="<?php echo e($reportaje['titulo']); ?>" class="img-fluid" /></a>
            <div class="blog-info">
              <h5><?php echo e(format_date($reportaje['fecha_publicacion'])); ?></h5>
              <h4><a href="<?php echo e($reportajeUrl); ?>" class="d-block"><?php echo e($reportaje['titulo']); ?></a></h4>
              <a href="<?php echo e($reportajeUrl); ?>" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
            </div>
          </div>
        <?php endwhile; ?>
      </div></div></section>
    </div>

    <section class="w3l-footer-29-main py-5" id="footer"><div class="footer-29 py-md-3"><div class="container"><div class="row footer-top-29"><div class="col-lg-6 col-md-6 footer-list-29 footer-1"><h6 class="footer-title-29">Quiénes Somos</h6><p>Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.</p></div></div></div></div></section>
    <script src="assets/js/jquery-3.3.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
  </body>
</html>

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

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
  http_response_code(404);
  exit('Reportaje no encontrado.');
}

$statement = mysqli_prepare(
  $conexion,
  "SELECT r.titulo, r.resumen_corto, r.desarrollo, r.foto_principal, r.pdf_adjunto, r.fecha_publicacion, r.es_destacado, a.nombres AS autor_nombre, a.ap_paterno AS autor_apellido FROM reportajes r INNER JOIN autores a ON a.id = r.autor_id WHERE r.id = ? LIMIT 1"
);
mysqli_stmt_bind_param($statement, 'i', $id);
mysqli_stmt_execute($statement);
$result = mysqli_stmt_get_result($statement);
$reportaje = mysqli_fetch_assoc($result);
mysqli_stmt_close($statement);

if (!$reportaje) {
  http_response_code(404);
  exit('Reportaje no encontrado.');
}

$latest = mysqli_query(
  $conexion,
  'SELECT id, titulo, fecha_publicacion FROM reportajes WHERE id <> ' . (int) $id . ' ORDER BY fecha_publicacion DESC, id DESC LIMIT 3'
);
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo e($reportaje['titulo']); ?> | DDP Noticias</title>
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&amp;subset=latin-ext,vietnamese" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-starter.css">
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
              <li class="nav-item"><a class="nav-link" href="boletines.php">Boletines</a></li>
              <li class="nav-item"><a class="nav-link" href="about.html">Alianzas</a></li>
              <li class="nav-item"><a class="nav-link" href="contact.html">Sobre D&amp;D</a></li>
              <li class="ml-2"><a href="#btn" class="btn btn-style btn-outline-secondary">Contacto</a></li>
            </ul>
          </div>
        </nav>
      </div>
    </header>

    <section class="breadcrumb-area py-sm-5 py-4">
      <div class="container">
        <div class="row"><div class="col-md-12"><div class="breadcrumb-contents">
          <h2 class="title-big">Reportajes</h2>
          <div class="breadcrumb"><ul><li><a href="index.php">Inicio</a></li><li class="active"><a href="reportajes.php">Reportajes</a></li></ul></div>
        </div></div></div>
      </div>
    </section>

    <section class="w3l-blog mt-lg-5">
      <div class="text-element-9 py-5 mt-lg-5">
        <div class="container py-lg-3">
          <div class="row grid-text-9">
            <div class="col-lg-8">
              <div class="blog-single-post">
                <div class="post-content">
                  <h2 class="title-single mb-3"><?php echo e($reportaje['titulo']); ?></h2>
                </div>
                <div class="single-post-image mb-4 text-center">
                  <?php if ($reportaje['foto_principal']): ?>
                    <?php if ($reportaje['pdf_adjunto']): ?><a target="_blank" rel="noopener noreferrer" href="<?php echo e($reportaje['pdf_adjunto']); ?>"><?php endif; ?>
                    <img src="<?php echo e($reportaje['foto_principal']); ?>" class="img-fluid w-100 radius-image" alt="<?php echo e($reportaje['titulo']); ?>" />
                    <?php if ($reportaje['pdf_adjunto']): ?><br />Clic en la imagen para ver el documento completo</a><?php endif; ?>
                  <?php elseif ($reportaje['pdf_adjunto']): ?>
                    <a target="_blank" rel="noopener noreferrer" href="<?php echo e($reportaje['pdf_adjunto']); ?>">Ver documento completo</a>
                  <?php endif; ?>
                </div>
                <div class="single-post-content">
                  <?php if ($reportaje['resumen_corto']): ?><blockquote class="blockquote my-5"><q class="mb-3 d-block"><?php echo e($reportaje['resumen_corto']); ?></q></blockquote><?php endif; ?>
                  <?php if (strcasecmp(trim($reportaje['autor_nombre']), 'Redacción') !== 0): ?>
                    <p class="reportaje-copy mb-3">Por <?php echo e(trim($reportaje['autor_nombre'] . ' ' . $reportaje['autor_apellido'])); ?></p>
                  <?php endif; ?>
                  <p class="reportaje-copy reportaje-development mb-4"><?php echo nl2br(e($reportaje['desarrollo'])); ?></p>
                </div>
                <nav class="post-navigation row mb-5 py-4">
                  <div class="post-prev col-md-6 pr-sm-5"><span class="nav-title"><span class="fa fa-arrow-left mr-2"></span> <a href="reportajes.php">Reportajes</a></span></div>
                </nav>
              </div>
            </div>
            <div class="col-lg-4 left-text-9 mt-lg-0 mt-5 pl-lg-4">
              <div class="left-top-9 mt-5 pt-sm-3">
                <h6 class="heading-small-text-9 mb-3">Últimas noticias</h6>
                <?php while ($recent = mysqli_fetch_assoc($latest)): ?>
                  <a href="reportaje.php?id=<?php echo (int) $recent['id']; ?>" class="p-post d-block py-2">
                    <h6 class="text-left-inner-9"><?php echo e($recent['titulo']); ?></h6>
                    <span class="sub-inner-text-9"><?php echo e(format_date($recent['fecha_publicacion'])); ?></span>
                  </a>
                <?php endwhile; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <script src="assets/js/jquery-3.3.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
  </body>
</html>
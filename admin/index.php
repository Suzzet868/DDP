<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../conexion.php';

require_admin();

$moduleRoutes = [
  'noticias' => 'noticias.php',
  'reportajes' => 'reportajes.php',
  'podcasts' => 'podcasts.php',
  'boletines' => 'boletines.php',
  'videos' => 'videos.php',
];

$requestedModule = $_GET['tabla'] ?? '';
if (isset($moduleRoutes[$requestedModule])) {
  header('Location: ' . $moduleRoutes[$requestedModule]);
  exit;
}

function render_partial($partial) {
  include __DIR__ . '/src/partials/' . $partial;
}

$contentTables = [
  'Noticias' => 'noticias',
  'Reportajes' => 'reportajes',
  'Podcasts' => 'podcasts',
  'Boletines' => 'boletines',
  'Videos' => 'videos',
];

$counts = [];
foreach ($contentTables as $label => $table) {
  $result = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM `$table`");
  if (!$result) {
    die('Error al consultar la tabla ' . e($table) . ': ' . e(mysqli_error($conexion)));
  }
  $counts[$label] = (int) mysqli_fetch_assoc($result)['total'];
}

$recentReportajes = mysqli_query(
  $conexion,
  'SELECT id, titulo, fecha_publicacion FROM reportajes ORDER BY fecha_publicacion DESC, id DESC LIMIT 8'
);
if (!$recentReportajes) {
  die('Error al consultar los reportajes: ' . e(mysqli_error($conexion)));
}

ob_start();
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <link rel="stylesheet" href="build/style.css" />
    <title>Panel administrativo | DDP Noticias</title>
  </head>
  <body
    x-data="{ page: 'ecommerce', 'loaded': true, 'darkMode': false, 'stickyMenu': false, 'sidebarToggle': false, 'scrollTop': false }"
    x-init="
         darkMode = JSON.parse(localStorage.getItem('darkMode'));
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}"
  >
    <!-- ===== Preloader Start ===== -->
    <?php render_partial('preloader.html'); ?>
    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden">
      <!-- ===== Sidebar Start ===== -->
      <?php render_partial('sidebar.html'); ?>
      <!-- ===== Sidebar End ===== -->

      <!-- ===== Content Area Start ===== -->
      <div
        class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto"
      >
        <!-- Small Device Overlay Start -->
        <?php render_partial('overlay.html'); ?>
        <!-- Small Device Overlay End -->

        <!-- ===== Header Start ===== -->
        <?php render_partial('header.html'); ?>
        <!-- ===== Header End ===== -->

        <!-- ===== Main Content Start ===== -->
        <main>
          <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
            <div class="mb-6 flex flex-col gap-1">
              <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Resumen de contenidos</h1>
              <p class="text-sm text-gray-500 dark:text-gray-400">Datos actuales de tu base de datos DDP.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5 md:gap-6">
              <?php foreach ($counts as $label => $total): ?>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                  <span class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($label); ?></span>
                  <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90"><?php echo $total; ?></p>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
              <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Reportajes recientes</h2>
              </div>
              <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                  <thead>
                    <tr class="border-y border-gray-100 dark:border-gray-800">
                      <th class="py-3 text-left font-medium text-gray-500 text-theme-xs dark:text-gray-400">Título</th>
                      <th class="py-3 text-left font-medium text-gray-500 text-theme-xs dark:text-gray-400">Fecha</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <?php while ($reportaje = mysqli_fetch_assoc($recentReportajes)): ?>
                      <tr>
                        <td class="py-3 pr-4 text-sm text-gray-800 dark:text-white/90"><?php echo e($reportaje['titulo']); ?></td>
                        <td class="whitespace-nowrap py-3 text-sm text-gray-500 dark:text-gray-400"><?php echo e($reportaje['fecha_publicacion'] ?: 'Sin fecha'); ?></td>
                      </tr>
                    <?php endwhile; ?>
                    <?php if (mysqli_num_rows($recentReportajes) === 0): ?>
                      <tr>
                        <td colspan="2" class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">No hay reportajes registrados.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </main>
        <!-- ===== Main Content End ===== -->
      </div>
      <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->
    <script src="build/bundle.js"></script>
  </body>
</html>
<?php
$page = ob_get_clean();
$page = str_replace('./images/', 'src/images/', $page);
echo $page;
?>

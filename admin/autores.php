<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../conexion.php';
require_admin();

$message = '';
$error = '';
$editId = (int) ($_GET['editar'] ?? 0);
$editRow = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['eliminar_id'])) {
    $id = (int) $_POST['eliminar_id'];
    $used = mysqli_query($conexion, 'SELECT COUNT(*) AS total FROM reportajes WHERE autor_id = ' . $id);
    if ((int) mysqli_fetch_assoc($used)['total'] > 0) {
      $error = 'No puedes eliminar un autor que está asignado a un reportaje.';
    } else {
      $statement = mysqli_prepare($conexion, 'DELETE FROM autores WHERE id = ?');
      mysqli_stmt_bind_param($statement, 'i', $id);
      $message = mysqli_stmt_execute($statement) ? 'Autor eliminado correctamente.' : 'No se pudo eliminar el autor.';
      mysqli_stmt_close($statement);
    }
  } else {
    $nombre = trim($_POST['nombres'] ?? '');
    $apellido = trim($_POST['ap_paterno'] ?? '');
    $id = (int) ($_POST['actualizar_id'] ?? 0);

    if ($nombre === '') {
      $error = 'Completa el nombre del autor.';
    } else {
      $duplicateCheck = mysqli_prepare($conexion, "SELECT id FROM autores WHERE LOWER(TRIM(nombres)) = LOWER(TRIM(?)) AND COALESCE(LOWER(TRIM(ap_paterno)), '') = LOWER(TRIM(?)) AND id <> ? LIMIT 1");
      mysqli_stmt_bind_param($duplicateCheck, 'ssi', $nombre, $apellido, $id);
      mysqli_stmt_execute($duplicateCheck);
      $duplicateResult = mysqli_stmt_get_result($duplicateCheck);
      $duplicate = mysqli_fetch_assoc($duplicateResult);
      mysqli_stmt_close($duplicateCheck);

      if ($duplicate) {
        $error = 'Ese autor ya existe.';
      } else {
        if ($id > 0) {
          $statement = mysqli_prepare($conexion, 'UPDATE autores SET nombres = ?, ap_paterno = ? WHERE id = ?');
          mysqli_stmt_bind_param($statement, 'ssi', $nombre, $apellido, $id);
        } else {
          $statement = mysqli_prepare($conexion, 'INSERT INTO autores (nombres, ap_paterno, ap_materno, nickname, es_nickname) VALUES (?, ?, NULL, NULL, 0)');
          mysqli_stmt_bind_param($statement, 'ss', $nombre, $apellido);
        }
        if (mysqli_stmt_execute($statement)) {
          $message = $id > 0 ? 'Autor actualizado correctamente.' : 'Autor creado correctamente.';
          $editId = 0;
          $_POST = [];
        } else {
          $error = mysqli_stmt_errno($statement) === 1062 ? 'Ese autor ya existe.' : 'No se pudo guardar el autor.';
        }
        mysqli_stmt_close($statement);
      }
    }
  }
}

if ($editId > 0) {
  $statement = mysqli_prepare($conexion, 'SELECT id, nombres, ap_paterno FROM autores WHERE id = ? LIMIT 1');
  mysqli_stmt_bind_param($statement, 'i', $editId);
  mysqli_stmt_execute($statement);
  $result = mysqli_stmt_get_result($statement);
  $editRow = mysqli_fetch_assoc($result) ?: [];
  mysqli_stmt_close($statement);
}

$authors = mysqli_query($conexion, 'SELECT a.id, a.nombres, a.ap_paterno, COUNT(r.id) AS usos FROM autores a LEFT JOIN reportajes r ON r.autor_id = a.id GROUP BY a.id ORDER BY a.nombres, a.ap_paterno');
if (!$authors) { die('Error al consultar autores: ' . e(mysqli_error($conexion))); }

function render_partial($partial) { include __DIR__ . '/src/partials/' . $partial; }
?>
<!doctype html>
<html lang="es"><head><meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" /><link rel="stylesheet" href="build/style.css" /><title>Autores | DDP Noticias</title></head>
<body x-data="{ page: 'autores', loaded: true, darkMode: false, stickyMenu: false, sidebarToggle: false, scrollTop: false }" :class="{'dark bg-gray-900': darkMode === true}">
<div class="flex h-screen overflow-hidden"><?php render_partial('sidebar.html'); ?><div class="relative flex flex-1 flex-col overflow-x-hidden overflow-y-auto"><?php render_partial('overlay.html'); ?><?php render_partial('header.html'); ?><main><div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
<div class="mb-6 flex flex-col gap-1"><h1 class="text-2xl font-bold text-gray-800 dark:text-white/90"><?php echo $editId ? 'Editar autor' : 'Crear autor'; ?></h1><p class="text-sm text-gray-500 dark:text-gray-400">Autores disponibles para los reportajes.</p></div>
<?php if ($message): ?><div class="mb-6 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700"><?php echo e($message); ?></div><?php endif; ?><?php if ($error): ?><div class="mb-6 rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700"><?php echo e($error); ?></div><?php endif; ?>
<form method="post" class="mb-8 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6"><div class="grid grid-cols-1 gap-5 md:grid-cols-2"><div><label for="nombres" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nombre</label><input id="nombres" name="nombres" required value="<?php echo e($_POST['nombres'] ?? ($editRow['nombres'] ?? '')); ?>" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" /></div><div><label for="ap_paterno" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Apellido paterno</label><input id="ap_paterno" name="ap_paterno" value="<?php echo e($_POST['ap_paterno'] ?? ($editRow['ap_paterno'] ?? '')); ?>" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" /></div></div><?php if ($editId): ?><input type="hidden" name="actualizar_id" value="<?php echo $editId; ?>" /><?php endif; ?><button type="submit" class="mt-6 rounded-lg bg-error-500 px-5 py-3 text-sm font-medium text-white hover:bg-error-600"><?php echo $editId ? 'Actualizar' : 'Guardar'; ?> autor</button><?php if ($editId): ?><a href="autores.php" class="ml-3 text-sm text-gray-500 hover:underline">Cancelar</a><?php endif; ?></form>
<section class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6"><h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Autores registrados</h2><div class="overflow-x-auto"><table class="min-w-full"><thead><tr class="border-y border-gray-100 dark:border-gray-800"><th class="py-3 pr-4 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Nombre</th><th class="py-3 pr-4 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Apellido paterno</th><th class="py-3 pr-4 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Reportajes</th><th class="py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Acciones</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800"><?php while ($author = mysqli_fetch_assoc($authors)): ?><tr><td class="py-3 pr-4 text-sm text-gray-800 dark:text-white/90"><?php echo e($author['nombres']); ?></td><td class="py-3 pr-4 text-sm text-gray-800 dark:text-white/90"><?php echo e($author['ap_paterno'] ?? ''); ?></td><td class="py-3 pr-4 text-sm text-gray-800 dark:text-white/90"><?php echo (int) $author['usos']; ?></td><td class="py-3 text-sm"><a href="autores.php?editar=<?php echo (int) $author['id']; ?>" class="mr-3 text-brand-500 hover:underline">Editar</a><?php if ((int) $author['usos'] === 0): ?><form class="inline" method="post" onsubmit="return confirm('¿Eliminar este autor?');"><input type="hidden" name="eliminar_id" value="<?php echo (int) $author['id']; ?>"><button type="submit" class="text-error-500 hover:underline">Eliminar</button></form><?php else: ?><span class="text-gray-400">En uso</span><?php endif; ?></td></tr><?php endwhile; ?></tbody></table></div></section>
</div></main></div></div><script src="build/bundle.js"></script></body></html>

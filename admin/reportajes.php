<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../conexion.php';
require_admin();

$message = '';
$error = '';
$editId = (int) ($_GET['editar'] ?? 0);
$editRow = [];

function render_partial($partial) { include __DIR__ . '/src/partials/' . $partial; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
  $statement = mysqli_prepare($conexion, 'DELETE FROM reportajes WHERE id = ?');
  $id = (int) $_POST['eliminar_id'];
  mysqli_stmt_bind_param($statement, 'i', $id);
  $ok = mysqli_stmt_execute($statement);
  mysqli_stmt_close($statement);
  header('Location: reportajes.php?mensaje=' . ($ok ? 'eliminado' : 'error'));
  exit;
}

if (isset($_GET['mensaje'])) {
  $message = $_GET['mensaje'] === 'eliminado' ? 'El reportaje se eliminó correctamente.' : 'No se pudo eliminar el reportaje.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['eliminar_id'])) {
  $titulo = trim($_POST['titulo'] ?? '');
  $resumen = trim($_POST['resumen_corto'] ?? '');
  $desarrollo = trim($_POST['desarrollo'] ?? '');
  $foto = trim($_POST['foto_principal'] ?? '');
  $pdf = trim($_POST['pdf_adjunto'] ?? '');
  $fecha = $_POST['fecha_publicacion'] ?? '';
  $tipoAutor = $_POST['tipo_autor'] ?? 'empresa';
  $autorId = (int) ($_POST['autor_id'] ?? 0);
  $destacado = isset($_POST['es_destacado']) ? 1 : 0;
  $editId = (int) ($_POST['actualizar_id'] ?? 0);

  if ($titulo === '' || $desarrollo === '' || $fecha === '') {
    $error = 'Completa el título, desarrollo y fecha de publicación.';
  } elseif ($tipoAutor === 'autor' && $autorId <= 0) {
    $error = 'Selecciona un autor de la lista.';
  } elseif ($tipoAutor === 'autor') {
    $authorCheck = mysqli_prepare($conexion, "SELECT id FROM autores WHERE id = ? AND NOT (LOWER(TRIM(nombres)) = 'redacción' AND COALESCE(TRIM(ap_paterno), '') = '') LIMIT 1");
    mysqli_stmt_bind_param($authorCheck, 'i', $autorId);
    mysqli_stmt_execute($authorCheck);
    $authorResult = mysqli_stmt_get_result($authorCheck);
    $validAuthor = mysqli_fetch_assoc($authorResult);
    mysqli_stmt_close($authorCheck);
    if (!$validAuthor) {
      $error = 'El autor seleccionado no es válido.';
    }
  } else {
    if ($tipoAutor === 'empresa') {
      $lookup = mysqli_query($conexion, "SELECT id FROM autores WHERE nombres = 'Redacción' AND (ap_paterno IS NULL OR ap_paterno = '') LIMIT 1");
      $company = mysqli_fetch_assoc($lookup);
      if ($company) {
        $autorId = (int) $company['id'];
      } else {
        $author = mysqli_prepare($conexion, "INSERT INTO autores (nombres, ap_paterno, ap_materno, nickname, es_nickname) VALUES ('Redacción', '', NULL, NULL, 0)");
        mysqli_stmt_execute($author);
        $autorId = mysqli_insert_id($conexion);
        mysqli_stmt_close($author);
      }
    }

    if ($editId > 0) {
      $statement = mysqli_prepare($conexion, 'UPDATE reportajes SET titulo = ?, resumen_corto = ?, desarrollo = ?, foto_principal = ?, pdf_adjunto = ?, fecha_publicacion = ?, es_destacado = ?, autor_id = ? WHERE id = ?');
      mysqli_stmt_bind_param($statement, 'ssssssiii', $titulo, $resumen, $desarrollo, $foto, $pdf, $fecha, $destacado, $autorId, $editId);
      $saved = mysqli_stmt_execute($statement);
      $message = $saved ? 'El reportaje se actualizó correctamente.' : 'No se pudo actualizar el reportaje: ' . mysqli_stmt_error($statement);
    } else {
      $statement = mysqli_prepare($conexion, 'INSERT INTO reportajes (titulo, resumen_corto, desarrollo, foto_principal, pdf_adjunto, fecha_publicacion, es_destacado, autor_id, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
      $usuarioId = (int) $_SESSION['admin_id'];
      mysqli_stmt_bind_param($statement, 'ssssssiii', $titulo, $resumen, $desarrollo, $foto, $pdf, $fecha, $destacado, $autorId, $usuarioId);
      $saved = mysqli_stmt_execute($statement);
      $message = $saved ? 'El reportaje se creó correctamente.' : 'No se pudo crear el reportaje: ' . mysqli_stmt_error($statement);
    }
    mysqli_stmt_close($statement);
    if ($saved) { $editId = 0; $_POST = []; }
  }
}

if ($editId > 0) {
  $statement = mysqli_prepare($conexion, 'SELECT r.*, a.nombres AS autor_nombre, a.ap_paterno AS autor_apellido FROM reportajes r INNER JOIN autores a ON a.id = r.autor_id WHERE r.id = ? LIMIT 1');
  mysqli_stmt_bind_param($statement, 'i', $editId);
  mysqli_stmt_execute($statement);
  $result = mysqli_stmt_get_result($statement);
  $editRow = mysqli_fetch_assoc($result) ?: [];
  mysqli_stmt_close($statement);
}

$authors = mysqli_query($conexion, "SELECT id, nombres, ap_paterno FROM autores WHERE NOT (LOWER(TRIM(nombres)) = 'redacción' AND COALESCE(TRIM(ap_paterno), '') = '') ORDER BY nombres, ap_paterno");
$reportajes = mysqli_query($conexion, 'SELECT r.id, r.titulo, r.fecha_publicacion, r.es_destacado, a.nombres AS autor_nombre, a.ap_paterno AS autor_apellido FROM reportajes r INNER JOIN autores a ON a.id = r.autor_id ORDER BY r.fecha_publicacion DESC, r.id DESC');
if (!$authors || !$reportajes) { die('Error al consultar reportajes o autores: ' . e(mysqli_error($conexion))); }
$isCompany = empty($editRow['autor_apellido']) && strcasecmp($editRow['autor_nombre'] ?? '', 'Redacción') === 0;
$selectedAutorId = (int) ($editRow['autor_id'] ?? 0);
?>
<!doctype html>
<html lang="es">
<head><meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" /><link rel="stylesheet" href="build/style.css" /><title>Reportajes | DDP Noticias</title></head>
<body x-data="{ page: 'reportajes', loaded: true, darkMode: false, stickyMenu: false, sidebarToggle: false, scrollTop: false }" :class="{'dark bg-gray-900': darkMode === true}">
<div class="flex h-screen overflow-hidden">
<?php render_partial('sidebar.html'); ?>
<div class="relative flex flex-1 flex-col overflow-x-hidden overflow-y-auto">
<?php render_partial('overlay.html'); ?><?php render_partial('header.html'); ?>
<main><div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
<div class="mb-6"><h1 class="text-2xl font-bold text-gray-800 dark:text-white/90"><?php echo $editId ? 'Editar reportaje' : 'Crear reportaje'; ?></h1><p class="text-sm text-gray-500 dark:text-gray-400">Registrado en la base de datos revista_digital.</p></div>
<?php if ($message): ?><div class="mb-6 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700"><?php echo e($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="mb-6 rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700"><?php echo e($error); ?></div><?php endif; ?>
<form method="post" class="mb-8 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
<div class="md:col-span-2"><label for="titulo" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Título</label><input id="titulo" name="titulo" value="<?php echo e($_POST['titulo'] ?? ($editRow['titulo'] ?? '')); ?>" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" /></div>
<div class="md:col-span-2"><label for="resumen_corto" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Resumen corto</label><input id="resumen_corto" name="resumen_corto" value="<?php echo e($_POST['resumen_corto'] ?? ($editRow['resumen_corto'] ?? '')); ?>" maxlength="500" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" /></div>
<div class="md:col-span-2"><label for="desarrollo" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Desarrollo</label><textarea id="desarrollo" name="desarrollo" rows="8" required class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90"><?php echo e($_POST['desarrollo'] ?? ($editRow['desarrollo'] ?? '')); ?></textarea></div>
<div><label for="fecha_publicacion" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Fecha de publicación</label><input id="fecha_publicacion" name="fecha_publicacion" type="date" value="<?php echo e($_POST['fecha_publicacion'] ?? ($editRow['fecha_publicacion'] ?? date('Y-m-d'))); ?>" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" /></div>
<div class="md:col-span-2"><label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Propiedad del reportaje</label><div class="flex gap-5 text-sm text-gray-800 dark:text-white/90"><label><input type="radio" name="tipo_autor" value="empresa" <?php echo (($_POST['tipo_autor'] ?? ($isCompany ? 'empresa' : 'autor')) === 'empresa') ? 'checked' : ''; ?> /> Empresa</label><label><input type="radio" name="tipo_autor" value="autor" <?php echo (($_POST['tipo_autor'] ?? ($isCompany ? 'empresa' : 'autor')) === 'autor') ? 'checked' : ''; ?> /> Autor</label></div></div>
<div class="md:col-span-2"><label for="autor_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Autor</label><select id="autor_id" name="autor_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" <?php echo $isCompany ? 'disabled' : ''; ?>><option value="">Selecciona un autor</option><?php while ($author = mysqli_fetch_assoc($authors)): ?><option value="<?php echo (int) $author['id']; ?>" <?php echo ((int) ($_POST['autor_id'] ?? $selectedAutorId) === (int) $author['id']) ? 'selected' : ''; ?>><?php echo e(trim($author['nombres'] . ' ' . ($author['ap_paterno'] ?? ''))); ?></option><?php endwhile; ?></select><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Se desactiva al elegir Empresa.</p></div>
<div><label for="foto_principal" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">URL de foto principal</label><input id="foto_principal" name="foto_principal" value="<?php echo e($_POST['foto_principal'] ?? ($editRow['foto_principal'] ?? '')); ?>" maxlength="255" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" /></div>
<div><label for="pdf_adjunto" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">URL de PDF adjunto</label><input id="pdf_adjunto" name="pdf_adjunto" value="<?php echo e($_POST['pdf_adjunto'] ?? ($editRow['pdf_adjunto'] ?? '')); ?>" maxlength="255" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" /></div>
<label class="flex items-center gap-3 text-sm text-gray-800 dark:text-white/90 md:col-span-2"><input type="checkbox" name="es_destacado" <?php echo (isset($_POST['es_destacado']) || (!empty($editRow['es_destacado']) && !$_POST)) ? 'checked' : ''; ?> /> Marcar como destacado</label>
</div>
<?php if ($editId): ?><input type="hidden" name="actualizar_id" value="<?php echo $editId; ?>" /><?php endif; ?><button type="submit" class="mt-6 rounded-lg bg-error-500 px-5 py-3 text-sm text-white"><?php echo $editId ? 'Actualizar' : 'Guardar'; ?> reportaje</button><?php if ($editId): ?><a href="reportajes.php" class="ml-3 text-sm text-gray-500">Cancelar</a><?php endif; ?>
</form>
<section class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6"><h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Reportajes registrados</h2><div class="w-full overflow-x-auto"><table class="min-w-full"><thead><tr class="border-y border-gray-100 dark:border-gray-800"><th class="py-3 pr-4 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Título</th><th class="py-3 pr-4 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Autor</th><th class="py-3 pr-4 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Fecha</th><th class="py-3 pr-4 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Estado</th><th class="py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Acciones</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800"><?php while ($reportaje = mysqli_fetch_assoc($reportajes)): ?><tr><td class="max-w-sm py-3 pr-4 text-sm text-gray-800 dark:text-white/90"><?php echo e($reportaje['titulo']); ?></td><td class="py-3 pr-4 text-sm text-gray-800 dark:text-white/90"><?php echo e(trim($reportaje['autor_nombre'] . ' ' . $reportaje['autor_apellido'])); ?></td><td class="whitespace-nowrap py-3 pr-4 text-sm text-gray-800 dark:text-white/90"><?php echo e($reportaje['fecha_publicacion']); ?></td><td class="whitespace-nowrap py-3 pr-4 text-sm text-gray-800 dark:text-white/90"><?php echo $reportaje['es_destacado'] ? 'Destacado' : 'Normal'; ?></td><td class="whitespace-nowrap py-3 text-sm"><a href="reportajes.php?editar=<?php echo (int) $reportaje['id']; ?>" class="mr-3 text-brand-500 hover:underline">Editar</a><form class="inline" method="post" onsubmit="return confirm('¿Eliminar este reportaje?');"><input type="hidden" name="eliminar_id" value="<?php echo (int) $reportaje['id']; ?>"><button type="submit" class="text-error-500 hover:underline">Eliminar</button></form></td></tr><?php endwhile; ?></tbody></table></div></section>
</div></main></div></div><script src="build/bundle.js"></script>
<script>document.querySelectorAll('input[name="tipo_autor"]').forEach(function (radio) { radio.addEventListener('change', function () { var select = document.getElementById('autor_id'); var empresa = this.value === 'empresa'; select.disabled = empresa; if (empresa) select.value = ''; }); });</script>
</body></html>

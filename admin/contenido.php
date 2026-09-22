<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../conexion.php';

require_admin();

$config = [
  'noticias' => [
    'label' => 'Noticias',
    'title' => 'titulo',
    'fields' => [
      ['name' => 'titulo', 'label' => 'Título', 'type' => 'text', 'required' => true],
      ['name' => 'foto', 'label' => 'URL de foto', 'type' => 'url'],
      ['name' => 'link_externo', 'label' => 'Enlace externo', 'type' => 'url'],
      ['name' => 'fecha_publicacion', 'label' => 'Fecha de publicación', 'type' => 'date', 'required' => true],
    ],
    'columns' => ['titulo', 'fecha_publicacion'],
  ],
  'podcasts' => [
    'label' => 'Podcasts',
    'title' => 'titulo',
    'fields' => [
      ['name' => 'titulo', 'label' => 'Título', 'type' => 'text', 'required' => true],
      ['name' => 'url_embed', 'label' => 'URL de inserción', 'type' => 'url', 'required' => true],
      ['name' => 'fecha_publicacion', 'label' => 'Fecha de publicación', 'type' => 'date', 'required' => true],
    ],
    'columns' => ['titulo', 'url_embed', 'fecha_publicacion'],
  ],
  'boletines' => [
    'label' => 'Boletines',
    'title' => 'numero_boletin',
    'fields' => [
      ['name' => 'numero_boletin', 'label' => 'Número de boletín', 'type' => 'text', 'required' => true],
      ['name' => 'resumen', 'label' => 'Resumen para la portada', 'type' => 'textarea'],
      ['name' => 'foto_portada', 'label' => 'URL de portada', 'type' => 'url'],
      ['name' => 'archivo_pdf', 'label' => 'URL del archivo PDF', 'type' => 'url', 'required' => true],
      ['name' => 'fecha_publicacion', 'label' => 'Fecha de publicación', 'type' => 'date', 'required' => true],
    ],
    'columns' => ['numero_boletin', 'fecha_publicacion'],
  ],
  'videos' => [
    'label' => 'Videos',
    'title' => 'titulo',
    'fields' => [
      ['name' => 'titulo', 'label' => 'Título', 'type' => 'text', 'required' => true, 'help' => 'Escribe entre paréntesis la palabra que deseas subrayar en rojo. Ejemplo: Visita del papa (León XIV) al Perú.'],
      ['name' => 'url_embed', 'label' => 'URL de inserción', 'type' => 'url', 'required' => true],
      ['name' => 'fecha_publicacion', 'label' => 'Fecha de publicación', 'type' => 'date', 'required' => true],
    ],
    'columns' => ['titulo', 'url_embed', 'fecha_publicacion'],
  ],
];

$table = $_GET['tipo'] ?? 'noticias';
if (!isset($config[$table])) {
  http_response_code(404);
  exit('Tipo de contenido no válido.');
}

$section = $config[$table];
$adminActiveModule = $table;
$message = '';
$error = '';
$editId = (int) ($_GET['editar'] ?? 0);
$editRow = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['eliminar_id'])) {
    $id = (int) $_POST['eliminar_id'];
    $statement = mysqli_prepare($conexion, "DELETE FROM `$table` WHERE id = ?");
    if (!$statement) {
      $error = 'No se pudo preparar la eliminación: ' . mysqli_error($conexion);
    } else {
      mysqli_stmt_bind_param($statement, 'i', $id);
      $message = mysqli_stmt_execute($statement) ? 'Registro eliminado correctamente.' : 'No se pudo eliminar el registro: ' . mysqli_stmt_error($statement);
      mysqli_stmt_close($statement);
    }
  } elseif (isset($_POST['actualizar_id'])) {
    $id = (int) $_POST['actualizar_id'];
    $values = [];
    $missing = false;
    foreach ($section['fields'] as $field) {
      $value = trim($_POST[$field['name']] ?? '');
      $values[$field['name']] = $value;
      if (!empty($field['required']) && $value === '') {
        $missing = true;
      }
    }

    if ($missing) {
      $error = 'Completa los campos obligatorios.';
      $editId = $id;
      $editRow = $values;
    } else {
      $setList = implode(', ', array_map(static fn($name) => "`$name` = ?", array_keys($values)));
      $statement = mysqli_prepare($conexion, "UPDATE `$table` SET $setList WHERE id = ?");
      if (!$statement) {
        $error = 'No se pudo preparar la actualización: ' . mysqli_error($conexion);
      } else {
        $bindValues = array_values($values);
        $bindValues[] = $id;
        $types = str_repeat('s', count($values)) . 'i';
        $bindParameters = [$types];
        foreach ($bindValues as &$bindValue) {
          $bindParameters[] = &$bindValue;
        }
        call_user_func_array([$statement, 'bind_param'], $bindParameters);
        $saved = mysqli_stmt_execute($statement);
        $message = $saved ? 'Registro actualizado correctamente.' : 'No se pudo actualizar: ' . mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        if ($saved) {
          $editId = 0;
          $_POST = [];
        }
      }
    }
  } else {
    $values = [];
    $missing = false;
    foreach ($section['fields'] as $field) {
      $value = trim($_POST[$field['name']] ?? '');
      $values[$field['name']] = $value;
      if (!empty($field['required']) && $value === '') {
        $missing = true;
      }
    }

    if ($missing) {
      $error = 'Completa los campos obligatorios.';
    } else {
      $names = array_merge(array_keys($values), ['usuario_id']);
      $placeholders = implode(', ', array_fill(0, count($names), '?'));
      $statement = mysqli_prepare($conexion, "INSERT INTO `$table` (`" . implode('`, `', $names) . "`) VALUES ($placeholders)");
      if (!$statement) {
        $error = 'No se pudo preparar el registro: ' . mysqli_error($conexion);
      } else {
        $values['usuario_id'] = (int) $_SESSION['admin_id'];
        $types = str_repeat('s', count($values) - 1) . 'i';
        $bindValues = array_values($values);
        $bindParameters = [$types];
        foreach ($bindValues as &$bindValue) {
          $bindParameters[] = &$bindValue;
        }
        call_user_func_array([$statement, 'bind_param'], $bindParameters);
        $saved = mysqli_stmt_execute($statement);
        $message = $saved ? 'Registro guardado correctamente.' : 'No se pudo guardar: ' . mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        if ($saved) {
          $_POST = [];
        }
      }
    }
  }
}

if ($editId > 0 && !$editRow) {
  $editStatement = mysqli_prepare($conexion, "SELECT * FROM `$table` WHERE id = ? LIMIT 1");
  mysqli_stmt_bind_param($editStatement, 'i', $editId);
  mysqli_stmt_execute($editStatement);
  $editResult = mysqli_stmt_get_result($editStatement);
  $editRow = mysqli_fetch_assoc($editResult) ?: [];
  mysqli_stmt_close($editStatement);
}

$columnList = implode(', ', array_map(static fn($column) => "`$column`", $section['columns']));
$rows = mysqli_query($conexion, "SELECT id, $columnList FROM `$table` ORDER BY fecha_publicacion DESC, id DESC");
if (!$rows) {
  die('Error al consultar ' . e($section['label']) . ': ' . e(mysqli_error($conexion)));
}

function render_partial($partial) {
  include __DIR__ . '/src/partials/' . $partial;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="build/style.css" />
  <title><?php echo e($section['label']); ?> | DDP Noticias</title>
</head>
<body x-data="{ page: '<?php echo e($table); ?>', loaded: true, darkMode: false, stickyMenu: false, sidebarToggle: false, scrollTop: false }" x-init="darkMode = JSON.parse(localStorage.getItem('darkMode')); $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))" :class="{'dark bg-gray-900': darkMode === true}">
  <div class="flex h-screen overflow-hidden">
    <?php render_partial('sidebar.html'); ?>
    <div class="relative flex flex-1 flex-col overflow-x-hidden overflow-y-auto">
      <?php render_partial('overlay.html'); ?>
      <?php render_partial('header.html'); ?>
      <main>
        <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
          <div class="mb-6 flex flex-col gap-1">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Crear <?php echo e($section['label']); ?></h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Registrado en la base de datos revista_digital.</p>
          </div>
          <?php if ($message): ?><div class="mb-6 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700"><?php echo e($message); ?></div><?php endif; ?>
          <?php if ($error): ?><div class="mb-6 rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700"><?php echo e($error); ?></div><?php endif; ?>
          <form method="post" class="mb-8 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
              <?php foreach ($section['fields'] as $field): ?>
                <div class="<?php echo $field['type'] === 'textarea' ? 'md:col-span-2' : ''; ?>">
                  <label for="<?php echo e($field['name']); ?>" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"><?php echo e($field['label']); ?><?php echo !empty($field['required']) ? '' : ''; ?></label>
                  <?php if ($field['type'] === 'textarea'): ?>
                    <textarea id="<?php echo e($field['name']); ?>" name="<?php echo e($field['name']); ?>" rows="4" <?php echo !empty($field['required']) ? 'required' : ''; ?> class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90"><?php echo e($_POST[$field['name']] ?? ($editRow[$field['name']] ?? '')); ?></textarea>
                  <?php else: ?>
                    <input id="<?php echo e($field['name']); ?>" name="<?php echo e($field['name']); ?>" type="<?php echo e($field['type']); ?>" value="<?php echo e($_POST[$field['name']] ?? ($editRow[$field['name']] ?? ($field['type'] === 'date' ? date('Y-m-d') : ''))); ?>" <?php echo !empty($field['required']) ? 'required' : ''; ?> class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                  <?php endif; ?>
                  <?php if (!empty($field['help'])): ?><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"><?php echo e($field['help']); ?></p><?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
            <?php if ($editId > 0): ?><input type="hidden" name="actualizar_id" value="<?php echo $editId; ?>" /><?php endif; ?>
            <button type="submit" class="mt-6 rounded-lg bg-error-500 px-5 py-3 text-sm font-medium text-white hover:bg-error-600"><?php echo $editId > 0 ? 'Actualizar' : 'Guardar'; ?> <?php echo e($section['label']); ?></button>
            <?php if ($editId > 0): ?><a href="contenido.php?tipo=<?php echo e($table); ?>" class="ml-3 text-sm text-gray-500 hover:underline">Cancelar</a><?php endif; ?>
          </form>
          <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90"><?php echo e($section['label']); ?> registrados</h2>
            <div class="w-full overflow-x-auto"><table class="min-w-full"><thead><tr class="border-y border-gray-100 dark:border-gray-800">
              <?php foreach ($section['columns'] as $column): ?><th class="py-3 pr-4 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400"><?php echo e(ucwords(str_replace('_', ' ', $column))); ?></th><?php endforeach; ?><th class="py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Acciones</th>
            </tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <?php while ($row = mysqli_fetch_assoc($rows)): ?><tr>
                <?php foreach ($section['columns'] as $column): ?><td class="max-w-sm py-3 pr-4 text-sm text-gray-800 dark:text-white/90"><?php echo e($row[$column]); ?></td><?php endforeach; ?>
                <td class="py-3 text-sm"><a href="contenido.php?tipo=<?php echo e($table); ?>&editar=<?php echo (int) $row['id']; ?>" class="mr-3 text-brand-500 hover:underline">Editar</a><form class="inline" method="post" onsubmit="return confirm('¿Eliminar este registro?');"><input type="hidden" name="eliminar_id" value="<?php echo (int) $row['id']; ?>"><button type="submit" class="text-error-500 hover:underline">Eliminar</button></form></td>
              </tr><?php endwhile; ?>
              <?php if (mysqli_num_rows($rows) === 0): ?><tr><td colspan="<?php echo count($section['columns']) + 1; ?>" class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">No hay registros.</td></tr><?php endif; ?>
            </tbody></table></div>
          </section>
        </div>
      </main>
    </div>
  </div>
  <script src="build/bundle.js"></script>
</body>
</html>

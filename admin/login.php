<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../conexion.php';

redirect_if_authenticated();

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email_a'] ?? '');
  $password = $_POST['contrasena_a'] ?? '';

  if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    $error = 'Ingresa un correo válido y una contraseña.';
  } else {
    $statement = mysqli_prepare(
      $conexion,
      "SELECT id, nombres, ap_paterno, ap_materno, email, rol, password_hash FROM usuarios WHERE email = ? AND rol = 'admin' LIMIT 1"
    );
    mysqli_stmt_bind_param($statement, 's', $email);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $admin = mysqli_fetch_assoc($result);
    mysqli_stmt_close($statement);

    $passwordIsValid = $admin && password_verify($password, $admin['password_hash']);
    $legacyPassword = $admin && strlen($admin['password_hash']) < 20
      && hash_equals($admin['password_hash'], $password);

    if ($passwordIsValid || $legacyPassword) {
      if ($legacyPassword) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $update = mysqli_prepare($conexion, 'UPDATE usuarios SET password_hash = ? WHERE id = ?');
        mysqli_stmt_bind_param($update, 'si', $newHash, $admin['id']);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
      }

      session_regenerate_id(true);
      $_SESSION['admin_id'] = (int) $admin['id'];
      $_SESSION['admin_nombre'] = $admin['nombres'];
      $_SESSION['admin_nombre_completo'] = trim(
        $admin['nombres'] . ' ' . $admin['ap_paterno'] . ' ' . ($admin['ap_materno'] ?? '')
      );
      $_SESSION['admin_email'] = $admin['email'];
      $_SESSION['admin_rol'] = $admin['rol'];
      header('Location: reportajes.php');
      exit;
    }

    $error = 'El correo o la contraseña no son correctos.';
  }
}
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="build/style.css" />
    <title>Ingresar | DDP Noticias</title>
  </head>
  <body
    x-data="{ loaded: true, darkMode: false }"
    x-init="darkMode = JSON.parse(localStorage.getItem('darkMode')); $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{ 'dark bg-gray-900': darkMode === true }"
  >
    <div class="relative z-1 flex min-h-screen items-center justify-center bg-white p-6 dark:bg-gray-900 sm:p-0">
      <main class="w-full max-w-md">
        <div class="mb-8 flex justify-center">
          <img class="h-auto w-56 object-contain" src="../assets/images/logo.png" alt="DDP Noticias" />
        </div>
        <div class="mb-5 text-center sm:mb-8">
          <h1 class="mb-2 text-title-sm font-semibold text-gray-800 dark:text-white/90 sm:text-title-md">Iniciar sesión</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Ingresa con los datos de tu cuenta de administrador.</p>
        </div>

        <?php if ($error): ?>
          <p class="mb-5 rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700" role="alert"><?php echo e($error); ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
          <div class="space-y-5">
            <div>
              <label for="email_a" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Correo electrónico<span class="text-error-500">*</span></label>
              <input id="email_a" name="email_a" type="email" value="<?php echo e($email); ?>" autocomplete="username" placeholder="correo@ejemplo.com" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-error-300 focus:outline-hidden focus:ring-3 focus:ring-error-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-error-800" />
            </div>
            <div>
              <label for="contrasena_a" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Contraseña<span class="text-error-500">*</span></label>
              <input id="contrasena_a" name="contrasena_a" type="password" autocomplete="current-password" placeholder="Ingresa tu contraseña" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-error-300 focus:outline-hidden focus:ring-3 focus:ring-error-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-error-800" />
            </div>
            <button type="submit" class="flex w-full items-center justify-center rounded-lg bg-error-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-error-600">Ingresar</button>
          </div>
        </form>
      </main>
    </div>
    <script src="build/bundle.js"></script>
  </body>
</html>
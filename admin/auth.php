<?php
session_start();
require_once __DIR__ . '/../conexion.php';

function require_admin() {
  global $conexion;

  if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
  }

  if (empty($_SESSION['admin_nombre_completo']) || empty($_SESSION['admin_email']) || empty($_SESSION['admin_rol'])) {
    $statement = mysqli_prepare(
      $conexion,
      "SELECT nombres, ap_paterno, ap_materno, email, rol FROM usuarios WHERE id = ? AND rol = 'admin' LIMIT 1"
    );
    mysqli_stmt_bind_param($statement, 'i', $_SESSION['admin_id']);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $admin = mysqli_fetch_assoc($result);
    mysqli_stmt_close($statement);

    if (!$admin) {
      $_SESSION = [];
      session_destroy();
      header('Location: login.php');
      exit;
    }

    $_SESSION['admin_nombre_completo'] = trim(
      $admin['nombres'] . ' ' . $admin['ap_paterno'] . ' ' . ($admin['ap_materno'] ?? '')
    );
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_rol'] = $admin['rol'];
  }
}

function redirect_if_authenticated() {
  if (!empty($_SESSION['admin_id'])) {
    header('Location: reportajes.php');
    exit;
  }
}

function e($value) {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
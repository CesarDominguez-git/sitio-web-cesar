<?php
session_start();

require_once '../config/Database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = "Completa todos los campos.";
    } else {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_rol'] = $user['rol'];
            $_SESSION['user_foto'] = $usuario['foto'];


            if ($user['rol'] === 'admin') {
                header("Location: ../admin/dashboard_admin.php");
            } else {
                header("Location: ../empleado/dashboard_empleado.php");
            }
            exit;
        } else {
            $error = "Credenciales incorrectas.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - TecnoSoluciones</title>


  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

  <style>

    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Roboto', sans-serif;
      background: linear-gradient(-45deg, #0f0f0f, #1e1e2f, #121212, #2c2c2c);
      background-size: 400% 400%;
      animation: fondoOscuro 15s ease infinite;
    }

    @keyframes fondoOscuro {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .login-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-card {
      background-color: #ffffff;
      border-radius: 15px;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.6);
      overflow: hidden;
      display: flex;
      flex-direction: row;
      width: 100%;
      max-width: 1000px;
      animation: entradaIzquierda 1s ease-out;
    }

    .login-image {
      background: #f5f5f5;
      padding: 40px;
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-image img {
      max-width: 100%;
      height: auto;
      transition: transform 0.4s ease, box-shadow 0.4s ease;
    }

    .login-image img:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 15px rgba(255, 255, 255, 0.1);
    }

    .login-form {
      flex: 1;
      padding: 50px;
      animation: entradaDerecha 1.2s ease-out;
    }

    .login-form h2 {
      font-weight: 700;
      margin-bottom: 30px;
      color: #343a40;
    }

    .form-label {
      color: #555;
    }

    .form-control {
      background-color: #f1f1f1;
      border: 1px solid #ccc;
      color: #333;
    }

    .form-control:focus {
      border-color: #4a89dc;
      box-shadow: 0 0 0 0.2rem rgba(74, 137, 220, 0.25);
      transition: box-shadow 0.3s ease;
    }

    .btn-login {
      background: linear-gradient(135deg, #4a89dc, #1e2f50);
      border: none;
      color: #fff;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .btn-login:hover {
      transform: scale(1.05);
      box-shadow: 0 0 15px rgba(74, 137, 220, 0.5);
    }

    .text-link a {
      color: #4a89dc;
      text-decoration: none;
      transition: color 0.3s;
    }

    .text-link a:hover {
      color: #2c70c4;
    }

    @keyframes entradaIzquierda {
      from {
        opacity: 0;
        transform: translateX(-50px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes entradaDerecha {
      from {
        opacity: 0;
        transform: translateX(50px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @media (max-width: 768px) {
      .login-card {
        flex-direction: column;
      }
      .login-image {
        display: none;
      }
      .login-form {
        padding: 30px;
      }
    }
  </style>
</head>
<body>

  <div class="login-wrapper">
    <div class="login-card">
      >
      <div class="login-image">
        <img src="../img/logo.png" alt="Logo TecnoSoluciones">
      </div>

      <div class="login-form">
        <h2>Iniciar sesión</h2>
        <form action="login.php" method="POST">
          <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="user@tec.com" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="•••••••••" required>
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="recordarme">
            <label class="form-check-label" for="recordarme">Recordarme</label>
          </div>
          <button type="submit" class="btn btn-login w-100">Ingresar</button>
          <div class="text-center text-link mt-3">
            <a href="#">¿Olvidaste tu contraseña?</a><br>
          </div>
        </form>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


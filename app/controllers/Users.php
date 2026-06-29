<?php
  class Users extends Controller {
    private $userModel;

    public function __construct() {
      // Cargamos sólo el modelo que existe (User)
      $this->userModel = $this->model('User');
    }

    public function index() {
      redirect('users/login');
    }

    public function register() {
      if ($this->isLoggedIn()) {
        redirect('pages/index');
      }

      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $data = [
          'name' => trim($_POST['name']),
          'email' => trim($_POST['email']),
          'password' => trim($_POST['password']),
          'confirm_password' => trim($_POST['confirm_password']),
          'name_err' => '',
          'email_err' => '',
          'password_err' => '',
          'confirm_password_err' => ''
        ];

        if (empty($data['email'])) {
          $data['email_err'] = 'Por favor ingresa un email';
        } else {
          if ($this->userModel->findUserByEmail($data['email'])) {
            $data['email_err'] = 'El email ya está registrado.';
          }
        }

        if (empty($data['name'])) {
          $data['name_err'] = 'Por favor ingresa un nombre';
        }

        if (empty($data['password'])) {
          $data['password_err'] = 'Por favor ingresa una contraseña.';
        } elseif (strlen($data['password']) < 6) {
          $data['password_err'] = 'La contraseña debe tener al menos 6 caracteres.';
        }

        if (empty($data['confirm_password'])) {
          $data['confirm_password_err'] = 'Por favor confirma la contraseña.';
        } else {
          if ($data['password'] != $data['confirm_password']) {
            $data['confirm_password_err'] = 'Las contraseñas no coinciden.';
          }
        }

        if (empty($data['name_err']) && empty($data['email_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
          $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

          if ($this->userModel->register($data)) {
            flash('register_success', 'Estás registrado de manera exitosa, ahora puedes iniciar sesión');
            redirect('users/login');
          } else {
            die('Algo salió mal');
          }
        } else {
          $this->view('users/register', $data);
        }
      } else {
        $data = [
          'name' => '', 'email' => '', 'password' => '', 'confirm_password' => '',
          'name_err' => '', 'email_err' => '', 'password_err' => '', 'confirm_password_err' => ''
        ];
        $this->view('users/register', $data);
      }
    }

    public function login() {
      if ($this->isLoggedIn()) {
        redirect('pages/index');
      }

      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $data = [
          'email' => trim($_POST['email']),
          'password' => trim($_POST['password']),
          'email_err' => '',
          'password_err' => '',
        ];

        if (empty($data['email'])) { $data['email_err'] = 'Ingresa tu email.'; }
        if (empty($data['password'])) { $data['password_err'] = 'Ingresa tu contraseña.'; }

        if ($this->userModel->findUserByEmail($data['email'])) {
          // User Found
        } else {
          $data['email_err'] = 'Usuario no encontrado.';
        }

        if (empty($data['email_err']) && empty($data['password_err'])) {
          $loggedInUser = $this->userModel->login($data['email'], $data['password']);

          if ($loggedInUser) {
            $isForced = (isset($loggedInUser->require_password_change) && $loggedInUser->require_password_change == 1);
            $this->createUserSession($loggedInUser, $isForced);
          } else {
            $data['password_err'] = 'Contraseña incorrecta.';
            $this->view('users/login', $data);
          }
        } else {
          $this->view('users/login', $data);
        }
      } else {
        $data = [
          'email' => '', 'password' => '', 'email_err' => '', 'password_err' => ''
        ];
        $this->view('users/login', $data);
      }
    }

    public function createUserSession($user, $isForced = false){
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['is_logged_in'] = true;
        
        if ($isForced) {
            $_SESSION['require_password_change'] = true;
            flash('success_message', 'Debes cambiar tu contraseña temporal.', 'alert alert-warning text-center rounded-pill shadow-sm mt-3 mb-0');
            redirect('users/change_password');
        } else {
            unset($_SESSION['require_password_change']);
            redirect('pages/index');
        }
    }

    public function forgot_password() {
      if ($this->isLoggedIn()) {
        redirect('pages/index');
      }

      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $data = [
          'email' => trim($_POST['email']),
          'email_err' => ''
        ];

        if (empty($data['email'])) {
          $data['email_err'] = 'Ingresa tu correo electrónico.';
        } elseif (!$this->userModel->findUserByEmail($data['email'])) {
          $data['email_err'] = 'El correo no está registrado en el sistema.';
        }

        if (empty($data['email_err'])) {
          $user = $this->userModel->getUserByEmail($data['email']);
          $temp_pass = strtoupper(substr(md5(uniqid()), 0, 8));

          if ($this->userModel->updatePassword($user->id, $temp_pass)) {
             $this->userModel->setRequirePasswordChange($user->id, true);
             flash('success_message', 'Tu contraseña temporal es: <strong>' . $temp_pass . '</strong>. Inicia sesión para cambiarla.', 'alert alert-info mt-4 mb-0 rounded-pill text-center shadow-sm');
             redirect('users/login');
          } else {
             die('Algo salió mal al generar la contraseña temporal.');
          }
        } else {
          $this->view('users/forgot_password', $data);
        }
      } else {
        $data = [
          'email' => '',
          'email_err' => ''
        ];
        $this->view('users/forgot_password', $data);
      }
    }

    public function change_password() {
      if (!$this->isLoggedIn()) { redirect('users/login'); }

      $isForced = isset($_SESSION['require_password_change']) && $_SESSION['require_password_change'] === true;

      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
          $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
          $data = [
            'isForced' => $isForced,
            'current_password' => isset($_POST['current_password']) ? trim($_POST['current_password']) : '',
            'password' => trim($_POST['password']),
            'confirm_password' => trim($_POST['confirm_password']),
            'current_password_err' => '',
            'password_err' => '',
            'confirm_password_err' => ''
          ];

          if (!$isForced) {
            if (empty($data['current_password'])) {
              $data['current_password_err'] = 'Ingresa tu contraseña actual.';
            } else {
              $user = $this->userModel->getUserById($_SESSION['user_id']);
              if (!password_verify($data['current_password'], $user->password)) {
                 $data['current_password_err'] = 'Contraseña actual incorrecta.';
              }
            }
          }

          if (empty($data['password'])) {
            $data['password_err'] = 'Ingresa una nueva contraseña.';
          } elseif(strlen($data['password']) < 6) {
            $data['password_err'] = 'Mínimo 6 caracteres.';
          }

          if (empty($data['confirm_password'])) {
            $data['confirm_password_err'] = 'Confirma la nueva contraseña.';
          } else {
             if ($data['password'] != $data['confirm_password']) {
                $data['confirm_password_err'] = 'Las contraseñas no coinciden.';
             }
          }

          if (empty($data['current_password_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
              if ($this->userModel->updatePassword($_SESSION['user_id'], $data['password'])) {
                 $this->userModel->setRequirePasswordChange($_SESSION['user_id'], false);
                 unset($_SESSION['require_password_change']);
                 flash('success_message', 'Contraseña actualizada exitosamente.');
                 redirect('pages/index');
              } else {
                 die('Algo salió mal al actualizar tu contraseña.');
              }
          } else {
              $this->view('users/change_password', $data);
          }
      } else {
          $data = [
            'isForced' => $isForced,
            'current_password' => '', 'password' => '', 'confirm_password' => '',
            'current_password_err' => '', 'password_err' => '', 'confirm_password_err' => ''
          ];
          $this->view('users/change_password', $data);
      }
    }

    public function logout() {
      unset($_SESSION['require_password_change']);
      unset($_SESSION['user_id']);
      unset($_SESSION['user_email']);
      unset($_SESSION['user_name']);
      unset($_SESSION['is_logged_in']);
      session_destroy();
      redirect('users/login');
    }

    public function isLoggedIn() {
      if (isset($_SESSION['user_id'])) {
        return true;
      } else {
        return false;
      }
    }

    // Profile page simplified
    public function profile() {
      if (!$this->isLoggedIn()) { redirect('users/login'); }

      $user = $this->userModel->getUserById($_SESSION['user_id']);

      if($_SERVER['REQUEST_METHOD'] == 'POST'){
          $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
          $data = [
              'user' => $user,
              'name_err' => '',
              'email_err' => ''
          ];

          $newName = trim($_POST['name']);
          $newEmail = trim($_POST['email']);

          if(empty($newName)){ $data['name_err'] = 'Ingresa tu nombre'; }
          if(empty($newEmail)){ $data['email_err'] = 'Ingresa tu email'; }

          if(empty($data['name_err']) && empty($data['email_err'])){
              if($this->userModel->updateProfile($_SESSION['user_id'], $newName, $newEmail)){
                  $_SESSION['user_name'] = $newName;
                  flash('register_success', 'Perfil actualizado');
                  redirect('pages/index');
              } else {
                  die('Error actualizando perfil');
              }
          } else {
              $data['user']->name = $newName;
              $data['user']->email = $newEmail;
              $this->view('profile/index', $data);
          }
      } else {
          $this->view('profile/index', ['user' => $user, 'name_err' => '', 'email_err' => '']);
      }
    }
  }
?>
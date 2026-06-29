<?php require APPROOT . '/views/inc/header.php'; ?>

<div class="row mt-5 mb-5">
  <div class="col-md-8 col-lg-6 mx-auto">
    <div class="card auth-card shadow">
      <div class="auth-header border-register bg-light text-center p-4">
          <i class="fas fa-user-plus text-success" style="font-size: 3rem;"></i>
          <h2 class="font-weight-bold mb-0 mt-3">Crear una Cuenta</h2>
          <p class="text-muted mb-0 mt-2">Comienza a controlar tus finanzas personales</p>
      </div>
      <div class="card-body p-4 p-md-5">
        <form action="<?php echo URLROOT; ?>/users/register" method="post">
          <div class="row">
              <div class="col-md-6 form-group mb-4">
                  <label class="text-muted font-weight-bold"><i class="fas fa-user mr-1"></i> Nombre Completo:</label>
                  <input type="text" name="name" class="form-control form-control-custom focus-success <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['name']; ?>" placeholder="Ej: Juan Pérez">
                  <span class="invalid-feedback ml-2"><?php echo $data['name_err']; ?></span>
              </div> 
              <div class="col-md-6 form-group mb-4">
                  <label class="text-muted font-weight-bold"><i class="fas fa-envelope mr-1"></i> Correo Electrónico:</label>
                  <input type="email" name="email" class="form-control form-control-custom focus-success <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['email']; ?>" placeholder="ejemplo@correo.com">
                  <span class="invalid-feedback ml-2"><?php echo $data['email_err']; ?></span>
              </div>
          </div>
          
          <div class="row mt-2">
              <div class="col-md-6 form-group mb-4">
                  <label class="text-muted font-weight-bold"><i class="fas fa-lock mr-1"></i> Contraseña:</label>
                  <input type="password" name="password" class="form-control form-control-custom focus-success <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['password']; ?>" placeholder="Mínimo 6 caracteres">
                  <span class="invalid-feedback ml-2"><?php echo $data['password_err']; ?></span>
              </div>
              <div class="col-md-6 form-group mb-4">
                  <label class="text-muted font-weight-bold"><i class="fas fa-check-double mr-1"></i> Confirmar Contraseña:</label>
                  <input type="password" name="confirm_password" class="form-control form-control-custom focus-success <?php echo (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['confirm_password']; ?>" placeholder="Repite la contraseña">
                  <span class="invalid-feedback ml-2"><?php echo $data['confirm_password_err']; ?></span>
              </div>
          </div>

          <div class="form-group mt-4">
              <button type="submit" class="btn btn-success btn-block btn-custom py-3 mb-3 shadow-sm">
                  <i class="fas fa-user-check mr-1"></i> Registrarse en el Sistema
              </button>
              <a href="<?php echo URLROOT; ?>/users/login" class="btn btn-light btn-block btn-custom text-secondary">
                  ¿Ya estás registrado? <strong class="text-dark">Inicia Sesión aquí</strong>
              </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
  
<?php require APPROOT . '/views/inc/footer.php'; ?>
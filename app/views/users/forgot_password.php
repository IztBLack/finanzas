<?php require APPROOT . '/views/inc/header.php'; ?>

<div class="row mt-5 mb-5">
    <div class="col-md-6 col-lg-5 mx-auto">
      <div class="card auth-card">
        <div class="auth-header border-login">
            <i class="fas fa-unlock-alt auth-icon"></i>
            <h2 class="font-weight-bold mb-0">Recuperar Contraseña</h2>
            <p class="text-light opacity-50 mb-0 mt-2">Ingresa tu correo para restablecerla</p>
        </div>
        <div class="card-body p-4 p-md-5">
            <form action="<?php echo URLROOT; ?>/users/forgot_password" method="post">
              <div class="form-group mb-4">
                  <label class="text-muted font-weight-bold"><i class="fas fa-envelope mr-1"></i> Correo Electrónico:</label>
                  <input type="email" name="email" class="form-control form-control-custom <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['email']; ?>" placeholder="ejemplo@correo.com">
                  <span class="invalid-feedback ml-2"><?php echo $data['email_err']; ?></span>
              </div>    
              <div class="form-group mt-5">
                  <button type="submit" class="btn btn-primary btn-block btn-custom mb-3 shadow-sm">
                      <i class="fas fa-paper-plane mr-1"></i> Enviar
                  </button>
                  <a href="<?php echo URLROOT; ?>/users/login" class="btn btn-light btn-block btn-custom text-secondary">
                      <i class="fas fa-arrow-left mr-1"></i> Volver al Login
                  </a>
              </div>
            </form>
        </div>
      </div>
    </div>
</div>

<?php require APPROOT . '/views/inc/footer.php'; ?>

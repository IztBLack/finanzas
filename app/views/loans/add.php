<?php require APPROOT . '/views/inc/header.php'; ?>
  <a href="<?php echo URLROOT; ?>/loans" class="btn btn-light"><i class="fa fa-backward"></i> Regresar</a>
  <div class="card card-body bg-light mt-4 shadow-sm">
    <h2>Registrar Préstamo</h2>
    <p>Registra dinero que hayas prestado a otras personas</p>
    
    <?php if(empty($data['accounts'])) : ?>
        <div class="alert alert-warning">
            <strong>Aviso:</strong> Necesitas tener al menos una <a href="<?php echo URLROOT; ?>/accounts" class="alert-link">Cuenta</a> creada desde donde saldrá el dinero.
        </div>
    <?php endif; ?>

    <form action="<?php echo URLROOT; ?>/loans/add" method="post">
      <div class="row">
          <div class="col-md-6">
              <div class="form-group">
                <label for="debtor_name">Nombre de a quién le prestas: <sup>*</sup></label>
                <input type="text" name="debtor_name" class="form-control form-control-lg <?php echo (!empty($data['debtor_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['debtor_name']; ?>" placeholder="Ej: Juan Pérez">
                <span class="invalid-feedback"><?php echo $data['debtor_err']; ?></span>
              </div>
          </div>
          <div class="col-md-6">
              <div class="form-group">
                <label for="amount">Monto ($): <sup>*</sup></label>
                <input type="number" step="0.01" name="amount" class="form-control form-control-lg <?php echo (!empty($data['amount_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['amount']; ?>">
                <span class="invalid-feedback"><?php echo $data['amount_err']; ?></span>
              </div>
          </div>
      </div>

      <div class="row">
          <div class="col-md-6">
              <div class="form-group">
                <label for="account_id">¿De qué cuenta salió el dinero?: <sup>*</sup></label>
                <select name="account_id" class="form-control form-control-lg <?php echo (!empty($data['account_err'])) ? 'is-invalid' : ''; ?>">
                    <option value="">Selecciona una cuenta...</option>
                    <?php foreach($data['accounts'] as $account) : ?>
                        <option value="<?php echo $account->id; ?>" <?php echo ($data['account_id'] == $account->id) ? 'selected' : ''; ?>><?php echo $account->name; ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="invalid-feedback"><?php echo $data['account_err']; ?></span>
              </div>
          </div>
          <div class="col-md-6">
              <div class="form-group">
                <label for="loan_date">Fecha del Préstamo: <sup>*</sup></label>
                <input type="date" name="loan_date" class="form-control form-control-lg <?php echo (!empty($data['date_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['loan_date']; ?>">
                <span class="invalid-feedback"><?php echo $data['date_err']; ?></span>
               </div>
          </div>
      </div>

      <div class="row">
          <div class="col-md-6">
              <div class="form-group">
                <label for="due_date">Fecha Límite de Pago (Opcional):</label>
                <input type="date" name="due_date" class="form-control form-control-lg" value="<?php echo $data['due_date']; ?>">
              </div>
          </div>
          <div class="col-md-6">
              <div class="form-group">
                <label for="description">Descripción / Motivo (Opcional):</label>
                <textarea name="description" class="form-control form-control-lg"><?php echo $data['description']; ?></textarea>
              </div>
          </div>
      </div>
      
      <input type="submit" class="btn btn-success btn-block" value="Guardar Préstamo" <?php echo empty($data['accounts']) ? 'disabled' : ''; ?>>
    </form>
  </div>
<?php require APPROOT . '/views/inc/footer.php'; ?>

<?php require APPROOT . '/views/inc/header.php'; ?>
  <a href="<?php echo URLROOT; ?>/loans" class="btn btn-light mb-3"><i class="fa fa-backward"></i> Regresar</a>

  <div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h4 class="m-0 font-weight-bold text-dark"><i class="fas fa-edit mr-2"></i> Editar Préstamo</h4>
    </div>
    <div class="card-body p-4">

    <form action="<?php echo URLROOT; ?>/loans/edit/<?php echo $data['id']; ?>" method="post">
      <div class="row">
          <div class="col-md-6 form-group mb-4">
              <label for="debtor_name" class="form-section-label">Nombre de a quién le prestas <sup>*</sup></label>
              <input type="text" name="debtor_name" class="form-control <?php echo (!empty($data['debtor_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['debtor_name']; ?>">
              <span class="invalid-feedback"><?php echo $data['debtor_err']; ?></span>
          </div>
          <div class="col-md-6 form-group mb-4">
              <label for="amount" class="form-section-label">Monto ($) <sup>*</sup></label>
              <input type="number" step="0.01" name="amount" class="form-control <?php echo (!empty($data['amount_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['amount']; ?>">
              <span class="invalid-feedback"><?php echo $data['amount_err']; ?></span>
          </div>
      </div>

      <div class="row">
          <div class="col-md-6 form-group mb-4">
              <label for="account_id" class="form-section-label">¿De qué cuenta salió el dinero? <sup>*</sup></label>
              <select name="account_id" class="form-control <?php echo (!empty($data['account_err'])) ? 'is-invalid' : ''; ?>">
                  <?php foreach($data['accounts'] as $account) : ?>
                      <option value="<?php echo $account->id; ?>" <?php echo ($data['account_id'] == $account->id) ? 'selected' : ''; ?>><?php echo $account->name; ?></option>
                  <?php endforeach; ?>
              </select>
              <span class="invalid-feedback"><?php echo $data['account_err']; ?></span>
          </div>
          <div class="col-md-6 form-group mb-4">
              <label for="loan_date" class="form-section-label">Fecha del Préstamo <sup>*</sup></label>
              <input type="date" name="loan_date" class="form-control <?php echo (!empty($data['date_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['loan_date']; ?>">
              <span class="invalid-feedback"><?php echo $data['date_err']; ?></span>
          </div>
      </div>

      <div class="row">
          <div class="col-md-6 form-group mb-4">
              <label for="due_date" class="form-section-label">Fecha Límite de Pago (Opcional)</label>
              <input type="date" name="due_date" class="form-control" value="<?php echo $data['due_date']; ?>">
          </div>
          <div class="col-md-6 form-group mb-4">
              <label for="description" class="form-section-label">Descripción / Motivo (Opcional)</label>
              <textarea name="description" class="form-control"><?php echo $data['description']; ?></textarea>
          </div>
      </div>

      <hr>
      <button type="submit" class="btn btn-dark btn-block px-4 py-2 font-weight-bold"><i class="fas fa-save mr-2"></i> Actualizar Préstamo</button>
    </form>
    </div>
  </div>
<?php require APPROOT . '/views/inc/footer.php'; ?>

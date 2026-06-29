<?php require APPROOT . '/views/inc/header.php'; ?>
  <a href="<?php echo URLROOT; ?>/transactions" class="btn btn-light mb-3"><i class="fa fa-backward"></i> Regresar</a>

  <div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h4 class="m-0 font-weight-bold text-primary"><i class="fas fa-plus-circle mr-2"></i> Añadir Transacción</h4>
        <small class="text-muted">Registra un nuevo ingreso o gasto</small>
    </div>
    <div class="card-body p-4">

    <?php if(empty($data['accounts']) || empty($data['categories'])) : ?>
        <div class="alert alert-warning">
            <strong>Aviso:</strong> Necesitas tener al menos una <a href="<?php echo URLROOT; ?>/accounts" class="alert-link">Cuenta</a> y una <a href="<?php echo URLROOT; ?>/categories" class="alert-link">Categoría</a> creadas para registrar transacciones.
        </div>
    <?php endif; ?>

    <form action="<?php echo URLROOT; ?>/transactions/add" method="post">
      <div class="row">
          <div class="col-md-6 form-group mb-4">
              <label for="type" class="form-section-label">Tipo de Operación <sup>*</sup></label>
              <select name="type" class="form-control <?php echo (!empty($data['type_err'])) ? 'is-invalid' : ''; ?>">
                  <option value="">Selecciona...</option>
                  <option value="expense" <?php echo ($data['type'] == 'expense') ? 'selected' : ''; ?>>Gasto</option>
                  <option value="income" <?php echo ($data['type'] == 'income') ? 'selected' : ''; ?>>Ingreso</option>
              </select>
              <span class="invalid-feedback"><?php echo $data['type_err']; ?></span>
          </div>
          <div class="col-md-6 form-group mb-4">
              <label for="amount" class="form-section-label">Monto ($) <sup>*</sup></label>
              <input type="number" step="0.01" name="amount" class="form-control <?php echo (!empty($data['amount_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['amount']; ?>">
              <span class="invalid-feedback"><?php echo $data['amount_err']; ?></span>
          </div>
      </div>

      <div class="row">
          <div class="col-md-6 form-group mb-4">
              <label for="account_id" class="form-section-label">Cuenta <sup>*</sup></label>
              <select name="account_id" class="form-control <?php echo (!empty($data['account_err'])) ? 'is-invalid' : ''; ?>">
                  <option value="">Selecciona una cuenta...</option>
                  <?php foreach($data['accounts'] as $account) : ?>
                      <option value="<?php echo $account->id; ?>" <?php echo ($data['account_id'] == $account->id) ? 'selected' : ''; ?>><?php echo $account->name; ?></option>
                  <?php endforeach; ?>
              </select>
              <span class="invalid-feedback"><?php echo $data['account_err']; ?></span>
          </div>
          <div class="col-md-6 form-group mb-4">
              <label for="category_id" class="form-section-label">Categoría <sup>*</sup></label>
               <select name="category_id" class="form-control <?php echo (!empty($data['category_err'])) ? 'is-invalid' : ''; ?>">
                  <option value="">Selecciona una categoría...</option>
                  <?php foreach($data['categories'] as $category) : ?>
                      <option value="<?php echo $category->id; ?>" <?php echo ($data['category_id'] == $category->id) ? 'selected' : ''; ?>><?php echo $category->name; ?> (<?php echo $category->type == 'income' ? 'Ingreso' : 'Gasto'; ?>)</option>
                  <?php endforeach; ?>
              </select>
              <span class="invalid-feedback"><?php echo $data['category_err']; ?></span>
          </div>
      </div>

      <div class="form-group mb-4">
        <label for="transaction_date" class="form-section-label">Fecha <sup>*</sup></label>
        <input type="date" name="transaction_date" class="form-control <?php echo (!empty($data['date_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['transaction_date']; ?>">
        <span class="invalid-feedback"><?php echo $data['date_err']; ?></span>
      </div>

      <div class="form-group mb-4">
        <label for="description" class="form-section-label">Descripción (Opcional)</label>
        <textarea name="description" class="form-control"><?php echo $data['description']; ?></textarea>
      </div>

      <hr>
      <button type="submit" class="btn btn-success btn-block px-4 py-2 font-weight-bold" <?php echo (empty($data['accounts']) || empty($data['categories'])) ? 'disabled' : ''; ?>><i class="fas fa-save mr-2"></i> Guardar Transacción</button>
    </form>
    </div>
  </div>
<?php require APPROOT . '/views/inc/footer.php'; ?>

<?php require APPROOT . '/views/inc/header.php'; ?>
  <a href="<?php echo URLROOT; ?>/transactions" class="btn btn-light mb-3"><i class="fa fa-backward"></i> Regresar</a>

  <div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h4 class="m-0 font-weight-bold text-dark"><i class="fas fa-edit mr-2"></i> Editar Transacción</h4>
    </div>
    <div class="card-body p-4">

    <form action="<?php echo URLROOT; ?>/transactions/edit/<?php echo $data['id']; ?>" method="post">
      <div class="row">
          <div class="col-md-6 form-group mb-4">
              <label for="type" class="form-section-label">Tipo de Operación <sup>*</sup></label>
              <select name="type" class="form-control <?php echo (!empty($data['type_err'])) ? 'is-invalid' : ''; ?>">
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
                  <?php foreach($data['accounts'] as $account) : ?>
                      <option value="<?php echo $account->id; ?>" <?php echo ($data['account_id'] == $account->id) ? 'selected' : ''; ?>><?php echo $account->name; ?></option>
                  <?php endforeach; ?>
              </select>
              <span class="invalid-feedback"><?php echo $data['account_err']; ?></span>
          </div>
          <div class="col-md-6 form-group mb-4">
              <label for="category_id" class="form-section-label">Categoría <sup>*</sup></label>
               <select name="category_id" class="form-control <?php echo (!empty($data['category_err'])) ? 'is-invalid' : ''; ?>">
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
      <button type="submit" class="btn btn-dark btn-block px-4 py-2 font-weight-bold"><i class="fas fa-save mr-2"></i> Actualizar Transacción</button>
    </form>
    </div>
  </div>
<?php require APPROOT . '/views/inc/footer.php'; ?>

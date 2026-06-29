<?php require APPROOT . '/views/inc/header.php'; ?>
  <a href="<?php echo URLROOT; ?>/transactions" class="btn btn-light"><i class="fa fa-backward"></i> Regresar</a>
  <div class="card card-body bg-light mt-4 shadow-sm">
    <h2>Editar Transacción</h2>
    
    <form action="<?php echo URLROOT; ?>/transactions/edit/<?php echo $data['id']; ?>" method="post">
      <div class="row">
          <div class="col-md-6">
              <div class="form-group">
                <label for="type">Tipo de Operación: <sup>*</sup></label>
                <select name="type" class="form-control form-control-lg <?php echo (!empty($data['type_err'])) ? 'is-invalid' : ''; ?>">
                    <option value="expense" <?php echo ($data['type'] == 'expense') ? 'selected' : ''; ?>>Gasto</option>
                    <option value="income" <?php echo ($data['type'] == 'income') ? 'selected' : ''; ?>>Ingreso</option>
                </select>
                <span class="invalid-feedback"><?php echo $data['type_err']; ?></span>
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
                <label for="account_id">Cuenta: <sup>*</sup></label>
                <select name="account_id" class="form-control form-control-lg <?php echo (!empty($data['account_err'])) ? 'is-invalid' : ''; ?>">
                    <?php foreach($data['accounts'] as $account) : ?>
                        <option value="<?php echo $account->id; ?>" <?php echo ($data['account_id'] == $account->id) ? 'selected' : ''; ?>><?php echo $account->name; ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="invalid-feedback"><?php echo $data['account_err']; ?></span>
              </div>
          </div>
          <div class="col-md-6">
              <div class="form-group">
                <label for="category_id">Categoría: <sup>*</sup></label>
                 <select name="category_id" class="form-control form-control-lg <?php echo (!empty($data['category_err'])) ? 'is-invalid' : ''; ?>">
                    <?php foreach($data['categories'] as $category) : ?>
                        <option value="<?php echo $category->id; ?>" <?php echo ($data['category_id'] == $category->id) ? 'selected' : ''; ?>><?php echo $category->name; ?> (<?php echo $category->type == 'income' ? 'Ingreso' : 'Gasto'; ?>)</option>
                    <?php endforeach; ?>
                </select>
                <span class="invalid-feedback"><?php echo $data['category_err']; ?></span>
              </div>
          </div>
      </div>

      <div class="form-group">
        <label for="transaction_date">Fecha: <sup>*</sup></label>
        <input type="date" name="transaction_date" class="form-control form-control-lg <?php echo (!empty($data['date_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['transaction_date']; ?>">
        <span class="invalid-feedback"><?php echo $data['date_err']; ?></span>
      </div>

      <div class="form-group">
        <label for="description">Descripción (Opcional):</label>
        <textarea name="description" class="form-control form-control-lg"><?php echo $data['description']; ?></textarea>
      </div>
      
      <input type="submit" class="btn btn-success btn-block" value="Actualizar Transacción">
    </form>
  </div>
<?php require APPROOT . '/views/inc/footer.php'; ?>

<?php require APPROOT . '/views/inc/header.php'; ?>
  <a href="<?php echo URLROOT; ?>/accounts" class="btn btn-light mb-3"><i class="fa fa-backward"></i> Regresar</a>

  <div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h4 class="m-0 font-weight-bold text-dark"><i class="fas fa-edit mr-2"></i> Editar Cuenta</h4>
    </div>
    <div class="card-body p-4">
    <form action="<?php echo URLROOT; ?>/accounts/edit/<?php echo $data['id']; ?>" method="post">
      <div class="row">
        <div class="col-md-6 form-group mb-4">
          <label for="type" class="form-section-label">Tipo de Cuenta <sup>*</sup></label>
          <select name="type" id="accountType" class="form-control <?php echo (!empty($data['type_err'])) ? 'is-invalid' : ''; ?>">
              <option value="debit" <?php echo ($data['type'] == 'debit') ? 'selected' : ''; ?>>Débito / Efectivo / Ahorro</option>
              <option value="credit" <?php echo ($data['type'] == 'credit') ? 'selected' : ''; ?>>Tarjeta de Crédito</option>
          </select>
          <span class="invalid-feedback"><?php echo $data['type_err']; ?></span>
        </div>
        <div class="col-md-6 form-group mb-4">
          <label for="name" class="form-section-label">Nombre de la cuenta <sup>*</sup></label>
          <input type="text" name="name" class="form-control <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['name']; ?>">
          <span class="invalid-feedback"><?php echo $data['name_err']; ?></span>
        </div>
      </div>

      <div class="form-group mb-4">
        <label for="initial_balance" class="form-section-label">Balance Inicial o Deuda Actual ($) <sup>*</sup></label>
        <input type="number" step="0.01" name="initial_balance" class="form-control <?php echo (!empty($data['balance_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['initial_balance']; ?>">
        <span class="invalid-feedback"><?php echo $data['balance_err']; ?></span>
      </div>

      <!-- Campos de Crédito -->
      <div id="creditFields" class="p-3 mb-3 rounded" style="background-color:#f8f9fc; display: <?php echo ($data['type'] == 'credit') ? 'block' : 'none'; ?>;">
        <div class="form-group mb-4">
            <label for="credit_limit" class="form-section-label">Límite de Crédito Total ($) <sup>*</sup></label>
            <input type="number" step="0.01" name="credit_limit" class="form-control <?php echo (!empty($data['type_err']) && empty($data['credit_limit'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['credit_limit']; ?>">
        </div>
        <div class="row">
            <div class="col-md-6 form-group mb-md-0">
                <label for="cutoff_date" class="form-section-label">Día de Corte del mes <sup>*</sup></label>
                <input type="number" min="1" max="31" name="cutoff_date" class="form-control <?php echo (!empty($data['type_err']) && empty($data['cutoff_date'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['cutoff_date']; ?>">
            </div>
            <div class="col-md-6 form-group mb-0">
                <label for="payment_date" class="form-section-label">Día límite de pago <sup>*</sup></label>
                <input type="number" min="1" max="31" name="payment_date" class="form-control <?php echo (!empty($data['type_err']) && empty($data['payment_date'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['payment_date']; ?>">
            </div>
        </div>
      </div>

      <hr>
      <button type="submit" class="btn btn-dark px-4 py-2 font-weight-bold"><i class="fas fa-save mr-2"></i> Actualizar Cuenta</button>
    </form>
    </div>
  </div>
  <script>
    document.getElementById('accountType').addEventListener('change', function() {
        var creditFields = document.getElementById('creditFields');
        if(this.value === 'credit') {
            creditFields.style.display = 'block';
        } else {
            creditFields.style.display = 'none';
        }
    });
  </script>
<?php require APPROOT . '/views/inc/footer.php'; ?>

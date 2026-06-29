<?php require APPROOT . '/views/inc/header.php'; ?>
  <a href="<?php echo URLROOT; ?>/accounts" class="btn btn-light"><i class="fa fa-backward"></i> Regresar</a>
  <div class="card card-body bg-light mt-4 shadow-sm">
    <h2>Añadir Cuenta</h2>
    <p>Crea una cuenta o cartera (Ej: Efectivo, Tarjeta BBVA)</p>
    <form action="<?php echo URLROOT; ?>/accounts/add" method="post">
      <div class="form-group">
        <label for="type">Tipo de Cuenta: <sup>*</sup></label>
        <select name="type" id="accountType" class="form-control form-control-lg <?php echo (!empty($data['type_err'])) ? 'is-invalid' : ''; ?>">
            <option value="debit" <?php echo ($data['type'] == 'debit') ? 'selected' : ''; ?>>Débito / Efectivo / Ahorro</option>
            <option value="credit" <?php echo ($data['type'] == 'credit') ? 'selected' : ''; ?>>Tarjeta de Crédito</option>
        </select>
        <span class="invalid-feedback"><?php echo $data['type_err']; ?></span>
      </div>
      <div class="form-group">
        <label for="name">Nombre de la cuenta: <sup>*</sup></label>
        <input type="text" name="name" class="form-control form-control-lg <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['name']; ?>" placeholder="Ej: Tarjeta de Débito Citibanamex">
        <span class="invalid-feedback"><?php echo $data['name_err']; ?></span>
      </div>
      <div class="form-group" id="balanceField">
        <label for="initial_balance">Balance Inicial O Deuda Actual ($): <sup>*</sup></label>
        <input type="number" step="0.01" name="initial_balance" class="form-control form-control-lg <?php echo (!empty($data['balance_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['initial_balance'] !== '' ? $data['initial_balance'] : '0.00'; ?>">
        <small class="form-text text-muted">Si es Crédito, ingresa cuánto debes actualmente (en positivo).</small>
        <span class="invalid-feedback"><?php echo $data['balance_err']; ?></span>
      </div>
      
      <!-- Campos de Crédito -->
      <div id="creditFields" style="display: <?php echo ($data['type'] == 'credit') ? 'block' : 'none'; ?>;">
        <div class="form-group">
            <label for="credit_limit">Límite de Crédito Total ($): <sup>*</sup></label>
            <input type="number" step="0.01" name="credit_limit" class="form-control form-control-lg <?php echo (!empty($data['type_err']) && empty($data['credit_limit'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['credit_limit']; ?>">
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label for="cutoff_date">Día de Corte del mes: <sup>*</sup></label>
                <input type="number" min="1" max="31" name="cutoff_date" class="form-control form-control-lg <?php echo (!empty($data['type_err']) && empty($data['cutoff_date'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['cutoff_date']; ?>" placeholder="Ej: 15">
            </div>
            <div class="col-md-6 form-group">
                <label for="payment_date">Día límite de pago: <sup>*</sup></label>
                <input type="number" min="1" max="31" name="payment_date" class="form-control form-control-lg <?php echo (!empty($data['type_err']) && empty($data['payment_date'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['payment_date']; ?>" placeholder="Ej: 5">
            </div>
        </div>
      </div>

      <input type="submit" class="btn btn-success mt-2" value="Guardar Cuenta">
    </form>
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

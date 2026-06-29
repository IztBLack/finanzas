<?php require APPROOT . '/views/inc/header.php'; ?>
  <a href="<?php echo URLROOT; ?>/subscriptions" class="btn btn-light mb-3"><i class="fa fa-backward"></i> Regresar</a>
  
  <div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h4 class="m-0 font-weight-bold text-primary"><i class="fas fa-plus-circle mr-2"></i> Añadir Suscripción</h4>
    </div>
    <div class="card-body p-4">
        <form action="<?php echo URLROOT; ?>/subscriptions/add" method="post">
        
        <div class="row">
            <div class="col-md-6 form-group mb-4">
                <label class="font-weight-bold text-muted small text-uppercase">Nombre del Servicio: <sup>*</sup></label>
                <input type="text" name="name" class="form-control <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['name']; ?>" placeholder="Ej: Netflix, Spotify, Gimnasio">
                <span class="invalid-feedback"><?php echo $data['name_err']; ?></span>
            </div>
            
            <div class="col-md-6 form-group mb-4">
                <label class="font-weight-bold text-muted small text-uppercase">Costo ($): <sup>*</sup></label>
                <input type="number" step="0.01" name="amount" class="form-control <?php echo (!empty($data['amount_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['amount']; ?>" placeholder="0.00">
                <span class="invalid-feedback"><?php echo $data['amount_err']; ?></span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-4">
                <label class="font-weight-bold text-muted small text-uppercase">Cuenta de Pago: <sup>*</sup></label>
                <select name="account_id" class="form-control <?php echo (!empty($data['account_err'])) ? 'is-invalid' : ''; ?>">
                    <option value="" disabled selected>-- Selecciona con qué pagas --</option>
                    <?php foreach($data['accounts'] as $account): ?>
                        <option value="<?php echo $account->id; ?>" <?php echo $data['account_id'] == $account->id ? 'selected' : ''; ?>>
                            <?php echo $account->name; ?> (<?php echo $account->type == 'credit' ? 'Crédito' : 'Débito'; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="invalid-feedback"><?php echo $data['account_err']; ?></span>
            </div>

            <div class="col-md-6 form-group mb-4">
                <label class="font-weight-bold text-muted small text-uppercase">Ciclo de Facturación: <sup>*</sup></label>
                <select name="billing_cycle" class="form-control">
                    <option value="monthly" <?php echo $data['billing_cycle'] == 'monthly' ? 'selected' : ''; ?>>Mensual</option>
                    <option value="yearly" <?php echo $data['billing_cycle'] == 'yearly' ? 'selected' : ''; ?>>Anual</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-4">
                <label class="font-weight-bold text-muted small text-uppercase">Día de Cobro (1-31): <sup>*</sup></label>
                <input type="number" min="1" max="31" name="billing_day" class="form-control <?php echo (!empty($data['day_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['billing_day'] ?? ''; ?>" placeholder="Ej: 15">
                <span class="invalid-feedback"><?php echo $data['day_err'] ?? ''; ?></span>
            </div>
        </div>

        <hr>
        <button type="submit" class="btn btn-success px-4 py-2 font-weight-bold"><i class="fas fa-save mr-2"></i> Guardar Suscripción</button>
        </form>
    </div>
  </div>
<?php require APPROOT . '/views/inc/footer.php'; ?>

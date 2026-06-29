<?php require APPROOT . '/views/inc/header.php'; ?>
<div class="row mb-3">
  <div class="col-md-6">
    <h1>Mis Cuentas</h1>
  </div>
  <div class="col-md-6">
    <a href="<?php echo URLROOT; ?>/accounts/add" class="btn btn-primary pull-right">
      <i class="fas fa-plus"></i> Añadir Cuenta
    </a>
  </div>
</div>
<?php flash('account_message'); ?>
<div class="row">
  <?php foreach ($data['accounts'] as $account): ?>
    <div class="col-md-4 mb-3">
      <div class="card card-body shadow-sm">
        <h4 class="card-title">
          <?php if($account->type == 'credit'): ?>
            <i class="fas fa-credit-card text-primary"></i>
          <?php else: ?>
            <i class="fas fa-wallet text-secondary"></i>
          <?php endif; ?>
          <?php echo $account->name; ?>
        </h4>
        
        <?php 
          if($account->type == 'credit'): 
            // Para tarjetas de crédito, initial_balance es la deuda inicial.
            // Los gastos aumentan la deuda, los ingresos (pagos a la tarjeta) la reducen.
            $current_debt = $account->initial_balance + $account->total_expense - $account->total_income;
            if($current_debt < 0) $current_debt = 0; // Evitar deuda negativa a menos que tengan saldo a favor, pero lo dejamos así para simplificar visualmente
            $available = ($account->credit_limit ?? 0) - $current_debt;
        ?>
            <div class="bg-light p-2 mb-2 rounded border">
              Límite: <strong>$<?php echo number_format($account->credit_limit ?? 0, 2); ?></strong><br>
              Deuda: <strong class="text-danger">$<?php echo number_format($current_debt, 2); ?></strong><br>
              <span class="text-success">Disponible: <strong>$<?php echo number_format($available, 2); ?></strong></span>
            </div>
            <div class="text-muted small mb-3">
              <i class="far fa-calendar-alt"></i> Corte: Día <strong><?php echo $account->cutoff_date; ?></strong> | Pago: Día <strong><?php echo $account->payment_date; ?></strong>
            </div>
        <?php else: 
            // Para cuentas de débito/efectivo, initial_balance es dinero a favor.
            // Los ingresos aumentan el balance, los gastos lo reducen.
            $current_balance = $account->initial_balance + $account->total_income - $account->total_expense;
        ?>
            <div class="bg-light p-3 mb-3 rounded border text-center">
              Balance Total: <br>
              <strong class="<?php echo $current_balance >= 0 ? 'text-success' : 'text-danger'; ?> h4">$<?php echo number_format($current_balance, 2); ?></strong>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between mt-auto">
          <a href="<?php echo URLROOT; ?>/accounts/edit/<?php echo $account->id; ?>" class="btn btn-dark btn-sm"><i
              class="fas fa-edit"></i> Editar</a>
          <form action="<?php echo URLROOT; ?>/accounts/delete/<?php echo $account->id; ?>" method="post">
            <button type="submit" class="btn btn-danger btn-sm"
              onclick="return confirm('¿Estás seguro de eliminar esta cuenta? Se eliminarán también las transacciones asociadas.');">
              <i class="fas fa-trash"></i> Eliminar
            </button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (empty($data['accounts'])): ?>
    <div class="col-12 text-center mt-4">
      <p class="lead text-muted">Aún no tienes cuentas creadas. ¡Empieza creando una!</p>
    </div>
  <?php endif; ?>
</div>
<?php require APPROOT . '/views/inc/footer.php'; ?>
<?php require APPROOT . '/views/inc/header.php'; ?>
<div class="list-header">
  <div>
    <h1><i class="fas fa-wallet text-primary mr-2"></i>Mis Cuentas</h1>
    <p class="text-muted mb-0">Tarjetas, efectivo y cuentas de ahorro en un solo lugar.</p>
  </div>
  <div class="list-header-action">
    <a href="<?php echo URLROOT; ?>/accounts/add" class="btn btn-primary shadow-sm">
      <i class="fas fa-plus mr-1"></i> Añadir Cuenta
    </a>
  </div>
</div>
<?php flash('account_message'); ?>
<div class="row">
  <?php foreach ($data['accounts'] as $account): ?>
    <div class="col-md-4 mb-4">
      <div class="card finance-card">
        <div class="card-body d-flex flex-column">
          <div class="finance-card-header mb-3">
            <div class="d-flex align-items-center">
              <span class="finance-icon-circle <?php echo $account->type == 'credit' ? 'bg-credit' : 'bg-debit'; ?> mr-3">
                <i class="fas <?php echo $account->type == 'credit' ? 'fa-credit-card' : 'fa-wallet'; ?>"></i>
              </span>
              <div>
                <h5 class="card-title font-weight-bold mb-0"><?php echo $account->name; ?></h5>
                <small class="text-muted text-uppercase"><?php echo $account->type == 'credit' ? 'Tarjeta de Crédito' : 'Débito / Efectivo'; ?></small>
              </div>
            </div>
          </div>

          <?php
            if ($account->type == 'credit'):
              // Para tarjetas de crédito, initial_balance es la deuda inicial.
              // Los gastos aumentan la deuda, los ingresos (pagos a la tarjeta) la reducen.
              $current_debt = $account->initial_balance + $account->total_expense - $account->total_income;
              if ($current_debt < 0) $current_debt = 0; // Evitar deuda negativa a menos que tengan saldo a favor, pero lo dejamos así para simplificar visualmente
              $available = ($account->credit_limit ?? 0) - $current_debt;
          ?>
              <div class="finance-stat-box mb-3">
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted small font-weight-bold text-uppercase">Límite</span>
                  <span class="font-weight-bold">$<?php echo number_format($account->credit_limit ?? 0, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted small font-weight-bold text-uppercase">Deuda</span>
                  <span class="font-weight-bold text-danger">$<?php echo number_format($current_debt, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted small font-weight-bold text-uppercase">Disponible</span>
                  <span class="font-weight-bold text-success">$<?php echo number_format($available, 2); ?></span>
                </div>
              </div>
              <div class="text-muted small mb-3">
                <i class="far fa-calendar-alt mr-1"></i> Corte: Día <strong><?php echo $account->cutoff_date; ?></strong> &middot; Pago: Día <strong><?php echo $account->payment_date; ?></strong>
              </div>
          <?php else:
              // Para cuentas de débito/efectivo, initial_balance es dinero a favor.
              // Los ingresos aumentan el balance, los gastos lo reducen.
              $current_balance = $account->initial_balance + $account->total_income - $account->total_expense;
          ?>
              <div class="finance-stat-box mb-3 text-center">
                <span class="text-muted small font-weight-bold text-uppercase d-block mb-1">Balance Total</span>
                <strong class="<?php echo $current_balance >= 0 ? 'text-success' : 'text-danger'; ?> h3 mb-0">$<?php echo number_format($current_balance, 2); ?></strong>
              </div>
          <?php endif; ?>

          <div class="d-flex justify-content-between mt-auto pt-2 border-top">
            <a href="<?php echo URLROOT; ?>/accounts/edit/<?php echo $account->id; ?>" class="btn btn-outline-dark btn-sm">
              <i class="fas fa-edit"></i> Editar
            </a>
            <form action="<?php echo URLROOT; ?>/accounts/delete/<?php echo $account->id; ?>" method="post" class="m-0">
              <button type="submit" class="btn btn-outline-danger btn-sm"
                onclick="return confirm('¿Estás seguro de eliminar esta cuenta? Se eliminarán también las transacciones asociadas.');">
                <i class="fas fa-trash"></i> Eliminar
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (empty($data['accounts'])): ?>
    <div class="col-12">
      <div class="empty-state">
        <i class="fas fa-wallet"></i>
        <h5>Aún no tienes cuentas creadas</h5>
        <p class="mb-3">Registra tu efectivo, débito o tarjetas de crédito para empezar a llevar el control.</p>
        <a href="<?php echo URLROOT; ?>/accounts/add" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Crear mi primera cuenta</a>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php require APPROOT . '/views/inc/footer.php'; ?>
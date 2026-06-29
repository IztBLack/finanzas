<?php require APPROOT . '/views/inc/header.php'; ?>
<div class="list-header">
  <div>
    <h1><i class="fas fa-exchange-alt text-primary mr-2"></i>Mis Transacciones</h1>
    <p class="text-muted mb-0">Historial completo de tus ingresos y gastos.</p>
  </div>
  <div class="list-header-action">
    <a href="<?php echo URLROOT; ?>/transactions/add" class="btn btn-primary shadow-sm">
      <i class="fas fa-plus mr-1"></i> Añadir Transacción
    </a>
  </div>
</div>
<?php flash('transaction_message'); ?>

<div class="card shadow-sm border-0">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-finance mb-0">
        <thead class="bg-light">
          <tr>
            <th class="pl-3">Fecha</th>
            <th>Descripción</th>
            <th>Categoría</th>
            <th>Cuenta</th>
            <th>Monto</th>
            <th class="pr-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data['transactions'] as $transaction): ?>
            <tr>
              <td class="pl-3"><?php echo date('d/m/Y', strtotime($transaction->transaction_date)); ?></td>
              <td><?php echo $transaction->description ?: '<span class="text-muted">Sin descripción</span>'; ?></td>
              <td><span class="badge <?php echo $transaction->type == 'income' ? 'badge-soft-income' : 'badge-soft-expense'; ?>"><?php echo $transaction->category_name; ?></span></td>
              <td><i class="fas fa-wallet text-secondary mr-1"></i> <?php echo $transaction->account_name; ?></td>
              <td class="font-weight-bold <?php echo $transaction->type == 'income' ? 'text-success' : 'text-danger'; ?>">
                <?php echo $transaction->type == 'income' ? '+' : '-'; ?>$<?php echo number_format($transaction->amount, 2); ?>
              </td>
              <td class="pr-3">
                <a href="<?php echo URLROOT; ?>/transactions/edit/<?php echo $transaction->id; ?>"
                  class="btn btn-sm btn-outline-dark"><i class="fas fa-edit"></i></a>
                <form action="<?php echo URLROOT; ?>/transactions/delete/<?php echo $transaction->id; ?>" method="post"
                  class="d-inline">
                  <button type="submit" class="btn btn-sm btn-outline-danger"
                    onclick="return confirm('¿Estás seguro de eliminar esta transacción?');"><i
                      class="fas fa-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($data['transactions'])): ?>
            <tr>
              <td colspan="6" class="p-0">
                <div class="empty-state">
                  <i class="fas fa-exchange-alt"></i>
                  <h5>Aún no hay transacciones registradas</h5>
                  <p class="mb-3">Registra tu primer ingreso o gasto para comenzar a llevar el control.</p>
                  <a href="<?php echo URLROOT; ?>/transactions/add" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Añadir Transacción</a>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require APPROOT . '/views/inc/footer.php'; ?>
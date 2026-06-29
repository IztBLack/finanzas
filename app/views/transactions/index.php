<?php require APPROOT . '/views/inc/header.php'; ?>
<div class="row mb-3">
  <div class="col-md-6">
    <h1>Mis Transacciones</h1>
  </div>
  <div class="col-md-6">
    <a href="<?php echo URLROOT; ?>/transactions/add" class="btn btn-primary pull-right">
      <i class="fas fa-plus"></i> Añadir Transacción
    </a>
  </div>
</div>
<?php flash('transaction_message'); ?>

<div class="card card-body bg-light mt-4 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover">
      <thead class="thead-dark">
        <tr>
          <th>Fecha</th>
          <th>Descripción</th>
          <th>Categoría</th>
          <th>Cuenta</th>
          <th>Monto</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data['transactions'] as $transaction): ?>
          <tr>
            <td><?php echo date('d/m/Y', strtotime($transaction->transaction_date)); ?></td>
            <td><?php echo $transaction->description; ?></td>
            <td><?php echo $transaction->category_name; ?></td>
            <td><i class="fas fa-wallet text-secondary"></i> <?php echo $transaction->account_name; ?></td>
            <td class="font-weight-bold <?php echo $transaction->type == 'income' ? 'text-success' : 'text-danger'; ?>">
              <?php echo $transaction->type == 'income' ? '+' : '-'; ?>$<?php echo number_format($transaction->amount, 2); ?>
            </td>
            <td>
              <a href="<?php echo URLROOT; ?>/transactions/edit/<?php echo $transaction->id; ?>"
                class="btn btn-sm btn-dark"><i class="fas fa-edit"></i></a>
              <form action="<?php echo URLROOT; ?>/transactions/delete/<?php echo $transaction->id; ?>" method="post"
                class="d-inline">
                <button type="submit" class="btn btn-sm btn-danger"
                  onclick="return confirm('¿Estás seguro de eliminar esta transacción?');"><i
                    class="fas fa-trash"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($data['transactions'])): ?>
          <tr>
            <td colspan="6" class="text-center text-muted">Aún no hay transacciones registradas.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require APPROOT . '/views/inc/footer.php'; ?>
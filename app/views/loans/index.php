<?php require APPROOT . '/views/inc/header.php'; ?>
<div class="row mb-3">
  <div class="col-md-6">
    <h1>Mis Préstamos</h1>
  </div>
  <div class="col-md-6">
    <a href="<?php echo URLROOT; ?>/loans/add" class="btn btn-primary pull-right">
      <i class="fas fa-plus"></i> Registrar Préstamo
    </a>
  </div>
</div>
<?php flash('loan_message'); ?>

<div class="row">
  <?php foreach ($data['loans'] as $loan): ?>
    <div class="col-md-6 mb-4">
      <div class="card shadow-sm <?php echo $loan->status == 'paid' ? 'border-success' : 'border-warning'; ?>">
        <div class="card-body">
          <h4 class="card-title">
            <i class="fas fa-user-friends text-info"></i> <?php echo $loan->debtor_name; ?>
          </h4>
          <div class="mb-3">
            <span class="badge <?php echo $loan->status == 'paid' ? 'badge-success' : 'badge-warning'; ?>">
              <?php echo $loan->status == 'paid' ? 'Pagado' : 'Pendiente'; ?>
            </span>
            <small class="text-muted ml-2">Prestado el: <?php echo date('d/m/Y', strtotime($loan->loan_date)); ?></small>
          </div>

          <div class="row text-center mb-3">
            <div class="col-4">
              <small class="text-muted d-block">Monto</small>
              <strong>$<?php echo number_format($loan->amount, 2); ?></strong>
            </div>
            <div class="col-4">
              <small class="text-muted d-block">Abonado</small>
              <strong class="text-success">$<?php echo number_format($loan->paid_amount, 2); ?></strong>
            </div>
            <div class="col-4">
              <small class="text-muted d-block">Restante</small>
              <strong class="text-danger">$<?php echo number_format($loan->amount - $loan->paid_amount, 2); ?></strong>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <a href="<?php echo URLROOT; ?>/loans/show/<?php echo $loan->id; ?>" class="btn btn-primary btn-sm"><i
                class="fas fa-eye"></i> Detalles / Abonos</a>
            <div>
              <a href="<?php echo URLROOT; ?>/loans/edit/<?php echo $loan->id; ?>" class="btn btn-dark btn-sm"
                title="Editar"><i class="fas fa-edit"></i></a>
              <form action="<?php echo URLROOT; ?>/loans/delete/<?php echo $loan->id; ?>" method="post" class="d-inline">
                <button type="submit" class="btn btn-danger btn-sm"
                  onclick="return confirm('¿Eliminar préstamo y todo su historial de abonos?');" title="Eliminar"><i
                    class="fas fa-trash"></i></button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (empty($data['loans'])): ?>
    <div class="col-12 text-center mt-4">
      <p class="lead text-muted">Aún no has registrado ningún préstamo.</p>
    </div>
  <?php endif; ?>
</div>
<?php require APPROOT . '/views/inc/footer.php'; ?>
<?php require APPROOT . '/views/inc/header.php'; ?>
<div class="list-header">
  <div>
    <h1><i class="fas fa-hand-holding-usd text-primary mr-2"></i>Mis Préstamos</h1>
    <p class="text-muted mb-0">Dinero que has prestado y los abonos recibidos.</p>
  </div>
  <div class="list-header-action">
    <a href="<?php echo URLROOT; ?>/loans/add" class="btn btn-primary shadow-sm">
      <i class="fas fa-plus mr-1"></i> Registrar Préstamo
    </a>
  </div>
</div>
<?php flash('loan_message'); ?>

<div class="row">
  <?php foreach ($data['loans'] as $loan): ?>
    <div class="col-md-6 mb-4">
      <div class="card finance-card" style="border-top: 4px solid <?php echo $loan->status == 'paid' ? '#28a745' : '#ffc107'; ?>;">
        <div class="card-body">
          <div class="finance-card-header mb-3">
            <div class="d-flex align-items-center">
              <span class="finance-icon-circle bg-debit mr-3">
                <i class="fas fa-user-friends"></i>
              </span>
              <div>
                <h5 class="card-title font-weight-bold mb-0"><?php echo $loan->debtor_name; ?></h5>
                <small class="text-muted">Prestado el <?php echo date('d/m/Y', strtotime($loan->loan_date)); ?></small>
              </div>
            </div>
            <span class="badge <?php echo $loan->status == 'paid' ? 'badge-success' : 'badge-warning'; ?> py-2 px-3">
              <?php echo $loan->status == 'paid' ? 'Pagado' : 'Pendiente'; ?>
            </span>
          </div>

          <div class="finance-stat-box mb-3">
            <div class="row text-center">
              <div class="col-4">
                <small class="text-muted d-block text-uppercase" style="font-size:0.7rem;">Monto</small>
                <strong><?php echo number_format($loan->amount, 2); ?></strong>
              </div>
              <div class="col-4 border-left border-right">
                <small class="text-muted d-block text-uppercase" style="font-size:0.7rem;">Abonado</small>
                <strong class="text-success">$<?php echo number_format($loan->paid_amount, 2); ?></strong>
              </div>
              <div class="col-4">
                <small class="text-muted d-block text-uppercase" style="font-size:0.7rem;">Restante</small>
                <strong class="text-danger">$<?php echo number_format($loan->amount - $loan->paid_amount, 2); ?></strong>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center pt-2 border-top">
            <a href="<?php echo URLROOT; ?>/loans/show/<?php echo $loan->id; ?>" class="btn btn-primary btn-sm"><i
                class="fas fa-eye"></i> Detalles / Abonos</a>
            <div>
              <a href="<?php echo URLROOT; ?>/loans/edit/<?php echo $loan->id; ?>" class="btn btn-outline-dark btn-sm"
                title="Editar"><i class="fas fa-edit"></i></a>
              <form action="<?php echo URLROOT; ?>/loans/delete/<?php echo $loan->id; ?>" method="post" class="d-inline">
                <button type="submit" class="btn btn-outline-danger btn-sm"
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
    <div class="col-12">
      <div class="empty-state">
        <i class="fas fa-hand-holding-usd"></i>
        <h5>Aún no has registrado ningún préstamo</h5>
        <p class="mb-3">Lleva el control del dinero que prestas y los abonos que te realizan.</p>
        <a href="<?php echo URLROOT; ?>/loans/add" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Registrar mi primer préstamo</a>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php require APPROOT . '/views/inc/footer.php'; ?>
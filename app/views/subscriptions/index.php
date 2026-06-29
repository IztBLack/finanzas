<?php require APPROOT . '/views/inc/header.php'; ?>
<div class="row mb-3">
  <div class="col-md-6">
    <h1>Mis Suscripciones</h1>
    <p class="text-muted">Servicios recurrentes (Netflix, Gimnasio, etc.)</p>
  </div>
  <div class="col-md-6 text-md-right">
    <a href="<?php echo URLROOT; ?>/subscriptions/add" class="btn btn-primary">
      <i class="fas fa-plus"></i> Nueva Suscripción
    </a>
  </div>
</div>

<?php flash('subscription_message'); ?>

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white;">
            <div class="card-body text-center py-4">
                <h5 class="font-weight-light text-white-50 text-uppercase mb-2">Gasto Mensual Estimado</h5>
                <h1 class="font-weight-bold mb-0">$<?php echo number_format($data['monthly_total'], 2); ?></h1>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Suscripciones -->
<div class="row">
  <?php foreach($data['subscriptions'] as $sub): ?>
    <div class="col-md-4 mb-4">
      <div class="card h-100 shadow-sm <?php echo $sub->status == 'active' ? 'border-left-success' : 'border-left-secondary'; ?>" style="border-left: 4px solid <?php echo $sub->status == 'active' ? '#1cc88a' : '#858796'; ?>;">
        <div class="card-body d-flex flex-column">
          <div class="d-flex justify-content-between align-items-center mb-3">
              <h4 class="card-title font-weight-bold mb-0 text-dark">
                 <i class="fas fa-sync-alt text-primary mr-2"></i> <?php echo $sub->name; ?>
              </h4>
              <span class="badge <?php echo $sub->status == 'active' ? 'badge-success' : 'badge-secondary'; ?>">
                  <?php echo $sub->status == 'active' ? 'Activa' : 'Pausada'; ?>
              </span>
          </div>
          
          <div class="p-3 mb-3 rounded" style="background-color: #f8f9fc;">
              <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted small font-weight-bold text-uppercase">Cobro</span>
                  <span class="font-weight-bold">$<?php echo number_format($sub->amount, 2); ?> <small class="text-muted">/<?php echo $sub->billing_cycle == 'monthly' ? 'mes' : 'año'; ?></small></span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted small font-weight-bold text-uppercase">Día de Cobro</span>
                  <span class="text-dark font-weight-bold">Día <?php echo $sub->billing_day; ?></span>
              </div>
              <div class="d-flex justify-content-between">
                  <span class="text-muted small font-weight-bold text-uppercase">Cuenta</span>
                  <span class="text-secondary small"><i class="fas fa-wallet mr-1"></i><?php echo $sub->account_name; ?></span>
              </div>
          </div>

          <div class="mt-auto pt-3 border-top d-flex justify-content-between">
              <a href="<?php echo URLROOT; ?>/subscriptions/edit/<?php echo $sub->id; ?>" class="btn btn-outline-dark btn-sm px-3"><i class="fas fa-cog mr-1"></i> Gestionar</a>
              <form action="<?php echo URLROOT; ?>/subscriptions/delete/<?php echo $sub->id; ?>" method="post" class="m-0">
                  <button type="submit" class="btn btn-outline-danger btn-sm px-3" onclick="return confirm('¿Seguro que deseas eliminar esta suscripción?');"><i class="fas fa-trash"></i></button>
              </form>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  
  <?php if(empty($data['subscriptions'])): ?>
    <div class="col-12 text-center mt-5">
      <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-50"></i>
      <h4 class="text-muted">No tienes suscripciones registradas</h4>
      <p class="text-muted">Agrega tus pagos recurrentes para llevar un mejor control.</p>
    </div>
  <?php endif; ?>
</div>

<?php require APPROOT . '/views/inc/footer.php'; ?>

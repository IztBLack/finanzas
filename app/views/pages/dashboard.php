<?php require APPROOT . '/views/inc/header.php'; ?>
  <div class="row mb-4 bg-white p-4 shadow-sm rounded">
      <div class="col-md-8">
          <h1 class="display-4"><i class="fas fa-chart-line text-primary"></i> Dashboard</h1>
          <p class="lead text-muted">Hola, <strong><?php echo $_SESSION['user_name']; ?></strong>. Aquí está tu resumen financiero.</p>
      </div>
      <div class="col-md-4 text-center d-flex flex-column justify-content-center border-left">
          <h5 class="text-secondary mb-1">Balance Total</h5>
          <h2 class="<?php echo $data['total_balance'] >= 0 ? 'text-success' : 'text-danger'; ?> font-weight-bold">
              $<?php echo number_format($data['total_balance'], 2); ?>
          </h2>
      </div>
  </div>

  <div class="row mb-4">
      <div class="col-md-6 mb-3">
          <div class="card border-bottom-success shadow-sm h-100 py-2" style="border-bottom: 4px solid #28a745;">
              <div class="card-body">
                  <div class="row no-gutters align-items-center">
                      <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Ingresos Registrados (Mes Actual)</div>
                          <div class="h5 mb-0 font-weight-bold text-dark">$<?php echo number_format($data['income_month'], 2); ?></div>
                      </div>
                      <div class="col-auto">
                          <i class="fas fa-arrow-up fa-2x text-success opacity-50"></i>
                      </div>
                  </div>
              </div>
          </div>
      </div>
      <div class="col-md-6 mb-3">
          <div class="card border-bottom-danger shadow-sm h-100 py-2" style="border-bottom: 4px solid #dc3545;">
              <div class="card-body">
                  <div class="row no-gutters align-items-center">
                      <div class="col mr-2">
                          <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Gastos Registrados (Mes Actual)</div>
                          <div class="h5 mb-0 font-weight-bold text-dark">$<?php echo number_format($data['expense_month'], 2); ?></div>
                      </div>
                      <div class="col-auto">
                          <i class="fas fa-arrow-down fa-2x text-danger opacity-50"></i>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>

  <div class="card shadow-sm mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-bottom">
          <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history"></i> Transacciones Recientes</h6>
          <a href="<?php echo URLROOT; ?>/transactions" class="btn btn-sm btn-outline-primary">Ver todas</a>
      </div>
      <div class="card-body p-0">
          <div class="table-responsive">
              <table class="table table-hover table-striped mb-0">
                  <thead class="bg-light">
                      <tr>
                          <th class="border-top-0">Fecha</th>
                          <th class="border-top-0">Descripción</th>
                          <th class="border-top-0">Cuenta</th>
                          <th class="border-top-0">Monto</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach($data['recent_transactions'] as $t) : ?>
                          <tr>
                              <td class="align-middle"><?php echo date('d/m/Y', strtotime($t->transaction_date)); ?></td>
                              <td class="align-middle">
                                  <strong><?php echo $t->description ?: 'Sin descripción'; ?></strong>
                                  <span class="badge badge-secondary d-block mt-1" style="width: fit-content;"><?php echo $t->category_name; ?></span>
                              </td>
                              <td class="align-middle"><i class="fas fa-wallet text-muted"></i> <?php echo $t->account_name; ?></td>
                              <td class="align-middle font-weight-bold <?php echo $t->type == 'income' ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $t->type == 'income' ? '+' : '-'; ?>$<?php echo number_format($t->amount, 2); ?>
                              </td>
                          </tr>
                      <?php endforeach; ?>
                      <?php if(empty($data['recent_transactions'])): ?>
                          <tr><td colspan="4" class="p-0">
                              <div class="empty-state">
                                  <i class="fas fa-receipt"></i>
                                  <h5>Aún no has registrado ninguna transacción</h5>
                                  <p class="mb-3">Comienza a llevar el control de tus ingresos y gastos.</p>
                                  <a href="<?php echo URLROOT; ?>/transactions/add" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Registrar mi primera transacción</a>
                              </div>
                          </td></tr>
                      <?php endif; ?>
                  </tbody>
              </table>
          </div>
      </div>
  </div>
<?php require APPROOT . '/views/inc/footer.php'; ?>

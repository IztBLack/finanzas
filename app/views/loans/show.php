<?php require APPROOT . '/views/inc/header.php'; ?>
  <a href="<?php echo URLROOT; ?>/loans" class="btn btn-light mb-3"><i class="fa fa-backward"></i> Regresar</a>
  <?php flash('loan_message'); ?>

  <div class="row">
      <div class="col-md-5">
          <div class="card shadow-sm border-0 mb-4">
              <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                  <h4 class="m-0 font-weight-bold text-dark"><i class="fas fa-info-circle mr-2 text-primary"></i>Detalles del Préstamo</h4>
                  <span class="badge <?php echo $data['loan']->status == 'paid' ? 'badge-success' : 'badge-warning'; ?> py-2 px-3">
                     <?php echo $data['loan']->status == 'paid' ? 'Pagado' : 'Pendiente'; ?>
                  </span>
              </div>
              <div class="card-body p-4">
                  <div class="d-flex align-items-center mb-4">
                      <span class="finance-icon-circle bg-debit mr-3">
                          <i class="fas fa-user-friends"></i>
                      </span>
                      <div>
                          <small class="text-muted text-uppercase d-block" style="font-size:0.7rem;">Deudor</small>
                          <h5 class="mb-0 font-weight-bold"><?php echo $data['loan']->debtor_name; ?></h5>
                      </div>
                  </div>

                  <div class="finance-stat-box mb-3">
                      <div class="d-flex justify-content-between mb-2">
                          <span class="text-muted small font-weight-bold text-uppercase">Monto Prestado</span>
                          <span class="font-weight-bold">$<?php echo number_format($data['loan']->amount, 2); ?></span>
                      </div>
                      <div class="d-flex justify-content-between mb-2">
                          <span class="text-muted small font-weight-bold text-uppercase">Total Abonado</span>
                          <span class="font-weight-bold text-success">$<?php echo number_format($data['loan']->paid_amount, 2); ?></span>
                      </div>
                      <div class="d-flex justify-content-between">
                          <span class="text-muted small font-weight-bold text-uppercase">Resta por Pagar</span>
                          <span class="font-weight-bold text-danger">$<?php echo number_format($data['loan']->amount - $data['loan']->paid_amount, 2); ?></span>
                      </div>
                  </div>

                  <p class="mb-2"><i class="fas fa-wallet text-muted mr-2"></i><strong>De Cuenta:</strong> <?php echo $data['loan']->account_name; ?></p>
                  <p class="mb-2"><i class="far fa-calendar-alt text-muted mr-2"></i><strong>Fecha Préstamo:</strong> <?php echo date('d/m/Y', strtotime($data['loan']->loan_date)); ?></p>
                  <?php if($data['loan']->due_date): ?>
                      <p class="mb-0"><i class="far fa-calendar-check text-muted mr-2"></i><strong>Fecha Límite:</strong> <?php echo date('d/m/Y', strtotime($data['loan']->due_date)); ?></p>
                  <?php endif; ?>
              </div>
          </div>
      </div>

      <div class="col-md-7">
          <div class="card shadow-sm border-0 mb-4">
              <div class="card-header bg-white py-3">
                  <h4 class="m-0 font-weight-bold text-dark"><i class="fas fa-hand-holding-usd mr-2 text-success"></i>Registrar Abono</h4>
              </div>
              <div class="card-body p-4">
              <?php if($data['loan']->status != 'paid'): ?>
                  <form action="<?php echo URLROOT; ?>/loans/addPayment/<?php echo $data['loan']->id; ?>" method="post" class="row mb-2">
                      <div class="col-md-4 form-group mb-3">
                          <label class="form-section-label">Monto abonado</label>
                          <div class="input-group">
                              <div class="input-group-prepend"><div class="input-group-text">$</div></div>
                              <input type="number" step="0.01" class="form-control" name="amount" placeholder="0.00" required max="<?php echo ($data['loan']->amount - $data['loan']->paid_amount); ?>">
                          </div>
                      </div>

                      <div class="col-md-4 form-group mb-3">
                          <label class="form-section-label">Cuenta que recibe</label>
                          <select name="account_id" class="form-control" required>
                              <option value="">Selecciona...</option>
                              <?php foreach($data['accounts'] as $account) : ?>
                                  <option value="<?php echo $account->id; ?>"><?php echo $account->name; ?></option>
                              <?php endforeach; ?>
                          </select>
                      </div>

                      <div class="col-md-3 form-group mb-3">
                          <label class="form-section-label">Fecha</label>
                          <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                      </div>

                      <div class="col-md-1 form-group mb-3 d-flex align-items-end">
                          <button type="submit" class="btn btn-success w-100" title="Abonar"><i class="fas fa-plus"></i></button>
                      </div>
                  </form>
              <?php else: ?>
                  <div class="alert alert-success mb-4"><i class="fas fa-check-circle mr-2"></i>Este préstamo ya ha sido pagado en su totalidad.</div>
              <?php endif; ?>

              <h6 class="form-section-label mb-3">Historial de Abonos</h6>
              <div class="table-responsive">
                  <table class="table table-hover table-finance mb-0">
                      <thead class="bg-light">
                          <tr>
                              <th>Fecha</th>
                              <th>Monto</th>
                              <th>Cuenta Destino</th>
                              <th>Acciones</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach($data['payments'] as $payment): ?>
                              <tr>
                                  <td><?php echo date('d/m/Y', strtotime($payment->payment_date)); ?></td>
                                  <td class="text-success font-weight-bold">+$<?php echo number_format($payment->amount, 2); ?></td>
                                  <td><i class="fas fa-wallet text-secondary mr-1"></i><?php echo $payment->account_name; ?></td>
                                  <td>
                                    <form action="<?php echo URLROOT; ?>/loans/deletePayment/<?php echo $payment->id; ?>/<?php echo $data['loan']->id; ?>" method="post">
                                      <button type="submit" class="btn btn-sm btn-outline-danger py-0" onclick="return confirm('¿Eliminar abono?');"><i class="fas fa-times"></i></button>
                                    </form>
                                  </td>
                              </tr>
                          <?php endforeach; ?>
                          <?php if(empty($data['payments'])): ?>
                              <tr><td colspan="4" class="text-muted text-center py-4">No hay abonos registrados</td></tr>
                          <?php endif; ?>
                      </tbody>
                  </table>
              </div>
              </div>
          </div>
      </div>
  </div>
<?php require APPROOT . '/views/inc/footer.php'; ?>

<?php require APPROOT . '/views/inc/header.php'; ?>
  <a href="<?php echo URLROOT; ?>/loans" class="btn btn-light mb-3"><i class="fa fa-backward"></i> Regresar</a>
  <?php flash('loan_message'); ?>
  
  <div class="row">
      <div class="col-md-5">
          <div class="card card-body shadow-sm mb-4">
              <h3 class="mb-4">Detalles del Préstamo</h3>
              <h5>Deudor: <span class="text-info"><?php echo $data['loan']->debtor_name; ?></span></h5>
              <p><strong>De Cuenta:</strong> <i class="fas fa-wallet"></i> <?php echo $data['loan']->account_name; ?></p>
              <p><strong>Monto Prestado:</strong> $<?php echo number_format($data['loan']->amount, 2); ?></p>
              <p><strong>Total Abonado:</strong> <span class="text-success">$<?php echo number_format($data['loan']->paid_amount, 2); ?></span></p>
              <p><strong>Resta por Pagar:</strong> <span class="text-danger">$<?php echo number_format($data['loan']->amount - $data['loan']->paid_amount, 2); ?></span></p>
              <p><strong>Fecha Préstamo:</strong> <?php echo date('d/m/Y', strtotime($data['loan']->loan_date)); ?></p>
              <?php if($data['loan']->due_date): ?>
                  <p><strong>Fecha Límite:</strong> <?php echo date('d/m/Y', strtotime($data['loan']->due_date)); ?></p>
              <?php endif; ?>
              <span class="badge <?php echo $data['loan']->status == 'paid' ? 'badge-success' : 'badge-warning'; ?> p-2" style="font-size:1rem;">
                 <?php echo $data['loan']->status == 'paid' ? 'PAGADO COMPLETO' : 'PENDIENTE'; ?>
              </span>
          </div>
      </div>

      <div class="col-md-7">
          <div class="card card-body shadow-sm mb-4">
              <h3 class="mb-3">Registrar Abono</h3>
              <?php if($data['loan']->status != 'paid'): ?>
                  <form action="<?php echo URLROOT; ?>/loans/addPayment/<?php echo $data['loan']->id; ?>" method="post" class="form-inline mb-4">
                      <div class="input-group mb-2 mr-sm-2">
                          <div class="input-group-prepend"><div class="input-group-text">$</div></div>
                          <input type="number" step="0.01" class="form-control" name="amount" placeholder="Monto abonado" required max="<?php echo ($data['loan']->amount - $data['loan']->paid_amount); ?>">
                      </div>
                      
                      <select name="account_id" class="form-control mb-2 mr-sm-2" required>
                          <option value="">Cuenta que recibe...</option>
                          <?php foreach($data['accounts'] as $account) : ?>
                              <option value="<?php echo $account->id; ?>"><?php echo $account->name; ?></option>
                          <?php endforeach; ?>
                      </select>

                      <input type="date" name="payment_date" class="form-control mb-2 mr-sm-2" value="<?php echo date('Y-m-d'); ?>" required>
                      
                      <button type="submit" class="btn btn-success mb-2">Abonar</button>
                  </form>
              <?php else: ?>
                  <div class="alert alert-success mt-2">Este préstamo ya ha sido pagado en su totalidad.</div>
              <?php endif; ?>

              <h4>Historial de Abonos</h4>
              <div class="table-responsive">
                  <table class="table table-sm table-striped">
                      <thead>
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
                                  <td class="text-success">+$<?php echo number_format($payment->amount, 2); ?></td>
                                  <td><?php echo $payment->account_name; ?></td>
                                  <td>
                                    <form action="<?php echo URLROOT; ?>/loans/deletePayment/<?php echo $payment->id; ?>/<?php echo $data['loan']->id; ?>" method="post">
                                      <button type="submit" class="btn btn-sm btn-danger py-0" onclick="return confirm('¿Eliminar abono?');"><i class="fas fa-times"></i></button>
                                    </form>
                                  </td>
                              </tr>
                          <?php endforeach; ?>
                          <?php if(empty($data['payments'])): ?>
                              <tr><td colspan="4" class="text-muted text-center">No hay abonos registrados</td></tr>
                          <?php endif; ?>
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
  </div>
<?php require APPROOT . '/views/inc/footer.php'; ?>

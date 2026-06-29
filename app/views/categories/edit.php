<?php require APPROOT . '/views/inc/header.php'; ?>
  <a href="<?php echo URLROOT; ?>/categories" class="btn btn-light mb-3"><i class="fa fa-backward"></i> Regresar</a>

  <div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h4 class="m-0 font-weight-bold text-dark"><i class="fas fa-edit mr-2"></i> Editar Categoría</h4>
    </div>
    <div class="card-body p-4">
    <form action="<?php echo URLROOT; ?>/categories/edit/<?php echo $data['id']; ?>" method="post">
      <div class="row">
        <div class="col-md-6 form-group mb-4">
          <label for="name" class="form-section-label">Nombre <sup>*</sup></label>
          <input type="text" name="name" class="form-control <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['name']; ?>">
          <span class="invalid-feedback"><?php echo $data['name_err']; ?></span>
        </div>
        <div class="col-md-6 form-group mb-4">
          <label for="type" class="form-section-label">Tipo <sup>*</sup></label>
          <select name="type" class="form-control <?php echo (!empty($data['type_err'])) ? 'is-invalid' : ''; ?>">
              <option value="expense" <?php echo ($data['type'] == 'expense') ? 'selected' : ''; ?>>Gasto</option>
              <option value="income" <?php echo ($data['type'] == 'income') ? 'selected' : ''; ?>>Ingreso</option>
          </select>
          <span class="invalid-feedback"><?php echo $data['type_err']; ?></span>
        </div>
      </div>
      <hr>
      <button type="submit" class="btn btn-dark px-4 py-2 font-weight-bold"><i class="fas fa-save mr-2"></i> Actualizar Categoría</button>
    </form>
    </div>
  </div>
<?php require APPROOT . '/views/inc/footer.php'; ?>

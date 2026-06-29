<?php require APPROOT . '/views/inc/header.php'; ?>
  <a href="<?php echo URLROOT; ?>/categories" class="btn btn-light"><i class="fa fa-backward"></i> Regresar</a>
  <div class="card card-body bg-light mt-4 shadow-sm">
    <h2>Editar Categoría</h2>
    <form action="<?php echo URLROOT; ?>/categories/edit/<?php echo $data['id']; ?>" method="post">
      <div class="form-group">
        <label for="name">Nombre: <sup>*</sup></label>
        <input type="text" name="name" class="form-control form-control-lg <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['name']; ?>">
        <span class="invalid-feedback"><?php echo $data['name_err']; ?></span>
      </div>
      <div class="form-group">
        <label for="type">Tipo: <sup>*</sup></label>
        <select name="type" class="form-control form-control-lg <?php echo (!empty($data['type_err'])) ? 'is-invalid' : ''; ?>">
            <option value="expense" <?php echo ($data['type'] == 'expense') ? 'selected' : ''; ?>>Gasto</option>
            <option value="income" <?php echo ($data['type'] == 'income') ? 'selected' : ''; ?>>Ingreso</option>
        </select>
        <span class="invalid-feedback"><?php echo $data['type_err']; ?></span>
      </div>
      <input type="submit" class="btn btn-success" value="Actualizar Categoría">
    </form>
  </div>
<?php require APPROOT . '/views/inc/footer.php'; ?>

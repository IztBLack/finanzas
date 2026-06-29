<?php require APPROOT . '/views/inc/header.php'; ?>
  <a href="<?php echo URLROOT; ?>/categories" class="btn btn-light"><i class="fa fa-backward"></i> Regresar</a>
  <div class="card card-body bg-light mt-4 shadow-sm">
    <h2>Añadir Categoría</h2>
    <p>Crea una categoría para clasificar tus ingresos y gastos (Ej: Comida, Transporte, Sueldo)</p>
    <form action="<?php echo URLROOT; ?>/categories/add" method="post">
      <div class="form-group">
        <label for="name">Nombre: <sup>*</sup></label>
        <input type="text" name="name" class="form-control form-control-lg <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['name']; ?>" placeholder="Ej: Supermercado">
        <span class="invalid-feedback"><?php echo $data['name_err']; ?></span>
      </div>
      <div class="form-group">
        <label for="type">Tipo: <sup>*</sup></label>
        <select name="type" class="form-control form-control-lg <?php echo (!empty($data['type_err'])) ? 'is-invalid' : ''; ?>">
            <option value="">Selecciona el tipo...</option>
            <option value="expense" <?php echo ($data['type'] == 'expense') ? 'selected' : ''; ?>>Gasto</option>
            <option value="income" <?php echo ($data['type'] == 'income') ? 'selected' : ''; ?>>Ingreso</option>
        </select>
        <span class="invalid-feedback"><?php echo $data['type_err']; ?></span>
      </div>
      <input type="submit" class="btn btn-success" value="Guardar Categoría">
    </form>
  </div>
<?php require APPROOT . '/views/inc/footer.php'; ?>

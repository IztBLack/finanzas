<?php require APPROOT . '/views/inc/header.php'; ?>
<div class="row mb-3">
  <div class="col-md-6">
    <h1>Mis Categorías</h1>
  </div>
  <div class="col-md-6">
    <a href="<?php echo URLROOT; ?>/categories/add" class="btn btn-primary pull-right">
      <i class="fas fa-plus"></i> Añadir Categoría
    </a>
  </div>
</div>
<?php flash('category_message'); ?>
<div class="row">
  <?php foreach ($data['categories'] as $category): ?>
    <div class="col-md-4 mb-3">
      <div class="card card-body shadow-sm">
        <h4 class="card-title">
          <?php if ($category->type == 'income'): ?>
            <i class="fas fa-arrow-up text-success mr-2"></i>
          <?php else: ?>
            <i class="fas fa-arrow-down text-danger mr-2"></i>
          <?php endif; ?>
          <?php echo $category->name; ?>
        </h4>
        <div class="bg-light p-2 mb-3 rounded border">
          Tipo: <strong><?php echo $category->type == 'income' ? 'Ingreso' : 'Gasto'; ?></strong>
        </div>
        <div class="d-flex justify-content-between">
          <a href="<?php echo URLROOT; ?>/categories/edit/<?php echo $category->id; ?>" class="btn btn-dark btn-sm"><i
              class="fas fa-edit"></i> Editar</a>
          <form action="<?php echo URLROOT; ?>/categories/delete/<?php echo $category->id; ?>" method="post">
            <button type="submit" class="btn btn-danger btn-sm"
              onclick="return confirm('¿Estás seguro de eliminar esta categoría? Las transacciones que la usan podrían verse afectadas.');">
              <i class="fas fa-trash"></i> Eliminar
            </button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (empty($data['categories'])): ?>
    <div class="col-12 text-center mt-4">
      <p class="lead text-muted">Aún no tienes categorías creadas.</p>
    </div>
  <?php endif; ?>
</div>
<?php require APPROOT . '/views/inc/footer.php'; ?>
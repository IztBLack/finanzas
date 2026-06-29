<?php require APPROOT . '/views/inc/header.php'; ?>
<div class="list-header">
  <div>
    <h1><i class="fas fa-tags text-primary mr-2"></i>Mis Categorías</h1>
    <p class="text-muted mb-0">Clasifica tus ingresos y gastos para un mejor análisis.</p>
  </div>
  <div class="list-header-action">
    <a href="<?php echo URLROOT; ?>/categories/add" class="btn btn-primary shadow-sm">
      <i class="fas fa-plus mr-1"></i> Añadir Categoría
    </a>
  </div>
</div>
<?php flash('category_message'); ?>
<div class="row">
  <?php foreach ($data['categories'] as $category): ?>
    <div class="col-md-4 mb-4">
      <div class="card finance-card">
        <div class="card-body d-flex flex-column">
          <div class="finance-card-header mb-3">
            <div class="d-flex align-items-center">
              <span class="finance-icon-circle <?php echo $category->type == 'income' ? 'bg-income' : 'bg-expense'; ?> mr-3">
                <i class="fas <?php echo $category->type == 'income' ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
              </span>
              <h5 class="card-title font-weight-bold mb-0"><?php echo $category->name; ?></h5>
            </div>
            <span class="badge <?php echo $category->type == 'income' ? 'badge-soft-income' : 'badge-soft-expense'; ?> py-2 px-3">
              <?php echo $category->type == 'income' ? 'Ingreso' : 'Gasto'; ?>
            </span>
          </div>
          <div class="d-flex justify-content-between mt-auto pt-2 border-top">
            <a href="<?php echo URLROOT; ?>/categories/edit/<?php echo $category->id; ?>" class="btn btn-outline-dark btn-sm">
              <i class="fas fa-edit"></i> Editar
            </a>
            <form action="<?php echo URLROOT; ?>/categories/delete/<?php echo $category->id; ?>" method="post" class="m-0">
              <button type="submit" class="btn btn-outline-danger btn-sm"
                onclick="return confirm('¿Estás seguro de eliminar esta categoría? Las transacciones que la usan podrían verse afectadas.');">
                <i class="fas fa-trash"></i> Eliminar
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (empty($data['categories'])): ?>
    <div class="col-12">
      <div class="empty-state">
        <i class="fas fa-tags"></i>
        <h5>Aún no tienes categorías creadas</h5>
        <p class="mb-3">Crea categorías como Sueldo, Comida o Transporte para organizar tus movimientos.</p>
        <a href="<?php echo URLROOT; ?>/categories/add" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Crear mi primera categoría</a>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php require APPROOT . '/views/inc/footer.php'; ?>
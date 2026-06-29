<?php
require_once APPROOT . '/libraries/ApiController.php';

class CategoriesApi extends ApiController {

  private $model;

  public function __construct() {
    parent::__construct();
    $this->requireAuth();
    $this->model = $this->model('Category');
  }

  /** GET /api/categories */
  public function index() {
    $this->success($this->model->getCategories());
  }

  /** GET /api/categories/{id} */
  public function show($id) {
    $item = $this->model->getCategoryById($id);
    if (!$item) $this->abort(404, 'Categoría no encontrada.');
    $this->success($item);
  }

  /** POST /api/categories */
  public function store() {
    $data = $this->getBody();
    if (empty($data['name'])) $this->error('El nombre es requerido.');
    if (empty($data['type'])) $this->error('El tipo es requerido (income/expense).');
    $this->validateEnum($data['type'], ['income', 'expense'], 'type');

    if ($this->model->addCategory($data)) {
      $this->success(null, 'Categoría creada.', 201);
    }
    $this->error('Error al crear la categoría.', 500);
  }

  /** PUT /api/categories/{id} */
  public function update($id) {
    $item = $this->model->getCategoryById($id);
    if (!$item) $this->abort(404, 'Categoría no encontrada.');

    $data = array_merge((array)$item, $this->pick($this->getBody(), ['name', 'type']), ['id' => $id]);
    $this->validateEnum($data['type'], ['income', 'expense'], 'type');
    if ($this->model->updateCategory($data)) {
      $this->success(null, 'Categoría actualizada.');
    }
    $this->error('Error al actualizar la categoría.', 500);
  }

  /** DELETE /api/categories/{id} */
  public function destroy($id) {
    $item = $this->model->getCategoryById($id);
    if (!$item) $this->abort(404, 'Categoría no encontrada.');

    if ($this->model->deleteCategory($id)) {
      $this->success(null, 'Categoría eliminada.');
    }
    $this->error('Error al eliminar la categoría.', 500);
  }
}

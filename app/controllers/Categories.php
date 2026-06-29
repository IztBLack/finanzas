<?php
class Categories extends Controller {
  public function __construct(){
    if(!isset($_SESSION['user_id'])){
      redirect('users/login');
    }
    $this->categoryModel = $this->model('Category');
  }

  public function index(){
    $categories = $this->categoryModel->getCategories();

    $data = [
      'categories' => $categories
    ];

    $this->view('categories/index', $data);
  }

  public function add(){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

      $data = [
        'name' => trim($_POST['name']),
        'type' => trim($_POST['type']),
        'name_err' => '',
        'type_err' => ''
      ];

      // Validate data
      if(empty($data['name'])){
        $data['name_err'] = 'Por favor ingresa un nombre para la categoría';
      }
      if(empty($data['type'])){
         $data['type_err'] = 'Por favor selecciona el tipo (Ingreso o Gasto)';
      }

      // Make sure no errors
      if(empty($data['name_err']) && empty($data['type_err'])){
        if($this->categoryModel->addCategory($data)){
          flash('category_message', 'Categoría Agregada');
          redirect('categories');
        } else {
          die('Something went wrong');
        }
      } else {
        $this->view('categories/add', $data);
      }
    } else {
      $data = [
        'name' => '',
        'type' => '',
        'name_err' => '',
        'type_err' => ''
      ];

      $this->view('categories/add', $data);
    }
  }

  public function edit($id){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

      $data = [
        'id' => $id,
        'name' => trim($_POST['name']),
        'type' => trim($_POST['type']),
        'name_err' => '',
        'type_err' => ''
      ];

      if(empty($data['name'])){
        $data['name_err'] = 'Por favor ingresa un nombre para la categoría';
      }
      if(empty($data['type'])){
         $data['type_err'] = 'Por favor selecciona el tipo';
      }

      if(empty($data['name_err']) && empty($data['type_err'])){
        if($this->categoryModel->updateCategory($data)){
          flash('category_message', 'Categoría Actualizada');
          redirect('categories');
        } else {
          die('Something went wrong');
        }
      } else {
        $this->view('categories/edit', $data);
      }
    } else {
      $category = $this->categoryModel->getCategoryById($id);

      if($category->user_id != $_SESSION['user_id']){
        redirect('categories');
      }

      $data = [
        'id' => $id,
        'name' => $category->name,
        'type' => $category->type,
        'name_err' => '',
        'type_err' => ''
      ];

      $this->view('categories/edit', $data);
    }
  }

  public function delete($id){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $category = $this->categoryModel->getCategoryById($id);
      
      if($category->user_id != $_SESSION['user_id']){
        redirect('categories');
      }

      if($this->categoryModel->deleteCategory($id)){
        flash('category_message', 'Categoría Eliminada');
        redirect('categories');
      } else {
        die('Something went wrong');
      }
    } else {
        redirect('categories');
    }
  }
}

<?php
session_start();

// Flash message helper
function flash($name = '', $message = '', $class = 'alert alert-success'){
  if(!empty($name)){
    //No message, create it
    if(!empty($message) && empty($_SESSION[$name])){
      if(!empty( $_SESSION[$name])){
          unset( $_SESSION[$name]);
      }
      if(!empty( $_SESSION[$name.'_class'])){
          unset( $_SESSION[$name.'_class']);
      }
      $_SESSION[$name] = $message;
      $_SESSION[$name.'_class'] = $class;
    }
    //Message exists, display it
    elseif(!empty($_SESSION[$name]) && empty($message)){
      $class = !empty($_SESSION[$name.'_class']) ? $_SESSION[$name.'_class'] : 'success';
      $msg = $_SESSION[$name];
      $icon = 'info';
      if(strpos($class, 'success') !== false) $icon = 'success';
      if(strpos($class, 'danger') !== false) $icon = 'error';
      if(strpos($class, 'warning') !== false) $icon = 'warning';
      
      $msg_json = json_encode($msg);
      
      echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    icon: "'.$icon.'",
                    title: '.$msg_json.',
                    showConfirmButton: false,
                    timer: 10000,
                    timerProgressBar: true,
                    showCloseButton: true
                });
            } else {
                let div = document.createElement("div");
                div.className = "'.$class.' alert-dismissible fade show fixed-top m-3";
                div.style.zIndex = "9999";
                div.innerHTML = '.$msg_json.' + "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>";
                document.body.appendChild(div);
                setTimeout(() => div.remove(), 10000);
            }
        });
      </script>';
      
      unset($_SESSION[$name]);
      unset($_SESSION[$name.'_class']);
    }
  }
}
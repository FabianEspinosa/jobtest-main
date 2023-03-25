<?php

namespace App\Controllers;

use App\Model\User;
use Respect\Validation\Validator;

class UserController extends Controller
{
  public function index()
  {
    $users = new User();
    $users = $users->get();

    json(['items' => $users['rows']]);
  }

  public function create()
  {
    $response = [];

    try {
      $user = new User();
      $user->name = $_POST['name'];
      $user->email = $_POST['email'];
      $user->password = $_POST['password'];
      $user = $user->save();

      $code = 200;
      $response['success'] = true;
      $response['item'] = $user;
    } catch (\Exception $e) {
      $response['success'] = false;
      $response['message'] = $e->getMessage();
      $code = 500;
    }

    json($response, $code);
  }

  public function getItem($id)
  {
    try {
      $user = new User();
      $user = $user->where($id)->get();

      if (empty($user['id'])) {
        throw new \Exception('User not found', 404);
      }

      json($user, 201);
    } catch (\Exception $e) {
      json(['message' => $e->getMessage()], $e->getCode());
    }
  }

  public function login()
  {
      try {
          $request = get_object_vars($_POST);        
  
          // Validamos que se hayan enviado los campos necesarios
          $validator = Validator::key('email', Validator::notEmpty()->email(), 'El campo email es requerido y debe ser un correo electrónico válido.')
              ->key('password', Validator::notEmpty(), 'El campo password es requerido.');
  
          $validator->assert($request);
  
          // Buscamos el usuario en la base de datos
          $user = new User();          
          $user = $user->where('email', $request['email'])->first()->get();

          // Verificamos si se encontró el usuario y si la contraseña coincide          
          if (empty($user['id']) || !password_verify($request['password'], $user['password'])) {              
              return json(['El email o el password no son correctos'], 422);
          }
  
          // Generamos el token
          $token = bin2hex(random_bytes(16)) . '|' . $user['id'];
  
          // Creamos el objeto de respuesta
          $objeto = array(
              "user" => array(
                  "id" => $user['id'],
                  "name" => $user['name'],
                  "email" => $user['email']
              ),
              "token" => $token
          );
  
          json($objeto, 200);
      } catch (\Exception $e) {
          json(['message' => $e->getMessage()], $e->getCode());
      }
  }
  

  public function logout()
  {
    session_destroy();
    json('', 204);
  }
}

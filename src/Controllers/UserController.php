<?php

namespace App\Controllers;

use App\Model\User;

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
}

<?php

namespace App\Model;

class User extends Model
{
  protected $table = 'users';
  protected $columns = ['id' => 'integer', 'name' => 'string', 'email' => 'string', 'password' => 'string'];
}

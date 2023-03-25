<?php

namespace App\Controllers;

use App\Model\Movie;
use Respect\Validation\Validator;

class MovieController extends Controller
{
  public function index()
  {
    $movies = new Movie();
    $movies = $movies->get();
    json($movies['rows']);
  }

  /**
   * Show the specified resource from database.
   */
  public function show($id)
  {
    try {
      $movie = new Movie();
      $movie = $movie->where($id)->get();
      if (!$movie) {
        json(['error' => 'La película no existe'], 404);
      }
      json($movie);
    } catch (\Exception $e) {
      json(['message' => $e->getMessage()], $e->getCode());
    }
  }

  /**
   * Store a newly created resource in storage.
   */
  public function create()
  {

    try {
      $response = [];
      $request = get_object_vars($_POST);     
      Validator::notEmpty()->stringType()->assert($request['title']);
      Validator::notEmpty()->stringType()->assert($request['description']);
      Validator::notEmpty()->date()->assert($request['release_date']);
      Validator::notEmpty()->url()->assert($request['img']); 
      $movie = new Movie();
      $movie->title = $request['title'];
      $movie->description = $request['description'];
      $movie->release_date = $request['release_date'];
      $movie->img = $request['img'];
      $movie->created_at = date('Y-m-d H:i:s');
      $movie->updated_at = null;
      $movie = $movie->save();
      $code = 200;
      $response['success'] = true;
      $response['item'] = $movie;
    } catch (\Exception $e) {
      $response['success'] = false;
      $response['message'] = $e->getMessage();
      $code = 500;
    }
    json($response, $code);
  }

  /**
   * Update a resource in storage.
   */
  public function update()
  {
    try {
      $request = get_object_vars($_POST);
      Validator::notEmpty()->number()->assert($request['id']);
      Validator::notEmpty()->stringType()->assert($request['title']);
      Validator::notEmpty()->stringType()->assert($request['description']);
      Validator::notEmpty()->date()->assert($request['release_date']);
      Validator::notEmpty()->url()->assert($request['img']); 
      $response = [];
      $movie = new Movie();
      $movie->id = $request['id'];
      $movie->title = $request['title'];
      $movie->description = $request['description'];
      $movie->release_date = $request['release_date'];
      $movie->img = $request['img'];
      $movie->updated_at = date('Y-m-d H:i:s');
      $movie = $movie->save();
      $code = 200;
      $response['success'] = true;
      $response['item'] = $movie;
    } catch (\Exception $e) {
      $response['success'] = false;
      $response['message'] = $e->getMessage();
      $code = 500;
    }
    json($response, $code);
  }

  /**
   * Update a resource in storage.
   */
  public function delete($id)
  {
    try {
      $movie = new Movie();
      $movie = $movie->delete($id);
      if (!$movie) {
        json(['error' => 'La película no existe'], 404);
      }
      json($movie);
    } catch (\Exception $e) {
      json(['message' => $e->getMessage()], $e->getCode());
    }
  }
}

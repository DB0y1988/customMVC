<?php

namespace App\Controllers;

use \Core\View;
use App\Models\recipePosts;
use App\Auth;

/* Blog controller used for the public facing front end */

class Blog extends \Core\Controller
{

    public function categoryAction()
    {

      // Get the category here
      $category = $this->route_params['category'];

      $posts = recipePosts::getPostsByCategory($category);

      View::renderTemplate('/Blog/index.html', [
        'posts' => $posts,
        'category' => $category,
        'uri' => $_SERVER['REQUEST_URI']
      ]);
    }

    public function singleAction(){

      $id = $this->route_params['id'];

      $post = recipePosts::getPostsById($id, "recipes");

      $steps = unserialize($post[0]['steps']);

      View::renderTemplate('/Blog/single.html', [
        'introduction' => $post[0]['introduction'],
        'title' => $post[0]['title'],
        'excerpt' => $post[0]['excerpt'],
        'featured_image' => $post[0]['featured_image'],
        'steps' => $steps
      ]);

    }

}

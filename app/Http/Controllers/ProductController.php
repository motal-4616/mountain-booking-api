<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $title = 'Product List';
       // return view('product.index',compact('title'));
        $name ="Nguyen Van A";
    //    // return view('product.index')->with('name',$name);
    //    $myoii = [
    //     'name' => 'Nguyen Van A',
    //     'age' => 30,
    //     'address' => 'Hanoi',
    //     'isfavorite' => true,
    //    ];
       //return view('product.index',compact('myoii'));
    }
     public function detail($id)
     {
        return "Product id: ".$id;
    }
    //
}

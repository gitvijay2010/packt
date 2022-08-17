<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;



class HomeController extends Controller
{
    //
    public function index(){

    	$key = config('app.packt_key');

    	$response = Http::withToken($key)->get('https://api.packt.com/api/v1/products?page=1&limit=10');

    	//print_r($response->body());
    	$products = $response->json('products');
    	for($i = 0; $i < count($products); $i++){
    		//echo $product['id'];
			$responses = Http::pool(fn (Pool $pool) => [
			    $pool->as('prices')->get("https://api.packt.com/api/v1/products/".$products[$i]['id']."/price/USD?token=$key"),
			    $pool->as('image')->get("https://api.packt.com/api/v1/products/".$products[$i]['id']."/cover/small?token=$key"),
			]);

    		$products[$i]['prices'] = $responses['prices']->json('prices');
			$products[$i]['image']['type'] = $responses['image']->header('Content-Type');
			$products[$i]['image']['content'] = base64_encode($responses['image']->body());

    	}
			return view('home', ['products' => $products]);

    }
}

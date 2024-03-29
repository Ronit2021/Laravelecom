<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;

use Session;
use Illuminate\Support\Facades\DB; 
class ProductController extends Controller
{
    //
    function index(){
        $data = Product::all();
        return view('product',['product'=>$data]);
    }
    function detail($id){
        $data =  Product::find($id);
        return view('detail',['products'=>$data]);
    }
    function addToCart(Request $request){
        
       
        if($request->session()->has('user')){
            $cart = new Cart;
            $cart->user_id = $request->session()->get('user')['id'];
            $cart->product_id = $request->product_id;
            $cart->save();
            return redirect('/');
        }else{
            return redirect('login');
        }
        
    }

    static function cartItem(){
        
        $userId = Session::get('user')['id'];
        return Cart::where('user_id', $userId)->count();

    }
    
    function cartList(){
        $userId = Session::get('user')['id'];
        $products = DB::table('cart')
                ->join('products','cart.product_id','=','products.id')
                ->where('cart.user_id',$userId)
                ->select('products.*','cart.id as cart_id')
                ->get();
        
        return view('cartlist',['products'=>$products]);

    }
    function removeCart($id){
        Cart::destroy($id);
       return redirect('cartlist');
        
   }
   function orderNow(){
    $userId = Session::get('user')['id'];
        $total = DB::table('cart')
                ->join('products','cart.product_id','=','products.id')
                ->where('cart.user_id',$userId)
                ->sum('products.price');
        
        return view('ordernow',['total'=>$total]);
   }  

   function orderPlace(Request $request){
    $userId = Session::get('user')['id'];
    $allCart = Cart::where('user_id',$userId)->get();
    
    foreach($allCart as $cart){
        
        $order = new Order;
        $order->product_id      = $cart['product_id'];
        $order->user_id         = $cart['user_id'];
        $order->status         = "Pending";
        $order->payment_method  = $request->payment_method;
        $order->payment_status  = "Pending";
        $order->address  = $request->address;
        $order->save();
        Cart::where('user_id',$userId)->delete();
    }  
        $request->input();
        return redirect('/');
   }


   function myOrders(){
        $userId = Session::get('user')['id'];
        $orders = DB::table('order')
                ->join('products','order.product_id','=','products.id')
                ->where('order.user_id',$userId)
                ->get();
       
        return view('myorders',['orders'=>$orders]);
   }
  
    
}


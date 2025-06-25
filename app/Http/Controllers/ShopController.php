<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use App\Models\Brand;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $o_column = "";
        $o_order = "";
        $order = $request->query('order') ?? -1;
        $f_categories = $request->query('categories');
        $f_subcategories = $request->query('subcategories');
        $min_price = $request->query('min') !== null ? (int) $request->query('min') : 0;
        $max_price = $request->query('max') !== null ? (int) $request->query('max') : 100000000;

        switch($order)
        {
            case 1:
                $o_column='created_at';
                $o_order='DESC';
                break;
            case 2:
                $o_column='created_at';
                $o_order='ASC';
                break;
            case 3:
                $o_column='sale_price';
                $o_order='ASC';
                break;
            case 4:
                $o_column='sale_price';
                $o_order='DESC';
                break;
            default:
                $o_column = 'id';
                $o_order = 'DESC';
        }

        $categories = Category::with('children')->whereNull('parent_id')->orderBy('name','ASC')->get();
        $subcategories = Subcategory::with('products')->orderBy('name','ASC')->get();

        // Bangun query produk
        $query = Product::query();

        if (!empty($f_subcategories)) {
            $query->whereIn('subcategory_id', explode(',', $f_subcategories));
        }

        if (!empty($f_categories)) {
            $query->whereIn('category_id', explode(',', $f_categories));
        }

        $query->where(function ($q) use ($min_price, $max_price) {
            $q->whereBetween('regular_price', [$min_price, $max_price])
            ->orWhereBetween('sale_price', [$min_price, $max_price]);
        });

        $products = $query->orderBy($o_column, $o_order)->paginate();

        return view('shop', compact(
            'products',
            'order',
            'subcategories',
            'f_subcategories',
            'categories',
            'f_categories',
            'min_price',
            'max_price'
        ));
    }

    public function product_details($product_slug)
    {
        $product = Product::where('slug', $product_slug)->first();
        $rproducts = Product::where('slug', '<>', $product_slug)->take(8)->get();

        return view('details', compact('product', 'rproducts'));
    }
}

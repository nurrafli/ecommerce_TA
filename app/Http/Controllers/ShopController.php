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
        $size = $request->query('size') ?? 12;
        $order = $request->query('order') ?? -1;
        $f_brands = $request->query('brands');
        $f_categories = $request->query('categories');
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

        $brands = Brand::orderBy('name','ASC')->get();
        $categories = Category::orderBy('name','ASC')->get();
        $query = Product::query();

        if ($f_brands) {
            $brandIds = explode(',', $f_brands);
            $query->whereIn('brand_id', $brandIds);
        }

        if ($f_categories) {
            $categoryIds = explode(',', $f_categories);
            $query->whereIn('category_id', $categoryIds);
        }

        
        $global_min_price = Product::min('regular_price');
        $global_max_price = Product::max('regular_price');

        
        $sale_min = Product::min('sale_price');
        $sale_max = Product::max('sale_price');

        
        $global_min_price = min($global_min_price, $sale_min);
        $global_max_price = max($global_max_price, $sale_max);

        
        $query->where(function($q) use($min_price, $max_price) {
            $q->whereBetween('regular_price', [$min_price, $max_price])
            ->orWhereBetween('sale_price', [$min_price, $max_price]);
        });

        
        $products = $query->orderBy($o_column, $o_order)->paginate($size);

        
        return view('shop', compact(
            'products', 'size', 'order', 'brands', 'f_brands',
            'categories', 'f_categories', 'min_price', 'max_price',
            'global_min_price', 'global_max_price'
        ));

    }
}
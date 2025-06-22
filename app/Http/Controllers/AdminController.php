<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Slide;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;


class AdminController extends Controller
{
    public function index()
    {
        $orders = Order::latest()->take(10)->get();

        // Dashboard summary
        $dashboardDatas = Order::selectRaw("
            SUM(total) AS TotalAmount,
            SUM(CASE WHEN status = 'ordered' THEN total ELSE 0 END) AS TotalOrderedAmount,
            SUM(CASE WHEN status = 'delivered' THEN total ELSE 0 END) AS TotalDeliveredAmount,
            SUM(CASE WHEN status = 'canceled' THEN total ELSE 0 END) AS TotalCanceledAmount,
            COUNT(*) AS Total,
            SUM(CASE WHEN status = 'ordered' THEN 1 ELSE 0 END) AS TotalOrdered,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS TotalDelivered,
            SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) AS TotalCanceled
        ")->get();

        // Monthly Data
        $monthlyDatas = DB::table(DB::raw('month_names AS M'))
            ->leftJoinSub(
                Order::selectRaw("
                    MONTH(created_at) AS MonthNo,
                    DATE_FORMAT(created_at, '%b') AS MonthName,
                    SUM(total) AS TotalAmount,
                    SUM(CASE WHEN status = 'ordered' THEN total ELSE 0 END) AS TotalOrderedAmount,
                    SUM(CASE WHEN status = 'delivered' THEN total ELSE 0 END) AS TotalDeliveredAmount,
                    SUM(CASE WHEN status = 'canceled' THEN total ELSE 0 END) AS TotalCanceledAmount
                ")
                ->whereYear('created_at', now()->year)
                ->groupByRaw("YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%b')")
                , 'D'
            , function ($join) {
                $join->on('D.MonthNo', '=', 'M.id');
            })
            ->select(
        'M.id as MonthNo',
        'M.name as MonthName', // <--- FIXED!
        DB::raw('IFNULL(D.TotalAmount,0) as TotalAmount'),
        DB::raw('IFNULL(D.TotalOrderedAmount,0) as TotalOrderedAmount'),
        DB::raw('IFNULL(D.TotalDeliveredAmount,0) as TotalDeliveredAmount'),
        DB::raw('IFNULL(D.TotalCanceledAmount,0) as TotalCanceledAmount')
        )
            ->orderBy('M.id')
            ->get();

        // Pluck untuk chart
        $AmountM = $monthlyDatas->pluck('TotalAmount')->implode(',');
        $OrderedAmountM = $monthlyDatas->pluck('TotalOrderedAmount')->implode(',');
        $DeliveredAmountM = $monthlyDatas->pluck('TotalDeliveredAmount')->implode(',');
        $CanceledAmountM = $monthlyDatas->pluck('TotalCanceledAmount')->implode(',');

        // Totals untuk dashboard
        $TotalAmount = $monthlyDatas->sum('TotalAmount');
        $TotalOrderedAmount = $monthlyDatas->sum('TotalOrderedAmount');
        $TotalDeliveredAmount = $monthlyDatas->sum('TotalDeliveredAmount');
        $TotalCanceledAmount = $monthlyDatas->sum('TotalCanceledAmount');

        return view('admin.index', compact(
            'orders', 'dashboardDatas',
            'AmountM', 'OrderedAmountM', 'DeliveredAmountM', 'CanceledAmountM',
            'TotalAmount', 'TotalOrderedAmount', 'TotalDeliveredAmount', 'TotalCanceledAmount'
        ));
    }

    public function subcategories()
    {
        $subcategories = Subcategory::whereNotNull('parent_id')->orderBy('id', 'DESC')->paginate(10);
        return view('admin.subcategories', compact('subcategories'));
    }

    public function add_subcategory()
    {
        $categories = Category::whereNull('parent_id')->orderBy('name')->get();
        return view('admin.subcategory-add', compact('categories'));
    }

    public function subcategory_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'parent_id' => 'required|exists:categories,id',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:3000',
        ]);

        $subcategory = new Subcategory();
        $subcategory->name = $request->name;
        $subcategory->slug = Str::slug($request->name);
        $subcategory->parent_id = $request->parent_id;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $file_name = Carbon::now()->timestamp . '.' . $image->extension();
            $this->generateThumbnailImage($image, $file_name);
            $subcategory->image = $file_name;
        }

        $subcategory->save();
        return redirect()->route('admin.subcategories')->with('status', 'Subcategory added successfully');
    }

    public function edit_subcategory($id)
    {
        $subcategory = Subcategory::findOrFail($id);
        $categories = Category::whereNull('parent_id')->orderBy('name')->get();
        return view('admin.subcategory-edit', compact('subcategory', 'categories'));
    }

    public function update_subcategory(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:subcategories,id',
            'name' => 'required',
            'slug' => 'required|unique:subcategories,slug,' . $request->id,
            'parent_id' => 'required|exists:categories,id',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:3000',
        ]);

        $subcategory = Subcategory::find($request->id);
        $subcategory->name = $request->name;
        $subcategory->slug = Str::slug($request->name);
        $subcategory->parent_id = $request->parent_id;

        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/categories/' . $subcategory->image))) {
                File::delete(public_path('uploads/categories/' . $subcategory->image));
            }

            $image = $request->file('image');
            $file_name = Carbon::now()->timestamp . '.' . $image->extension();
            $this->generateThumbnailImage($image, $file_name);
            $subcategory->image = $file_name;
        }

        $subcategory->save();
        return redirect()->route('admin.subcategories')->with('status', 'Subcategory updated successfully');
    }

    public function delete_subcategory($id)
    {
        $subcategory = Subcategory::find($id);

        if ($subcategory && $subcategory->image && File::exists(public_path('uploads/categories/' . $subcategory->image))) {
            File::delete(public_path('uploads/categories/' . $subcategory->image));
        }

        $subcategory->delete();
        return redirect()->route('admin.subcategories')->with('status', 'Subcategory deleted successfully');
    }

    private function generateThumbnailImage($image, $imageName)
    {
 
        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    
    }

    public function categories()
    {
        $categories = Category::orderBy('id','DESC')->paginate(10);
        return view('admin.categories', compact('categories'));
    }

    public function category_add()
    {
        return view('admin.category-add');
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:3000',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->parent_id = null;
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp.'.'.$file_extention;
        $this->GenerateCategoryThumbailsImage($image,$file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category has been added succesfully');
    }

    public function GenerateCategoryThumbailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }

    public function category_edit($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit',compact('category'));
    }

    public function category_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,'.$request->id,
            'image' => 'mimes:png,jpg,jpeg|max:3000',
        ]);

        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        if($request->hasFile('image')){
            if(File::exists(public_path('uploads/categories').'/'.$category->image))
            {
                File::delete(public_path('uploads/categories').'/'.$category->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp.'.'.$file_extention;
            $this->GenerateCategoryThumbailsImage($image,$file_name);
            $category->image = $file_name;
        }
        
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category has been updated succesfully');
    }

    public function category_delete($id){
        $category = Category::find($id);
        if(File::exists(public_path('uploads/categories').'/'.$category->image))
        {
            File::delete(public_path('uploads/categories').'/'.$category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status', 'Category has been deleted successfully!');
    }

    public function products()
    {
        $products = Product::orderBy('created_at','DESC')->paginate(10);
        return view('admin.products',compact('products'));
    }

    public function product_add()
    {
        $subcategories = Subcategory::select('id','name')->orderBy('name')->get();
        return view('admin.product-add',compact('subcategories'));
    }

    public function product_store(Request $request)
    {
        $request->validate([
            'name' =>'required',
            'slug' =>'required|unique:products,slug',
            'short_description' =>'required',
            'description' =>'required',
            'regular_price' =>'required',
            'sale_price' =>'required',
            'SKU' =>'required',
            'stock_status' =>'required',
            'featured' =>'required',
            'quantity' =>'required',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:3000',
            'images' => 'required|array',
            'images.*' => 'mimes:jpg,jpeg,png|max:3000',
            'subcategory_id' =>'required',
        ]);
        

        $product = new Product();
        $product -> name = $request-> name;
        $product -> slug = Str::slug($request->name);
        $product -> short_description = $request-> short_description;
        $product -> description = $request-> description;
        $product -> regular_price = $request-> regular_price;
        $product -> sale_price = $request-> sale_price;
        $product -> SKU = $request-> SKU;
        $product -> stock_status = $request-> stock_status;
        $product -> featured = $request-> featured;
        $product -> quantity = $request-> quantity;
        $product -> subcategory_id = $request-> subcategory_id;

        $current_timestamp = Carbon::now()->timestamp;

        if($request->hasFile('image'))
        {
            $image = $request->file('image');
            $imageName = $current_timestamp.'.'. $image->extension();
            $this->GenerateProductThumbnailImage($image,$imageName);
            $product->image= $imageName;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if($request->hasFile('images'))
        {
            $allowedfileExtion = [ 'jpg', 'png', 'jpeg'];
            $files = $request->file('images');
            foreach($files as $file)
            {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension,$allowedfileExtion);
                if($gcheck)
                {
                    $gfileName = $current_timestamp . "-". $counter . ".". $gextension;
                    $this->GenerateProductThumbnailImage($file,$gfileName);
                    array_push($gallery_arr,$gfileName);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(',',$gallery_arr);
        }
        $product->images = $gallery_images;
        $product->save();
        return redirect()->route('admin.products')->with('success', 'Product added successfully!');
    }

    public function GenerateProductThumbnailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/products'); // Folder penyimpanan

        // Baca gambar asli
        $img = Image::read($image->path());

        // Simpan gambar utama (ukuran asli, kualitas tinggi)
        $img->save($destinationPath.'/'.$imageName, 90); // Kualitas 90 untuk menjaga file HD

        // Buat nama file thumbnail
        $thumbnailName = pathinfo($imageName, PATHINFO_FILENAME) . '_thumb.' . pathinfo($imageName, PATHINFO_EXTENSION);

        // Simpan thumbnail tanpa resize (tetap ukuran asli)
        $img->save($destinationPath.'/'.$thumbnailName, 90); // Hasilnya thumbnail sama dengan gambar asli


    }

    public function product_edit($id)
    {
        $product = Product::find($id);
        $subcategories = Subcategory::select('id','name')->orderBy('name')->get();
        return view('admin.product-edit',compact('product','subcategories'));
    }

    public function product_update(Request $request)
    {
        $request->validate([
            'name' =>'required',
            'slug' =>'required|unique:products,slug,'.$request->id,
            'short_description' =>'required',
            'description' =>'required',
            'regular_price' =>'required',
            'sale_price' =>'required',
            'SKU' =>'required',
            'stock_status' =>'required',
            'featured' =>'required',
            'quantity' =>'required',
            'image' => 'mimes:jpg,jpeg,png|max:3000',
            'images' => 'required|array',
            'images.*' => 'mimes:jpg,jpeg,png|max:3000',
            'subcategory_id' =>'required',
        ]);

        $product = Product::find($request->id);
        $product -> name = $request-> name;
        $product -> slug = Str::slug($request->name);
        $product -> short_description = $request-> short_description;
        $product -> description = $request-> description;
        $product -> regular_price = $request-> regular_price;
        $product -> sale_price = $request-> sale_price;
        $product -> SKU = $request-> SKU;
        $product -> stock_status = $request-> stock_status;
        $product -> featured = $request-> featured;
        $product -> quantity = $request-> quantity;
        $product -> subcategory_id = $request-> subcategory_id;

        $current_timestamp = Carbon::now()->timestamp;

        if($request->hasFile('image'))
        {
            if(File::exists(public_path('uploads/products').'/'.$product->image))
            {
                File::delete(public_path('uploads/products').'/'.$product->image);
            }
            if(File::exists(public_path('uploads/products/thumbnails').'/'.$product->image))
            {
                File::delete(public_path('uploads/products/thumbnails').'/'.$product->image);
            }
            $image = $request->file('image');
            $imageName = $current_timestamp.'.'. $image->extension();
            $this->GenerateProductThumbnailImage($image,$imageName);
            $product->image= $imageName;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if($request->hasFile('images'))
        {
            foreach(explode(',',$product->images) as $ofile)
            {
                if(File::exists(public_path('uploads/products').'/'.$ofile))
                {
                    File::delete(public_path('uploads/products').'/'.$ofile);
                }
                if(File::exists(public_path('uploads/products/thumbnails').'/'.$ofile))
                {
                    File::delete(public_path('uploads/products/thumbnails').'/'.$ofile);
                }
            }

            $allowedfileExtion = [ 'jpg', 'png', 'jpeg'];
            $files = $request->file('images');
            foreach($files as $file)
            {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension,$allowedfileExtion);
                if($gcheck)
                {
                    $gfileName = $current_timestamp . "-". $counter . ".". $gextension;
                    $this->GenerateProductThumbnailImage($file,$gfileName);
                    array_push($gallery_arr,$gfileName);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(',',$gallery_arr);
            $product->images = $gallery_images;
        }
        
        $product->save();
        return redirect()->route('admin.products')->with('status','Product has been updated successfully!');
    }

    public function product_delete($id)
    {
        $product = Product::find($id);
        if(File::exists(public_path('uploads/products').'/'.$product->image))
        {
            if(File::exists(public_path('uploads/products').'/'.$product->image))
            {
                File::delete(public_path('uploads/products').'/'.$product->image);
            }
            if(File::exists(public_path('uploads/products/thumbnails').'/'.$product->image))
            {
                File::delete(public_path('uploads/products/thumbnails').'/'.$product->image);
            }

            foreach(explode(',',$product->images) as $ofile)
            {
                if(File::exists(public_path('uploads/products').'/'.$ofile))
                {
                    File::delete(public_path('uploads/products').'/'.$ofile);
                }
                if(File::exists(public_path('uploads/products/thumbnails').'/'.$ofile))
                {
                    File::delete(public_path('uploads/products/thumbnails').'/'.$ofile);
                }
            }
        }
        
        $product->delete();
        return redirect()->route('admin.products')->with('status','Product has been deleted succesfully!');
    }

    public function coupons()
    {
        $coupons = Coupon::orderBy('expiry_date','DESC')->paginate(12);
        return view('admin.coupons', compact('coupons'));
    }

    public function coupon_add()
    {
        return view('admin.coupon-add');
    }

    public function coupon_store(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date'
        ]);

        $coupon = new Coupon();
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        return redirect()->route('admin.coupons')->with('status','Coupon has been added succesfully!');
    }

    public function coupon_edit($id)
    {
        $coupon = Coupon::find($id);
        return view('admin.coupon-edit',compact('coupon'));
    }

    public function coupon_update(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date'
        ]);

        $coupon = Coupon::find($request->id);
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        return redirect()->route('admin.coupons')->with('status','Coupon has been updated succesfully!');
    }

    public function coupon_delete($id)
    {
        $coupon = Coupon::find($id);
        $coupon->delete();
        return redirect()->route('admin.coupons')->with('status','Coupon has been deleted succesfully!');
    }
    
    public function orders()
    {
        $orders = Order::orderBy('created_at','DESC')->paginate(12);
        return view('admin.orders',compact('orders'));
    }

    public function order_details($order_id)
    {
        $order =  Order::find($order_id);
        $orderItems = OrderItem::where('order_id',$order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id',$order_id)->first();
        return view('admin.order-details',compact('order','orderItems','transaction'));
    }

    public function update_order_status(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = $request->order_status;
        if($request->order_status == 'delivered')
        {
            $order->delivered_date = Carbon::now();
        }
        elseif($request->order_status == 'canceled')
        {
            $order->canceled_date = Carbon::now();
        }
        $order->save();

        if($request->order_status == 'delivered')
        {
            $transaction = Transaction::where('order_id',$request->order_id)->first();
            $transaction->status = 'approved';
            $transaction->save();
        }
        return back()->with("status","Status has changed successfully!");
    }

    public function slides()
    {
        $slides = Slide::orderBy('id','DESC')->paginate(12);
        return view('admin.slides',compact('slides'));
    }

    public function slide_add()
    {
        return view('admin.slide-add');
    }

    public function slide_store(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:3000'
        ]);
        $slide = new Slide();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp.'.'.$file_extention;
        $this->GenerateSlideThumbailsImage($image,$file_name);
        $slide->image = $file_name;
        $slide->save();
        return redirect()->route('admin.slides')->with("status","Slide added succesfully!");
    }

    public function GenerateSlideThumbailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/slides');
        $img = Image::read($image->path());
        $img->cover(400,690,"top");
        $img->resize(400,690,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }

    public function slide_edit($id)
    {
        $slide = Slide::find($id);
        return view('admin.slide-edit',compact('slide'));
    }

    public function slide_update(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:3000'
        ]);
        $slide = Slide::find($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        if($request->hasFile('image'))
        {
            if(File::exists(public_path('uploads/slides').'/'.$slide->image))
            {
                File::delete(public_path('uploads/slides').'/'.$slide->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp.'.'.$file_extention;
            $this->GenerateSlideThumbailsImage($image,$file_name);
            $slide->image = $file_name;
        }
        $slide->save();
        return redirect()->route('admin.slides')->with("status","Slide updated succesfully!");
    }

    public function slide_delete($id)
    {
        $slide = Slide::find($id);
        if(File::exists(public_path('uploads/slides').'/'.$slide->image))
        {
            File::delete(public_path('uploads/slides').'/'.$slide->image);
        }
        $slide->delete();
        return redirect()->route('admin.slides')->with("status","Slide deleted succesfully!");
    }

    public function contacts()
    {
        $contacts = Contact::orderBy('created_at','DESC')->paginate(10);
        return view('admin.contacts',compact('contacts'));
    }

    public function contact_delete($id)
    {
        $contact = Contact::find($id);
        $contact->delete();
        return redirect()->route('admin.contacts')->with("status","Contact deleted succesfully!");
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = Product::where('name', 'LIKE', '%' . $query . '%')
                        ->take(10)
                        ->get();
        return response()->json($results);
    }
}



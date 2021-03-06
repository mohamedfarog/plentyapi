<?php

namespace App\Http\Controllers;

use App\Actions\UploadHelper;
use App\Models\Addon;
use App\Models\Color;
use App\Models\Designer;
use App\Models\Image;
use App\Models\Product;
use App\Models\Productag;
use App\Models\ShopInfo;
use App\Models\Size;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $user = Auth::user();
        switch ($user->typeofuser) {
            case 'S':
                $perpage = 15;
                if (isset($request->perpage)) {
                    $perpage = $request->perpage;
                }
                if (isset($request->all)) {
                    return Product::where('deleted_at', null)->with(['sizes' => function ($sizes) {
                        return $sizes->with(['color']);
                    }, 'colors', 'addons', 'images', 'designer'])->get();
                }
                if (isset($request->id)) {
                    return Product::where('id', $request->id)->with(['sizes' => function ($sizes) {
                        return $sizes->with(['color']);
                    }, 'addons', 'images', 'designer'])->first();
                }
                if (isset($request->eventcat_id))
                    return Product::where('deleted_at', null)->where('eventcat_id', $request->eventcat_id)->with(['sizes' => function ($sizes) {
                        return $sizes->with(['color']);
                    }, 'colors', 'addons', 'images', 'designer'])->paginate($perpage);
                break;
            case 'V':
            case 'v':
                $shop = ShopInfo::where('user_id', $user->id)->first();
                if (!$shop)
                    return response()->json(['success' => false, 'message' => "You dont't have enough perimission to access the data",], 400);
                return Product::where('deleted_at', null)->where("shop_id", $shop->id)->with(['sizes' => function ($sizes) {
                    return $sizes->with(['color']);
                }, 'colors', 'addons', 'images', 'designer'])->paginate();
                break;

                case 'D':
                    case 'd':
                        $designer = Designer::where('user_id', $user->id)->first();
                        if (!$designer)
                            return response()->json(['success' => false, 'message' => "You dont't have enough perimission to access the data",], 400);
                        return Product::where('deleted_at', null)->where("designer_id", $designer->id)->where('shop_id',12)->with(['sizes' => function ($sizes) {
                            return $sizes->with(['color']);
                        }, 'colors', 'addons', 'images', 'designer'])->paginate();
                        break;
            default:
                # code...
                break;
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function deleteproduct(Request $request)
    {
        if (isset($request->pid)) {
            $product = Product::find($request->pid);
            $product->deleted_at = Carbon::now();
            $product->save();
            return response()->json(['success' => !!$product]);
        }
    }
    public function store(Request $request, UploadHelper $helper)
    {
        //


        $user = Auth::user();

        switch ($user->typeofuser) {
            case 'S':
                $product = '';
                $msg = '';
                if (isset($request->id)) {
                    $product = Product::where('id', $request->id)->first();
                    switch ($request->action) {
                        case 'delete':
                            $product->delete();
                            $msg = 'Product has been deleted';
                            return response()->json(['success' => !!$product, 'message' => $msg]);
                            break;
                        case 'update':
                            if (isset($request->name_en)) {
                                $product->name_en = $request->name_en;
                            }
                            if (isset($request->name_ar)) {
                                $product->name_ar = $request->name_ar;
                            }
                            if (isset($request->desc_en)) {
                                $product->desc_en = $request->desc_en;
                            }
                            if (isset($request->desc_ar)) {
                                $product->desc_ar = $request->desc_ar;
                            }
                            if (isset($request->price)) {
                                $product->price = $request->price;
                            }
                            if (isset($request->offerprice)) {
                                $product->offerprice = $request->offerprice;
                            }
                            if (isset($request->isoffer)) {
                                $product->isoffer = $request->isoffer;
                            }
                            if (isset($request->stocks)) {
                                $product->stocks = $request->stocks;
                            }
                            if (isset($request->shop_id)) {
                                $product->shop_id = $request->shop_id;
                            }
                            if (isset($request->designer_id)) {
                                $product->designer_id = $request->designer_id;
                            }
                            if (isset($request->sizes)) {
                                Size::where('product_id', $request->id)->delete();
                                Color::where('product_id', $request->id)->delete();
                                foreach ($request->sizes as $size) {
                                    $arr = array();
                                    $arr['product_id'] = $product->id;
                                    $arr['value'] = $size['value'];
                                    if (isset($size['others'])) {
                                        $arr['others'] = $size['others'];
                                    }
                                    $arr['price'] = $size['price'];
                                    if (isset($size['stocks'])) {
                                        $arr['stocks'] = $size['stocks'];
                                    }
                                    if (isset($size['image']) && $size['image'] != null) {
                                        $arr['image'] = $helper->store($size['image']);
                                    }
                                    $sizes = Size::create($arr);

                                    if (isset($size['color'])) {
                                        foreach ($size['color'] as $color) {
                                            $arr = array();
                                            $arr['product_id'] = $product->id;
                                            $arr['value'] = $color['value'];
                                            if (isset($color['others'])) {
                                                $arr['others'] = $color['others'];
                                            }
                                            $arr['stock'] = $color['stock'];
                                            $arr['size_id'] = $sizes->id;
                                            $color = Color::create($arr);
                                        }
                                    }
                                }
                            }

                            $msg = 'Product has been updated';

                            $product->save();
                            return response()->json(['success' => !!$product, 'message' => $msg]);
                            break;
                    }
                } else {
                    $validator = Validator::make($request->all(), [
                        "name_en" => "required",
                        "price" => "required",
                        "desc_en" => "required",
                        "prodcat_id" => "required"
                    ]);

                    if ($validator->fails()) {
                        return response()->json(["error" => $validator->errors(),  "status_code" => 0]);
                    }


                    $data = array();
                    if (isset($request->name_en)) {
                        $data['name_en'] = $request->name_en;
                    }
                    if (isset($request->name_ar)) {
                        $data['name_ar'] = $request->name_ar;
                    }
                    if (isset($request->desc_en)) {
                        $data['desc_en'] = $request->desc_en;
                    }
                    if (isset($request->desc_ar)) {
                        $data['desc_ar'] = $request->desc_ar;
                    }
                    if (isset($request->price)) {
                        $data['price'] = $request->price;
                    }
                    if (isset($request->offerprice)) {
                        $data['offerprice'] = $request->offerprice;
                    }
                    if (isset($request->isoffer)) {
                        $data['isoffer'] = $request->isoffer;
                    }
                    if (isset($request->stocks)) {
                        $data['stocks'] = $request->stocks;
                    }
                    if (isset($request->prodcat_id)) {
                        $data['prodcat_id'] = $request->prodcat_id;
                    }
                    if (isset($request->shop_id)) {
                        $data['shop_id'] = $request->shop_id;
                    }

                    if (isset($request->eventcat_id)) {
                        $data['eventcat_id'] = $request->eventcat_id;
                    }
                    $product = Product::create($data);

                    if (isset($request->sizes)) {
                        foreach ($request->sizes as $size) {
                            $arr = array();
                            $arr['product_id'] = $product->id;
                            $arr['value'] = $size['value'];
                            if (isset($size['others'])) {
                                $arr['others'] = $size['others'];
                            }
                            $arr['price'] = $size['price'];
                            if (isset($size['stocks'])) {
                                $arr['stocks'] = $size['stocks'];
                            }
                            if (isset($size['image']) && $size['image'] != null) {
                                $arr['image'] = $helper->store($size['image']);
                            }
                            $sizes = Size::create($arr);

                            if (isset($size['color'])) {
                                foreach ($size['color'] as $color) {
                                    $arr = array();
                                    $arr['product_id'] = $product->id;
                                    $arr['value'] = $color['value'];
                                    if (isset($color['others'])) {
                                        $arr['others'] = $color['others'];
                                    }
                                    $arr['stock'] = $color['stock'];
                                    $arr['size_id'] = $sizes->id;
                                    $color = Color::create($arr);
                                }
                            }
                        }
                    }

                    // if (isset($request->sizes)) {
                    //     foreach ($request->sizes as $size) {
                    //         $arr = array();
                    //         $arr['product_id'] = $product->id;
                    //         $arr['value'] = $size['value'];
                    //         if (isset($size['others'])) {
                    //             $arr['others'] = $size['others'];
                    //         }
                    //         $arr['price'] = $size['price'];
                    //         if (isset($size['stocks'])) {
                    //             $arr['stocks'] = $size['stocks'];
                    //         }
                    //         if (isset($size['image']) && $size['image'] != null) {
                    //             $arr['image'] = $helper->store($size['image']);
                    //         }

                    //         $sizequery = Size::create($arr);
                    //     }
                    // }
                    // if (isset($request->colors)) {
                    //     foreach ($request->colors as $color) {
                    //         $arr = array();
                    //         $arr['product_id'] = $product->id;
                    //         $arr['value'] = $color['value'];
                    //         $arr['others'] = $color['others'];
                    //         $arr['stock'] = $color['stock'];
                    //         $arr['size_id'] = $sizequery->id;


                    //         $sizes = Color::create($arr);
                    //     }
                    // }
                    if (isset($request->addons)) {
                        foreach ($request->addons as $addon) {
                            $arr = array();
                            $arr['product_id'] = $product->id;
                            $arr['name_en'] = $addon['name_en'];
                            if (isset($addon['name_ar'])) {

                                $arr['name_ar'] = $addon['name_ar'];
                            }

                            $arr['desc_en'] = $addon['desc_en'];
                            if (isset($addon['desc_ar'])) {

                                $arr['desc_ar'] = $addon['desc_ar'];
                            }
                            $arr['others'] = $addon['others'];
                            $arr['price'] = $addon['price'];
                            $sizes = Addon::create($arr);
                        }
                    }

                    if (isset($request->images)) {
                        foreach ($request->images as $image) {
                            $arr = array();
                            $arr['product_id'] = $product->id;
                            $arr['url'] = $helper->store($image['img']);


                            $sizes = Image::create($arr);
                            // return response()->json(['Sizes' => !!$sizes , 'message' => $msg, 'Size'=>$sizes]);

                        }
                    }

                    $product = Product::with(['addons', 'sizes' => function ($sizes) {
                        return $sizes->with(['color']);
                    }, 'colors', 'designer', 'images'])->find($product->id);

                    $msg = 'Product has been added';
                    return response()->json(['success' => !!$product, 'message' => $msg, 'product' => $product]);
                }
                break;
            case 'V':
                $validator = Validator::make($request->all(), [
                    "name_en" => "required",
                    "price" => "required",
                    "desc_en" => "required",

                ]);

                if ($validator->fails()) {
                    return response()->json(["error" => $validator->errors(),  "status_code" => 0]);
                }



                $data = array();
                if (isset($request->name_en)) {
                    $data['name_en'] = $request->name_en;
                }
                if (isset($request->name_ar)) {
                    $data['name_ar'] = $request->name_ar;
                }
                if (isset($request->desc_en)) {
                    $data['desc_en'] = $request->desc_en;
                }
                if (isset($request->desc_ar)) {
                    $data['desc_ar'] = $request->desc_ar;
                }
                if (isset($request->price)) {
                    $data['price'] = $request->price;
                }
                if (isset($request->offerprice)) {
                    $data['offerprice'] = $request->offerprice;
                }
                if (isset($request->isoffer)) {
                    $data['isoffer'] = $request->isoffer;
                }
                if (isset($request->stocks)) {
                    $data['stocks'] = $request->stocks;
                }
                if (isset($request->prodcat_id)) {
                    $data['prodcat_id'] = $request->prodcat_id;
                }
                if (isset($request->shop_id)) {
                    $data['shop_id'] = $request->shop_id;
                }
                if (isset($request->shop_id)) {
                    $data['shop_id'] = $request->shop_id;
                }
                if (isset($request->eventcat_id)) {
                    $data['eventcat_id'] = $request->eventcat_id;
                }
                if ($request->offerprice == 0) {
                    $data['isoffer'] = 0;
                }
                if (isset($request->productid)) {
                    $product = Product::find($request->productid);
                    $product->update($data);
                } else {
                    $product = Product::create($data);
                }


                if (isset($request->productid)) {
                    Size::where('product_id', $request->productid)->delete();
                    Color::where('product_id', $request->productid)->delete();
                }
                if (isset($request->sizes)) {
                    foreach ($request->sizes as $size) {
                        $arr = array();
                        $arr['product_id'] = $product->id;
                        $arr['value'] = $size['value'];
                        if (isset($size['others'])) {
                            $arr['others'] = $size['others'];
                        }
                        $arr['price'] = $size['price'];
                        if (isset($size['stocks'])) {
                            $arr['stocks'] = $size['stocks'];
                        }
                        if (isset($size['image']) && $size['image'] != null) {
                            $arr['image'] = $helper->store($size['image']);
                        }
                        $sizes = Size::create($arr);

                        if (isset($size['color'])) {
                            foreach ($size['color'] as $color) {
                                $arr = array();
                                $arr['product_id'] = $product->id;
                                $arr['value'] = $color['value'];
                                if (isset($color['others'])) {
                                    $arr['others'] = $color['others'];
                                }
                                $arr['stock'] = $color['stock'];
                                $arr['size_id'] = $sizes->id;
                                $color = Color::create($arr);
                            }
                        }
                    }
                }
                if (isset($request->addons)) {
                    foreach ($request->addons as $addon) {
                        $arr = array();
                        $arr['product_id'] = $product->id;
                        $arr['name_en'] = $addon['name_en'];
                        if (isset($addon['name_ar'])) {

                            $arr['name_ar'] = $addon['name_ar'];
                        }

                        $arr['desc_en'] = $addon['desc_en'];
                        if (isset($addon['desc_ar'])) {

                            $arr['desc_ar'] = $addon['desc_ar'];
                        }
                        $arr['others'] = $addon['others'];
                        $arr['price'] = $addon['price'];
                        $sizes = Addon::create($arr);
                    }
                }
                // if (isset($request->colors)) {
                //     foreach ($request->colors as $color) {
                //         $arr = array();
                //         $arr['product_id'] = $product->id;
                //         $arr['value'] = $color['value'];
                //         $arr['others'] = $color['others'];


                //         $sizes = Color::create($arr);
                //     }
                // }

                if (isset($request->images)) {
                    foreach ($request->images as $image) {
                        $arr = array();
                        $arr['product_id'] = $product->id;
                        $arr['url'] = $helper->store($image['img']);


                        $sizes = Image::create($arr);
                        // return response()->json(['Sizes' => !!$sizes , 'message' => $msg, 'Size'=>$sizes]);

                    }
                }

                // if (isset($request->tags)) {
                //     foreach ($request->tags as $tags) {
                //         $arr = array();
                //         $arr['product_id'] = $product->id;
                //         $arr['tag_id'] = $tags;

                //         $tags = Productag::create($arr);
                //     }
                // }


                $product = Product::with(['addons', 'sizes' => function ($sizes) {
                    return $sizes->with(['color']);
                }, 'colors', 'designer', 'images'])->find($product->id);

                $msg = 'Product has been added';
                return response()->json(['success' => !!$product, 'message' => $msg, 'product' => $product]);
                break;

            case 'D':
                $validator = Validator::make($request->all(), [
                    "name_en" => "required",
                    "price" => "required",
                    "desc_en" => "required",

                ]);

                if ($validator->fails()) {
                    return response()->json(["error" => $validator->errors(),  "status_code" => 0]);
                }


               $myuser = User::with('designer')->find($user->id);
                $data = array();
                $data['shop_id'] = 12;
                $data['designer_id'] = $myuser->designer->id;
                if (isset($request->name_en)) {
                    $data['name_en'] = $request->name_en;
                }
                if (isset($request->name_ar)) {
                    $data['name_ar'] = $request->name_ar;
                }
                if (isset($request->desc_en)) {
                    $data['desc_en'] = $request->desc_en;
                }
                if (isset($request->desc_ar)) {
                    $data['desc_ar'] = $request->desc_ar;
                }
                if (isset($request->price)) {
                    $data['price'] = $request->price;
                }
                if (isset($request->offerprice)) {
                    $data['offerprice'] = $request->offerprice;
                }
                if (isset($request->isoffer)) {
                    $data['isoffer'] = $request->isoffer;
                }
                if (isset($request->stocks)) {
                    $data['stocks'] = $request->stocks;
                }
                if (isset($request->prodcat_id)) {
                    $data['prodcat_id'] = $request->prodcat_id;
                }
                if (isset($request->shop_id)) {
                    $data['shop_id'] = $request->shop_id;
                }
                
                if (isset($request->eventcat_id)) {
                    $data['eventcat_id'] = $request->eventcat_id;
                }
                if ($request->offerprice == 0) {
                    $data['isoffer'] = 0;
                }
                if (isset($request->designer_id)) {
                    $data['designer_id'] = $request->designer_id;
                }
                if (isset($request->productid)) {
                    $product = Product::find($request->productid);
                    $product->update($data);
                } else {
                    $product = Product::create($data);
                }
                

                if (isset($request->productid)) {
                    Size::where('product_id', $request->productid)->delete();
                    Color::where('product_id', $request->productid)->delete();
                }
                if (isset($request->sizes)) {
                    foreach ($request->sizes as $size) {
                        $arr = array();
                        $arr['product_id'] = $product->id;
                        $arr['value'] = $size['value'];
                        if (isset($size['others'])) {
                            $arr['others'] = $size['others'];
                        }
                        $arr['price'] = $size['price'];
                        if (isset($size['stocks'])) {
                            $arr['stocks'] = $size['stocks'];
                        }
                        if (isset($size['image']) && $size['image'] != null) {
                            $arr['image'] = $helper->store($size['image']);
                        }
                        $sizes = Size::create($arr);

                        if (isset($size['color'])) {
                            foreach ($size['color'] as $color) {
                                $arr = array();
                                $arr['product_id'] = $product->id;
                                $arr['value'] = $color['value'];
                                if (isset($color['others'])) {
                                    $arr['others'] = $color['others'];
                                }
                                $arr['stock'] = $color['stock'];
                                $arr['size_id'] = $sizes->id;
                                $color = Color::create($arr);
                            }
                        }
                    }
                }
                if (isset($request->addons)) {
                    foreach ($request->addons as $addon) {
                        $arr = array();
                        $arr['product_id'] = $product->id;
                        $arr['name_en'] = $addon['name_en'];
                        if (isset($addon['name_ar'])) {

                            $arr['name_ar'] = $addon['name_ar'];
                        }

                        $arr['desc_en'] = $addon['desc_en'];
                        if (isset($addon['desc_ar'])) {

                            $arr['desc_ar'] = $addon['desc_ar'];
                        }
                        $arr['others'] = $addon['others'];
                        $arr['price'] = $addon['price'];
                        $sizes = Addon::create($arr);
                    }
                }
                // if (isset($request->colors)) {
                //     foreach ($request->colors as $color) {
                //         $arr = array();
                //         $arr['product_id'] = $product->id;
                //         $arr['value'] = $color['value'];
                //         $arr['others'] = $color['others'];


                //         $sizes = Color::create($arr);
                //     }
                // }

                if (isset($request->images)) {
                    foreach ($request->images as $image) {
                        $arr = array();
                        $arr['product_id'] = $product->id;
                        $arr['url'] = $helper->store($image['img']);


                        $sizes = Image::create($arr);
                        // return response()->json(['Sizes' => !!$sizes , 'message' => $msg, 'Size'=>$sizes]);

                    }
                }

                if (isset($request->tags)) {
                    foreach ($request->tags as $tags) {
                        $arr = array();
                        $arr['product_id'] = $product->id;
                        $arr['tag_id'] = $tags;

                        $tags = Productag::create($arr);
                    }
                }


                $product = Product::with(['addons', 'sizes' => function ($sizes) {
                    return $sizes->with(['color']);
                }, 'colors', 'designer', 'images'])->find($product->id);

                $msg = 'Product has been added';
                return response()->json(['success' => !!$product, 'message' => $msg, 'product' => $product]);
                break;
            default:
                # code...
                break;
        }
    }

    public function undoDelete(Request $request)
    {
        $user = Auth::user();
        if (isset($request->id)) {
            $undo = Product::find($request->id);
            $undo->deleted_at = NULL;
            $undo->save();

            return response()->json(['success' => !!$undo, 'message' => 'done']);
        }

        return response()->json(['error' => 'You are not permitted']);
    }

    public function toggleFeatured(Request $request)
    {
        $user = Auth::user();
        $prodsave = false;
        $msg = "You are currently logged out, please login to update.";
        $status = 400;
        if ($user) {
            if (isset($request->id)) {

                $prod =  Product::find($request->id);
                if ($prod) {
                    $prod->featured = $request->featured;
                    $prodsave = $prod->save();
                }
                $msg = "Product has been updated.";
                $status = 200;
            }
        }

        return response()->json(['success' => !!$prodsave, 'msg' => $msg], $status);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
    public function getProducts(Request $request)
    {
        $sortBy = "updated_at";
        $sortOrder = "desc";
        if (isset($request->sort)) {
            switch ($request->sort) {
                case 'price':
                    $sortBy = 'price';
                    break;
                case 'name':
                    $sortBy = 'name_en';
                    break;
                case 'id':
                    $sortBy = 'id';
                    break;
                case 'created_at':
                    $sortBy = 'created_at';
                    break;
                default:
                    # code...
                    break;
            }
        }

        if (isset($request->order) && $request->order == "asc") {
            $sortOrder = "asc";
        }
        $product = Product::where('deleted_at', null)->where("stocks", ">", 0)->with(['sizes', 'colors', 'addons', 'images', 'designer', ]);

        if (isset($request->delete)) {

            $product =  Product::find($request->id);
            $product->deleted_at = Carbon::now();
            $product->save();
            // return $product->orderby($sortBy, $sortOrder)->paginate();
            $msg = 'Product has been deleted';
            return response()->json(['success' => !!$product, 'message' => $msg]);
        }
        if (isset($request->eventcat_id)) {

            $product = $product->where("eventcat_id", $request->eventcat_id);
        }
        if (isset($request->room)) {
            // 13 is a category reserved for room purposes
            $product = $product->where("eventcat_id", 13);
        }

        return $product->orderby($sortBy, $sortOrder)->paginate();
    }

    public function stockcheck(Request $request)
    {
        $product = Product::where('deleted_at', null)->where("stocks", ">", 0)->with(['sizes', 'colors', 'addons', 'images', 'designer']);

        if (isset($request->product)) {
            $arr = array();
            // This is used for fetch products for array
            foreach ($request->product as $requestproduct) {
                if ($requestproduct['color'] != -1) {

                    $color = Color::find($requestproduct['color']);
                    if ($color->stock >= $requestproduct['qty']) {
                        array_push($arr, [
                            "id" => $requestproduct['id'], "stock" => $color->stock

                        ]);
                    }
                } else {
                    if (($requestproduct['color'] == -1)  && ($requestproduct['size'] != -1)) {
                        $size = Size::find($requestproduct['size']);
                        if ($size->stocks != null) {
                            if ($size->stocks >= $requestproduct['qty']) {
                                array_push($arr, ["id" => $requestproduct['id'], "stock" => $size->stocks]);
                            }
                        }
                    }

                    if (($requestproduct['color']  == -1) && ($requestproduct['size'] == -1)) {
                        $product = Product::find($requestproduct['id']);
                        if ($product->stocks >= $requestproduct['qty']) {
                            array_push($arr, ["id" => $requestproduct['id'], "stock" => $product->stocks]);
                        }
                    }
                }
            }
            return $arr;
            $product = $product->whereIn("id", $request->products);
        }
    }


    public function search(Request $request)
    {
        $user = Auth::user();
        $shop = ShopInfo::where('user_id', $user->id)->first();
        if (!$shop)
            return response()->json(['success' => false, 'message' => "You dont't have enough perimission to access the data",], 400);
        $name = $request->name;
        return Product::where('deleted_at', null)->where("shop_id", $shop->id)->where(function ($searching) use ($name) {
            $searching->where('name_ar', 'like', "%" . $name . "%",)->orwhere('name_en', 'like', "%" . $name . "%",);
        })->with(['sizes', 'colors', 'addons', 'images', 'designer'])->paginate();
    }
}

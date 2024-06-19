<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product as ProductDetails;
use App\Models\ProductImages;
use App\Models\ProductDiscounts;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    /**
    * Add product function to create new prodcuts 
    */

    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'product_name' => 'required|max:100',
            'product_description' => 'required|max:500',
            'product_slug' => 'required|max:100', 
            'product_price' => 'required',
            'discount_type' => 'required|string',
            'discount_amount' => 'required',
            //'product_images' => 'required'
        ]);

        if ($validator->fails()) 
        {  
            return response()->json(['error'=>$validator->errors()], 401); 
        }  

        $productDetails = new ProductDetails();
        $productDetails->name = $request->product_name;
        $productDetails->description = $request->product_description;
        $productDetails->slug = $request->product_slug;
        $productDetails->price = $request->product_price;
        $productDetails->active = 1;

        $productDiscounts = new ProductDiscounts();
        $productDiscounts->type = $request->discount_type;
        $productDiscounts->discount = $request->discount_amount;
        
        if($productDetails->save())
        {
            $productDetails->productDiscounts()->Save($productDiscounts);

            $productImages = new ProductImages();
            $productImages->path = "image-path-1.png";
            $productDetails->productImages()->Save($productImages);
    
            $productImages = new ProductImages();
            $productImages->path = "image-path-2.png";
            $productDetails->productImages()->Save($productImages);
    
            $productImages = new ProductImages();
            $productImages->path = "image-path-2.png";
            $productDetails->productImages()->Save($productImages);
    
            $productImages = new ProductImages();
            $productImages->path = "image-path-4.png";
            $productDetails->productImages()->Save($productImages);
    
            return response()->json([
                'success' => 'Product created successfully'
            ], Response::HTTP_OK);
        }
        else
        {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, product is not created'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

       
    }

    /**
    * getProduct function is created for get the product details as per product id 
    */

    public function getProduct(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'product_id' => 'required' 
        ]);

        if ($validator->fails()) 
        {  
            return response()->json(['error'=>$validator->errors()], 401); 
        }  
        
        $product_id = $request->product_id;
        
        $productDetails = ProductDetails::where('id', $product_id)
                            ->where('active', 1)
                            ->first();

        if($productDetails)
        {
            $images = [];
            $discountedPrice = $productDetails->price;

            $productImages = ProductDetails::find($product_id)->ProductImages;

            if($productImages)
            {
                foreach($productImages as $image)
                {
                    $images[] = $image->path;
                }
            }

            $productDiscounts = ProductDetails::find($product_id)->ProductDiscounts;

            /**
            * Calculated discount as per discount type
            */

            if($productDiscounts)
            {
                if($productDiscounts->type == "percent")
                {
                    $discountedPrice = ($productDiscounts->discount/100) * $productDetails->price;
                }

                if($productDiscounts->type == "amount")
                {
                    $discountedPrice = $productDetails->price - $productDiscounts->discount;
                }
            }
            
            $product = array(
                'id' => $productDetails->id,
                'name' => $productDetails->name,
                'description' => $productDetails->description,
                'slug' => $productDetails->slug,
                'price' => array(
                    'full' => $productDetails->price,
                    'discounted' => $discountedPrice
                ),
                'discount' => array(
                    'type' => $productDiscounts->type ,
                    'amount' => $productDiscounts->discount 
                ),
                'images' => $images
            );

            return response()->json($product, Response::HTTP_OK);
        }
        else
        {
            return response()->json(['error' => "Product not found"], 404);
        }    
    }
}

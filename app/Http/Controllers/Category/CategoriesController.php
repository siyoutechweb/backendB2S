<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// use Illuminate\Support\Facades\Request;

class CategoriesController extends Controller
{

    // public function getCategoryList($id) {
    //     $subCat = Category::find($id);
    //     $parentCat = $subCat->getParentCategory;

    //     return response()->json($parentCat);
    // }
    public function deleteCategory($id)
    {
        $user=AuthController::me();
        if ($user->hasRole('Super_Admin')) {
        $category = Category::find($id);
        $category->Delete();
        return response()->json(["msg" => "the category has been deleted !!"]);
        }
    }


    public function updateCategory(Request $request, $id)
    {
        $user=AuthController::me();
        if ($user->hasRole('Super_Admin')) {
        $category = Category::findorfail($id);
        $category->category_name = $request->input('category_name');
        $category->parent_category_id = $request->input('parent_category_id');
        if ($category->save()) {
            return response()->json($category);
        }
        return response()->json(["msg" => "ERROR !!"]);
    }
}
    public function addCategory(Request $request)
    {   $user=AuthController::me();
        if ($user->hasRole('Super_Admin')) {
        $category = new Category();
        $category->category_name = $request->input('enter the cataegory name');
        $category->parent_category_id = $request->input('choose the parent category');
        $category->save();
            return response()->json("The Category has been added Successfully !!");
        }
        return response()->json("Error !!");
    
    }
    public function getSupplierCategories()
    {
        $supplier = AuthController::me();
        $suppCategoryList = $supplier->getSupplierCategoryThroughProduct()->distinct()->orderBy('parent_category_id')->get(); 
        $parentCategoryIds = $supplier->getSupplierCategoryThroughProduct()->distinct()->pluck('parent_category_id');
        $categoryIds = array();
        foreach($suppCategoryList as $category) {
            $categoryIds[] = $category->id;
        }
        $categoryList = Category::whereIn('id', $parentCategoryIds)->with(['getChildCategories' => function($query) use ($categoryIds) {
            $query->whereIn('id', $categoryIds);
        }])->get();
        return response()->json($categoryList, 200);
    }
    public function getCategoryParent($id)
    {
        $subCat = Category::Find($id);
        $parentCat = $subCat->getParentCategory;

        return response()->json($parentCat);
    }

    public function getCategoryChild($id)
    {
        $parentCat = Category::find($id);
        $subCat = $parentCat->getChildCategories;
        return response()->json($subCat);
    }

    public function ShowCategory($id)
    {
        $category = Category::findorfail($id)->with('subCategories')->get();
        return response()->json($category);
    }
    public function getCategories()
    {
        $categories = Category::whereNull('parent_category_id')->with('subCategories')->get();
        return response()->json($categories);
    }
}

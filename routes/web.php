<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->get('/key', function () {
    return \Illuminate\Support\Str::random(32);
});

$router->post('/login', [
    'uses' => 'AuthController@login'
]);


// $router->group(['prefix' => 'infos', "roles" => ["SalesManager"]], function () use ($router) {
//     $router->get('/', ["uses" => "AuthController@infos", "as" => "infos"]);
// });

$router->get('/infos', [
    "uses" => "AuthController@infos",
    "as" => "infos",
    "roles" => ["salesmanager"]
]);

$router->post('/addSalesManager', ['uses' => 'User\UsersController@addSalesManager']);
$router->post('/addSupplier', ['uses' => 'User\UsersController@addSupplier']);
$router->post('/addShop_Owner', ['uses' => 'User\UsersController@addShop_Owner']);
$router->get('/getInvalidUsers', ['uses' => 'User\UsersController@getInvalidUsers']);
$router->put('/validateUser/{userId}', ['uses' => 'User\UsersController@validateUser']);


$router->group(['prefix' => 'users'], function () use ($router) {
    $router->get('/suppliers', ['uses' => 'User\UsersController@getSupplierList']);
    $router->get('/shops', ['uses' => 'User\UsersController@getShopsList']);
    $router->get('/SM', ['uses' => 'User\UsersController@getSalesManagersList']);
    $router->delete('/{id}', ['uses' => 'User\UsersController@deleteUser']);
    // $router->get('/{id}', ['uses' => 'User\UsersController@ShowUser']);
    $router->get('/all', ['uses' => 'User\UsersController@UsersList']);
    $router->get('/GetUsersByRole/{id}', ['uses' => 'User\UsersController@GetUsersByRole']);
    $router->put('/{id}', ['uses' => 'User\UsersController@UpdateUser']);
    $router->get('/GetUserByRole/{id}', ['uses' => 'User\UsersController@GetUserByRole']);
    $router->get('/Account',['uses'=>'User\UsersController@account']);
    $router->put('/block/{user_id}',['uses'=>'User\UsersController@blockUser']);
    // $router->post('/addSalesManager', ['uses' => 'User\UsersController@affectSalesManagerToShop']);
  
});

$router->group(['prefix' => 'supplier', "roles" => ["Supplier"]], function () use ($router) {
    $router->get('/orders/shops', ['uses' => 'User\UsersController@getSupplierOrderShop']);
    $router->get('/salesmanagers/shops', ['uses' => 'User\UsersController@getSupplierSalesmanagerShop']);
    $router->post('/shops', ['uses' => 'User\UsersController@linkSalesManagerToShop']);
    $router->post('/salesmanager', ['uses' => 'User\UsersController@addSalesManagerToSupplier']);
    $router->get('/salesmanager', ['uses' => 'User\UsersController@getSalesManagerList']);
    $router->put('/salesmanager', ['uses' => 'User\UsersController@updateSalesmanagerCommission']);    
    $router->put('/shops', ['uses' => 'User\UsersController@updatedepositshop']);    
    $router->post('/salesmanager/search', ['uses' => 'User\UsersController@getSalesManagerByEmail']);
    $router->post('/shop/search', ['uses' => 'User\UsersController@getShopOwnerByEmail']);
});

$router->group(['prefix' => 'products',  "roles" => ["Supplier", "Shop_Owner"]], function () use ($router) {
    $router->post('/', ['uses' => 'Product\ProductsController@addProduct']);
    $router->get('/filter', ['uses' => 'Product\ProductsController@getFilteredProductsList']);
    $router->get('/supplier', ['uses' => 'Product\ProductsController@getProductsofSupplier']);
    $router->get('/salesmanager', ['uses' => 'Product\ProductsController@getSalesmanagerProducts']);
    $router->post('/upload', ['uses' => 'Product\ProductsController@addProductsWithExcel']);
    // $router->get('/', ['uses' => 'Product\ProductsController@getProductBySupplier']);
    $router->get('/by_suppliers', ['uses' => 'Product\ProductsController@GetAllSupplierWithProducts']);
    $router->get('/by_category', ['uses' => 'Product\ProductsController@GetAllCategoryWithProducts']);
    $router->get('/{id}', ['uses' => 'Product\ProductsController@showProduct']);
    $router->get('/', ['uses' => 'Product\ProductsController@productList']);
    $router->put('/{id}', ['uses' => 'Product\ProductsController@updateProduct']);
    $router->delete('/{id}', ['uses' => 'Product\ProductsController@deleteProduct']);
    $router->get('/category/{id}', ['uses' => 'Product\ProductsController@getProductByCategory']);
    $router->get('/supplier/{id}', ['uses' => 'Product\ProductsController@getProductBySupplier']);
});

// $router->group(['prefix' => 'categories'], function () use ($router) {
//     $router->get('/{id}', ['uses' => 'Category\CategoriesController@getCategoryList']);
// });

$router->group(['prefix' => 'categories', "roles" => ["Supplier", "Shop_Owner"]], function () use ($router) {
    $router->get('/supplier', ['uses' => 'Category\CategoriesController@getSupplierCategories']);
    $router->get('/', ['uses' => 'Category\CategoriesController@getCategories']);
    $router->delete('/{id}', ['uses' => 'Category\CategoriesController@deleteCategory']);
    $router->put('/{id}', ['uses' => 'Category\CategoriesController@updateCategory']);
    $router->get('/getCategoryParent/{id}', ['uses' => 'Category\CategoriesController@getCategoryParent']);
    $router->get('/getCategoryChild/{id}', ['uses' => 'Category\CategoriesController@getCategoryChild']);
    $router->get('/{id}', ['uses' => 'Category\CategoriesController@ShowCategory']);
    $router->post('/', ['uses' => 'Category\CategoriesController@addCategory']);
});


$router->group(['prefix' => 'orders', "roles" => ["Supplier", "Shop_Owner", "SalesManager"]], function () use ($router) {
    $router->post('/test', ['uses' => 'Order\OrdersController@ifSalesmanager']);
    $router->post('/', ['uses' => 'Order\OrdersController@addOrder']);
    $router->get('/calculateCom', ['uses' => 'Order\OrdersController@calculateCommissionValue']);
    $router->get('/shop', ['uses' => 'Order\OrdersController@getShopOwnerOrderList']);
    $router->get('/supplier', ['uses' => 'Order\OrdersController@getSupplierOrderList']);
    $router->get('/salesmanager', ['uses' => 'Order\OrdersController@getSalesmanagerOrderList']);
    $router->put('/{order_id}', ['uses' => 'Order\OrdersController@updateOrderStatus']);
    // $router->put('/{id}', ['uses' => 'OrdersController@UpdateOrder']);
    // $router->delete('/{id}', ['uses' => 'OrdersController@DeleteOrder']);
    // $router->get('/getOrderlistByShop/{shopId}', ['uses' => 'OrdersController@getOrderlistByShop']);
    // $router->get('/getOrderlistBySupplier/{supplierId}', ['uses' => 'OrdersController@getOrderlistBySupplier']);
    // $router->get('/{orderId}', ['uses' => 'OrdersController@getOrder']);
    // $router->post('addToBasket', ['uses' => 'OrdersController@addToBasket']);
    // $router->put('editeOrder/{orderId}', ['uses' => 'OrdersController@editeOrder']);
    // $router->get('getOrderInBasket/{shopOwnerId}', ['uses' => 'OrdersController@getOrderInBasket']);
    // $router->put('validateOrderByShop/{orderId}', ['uses' => 'OrdersController@validateOrderByShop']);
    // $router->get('getOrderNoValidated/{supplierId}', ['uses' => 'OrdersController@getOrderNoValidated']);
    // $router->put('ValidateOrderBySupplier/{orderId}', ['uses' => 'OrdersController@ValidateOrderBySupplier']);
    // $router->get('getInvoice/{orderId}', ['uses' => 'OrdersController@getInvoice']);
});


/**
 * Routes for resource statut
 */
$router->group(['prefix' => 'statuts'], function () use ($router) {
    $router->post('/', ['uses' => 'Statut\StatutsController@addStatut']);
    $router->get('/', ['uses' => 'Statut\StatutsController@statutlist']);
    $router->get('/{id}', ['uses' => 'Statut\StatutsController@ShowStatut']);
    $router->delete('/{id}', ['uses' => 'Statut\StatutsController@DeleteStatut']);
    $router->put('/{id}', ['uses' => 'Statut\StatutsController@UpdateStatut']);
});



/**
 * Routes for resource commission
 */
$router->group(['prefix' => 'commissions'], function () use ($router) {
    $router->post('/', ['uses' => 'Commission\CommissionsController@addCommission']);
    $router->put('/{id}', ['uses' => 'Commission\CommissionsController@updateCommission']);
    $router->delete('/{id}', ['uses' => 'Commission\CommissionsController@DeleteCommission']);
    $router->get('/{id}', ['uses' => 'Commission\CommissionsController@GetCommissionbySupplier']);
});
$router->group(['prefix' => 'siyoucommissions'], function () use ($router) {
    $router->post('/', ['uses' => 'SiyouCommissionsController@addCommission']);
    $router->put('/{id}', ['uses' => 'SiyouCommissionsController@updateCommission']);
    $router->delete('/{id}', ['uses' => 'SiyouCommissionsController@DeleteCommission']);
    $router->get('/{id}', ['uses' => 'SiyouCommissionsController@GetCommission']);
    $router->get('/supplier', ['uses' => 'SiyouCommissionsController@GetsupplierCommission']);
    $router->get('/', ['uses' => 'SiyouCommissionsController@GetCommissionlist']);
    $router->put('/UpdateDeposit/{supplier_id}', ['uses' => 'SiyouCommissionsController@UpdateDeposit']);
});
$router->group(['prefix' => 'funds'], function () use ($router) {
    $router->get('/', ['uses' => 'fund\FundsController@FundsList']);
});
$router->group(['prefix' => 'catalogs'], function () use ($router){
    $router->post('/',['uses'=>'Catalog\CatalogsController@AddCatalog']);
    $router->post('/AddProductstocatalog/{id}',['uses'=>'Catalog\CatalogsController@AddProductstocatalog']);
    $router->get('/TopProductslist',['uses'=>'Catalog\CatalogsController@TopProductslist']);
    $router->get('/supplier_Cataloglist',['uses'=>'Catalog\CatalogsController@supplier_Cataloglist']);
    $router->get('/Supplier_showCatalog/{id}',['uses'=>'Catalog\CatalogsController@Supplier_showCatalog']);
    $router->put('/{id}',['uses'=>'Catalog\CatalogsController@UpdateCatalog']);
    $router->get('/',['uses'=>'Catalog\CatalogsController@Cataloglist']);
    $router->delete('/{id}',['uses'=>'Catalog\CatalogsController@DeleteCatalog']);
    $router->get('/{id}',['uses'=>'Catalog\CatalogsController@getCatalogsBysupplier']);
    });
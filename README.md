# laravel-route-notes
Laravel framework extension, native annotation generates route
The advantage is that the routing file is directly generated, the routing is not analyzed in operation, and the efficiency is improved.


English | [中文介绍](https://github.com/lovefc/laravel-route-notes/blob/master/doc/readme-zh.md)

## Use environment
* [PHP](https://php.net/) >= 8.0
* [Laravel](https://laravel.com/) >= 9.0

## How to install it
Use composer to install directly:
```bash
composer require --dev lovefc/laravel-route-notes
```
## Command using
```
php artisan notes:route [-p dirname] [-f filename]
```
**\-p** The directory name of the controller to be generated, the default is `app/http/controllers/`.

**\-f** The address of the generated routing file, which defaults to ` route/date ("y-m-d-his") .php`.


> If you don't specify a controller directory, all controller files under app/Http/Controllers/ will be scanned for generation by default.

## Annotation use
First, the annotation function should be marked on in the controller class, so that running the command will generate the route.
```
<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\User;


#[annotate('true')]
class UserController extends Controller
{
    #[get('/show')]
    public function show()
    {
        return view('welcome');
    }
}
```
The above is a conventional controller, and you must add #[annotate('true')] to the declaration class, so that annotations will be generated.
Global attributes can be declared on the annotation of the class, such as:

`#[annotate('true'),prefix('/user')]`

In this way, the following method comments will be automatically prefixed. Of course, you can also change this prefix on the method.

The attribute of annotation method is basically the same as that of routing.
For example:

`#[get('show'),prefix('/user'),middleware('myauth')]`

The above declared annotation will eventually generate the following route:

`Route::prefix("/user")->post("all",[userController::class,"show"])->middleware("myauth"); `

In addition, where regular validation is also supported:

`#[get('show/{name}'),where(['name'=>'[a-z]+'])]`

Or this again:

`#[get('show/{name}'),where('name','[a-z]+')]`

In addition, you can declare the global where attribute on the class annotation:
`
#[annotate('true'),prefix('/user'),where(['name'=>'[a-z]+'])]`

Redirect annotation case:
```
#[annotate('true')]
class MyController extends Controller
{
	#[get('/index')]
    public function index(Request $request){
		return view('welcome');
	}
	
	#[redirect('/','/index')]
	public function home(){
		
	}
}
```
The attribute names of class annotations and method annotations are as follows:

All comments of the class will be automatically registered in the method comments, and can also be covered in the method comments.

| Annotation class attribute (global attribute) | Method attribute |
| --- | --- |
| prefix,name,where,domain,middleware | prefix,name,where,domain,middleware，post,get,any,match,options,patch,view,redirect,put,delete |


## License
Laravel-route-notes is released under the MIT license
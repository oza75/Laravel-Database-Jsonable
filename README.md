### Laravel package to fill certain fields in JSON format
this package allows you to fill some fields of your model in json format. 
For example, if you have an avatars table, you can easily save 
and retrieve the thumbnails of your avatars with this package

### Installation 
```
   composer require oza/laravel-database-jsonable 
```
### Usage
  - Just add `DatabaseJsonable` trait and `$jsonable` 
 property that contains jsonable fields to your model.
 
 ```php
 <?php

 namespace App;
 
 use Illuminate\Database\Eloquent\Model;
 use Oza\DatabaseJsonable\Traits\DatabaseJsonable;
 
 class Posts extends Model
 {
     Use DatabaseJsonable;
 
     protected $jsonable = [
         'actions'
     ];
     
     protected $guarded = [];
 }
 ```
 ###Api
 
 - Now your `actions` field will be a Jsonable class that contains lots of methods that you can use to
  add, edit, remove items in your field
  ```php
  $post = Posts::first();
  $id = $post->actions->add(['type' => 'like', 'count' => 1234])
  //output 1
  ```
 - You can also add data like this
 ```php
 Posts::create(['content' => 'blablab', 'actions' => ['type' => 'like', 'count' => 0] ]) 
 // output an instance of App\Posts
 ```
  If you do this, all the fields contained in the jsonable property of your model will be directly encoded in json and save
 - #### Define Schema for jsonable field
 Always typing the board to be transmitted can be a bit tiring.
 To allow you to save your energy and continue coding
 pretty application Laravel, the jsonable fields  can take a schema to follow.
 To add just modify your jsonable property like that:
 ```php
 <?php

 namespace App;
 
 use Illuminate\Database\Eloquent\Model;
 use Oza\DatabaseJsonable\Traits\DatabaseJsonable;
 
 class Posts extends Model
 {
     Use DatabaseJsonable;
 
     protected $jsonable = [
         'actions' => [ 'type' , 'count' ]
     ];
     
     protected $guarded = [];
 }
 ```
 Then you can easily add data like this:
```php
  $post = Posts::first();
  $id = $post->actions->add('like', 12345)
  //output 1

  ```
  **You can also use strict mode by adding `strictJsonableSchema` property**   
 ```php
 <?php

 namespace App;
 
 use Illuminate\Database\Eloquent\Model;
 use Oza\DatabaseJsonable\Traits\DatabaseJsonable;
 
 class Posts extends Model
 {
     Use DatabaseJsonable;
 
     protected $jsonable = [
         'actions' => [ 'type' , 'count' ]
     ];
     
     protected $strictJsonableSchema = true;
     
     protected $guarded = [];
 }
 ```   
 
 - Retrieve data
 
 All items saved are [Laravel Collection](https://laravel.com/docs/5.6/collections), which gives
  you access to many methods that you can use to make your life easier.
 ```php
  $post = Posts::first();
  $post->actions->all();
  // return an array of all items   
``` 
- You can also retrieve data like this
```php
$post->actions->items;
// Return a laravel Collection
```
- Get First item 
```php
$post->actions->first(); 
// return a Laravel Collection

$post->actions->first()->all();
// return an array

$post->actions->items->first();
// Return a Laravel Collection

$post->actions->items->first()->all();
// Return an array

```
- Get last Item
```php
$post->actions->last(); 
// return a Laravel Collection

$post->actions->last()->all();
// return an array

$post->actions->items->last();
// Return a Laravel Collection

$post->actions->items->last()->all();
// Return an array

```
- Get with id

Get an item with its id
```php
$post = Posts::create(['contents' => 'blabla', 'actions' => ['type' => 'like', 'count' => 0]]);
$id = $post->actions->add(['like', 12345);
$item = $post->actions->get($id); 
// return a Laravel Collection
```
- Change value or add new entry

change the value of an entry in your jsonable field
```php
$post = Posts::create(['contents' => 'blabla', 'actions' => ['type' => 'like', 'count' => 0]]);
$id = $post->actions->first()['id'];
$post->actions->add('like', 146);

$item = $post->actions->change($id, 'count', 147); 
// return a Laravel Collection

$item->get('count'); 
// output 147

$item->get('count', 'default-value');
// if a count key does not exist the default value will be return

$item = $post->actions->change($id, 'user_id', 1);
$item->get('user_id');
//output 1
```
- Update an Item

Totally change an entry
```php
$item = $post->actions->items->firstWhere('id', 1);
$item['count'] = 457;
$item['type'] = 'comments';
$item['user_id'] = 1
$post->actions->update($item['id'], $item);
// output 
[
 [
    'count' => 457,
    'type' => 'comments',
    'user_id' => 1
 ]
 ...
]

```
- Remove an item

```php
 $post->actions->remove(2);
 // output true
```

- Add timestamps to entries

just set `jsonableTimestamps` to your model
```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Oza\DatabaseJsonable\Traits\DatabaseJsonable;

class Posts extends Model
{
    Use DatabaseJsonable;

    protected $jsonable = [
        'actions' => [
            'type', 'count'
        ]
    ];
    
     protected $strictJsonableSchema = true;
     protected $jsonableTimestamps = true;
     
    protected $guarded = [];
}

```  
Then when you add some items the timestamps will be set

- Each item is a Laravel Collection

As I mentioned above all items are Laravel collections, 
which opens the door to many methods on array. 
For all available methods, see here [Laravel Collection](https://laravel.com/docs/5.6/collections)

```php
//e.g:
$post->actions->items->firstWhere('id', 1)->map(function ($value) {
    return Str::camel($value);
})
```
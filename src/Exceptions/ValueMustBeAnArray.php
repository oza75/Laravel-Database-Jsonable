<?php
/**
 * Created by PhpStorm.
 * User: oza
 * Date: 13/05/18
 * Time: 03:52
 */

namespace Oza\DatabaseJsonable\Exceptions;


class ValueMustBeAnArray extends \Exception
{
  public $message = "The Jsonable field must contains an array.";

}
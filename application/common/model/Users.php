<?php

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class Users extends Model
{
    use SoftDelete;
    protected $pk = 'id';
    protected $table = 'user';
    protected $defaultSoftDelete = 0;
}

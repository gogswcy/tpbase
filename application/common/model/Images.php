<?php

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class Images extends Model
{
    use SoftDelete;
    protected $table = 'image';
    protected $pk = 'id';
    protected $defaultSoftDelete = 0;
}

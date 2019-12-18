<?php

namespace app\admin\controller;

use app\common\model\Log as AppLog;
use think\Controller;
use think\Request;

class Log extends Controller
{
    public function index()
    {
        $conditions = [];
        $name = input('get.name') ?? '';
        $action = input('get.action') ?? '';
        $datemin = input('get.datemin') ?? '';
        $datemax = input('get.datemax') ?? '';
        if ($name)
            $conditions[] = ['name', 'like', '%'.$name.'%'];
        if ($action)
            $conditions[] = ['action', 'like', '%'.$action.'%'];
        if ($datemin)
            $conditions[] = ['create_time', '>=', strtotime($datemin)];
        if ($datemax)
            $conditions[] = ['create_time', '<', 24*60*60+strtotime($datemax)];
        $pageSize = 15;
        // halt($conditions);
        $logs = AppLog::order('create_time desc')
            ->where($conditions)
            ->paginate($pageSize, false, ['query' => request()->param()]);

        return view('log/index', ['pageSize' => $pageSize, 'logs' => $logs]);
    }
}

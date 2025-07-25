<?php

namespace DagaSmart\OperationLog\Models;

use DagaSmart\BizAdmin\Admin;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use DagaSmart\OperationLog\OperationLogServiceProvider;

class OperationLog extends Model
{
    protected $table = 'admin_operation_log';

    protected $appends = ['method_color'];

    const METHODS = ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH'];

    public function user()
    {
        return $this->belongsTo(Admin::adminUserModel());
    }

    public function methodColor(): Attribute
    {
        $color = [
            'GET'    => 'processing',
            'POST'   => 'success',
            'PUT'    => 'warning',
            'DELETE' => 'error',
        ];

        return Attribute::get(fn() => $color[$this->method] ?? 'gray');
    }

    public function path():Attribute
    {
        $settings = OperationLogServiceProvider::setting('path_map');
        return Attribute::get(function ($value) use ($settings) {
            if(!$settings){
                return $value;
            }

            foreach ($settings as $item) {
                if (Str::is($item['path'], $value)) {
                    return "[{$item['name']}] {$value} ";
                }
            }

            return $value;
        });
    }

    /**
     * 清理商户操作日志
     * @return bool
     */
    public function clean(): bool
    {
        return $this->query()
            ->where('module', admin_current_module(true))
            ->where('mer_id', admin_mer_id())
            ->delete();
    }

    /**
     * 清空平台操作日志并重建索引
     * @return bool
     */
    public function truncate(): bool
    {
        $this->query()->truncate();
        return true;
    }

}

<?php

namespace System\OperationLog;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use DagaSmart\BizAdmin\Extend\ServiceProvider;
use System\OperationLog\Http\Middleware\OperationLogMiddleware;

class OperationLogServiceProvider extends ServiceProvider
{
    protected $middleware = [OperationLogMiddleware::class];

    protected $menu = [
        [
            'title' => '操作日志',
            'url'   => '/admin_operation_log',
            'icon'  => 'fluent:document-bullet-list-clock-20-regular',
        ],
    ];

    public function settingForm()
    {
        return $this->baseSettingForm()->tabs([
            amisMake()->Tab()->title('基础设置')->body([
                amisMake()->TagControl('except', '排除的路由')->description('路由路径，使用 Str::is() 匹配')->clearable(),
                amisMake()->TagControl('secret_fields', '敏感字段')->description('会替换成 ******')->clearable(),
                amisMake()->TagControl('allowed_methods', '允许记录的请求方法')->description('为空则记录所有请求方法'),
            ]),
            amisMake()->Tab()->title('路径映射')->body([
                amisMake()
                    ->ComboControl('path_map', '路径映射')
                    ->description('用于名称显示，使用 Str::is() 匹配')
                    ->multiLine()
                    ->multiple()
                    ->items([
                        amisMake()
                            ->SelectControl('path', '路径')
                            ->required()
                            ->options($this->routeList())
                            ->creatable()
                            ->searchable()
                            ->unique(true),
                        amisMake()->TextControl('name', '名称')->required(),
                    ]),
            ])
        ]);
    }

    protected function routeList()
    {
        $replacePrefix = function ($path) {
            $prefix = [config('admin.route.prefix') . '/'];
            $module = config('admin.modules');
            if ($module) {
                foreach ($module as $item) {
                    $_prefix  = config(strtolower($item) . '.admin.route.prefix');
                    $prefix[] = $_prefix ? $_prefix . '/' : '';
                }
            }

            return preg_replace('/\{.*?\}/', '*', Str::remove($prefix, $path));
        };

        return collect(Route::getRoutes()->getRoutes())
            ->pluck('uri')
            ->map($replacePrefix)
            ->unique()
            ->values()
            ->toArray();
    }
}

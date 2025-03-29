<?php

namespace System\OperationLog\Http\Controllers;

use DagaSmart\BizAdmin\Controllers\AdminController;
use System\OperationLog\Services\OperationLogService;

/**
 * @property OperationLogService $service
 */
class OperationLogController extends AdminController
{
    protected string $serviceName = OperationLogService::class;

    public function list()
    {
        $crud = $this->baseCRUD()
            ->headerToolbar([...$this->baseHeaderToolBar()])
            ->footable()
            ->filter(
                $this->baseFilter()->body([
                    amis()->TextControl('path', '请求地址')->clearable()->size('md'),
                    amis()->SelectControl('method', '请求方法')->clearable()->size('md')->options($this->service->getModel()::METHODS),
                    amis()->TextControl('user', '用户')->clearable()->size('md'),
                    amis()->TextControl('ip', 'IP')->clearable()->size('md'),
                ])
            )
            ->columns([
                amis()->TableColumn('id', 'ID')->sortable(),
                amis()->TableColumn('path', '请求地址'),
                amis()->TableColumn('method', '请求方法')->type('tag')->color('${method_color}'),
                amis()->TableColumn('user.name', '用户'),
                amis()->TableColumn('ip', 'IP'),
                amis()->TableColumn('input', '请求数据')->breakpoint('*')->type('json')->jsonTheme('eighties'),
                amis()
                    ->TableColumn()
                    ->label(__('admin.created_at'))
                    ->name('created_at')
                    ->type('datetime')
                    ->sortable(true),
                $this->rowActions([
                    $this->rowDeleteButton()
                ]),
            ]);

        return $this->baseList($crud);
    }

    public function form()
    {
        return $this->baseForm()->body([
            amis()->TextControl()->label(__('admin.admin_role.name'))->name('name')->required(),
            amis()->TextControl()
                ->label(__('admin.admin_role.slug'))
                ->name('slug')
                ->description(__('admin.admin_role.slug_description'))
                ->required(),
        ]);
    }

    public function detail()
    {
        return $this->baseDetail()->body([]);
    }
}

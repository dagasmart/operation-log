<?php

namespace DagaSmart\OperationLog\Http\Controllers;

use DagaSmart\BizAdmin\Controllers\AdminController;
use DagaSmart\OperationLog\Services\OperationLogService;

/**
 * @property OperationLogService $service
 */
class OperationLogController extends AdminController
{
    protected string $serviceName = OperationLogService::class;

    public function list()
    {
        $crud = $this->baseCRUD()
            ->headerToolbar([
                ...$this->baseHeaderToolBar(),
                amis()->DialogAction()->label('清理当前记录')
                    ->icon('fa fa-delete')
                    ->dialog([
                        'closeOnEsc' => true,
                        'closeOnOutside' => true,
                        'title' => '系统提示',
                        'size' => 'sm',
                        'body' => [
                            [
                                'type' => 'form',
                                'body'=> '清理当前相关日志数据么? 将不可恢复',
                                'api'=> admin_url($this->queryPath.'/clean'),
                                'reload'=> 'crud',
                            ]
                        ]
                    ]),
                amis()->DialogAction()
                    ->label('清空数据')
                    ->icon('fa fa-brush')
                    ->hidden(admin_user()->mer_id)
                    ->dialog([
                        'closeOnEsc' => true,
                        'closeOnOutside' => true,
                        'title' => '系统提示',
                        'size' => 'sm',
                        'body' => [
                            [
                                'type' => 'form',
                                'body'=> '清空所有日志数据么? 将不可恢复',
                                'api'=> admin_url($this->queryPath.'/truncate'),
                                'reload'=> 'crud',
                            ]
                        ]
                    ]),
            ])
            ->footable()
            ->filter(
                $this->baseFilter()->body([
                    amis()->TextControl('path', '请求地址')->clearable()->size('md'),
                    amis()->SelectControl('method', '请求方法')->clearable()->size('md')->options($this->service->getModel()::METHODS),
                    amis()->TextControl('user', '用户')->clearable()->size('md'),
                    amis()->TextControl('ip', 'IP')->clearable()->size('md'),
                ])
            )
            ->autoFillHeight(true)
            ->columns([
                amis()->TableColumn('id', 'ID')->sortable(),
                amis()->TableColumn('path', '请求地址')->searchable(),
                amis()->TableColumn('method', '请求方法')->type('tag')->color('${method_color}'),
                amis()->TableColumn('user.name', '用户')->searchable(['name'=>'user','type'=>'input-text']),
                amis()->TableColumn('ip', 'IP')->searchable(),
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


    //清理相关数据
    public function clean()
    {
        $this->service->clean();
        return $this->response()->successMessage('当前相关日志清理' . __('admin.successfully'));
    }

    //清空数据并重建索引
    public function truncate()
    {
        if ($this->service->truncate()) {
            return $this->response()->successMessage('所有日志数据清空' . __('admin.successfully'));
        }
        return $this->response()->fail($this->service->getError() ?? '所有日志数据清空' . __('admin.failed'));
    }


}

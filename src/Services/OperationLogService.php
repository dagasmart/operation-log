<?php

namespace System\OperationLog\Services;

use Illuminate\Database\Eloquent\Builder;
use DagaSmart\BizAdmin\Services\AdminService;
use System\OperationLog\Models\OperationLog;

/**
 * @method OperationLog getModel()
 * @method OperationLog|Builder query()
 */
class OperationLogService extends AdminService
{
    protected string $modelName = OperationLog::class;

    public function listQuery()
    {
        $path   = request('path');
        $method = request('method');
        $user   = request('user');
        $ip     = request('ip');

        $query = $this->query()->orderByDesc($this->sortColumn());

        $this->sortable($query);

        return $query->with('user')
            ->when($path, fn($query) => $query->where('path', 'like', "%{$path}%"))
            ->when($method, fn($query) => $query->where('method', $method))
            ->when($user, fn($query) => $query->whereHas('user', fn($query) => $query->where('name', 'like', "%{$user}%")))
            ->when($ip, fn($query) => $query->where('ip', 'like', "%{$ip}%"));
    }
}

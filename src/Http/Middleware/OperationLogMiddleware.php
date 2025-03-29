<?php

namespace System\OperationLog\Http\Middleware;

use Closure;
use DagaSmart\BizAdmin\Admin;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use System\OperationLog\Models\OperationLog;
use System\OperationLog\OperationLogServiceProvider;

class OperationLogMiddleware
{
    protected array $secretFields = ['password', 'password_confirmation'];

    protected array $except = ['current-user', 'menus', '_settings', 'admin_operation_log', 'dev_tools/*'];

    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldLogOperation($request)) {
            $user = Admin::user();

            $log = [
                'user_id' => $user ? $user->id : 0,
                'path'    => substr($this->currentPath(), 0, 255),
                'method'  => $request->method(),
                'ip'      => $request->getClientIp(),
                'module'  => Admin::currentModule(true),
                'input'   => $this->formatInput($request->input()),
            ];

            @OperationLog::unguarded(fn() => OperationLog::create($log));
        }

        return $next($request);
    }

    protected function formatInput(array $input)
    {
        foreach ($this->getSecretFields() as $field) {
            if ($field && !empty($input[$field])) {
                $input[$field] = '******';
            }
        }

        return json_encode($input, JSON_UNESCAPED_UNICODE);
    }

    protected function shouldLogOperation(Request $request)
    {
        return !$this->inExceptArray() && $this->inAllowedMethods($request->method());
    }

    protected function inAllowedMethods($method)
    {
        $allowedMethods = collect($this->getAllowedMethods())->filter();

        if ($allowedMethods->isEmpty()) {
            return true;
        }

        return $allowedMethods->map(fn($method) => strtoupper($method))->contains($method);
    }

    protected function currentPath()
    {
        $prefix = [config('admin.route.prefix') . '/'];
        $module = config('admin.modules');
        if ($module) {
            foreach ($module as $item) {
                $_prefix = config(strtolower($item) . '.admin.route.prefix');
                $prefix[] = $_prefix ? $_prefix . '/' : '';
            }
        }

        return Str::remove($prefix, request()->path());
    }

    protected function inExceptArray()
    {
        return Str::is($this->except(), $this->currentPath());
    }

    protected function except()
    {
        return array_merge((array)$this->setting('except'), $this->except);
    }

    protected function getSecretFields()
    {
        return array_merge((array)$this->setting('secret_fields'), $this->secretFields);
    }

    protected function getAllowedMethods()
    {
        return (array)(explode(',', $this->setting('allowed_methods')) ?: OperationLog::METHODS);
    }

    protected function setting($key, $default = null)
    {
        return OperationLogServiceProvider::setting($key, $default);
    }
}

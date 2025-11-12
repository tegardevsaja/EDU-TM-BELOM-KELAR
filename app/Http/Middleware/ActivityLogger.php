<?php
namespace App\Http\Middleware;

use App\Models\Activity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActivityLogger
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            if (!Auth::check()) {
                return $response;
            }
            $user = Auth::user();
            // Log hanya untuk admin/guru
            if (!method_exists($user, 'hasRole') || (!($user->hasRole('admin') || $user->hasRole('guru')))) {
                return $response;
            }

            $method = strtoupper($request->method());
            $route = $request->route();
            $routeName = $route?->getName();
            $path = $request->path();

            // Tulis = POST/PUT/PATCH/DELETE
            $isWrite = in_array($method, ['POST','PUT','PATCH','DELETE']);
            // Baca tertentu: GET ke halaman index/create/show/edit pada modul admin/guru untuk entity utama
            $modules = ['pengguna','users','jurusan','kelas','siswa','penilaian','nilai','sertifikat','tahunajaran','tahun-ajaran'];
            $isUnderRolePrefix = Str::startsWith($path, 'admin/') || Str::startsWith($path, 'guru/');
            $isModule = false;
            foreach ($modules as $m) {
                if (Str::contains($path, '/'.$m)) { $isModule = true; break; }
            }
            $isNamedPage = $routeName && (Str::endsWith($routeName, '.index') || Str::endsWith($routeName, '.create') || Str::endsWith($routeName, '.show') || Str::endsWith($routeName, '.edit'));
            $isPathPage = Str::endsWith($path, '/create') || Str::endsWith($path, '/edit') || preg_match('#/(\\d+)$#', $path);
            $isReadPage = $method === 'GET' && $isUnderRolePrefix && $isModule && ($isNamedPage || $isPathPage);

            if (!$isWrite && !$isReadPage) {
                return $response;
            }

            $action = $routeName ?: ($route?->uri() ?? 'unknown');

            $modelType = null;
            $modelId = null;
            // Ambil id dari parameter umum jika ada
            $params = $route?->parameters() ?? [];
            foreach (['id', 'user', 'siswa', 'kelas', 'jurusan', 'template', 'nilai'] as $key) {
                if (isset($params[$key])) {
                    $modelId = is_object($params[$key]) ? ($params[$key]->id ?? null) : $params[$key];
                    $modelType = is_object($params[$key]) ? get_class($params[$key]) : null;
                    break;
                }
            }

            $meta = [
                'path' => $request->path(),
                'query' => $request->query(),
                'payload_keys' => array_keys($request->except(['password', 'password_confirmation', '_token'])),
                'status' => $response->getStatusCode(),
            ];

            Activity::create([
                'user_id' => $user->id,
                'role' => $user->roles()->first()->name ?? null,
                'action' => (string) $action,
                'method' => strtoupper($request->method()),
                'model_type' => $modelType,
                'model_id' => $modelId ? (string)$modelId : null,
                'metadata' => $meta,
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Jangan ganggu alur utama bila logging gagal
        }

        return $response;
    }
}

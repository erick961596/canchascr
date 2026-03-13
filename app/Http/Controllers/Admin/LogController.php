<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemLog::with('user')->latest();

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }
        if ($request->filled('type')) {
            $query->where('type', 'like', '%'.$request->type.'%');
        }
        if ($request->filled('search')) {
            $query->where('subject', 'like', '%'.$request->search.'%');
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs   = $query->paginate(50)->withQueryString();
        $counts = SystemLog::selectRaw('level, count(*) as total')
            ->groupBy('level')->pluck('total','level');

        return view('pages.admin.logs.index', compact('logs', 'counts'));
    }

    public function show(SystemLog $log)
    {
        return response()->json([
            'id'         => $log->id,
            'level'      => $log->level,
            'type'       => $log->type,
            'subject'    => $log->subject,
            'context'    => $log->context,
            'user'       => $log->user?->name,
            'ip'         => $log->ip,
            'user_agent' => $log->user_agent,
            'created_at' => $log->created_at->format('d/m/Y H:i:s'),
        ]);
    }

    public function clear(Request $request)
    {
        $days = (int) ($request->days ?? 30);
        $deleted = SystemLog::where('created_at', '<', now()->subDays($days))->delete();
        return response()->json(['message' => "{$deleted} logs eliminados (más de {$days} días)."]);
    }
}

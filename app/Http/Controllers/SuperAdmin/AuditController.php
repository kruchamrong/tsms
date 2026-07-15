<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use OwenIt\Auditing\Models\Audit;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Audit::with('user')->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('event', 'like', "%{$search}%")
              ->orWhere('auditable_type', 'like', "%{$search}%");
        }

        if ($request->has('event') && $request->event != '') {
            $query->where('event', $request->event);
        }

        $audits = $query->paginate(15)->withQueryString();

        // Format audits for the frontend
        $audits->getCollection()->transform(function ($audit) {
            $auditableType = class_basename($audit->auditable_type);
            
            // Translate events
            $eventKhmer = $audit->event;
            if ($audit->event === 'created') $eventKhmer = 'បង្កើតថ្មី';
            if ($audit->event === 'updated') $eventKhmer = 'កែប្រែ';
            if ($audit->event === 'deleted') $eventKhmer = 'លុប';
            if ($audit->event === 'restored') $eventKhmer = 'ស្តារឡើងវិញ';

            return [
                'id' => $audit->id,
                'user_name' => $audit->user ? $audit->user->name : 'System',
                'event' => $audit->event,
                'event_khmer' => $eventKhmer,
                'auditable_type' => $auditableType,
                'old_values' => $audit->old_values,
                'new_values' => $audit->new_values,
                'url' => $audit->url,
                'ip_address' => $audit->ip_address,
                'user_agent' => $audit->user_agent,
                'created_at' => $audit->created_at->format('d/m/Y h:i A'),
                'created_at_human' => $audit->created_at->diffForHumans(),
            ];
        });

        return Inertia::render('SuperAdmin/Audits/Index', [
            'audits' => $audits,
            'filters' => $request->only(['search', 'event'])
        ]);
    }
}

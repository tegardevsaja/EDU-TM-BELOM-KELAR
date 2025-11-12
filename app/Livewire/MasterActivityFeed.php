<?php
namespace App\Livewire;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MasterActivityFeed extends Component
{
    public array $items = [];
    public int $limit = 10;

    public function mount(): void
    {
        $this->loadFeed();
    }

    public function refreshFeed(): void
    {
        $this->loadFeed();
    }

    protected function loadFeed(): void
    {
        if (!Auth::check() || !method_exists(Auth::user(), 'hasRole') || !Auth::user()->hasRole('master_admin')) {
            $this->items = [];
            return;
        }
        try {
            $this->items = Activity::with('user')
                ->orderByDesc('created_at')
                ->limit($this->limit)
                ->get()
                ->map(function ($a) {
                    return [
                        'id' => $a->id,
                        'user' => $a->user?->name ?? 'Unknown',
                        'role' => $a->role,
                        'action' => $a->action,
                        'method' => $a->method,
                        'time' => $a->created_at?->diffForHumans(),
                    ];
                })->toArray();
        } catch (\Throwable $e) {
            $this->items = [];
        }
    }

    public function render()
    {
        return view('livewire.master-activity-feed');
    }
}

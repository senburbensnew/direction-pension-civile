<?php

namespace App\View\Components;

use App\Services\SiteVisitService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Footer extends Component
{
    public int $totalVisits;

    public int $todayVisits;

    public function __construct(SiteVisitService $visits)
    {
        try {
            $summary = $visits->summary();
        } catch (\Throwable) {
            $summary = ['total_hits' => 0, 'today_hits' => 0, 'total_visitors' => 0, 'today_visitors' => 0];
        }

        $this->totalVisits = $summary['total_visitors'];
        $this->todayVisits = $summary['today_visitors'];
    }

    public function render(): View|Closure|string
    {
        return view('components.footer');
    }
}

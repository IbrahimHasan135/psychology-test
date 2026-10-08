<?php

namespace Addons\Demo\Dashboard;

use Illuminate\Contracts\View\View;

class DemoOverviewCard
{
    public function id(): string
    {
        return 'demo-overview';
    }

    public function title(): string
    {
        return 'Demo Addon Ready';
    }

    public function icon(): string
    {
        return 'package';
    }

    public function order(): int
    {
        return 10;
    }

    public function permission(): string
    {
        return 'demo.view';
    }

    public function render(): View
    {
        return view('demo::dashboard-card');
    }
}

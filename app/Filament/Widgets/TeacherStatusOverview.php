<?php

namespace App\Filament\Widgets;

use App\Models\Teacher;
use Filament\Widgets\Widget;

class TeacherStatusOverview extends Widget
{
    protected string $view = 'filament.widgets.teacher-status-overview';

        protected int | string | array $columnSpan = 'full';

    // pass counts to view
    public function getData(): array
    {
        return [
            'pending'   => Teacher::where('status', 'pending')->count(),
            'approved'  => Teacher::where('status', 'approved')->count(),
            'rejected'  => Teacher::where('status', 'rejected')->count(),
            'suspended' => Teacher::where('status', 'suspended')->count(),
        ];
    }
}

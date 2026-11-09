<?php

namespace App\Listeners;

use App\Events\OpenWindowClicked;
use Native\Desktop\Facades\Window;

class OpenMainWindow
{
    public function handle(OpenWindowClicked $event): void
    {
        Window::open();
    }
}

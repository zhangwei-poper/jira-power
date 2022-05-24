<?php

namespace Tests\Unit;

use App\Services\WorkLogService;
use Tests\TestCase;

class WorkLogServiceTest extends TestCase
{
    public function testGetWorkLog()
    {
        $service = app(WorkLogService::class);
        $service->getWorkLogsPlainText();
    }
}

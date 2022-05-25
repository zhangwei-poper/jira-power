<?php

namespace Tests\Unit;

use App\Services\WorkLogService;
use Carbon\CarbonImmutable;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Issue\PaginatedWorklog;
use Tests\TestCase;

class WorkLogServiceTest extends TestCase
{
    public function testGetWorkLog()
    {
        $issueService = $this->mock(IssueService::class);
        $issueService->shouldReceive('search')
            ->withAnyArgs()
            ->andReturn(tap(new \stdClass(), function ($obj) {
                $obj->issues = [
                    tap(new \stdClass(), function ($obj) {
                        $obj->id = 1;
                        $obj->key = 'TEST-1';
                        $obj->fields = (object)[
                            'summary' => 'test',
                        ];
                    }),
                ];
            }));

        $workLogs = $this->mock(PaginatedWorklog::class);
        $workLogs->shouldReceive('getTotal')->andReturn(1);
        $workLogs->shouldReceive('getWorklogs')->andReturn([
            tap(new \stdClass(), function ($obj) {
                $obj->timeSpent = '1h';
                $obj->started = CarbonImmutable::now()->toDateTimeString();
                $obj->comment = (object)[
                    'content' => [
                        (object)['type' => 'text', 'text' => "test line 1\n\ntest line 2"],
                        (object)[
                            'type'    => 'paragraph',
                            'content' => [
                                (object)['type' => 'text', 'text' => 'test line 3 from paragraph'],
                            ],
                        ],
                    ],
                ];
            }),
        ]);
        $issueService->shouldReceive('getWorkLog')->andReturn($workLogs);


        $service = new WorkLogService($issueService);
        $text = $service->getWorkLogsPlainText();
        self::assertSame(<<<EOT
1. TEST-1 test (1h)
    - test line 1
    - test line 2
    - test line 3 from paragraph

EOT,
            $text);
    }
}

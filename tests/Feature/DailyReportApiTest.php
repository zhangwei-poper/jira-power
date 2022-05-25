<?php

namespace Tests\Feature;

use Tests\TestCase;

class DailyReportApiTest extends TestCase
{
    public function testApi()
    {
        $response = $this->post('/api/daily-report', [
            'jira_host' => env('JIRA_HOST'),
            'jira_user' => env('JIRA_USER'),
            'jira_pass' => env('JIRA_PASS'),
        ]);
        $response->assertStatus(200);
        self::assertTrue(isset($response->json()['text']));
    }
}

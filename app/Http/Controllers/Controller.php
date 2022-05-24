<?php

namespace App\Http\Controllers;

use App\Services\WorkLogService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\IssueService;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(Request $request)
    {
        return view('welcome', [
            'defaults' => $this->getDefaults($request),
        ]);
    }

    const DAILY_REPORT_TEMPLATE = <<<'EOT'
【今日の作業內容】
%s

【今日の体調】
正常

【今日の作業環境】
家
EOT;


    public function dailyReport(Request $request)
    {
        $params = $request->validate([
            'jira_host' => 'required|url',
            'jira_user' => 'required|email',
            'jira_pass' => 'required',
        ]);

        $cookie = cookie();
        $cookie->queue('jira_host', $params['jira_host']);
        $cookie->queue('jira_user', $params['jira_user']);
        $cookie->queue('jira_pass', $params['jira_pass']);

        try {
            $dailyReportLines = $this->createService(
                $params['jira_host'],
                $params['jira_user'],
                $params['jira_pass']
            )->getWorkLogsPlainText();
        } catch (\Exception $exception) {
            report($exception);
            return view('welcome', [
                'error'    => $exception->getMessage(),
                'defaults' => $this->getDefaults($request),
            ]);
        }

        return view('welcome', [
            'text'     => sprintf(self::DAILY_REPORT_TEMPLATE, implode("\n", $dailyReportLines)),
            'defaults' => $this->getDefaults($request),
        ]);
    }

    private function createService($jiraHost, $jiraUser, $jiraPassword)
    {
        $config = new ArrayConfiguration([
            'jiraHost'          => $jiraHost,
            'jiraUser'          => $jiraUser,
            'jiraPassword'      => $jiraPassword,
            'useV3RestApi'      => true,
            'useTokenBasedAuth' => false,
        ]);
        return new WorkLogService(
            new IssueService($config),
        );
    }

    private function getDefaults(Request $request)
    {
        return [
            'jira_host' => $request->cookie('jira_host'),
            'jira_user' => $request->cookie('jira_user'),
            'jira_pass' => $request->cookie('jira_pass'),
        ];
    }

}

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
            'defaults' => $this->getDefaultsFromCookie($request),
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

    const FORM_INPUTS = [
        'jira_host' => 'required|url',
        'jira_user' => 'required|email',
        'jira_pass' => 'required',
    ];


    public function dailyReport(Request $request)
    {
        $params = $request->validate(self::FORM_INPUTS);
        $cookie = cookie();
        foreach ($params as $key => $value) {
            $cookie->queue($key, $value);
        }

        try {
            $text = $this->createService(
                $params['jira_host'],
                $params['jira_user'],
                $params['jira_pass']
            )->getWorkLogsPlainText();
        } catch (\Exception $exception) {
            report($exception);
            return view('welcome', [
                'error'    => $exception->getMessage(),
                'defaults' => $this->getDefaultsFromInput($request),
            ]);
        }

        return view('welcome', [
            'text'     => sprintf(self::DAILY_REPORT_TEMPLATE, $text),
            'defaults' => $this->getDefaultsFromInput($request),
        ]);
    }

    public function dailyReportApi(Request $request)
    {
        $params = $request->validate(self::FORM_INPUTS);
        try {
            $text = $this->createService(
                $params['jira_host'],
                $params['jira_user'],
                $params['jira_pass']
            )->getWorkLogsPlainText();

            return response()->json([
                'text' => sprintf(self::DAILY_REPORT_TEMPLATE, $text),
            ]);

        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'error' => $exception->getMessage(),
            ], 500);
        }
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


    private function getDefaultsFromCookie(Request $request)
    {
        $defaults = [];
        foreach (array_keys(self::FORM_INPUTS) as $key) {
            $defaults[$key] = $request->cookie($key);
        }
        return $defaults;
    }

    private function getDefaultsFromInput(Request $request)
    {
        $defaults = [];
        foreach (array_keys(self::FORM_INPUTS) as $key) {
            $defaults[$key] = $request->input($key);
        }
        return $defaults;
    }

}

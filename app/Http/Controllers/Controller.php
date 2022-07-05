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
        'others'    => 'nullable',
    ];


    public function dailyReport(Request $request)
    {
        $params = $request->validate(self::FORM_INPUTS);
        $cookie = cookie();
        foreach ($params as $key => $value) {
            $cookie->forever($key, $value);
        }

        try {
            $text = $this->createService(
                $params['jira_host'],
                $params['jira_user'],
                $params['jira_pass']
            )->getWorkLogsPlainTextContent();
        } catch (\Exception $exception) {
            report($exception);
            return view('welcome', [
                'error'    => $exception->getMessage(),
                'defaults' => $this->getDefaultsFromInput($request),
            ]);
        }

        return view('welcome', [
            'text'     => self::formatContentToText($text, $params['others'] ?? ''),
            'defaults' => $this->getDefaultsFromInput($request),
        ]);
    }

    private static function formatContentToText(array $content, $others)
    {
        $lines = [];
        foreach ($content as $nth => $part) {
            $lines[] = "$nth. {$part['title']} ({$part['cost']})";
            foreach ($part['content'] as $line) {
                $lines[] = "    - " . $line;
            }
            $lines[] = "";
        }

        if ($others) {
            $nth = ($nth ?? 0) + 1;
            foreach (explode("\n", $others) as $line) {
                if (!trim($line)) {
                    continue;
                }

                $lines[] = "$nth. {$line}";
                $lines[] = "";
                $nth++;
            }
        }

        return sprintf(self::DAILY_REPORT_TEMPLATE, implode("\n", $lines));
    }

    public function dailyReportApi(Request $request)
    {
        $params = $request->validate(self::FORM_INPUTS);
        try {
            $text = $this->createService(
                $params['jira_host'],
                $params['jira_user'],
                $params['jira_pass']
            )->getWorkLogsPlainTextContent();

            return response()->json([
                'content' => $text,
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

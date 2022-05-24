<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use JiraRestApi\Issue\IssueService;

class WorkLogService
{
    public function __construct(private IssueService $issueService)
    {
    }

    public function getWorkLogsPlainText(CarbonImmutable $date = null)
    {
        $date = $date ?? CarbonImmutable::today();

        $works = [];
        $result = $this->issueService->search('worklogAuthor = currentUser() and worklogDate = now()');
        foreach ($result->issues as $issue) {
            $issueTitle = "{$issue->key} {$issue->fields->summary}";
            $works[$issueTitle] = [];
            $workLogs = $this->issueService->getWorklog($issue->id);
            if ($workLogs->getTotal() === 0) {
                continue;
            }

            foreach ($workLogs->getWorklogs() as $workLog) {
                $workLogStartedDate = CarbonImmutable::parse($workLog->started);
                if (!$workLogStartedDate->isSameDay($date)) {
                    continue;
                }

                $works[$issueTitle][] = [
                    'timeSpent'     => $workLog->timeSpent,
                    'content'       => $workLog->comment->content,
                    'content_plain' => self::formatContentToTextLines($workLog->comment->content),
                ];
            }

        }

        $text = [];
        $nth = 1;
        foreach ($works as $issueTitle => $work) {
            foreach ($work as $workLog) {
                $text[] = "$nth. $issueTitle ({$workLog['timeSpent']})";
                $nth++;
                foreach ($workLog['content_plain'] as $line) {
                    $text[] = "\t$line";
                }
            }
        }

        return $text;
    }

    private static function formatContentToTextLines($content): array
    {
        $text = [];
        foreach ($content as $block) {
            switch ($block->type) {
                default:
                    if (isset($block->content)) {
                        $text = array_merge($text, self::formatContentToTextLines($block->content));
                    }
                    break;

                case 'text':
                    $text = array_merge($text, explode("\n", $block->text));
                    break;

                case 'codeBlock':
                case 'paragraph':
                    $text = array_merge($text, self::formatContentToTextLines($block->content));
                    break;
            }
        }

        return array_filter(array_map(function ($line) {
            return trim($line);
        }, $text));
    }

}

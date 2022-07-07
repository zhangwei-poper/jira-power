<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use JiraRestApi\Issue\IssueService;

class WorkLogService
{
    public function __construct(private IssueService $issueService)
    {
    }

    public function getWorkLogsPlainTextContent(CarbonImmutable $date = null): array
    {
        $date = $date ?: CarbonImmutable::today();
        $dateString = $date->isToday() ? 'now()' : $date->toDateString();

        $works = [];
        $result = $this->issueService->search("worklogAuthor = currentUser() and worklogDate = {$dateString}");
        foreach ($result->issues as $issue) {
            $issueTitle = "{$issue->key} {$issue->fields->summary}";
            $works[$issueTitle] = [];
            $workLogs = $this->issueService->getWorklog($issue->id);
            if ($workLogs->getTotal() === 0) {
                continue;
            }

            foreach ($workLogs->getWorklogs() as $workLog) {
                $workLogStartedDate = CarbonImmutable::parse($workLog->started, $date->getTimezone());
                if (!$workLogStartedDate->isSameDay($date)) {
                    continue;
                }
                if (false === isset($workLog->author['emailAddress'])) {
                    continue; // skip worklogs not from current user
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
                $part = [
                    'title'   => $issueTitle,
                    'content' => [],
                    'cost'    => $workLog['timeSpent'],
                ];
                foreach ($workLog['content_plain'] as $line) {
                    $part['content'][] = ltrim(trim($line), "- \t\n\r\0\x0B");
                }
                $text[$nth++] = $part;
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
            }
        }

        return array_filter(array_map(function ($line) {
            return trim($line);
        }, $text));
    }

}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Ucp;
use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Services\SessionHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Event;
use Carbon\Carbon;

class SessionController extends Controller
{
    private SessionHistoryService $sessionHistoryService;

    public function __construct(SessionHistoryService $sessionHistoryService)
    {
        $this->sessionHistoryService = $sessionHistoryService;
    }

    public function getSessionDetails(Request $request, int $sessionId): JsonResponse
    {
        $this->checks();
        $accountId = intval(session('account.id'));

        $sessionDetails = $this->sessionHistoryService->getSessionDetail(
            $accountId,
            $sessionId,
        );

        return response()->json([
            'success' => true,
            'data' => $sessionDetails
        ]);
    }

    public function getRelatedEvents(Request $request): JsonResponse
    {
        $sessionRefId = $request->input('session_ref_id');
        $timestamp = $request->input('timestamp');

        $relatedEvents = [];
        $date = Carbon::parse($timestamp);
        $current = $date->format('Y-m-d H:i:s');
        $id = $date->timestamp;

        $relatedSessions = Session::query()->where('session_ref_id', '=', $sessionRefId)->get();

        foreach ($relatedSessions as $item) {
            $start = $item->session_start instanceof Carbon
                ? $item->session_start->format('Y-m-d H:i:s')
                : Carbon::parse($item->session_start)->format('Y-m-d H:i:s');

            $end = $item->session_end instanceof Carbon
                ? $item->session_end->format('Y-m-d H:i:s')
                : Carbon::parse($item->session_end)->format('Y-m-d H:i:s');

            if ($end >= $current && $start <= $current) {
                $relatedEvents[$id] = Event::query()
                    ->where('event_session_id', '=', $item->session_id)
                    ->get();
            }
        }

        return response()->json([
            'success' => true,
            'data' => $relatedEvents
        ]);
    }

    private function checks()
    {
        if (empty(session('account.id'))) {
            \Log::error('empty account id in ' . __FILE__ . ' ' . __METHOD__);
            abort(500);
        }
    }
}
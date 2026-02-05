<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventEvalForm;
use App\Models\Course;
use App\Models\StudentYear;
use App\Http\Requests\UpdateEventEvalQuestionRequest;
use App\Http\Requests\StoreEvalFormResponseRequest;
use App\Services\EvalFormStep;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class EventEvalFormController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth.event:update,event', only: [
                'updateQuestions',
				'editQuestions'
            ]),
        ];
    }

    public function editQuestions(Event $event)
    {
        return view('events.edit-eval-questions', [
            'event' => $event,
            'question' => $event?->evalForm,
            'backRoute' => route('events.show', ['event' => $event->public_id]),
            'formAction' => route('events.eval-form.update-questions', [
                'event' => $event->public_id
            ])
        ]);
    }

    public function updateQuestions(UpdateEventEvalQuestionRequest $request, 
            Event $event)
    {
        $form = $event->evalForm;
        if (!$form) {
            $form = new EventEvalForm;
            $form->event()->associate($event);
        }
        $form->introduction = $request->introduction;
        $form->overall_satisfaction = $request->overall_satisfaction;
        $form->content_relevance = $request->content_relevance;
        $form->speaker_effectiveness = $request->speaker_effectiveness;
        $form->engagement_level = $request->engagement_level;
        $form->duration = $request->duration;
        $form->topics_covered = $request->topics_covered;
        $form->suggestions_for_improvement = $request->suggestions_for_improvement;
        $form->future_topics = $request->future_topics;
        $form->overall_experience = $request->overall_experience;
        $form->additional_comments = $request->additional_comments;
        $form->acknowledgement = $request->acknowledgement;
        $form->save();
        return redirect()->route('events.show', ['event' => $event->public_id])
            ->with('status', 'Evaluation form updated.');
    }

    public function editResponses(Event $event)
    {
        return view('events/edit-eval-response', [
            'formAction' => route('events.eval-form.update-responses', [
                'event' => $event->public_id
            ]),
            'backRoute' => route('events.edit', ['event' => $event->public_id]),
            'evaluations' => $event->evaluations
        ]);
    }

    public function updateResponses(Request $request, Event $event)
    {
        return redirect()->route('events.show', ['event' => $event->public_id])
            ->with('status', 'Evaluation updated.');
    }
}

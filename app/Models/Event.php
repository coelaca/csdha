<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Traits\HasPublicId;

class Event extends Model
{
    use HasPublicId;

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(EventLink::class);
    }

    public function lastDate()
    {
        if (config('database.default') === 'sqlite') {
            $endDateColQuery = <<<SQL
case
    when "end_time" <= "start_time" then datetime("date" || ' ' || "end_time", '+1 day')
    else datetime("date" || ' ' || "end_time")
end
SQL;
        } else {
            $endDateColQuery = <<<SQL
case
    when "end_time" <= "start_time" then concat("date", ' ', "end_time") + interval 1 day
    else concat("date", ' ', "end_time")
end
SQL;
        }
        return $this->dates()->orderBy('date', 'desc')
            ->orderBy('start_date', 'desc')
            ->orderBy('end_date', 'desc')->first();
    }

    public function gpoa()
    {
        return $this->gpoaActivity->gpoa;
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(EventEvaluation::class);
    }

    public function attachmentSets(): HasMany
    {
        return $this->hasMany(EventAttachmentSet::class);
    }

    public function accomReport(): HasOne
    {
        return $this->hasOne(AccomReport::class);
    }

    public function regisForm(): HasOne
    {
        return $this->hasOne(EventRegisForm::class);
    }

    public function evalForm(): HasOne
    {
        return $this->hasOne(EventEvalForm::class);
    }

    public function gpoaActivity(): BelongsTo
    {
        return $this->belongsTo(GpoaActivity::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fund(): HasOne
    {
        return $this->hasOne(Fund::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'event_attendances')
            ->as('attendance')
            ->withPivot(
                'course_id',
                'student_year_id',
                'student_section_id'
            )
            ->withTimestamps()
            ->using(EventAttendance::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(EventAttendance::class, 'event_id');
    }

    public function studentYears(): BelongsToMany
    {
        return $this->belongsToMany(StudentYear::class, 'event_attendances')
            ->as('attendance')
            ->withPivot(
                'course_id',
                'student_year_id',
                'student_section_id'
            )
            ->using(EventAttendance::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(StudentYear::class, 'event_participants');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'event_participant_courses');
    }

    public function attendeesByYear($year)
    {
        return $this->studentYears()->where('year', '=', $year)->get();
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(EventDeliverable::class);
    }

    public function editors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_editor');
    }

    public function gspoaEvent(): belongsTo
    {
        return $this->belongsTo(GSPOAEvent::class, 'gspoa_event_id');
    }


    public function canBeEditedBy(User $user)
    {
        if ($this->editors->contains($user)
            || $this->creator->is($user)
        ) return true;

        return false;
    }

    public function dates(): HasMany
    {
        return $this->hasMany(EventDate::class);
    }

    public function attendanceTest()
    {
        return $attendance;
    }

    public function allMembersAttend() {
        $members = [
            'BSIT' => ['1', '2', '3', '4'],
            'DIT' => ['1', '2']
        ];
        foreach ($members as $program => $years) {
            foreach ($years as $year) {
                $exists = EventStudent::whereHas(
                    'eventDates.event', function ($query) {
                        $query->whereKey($this->id);
                    })->whereRelation('yearModel', 'year', $year)
                    ->whereRelation('course', 'acronym', $program)->exists();
                if (!$exists) return false;
            }
        }
        return true;
    }

    public function eventData()
    {
        $members = [
            'BSIT' => ['1', '2', '3', '4'],
            'DIT' => ['1', '2']
        ];
        $attendance = collect();
        $attendanceTotal = null;
        $attendanceView = null;
        switch ($this->participant_type) {
        case 'students':
            $attendanceQuery = EventStudent::whereHas(
                'eventDates.event', function ($query) {
                    $query->whereKey($this->id);
            });
            $yearLevels = $this->participants;
            $attendeesListQuery = (clone $attendanceQuery)
                ->whereHas('yearModel', function ($query) use ($yearLevels) {
                    $query->whereIn('id', $yearLevels->pluck('id')->toArray());
            });
            $attendanceTotal = (clone $attendeesListQuery)->count();
            if ($attendanceTotal <= 15) {
                $attendanceView = 'student';
                $attendance = (clone $attendeesListQuery)
                    ->orderBy('last_name', 'asc')->get();
            } elseif ($this->members_only) {
                $attendanceView = 'year';
                foreach ($yearLevels as $yearLevel) {
                    $attendance[$yearLevel->label] = (clone $attendanceQuery)
                        ->whereBelongsTo($yearLevel, 'yearModel')->count();
                }
            } else {
                $attendanceView = 'program';
                foreach ($members as $program => $years) {
                    foreach ($years as $year) {
                        $count = EventStudent::whereHas(
                            'eventDates.event', function ($query) {
                                $query->whereKey($this->id);
                            })->whereRelation('yearModel', 'year', $year)
                            ->whereRelation('course', 'acronym', $program)
                            ->count();
                        if (!$count) continue;
                        $progYear = "{$program} {$year}";
                        $attendance[$progYear] = $count;
                    }
                }
            }
            break;
        case 'officers':
            $attendance = $this->officerAttendees();
            break;
        }
        $eventData = [
            'event' => $this,
            'attendance' => $attendance,
            'attendanceTotal' => $attendanceTotal,
            'attendanceView' => $attendanceView,
/*
            'attendanceView' => 'program',
*/
            'activity' => $this->gpoaActivity,
            'comments' => $this->comments(),
            'ratings' => $this->ratings()
        ];
        return $eventData;
    }

    public function accomReportViewData()
    {
        return [
            'events' => [$this->eventData()],
            'editors' => User::withPerm('accomplishment-reports.edit')
                ->notOfPosition('adviser')->get(),
            'approved' => $this->accomReport?->status === 'approved',
            'president' => $this->accomReport?->president,
        ];
    }

    public function comments()
    {
        $subQuery = $this->evaluations()
                ->select('topics_covered as comment')
                ->where('feature_topics_covered', 1);
        $commentQueries = [
            $this->evaluations()
                ->select('suggestions_for_improvement as comment')
                ->where('feature_suggestions_for_improvement', 1),
            $this->evaluations()
                ->select('future_topics as comment')
                ->where('feature_future_topics', 1),
            $this->evaluations()
                ->select('overall_experience as comment')
                ->where('feature_overall_experience', 1),
            $this->evaluations()
                ->select('additional_comments as comment')
                ->where('feature_additional_comments', 1)
        ];
        foreach ($commentQueries as $commentQuery) {
            $subQuery->unionAll($commentQuery);
        }
        return DB::query()->fromSub($subQuery, 'comment')
            ->orderByRaw('length(comment) desc')->pluck('comment');
    }

    public function compactDates()
    {
        $dates = $this->dates()->orderBy('date', 'asc')
            ->orderBy('start_date', 'asc')->get();
        $newDates = [];
        $dateCount = count($dates);
        for ($i = 0; $i < $dateCount; ++$i) {
            $fullDate = $dates[$i]->date_fmt . ' | ' .
                $dates[$i]->start_time_fmt . ' - ' . $dates[$i]->end_time_fmt;
            while ($i + 1 < $dateCount && Carbon::parse($dates[$i]->date)->toDateString()
                    === Carbon::parse($dates[$i + 1]->date)->toDateString()) {
                ++$i;
                if ($i + 1 < $dateCount) {
                    $fullDate .= ', ' . $dates[$i]->start_time_fmt . ' - ' .
                        $dates[$i]->end_time_fmt;
                } else {
                    $fullDate .= ' and ' . $dates[$i]->start_time_fmt . ' - ' .
                        $dates[$i]->end_time_fmt;
                    break;
                }
            }
            $newDates[] = $fullDate;
        }
        return $newDates;
    }

    public function ratings(): array
    {
        $all = [];
        $all[] = $os = $this->evaluations()->pluck('overall_satisfaction')->avg();
        $all[] = $cr = $this->evaluations()->pluck('content_relevance')->avg();
        $all[] = $se = $this->evaluations()->pluck('speaker_effectiveness')->avg();
        $all[] = $el = $this->evaluations()->pluck('engagement_level')->avg();
        $all[] = $du = $this->evaluations()->pluck('duration')->avg();
        $overall = collect($all)->avg();
        return [
            'os' => $os,
            'cr' => $cr,
            'se' => $se,
            'el' => $el,
            'du' => $du,
            'overall' => $overall
        ];
    } 

    public function bannerPlaceholderColor(): Attribute
    {
        $colors = ['#F3ECD6', '#DFF4FF', '#FBF1E7', '#D5F4EE', '#DCE6EE', '#F5EEE9'];
        $limit = count($colors);
        $color = $colors[crc32($this->gpoaActivity->name) % $limit];
        return Attribute::make(
            get: fn () => $color,
        );
    }

    public function membersOnly(): Attribute
    {
        $members = ['BSIT', 'DIT'];
        $selectedCourses = array_map('strtoupper', $this->courses()
            ->pluck('acronym')->toArray());
        $membersOnly = $members === $selectedCourses; 
        return Attribute::make(
            get: fn () => $membersOnly,
        );
    }

    public function status(): Attribute
    {
        $status = '';
	if ($this->is_ongoing) $status = 'Ongoing';
	elseif ($this->is_upcoming) $status = 'Upcoming';
	elseif ($this->is_completed) $status = 'Completed';
        return Attribute::make(
            get: fn () => $status,
        );
    }

    public function isUpcoming(): Attribute
    {
        $now = Carbon::now();
        if (config('database.default') === 'sqlite') {
            $endDateColQuery = <<<SQL
case
    when "end_time" <= "start_time" then datetime("date" || ' ' || "end_time", '+1 day')
    else datetime("date" || ' ' || "end_time")
end
SQL;
        } else {
            $endDateColQuery = <<<SQL
case
    when "end_time" <= "start_time" then concat("date", ' ', "end_time") + interval 1 day
    else concat("date", ' ', "end_time")
end
SQL;
        }
        $isUpcoming = $this->dates()
            ->selectRaw("start_date > ? as value", [$now])
            ->orderBy('end_date', 'desc')->limit(1)
            ->value('value');
        return Attribute::make(
            get: fn () => $isUpcoming,
        );
    }

    public function isOngoing(): Attribute
    {
        $now = Carbon::now();
        $startDate = "concat(\"date\", ' ', \"start_time\")";
        $endDate = "concat(\"date\", ' ', \"end_time\")";
        $ongoingQuery = <<<SQL
? between start_date and end_date
SQL;
        $isOngoing = $this->dates()
            ->whereRaw($ongoingQuery, [$now])->exists();
        return Attribute::make(
            get: fn () => $isOngoing,
        );
    }

    public function isCompleted(): Attribute
    {
        $now = Carbon::now();
        if (config('database.default') === 'sqlite') {
            $endDateColQuery = <<<SQL
case
    when "end_time" <= "start_time" then datetime("date" || ' ' || "end_time", '+1 day')
    else datetime("date" || ' ' || "end_time")
end
SQL;
        } else {
            $endDateColQuery = <<<SQL
case
    when "end_time" <= "start_time" then concat("date", ' ', "end_time") + interval 1 day
    else concat("date", ' ', "end_time")
end
SQL;
        }
        $isCompleted = $this->dates()
            ->selectRaw("end_date < ? as is_completed", 
                [$now])
            ->orderBy('end_date', 'desc')->limit(1)
            ->value('is_completed');
        return Attribute::make(
            get: fn () => $isCompleted,
        );
    }

    public function officerAttendees()
    {
        $attendees = User::withAggregate('position', 'position_order')
            ->whereHas('eventDates.event', function ($query) {
                $query->whereKey($this->id);
            })->orderBy('position_position_order', 'asc')->get();
        return $attendees;
    }

    public function attendees()
    {
        $attendees = EventStudent::whereHas('eventDates.event', function ($query) {
            $query->whereKey($this->id);
        })->get();
        return $attendees;
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereHas('gpoaActivity.gpoa', function ($query) {
            $query->where('active', 1);
        });
    }

    #[Scope]
    protected function approved(Builder $query, $startDate = null,
            $endDate = null): void
    {
        $query->withAggregate('dates', 'date')
            ->whereRelation('accomReport', 'status', 'approved');
        if ($startDate && !$endDate) {
            $query->whereHas('dates', function ($query)
                    use ($startDate) {
                $query->where('date', '>=', $startDate);
            });
        } elseif ($startDate && $endDate) {
            $query->whereHas('dates', function ($query)
                    use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            });
        }
        $query->orderBy('dates_date', 'asc');
    }

    #[Scope]
    protected function completed(Builder $query): void
    {
/*
        $latestDatesSub = EventDate::select('event_id', 
            DB::raw("max(concat(\"date\", ' ', \"end_time\")) as latest_date"))
            ->groupBy('event_id');
        $latestDates = EventDate::from('event_dates as ed')
            ->joinSub($latestDatesSub, 'ed_max', function ($join) {
                $join->on('ed.event_id', '=', 'ed_max.event_id')
                    ->on(DB::raw("concat(\"ed.date\", ' ', \"ed.end_time\")"), '=', 
                    'ed_max.latest_date');
        })->select('ed.*');
        $query->leftJoinSub($latestDates, 'latest_dates', function ($join) { 
            $join->on('events.id', '=', 'latest_dates.event_id'); 
        })->select('events.*')->distinct()->where(function ($query) {
            $query->whereRaw("concat(\"latest_dates.date\", 
                ' ', \"latest_dates.end_time\") < ?", [Carbon::now()])
            ->orWhereNull('latest_dates.date');
        })->orderBy('date', 'desc')->orderBy('end_time', 'desc');
*/
        if (config('database.default') === 'sqlite') {
            $endDateColQuery = <<<SQL
case
    when "end_time" <= "start_time" then datetime("date" || ' ' || "end_time", '+1 day')
    else datetime("date" || ' ' || "end_time")
end
SQL;
        } else {
            $endDateColQuery = <<<SQL
case
    when "end_time" <= "start_time" then concat("date", ' ', "end_time") + interval 1 day
    else concat("date", ' ', "end_time")
end
SQL;
        }
        $latestDates = EventDate::select('event_id', 
            DB::raw("max(end_date) as latest_date"))
            ->groupBy('event_id');
        $query->leftJoinSub($latestDates, 'latest_dates', function ($join) { 
            $join->on('events.id', '=', 'latest_dates.event_id'); 
        })->select('events.*')->distinct()->where(function ($query) {
            $query->whereRaw("latest_date < ?", [Carbon::now()])
            ->orWhereNull('latest_date');
        })->orderBy('latest_date', 'desc');
    }

    #[Scope]
    protected function upcoming(Builder $query): void
    {
        $nextDays = 5;
        $query->join('event_dates', 'event_dates.event_id', '=', 'events.id')
            ->select('events.*')->distinct()
            ->whereRaw("start_date > ?", [Carbon::now()])
            ->orderBy('start_date', 'desc');
    }

    #[Scope]
    protected function ongoing(Builder $query): void
    {
        $now = Carbon::now();
        $startDate = "concat(\"date\", ' ', \"start_time\")";
        $endDate = "concat(\"date\", ' ', \"end_time\")";
        $ongoingQuery = <<<SQL
? between start_date and end_date
SQL;
        $query->join('event_dates', 'event_dates.event_id', '=', 'events.id')
            ->select('events.*')
            ->distinct()
            ->whereRaw($ongoingQuery, [$now]);
    }

    #[Scope]
    protected function ongoingAndUpcoming(Builder $query): void
    {
        $now = Carbon::now();
        $startDate = "concat(\"date\", ' ', \"start_time\")";
        $endDate = "concat(\"date\", ' ', \"end_time\")";
        $ongoingQuery = <<<SQL
? between start_date and end_date
SQL;
        $query->join('event_dates', 'event_dates.event_id', '=', 'events.id')
            ->select('events.*')
            ->distinct()
            ->where(function ($query) use ($now, $ongoingQuery) {
                $query->whereRaw("start_date > ?", [$now])
                    ->orWhereRaw($ongoingQuery, [$now]);
            })
            ->orderByRaw($ongoingQuery . ' desc', [$now])
            ->orderBy('start_date', 'desc');
    }
}

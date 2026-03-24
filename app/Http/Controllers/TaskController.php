<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\TaskStatus;
use App\Models\Event;
use App\Http\Requests\SaveTaskRequest;

class TaskController extends Controller
{
    public function index(Event $event)
    {
        return view('tasks.index', [
            'tasks' => $tasks,
        ]);
    }

    public function create(Event $event)
    {
        return view('tasks.create', [

        ]);
    }

    public function show(Event $event, Task $task)
    {
        return view('tasks.create', [
            'task' => $task,
        ]);
    }

    public function confirmDestroy(Event $event, Task $task)
    {
        return view('tasks.delete', [
            'task' => $task,
        ]);
    }

    public function store(SaveTaskRequest $request, Event $event)
    {
        self::storeOrUpdate($request, $event);
        return redirect()->route('task.index', [
            'event' => $event->public_id,
        ]);
    }

    public function update(SaveTaskRequest $request, Event $event, Task $task)
    {
        self::storeOrUpdate($request, $event, $task);
        return redirect()->route('task.index', [
            'event' => $event->public_id,
        ]);
    }

    public function destroy(Event $event, Task $task)
    {
        $task->destroy();
        return redirect()->route('task.index', [
            'event' => $event->public_id,
        ]);
    }

    private static function storeOrUpdate(Request $request, 
        Event $event, Task $task = null)
    {
        if (!$task) {
            $task = new Task;
            $task->event()->associate($event);
        }
        $task->task = $request->task;
        $task->deadline = $request->deadline;
        $task->notes = $request->notes;
        $task->save();
        if ($request->has('type')) {
            $taskType = TaskType::find($request->type);
        }
        if ($request->has('status')) {
            $taskStatus = TaskStatus::find($request->type);
            $task->status()->associate($taskStatus);
        }
        $task->save();
    }
}

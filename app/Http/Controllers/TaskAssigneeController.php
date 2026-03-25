<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Task;
use App\Models\TaskAssignee;
use App\Models\User;
use App\Http\Requests\StoreTaskAssigneeRequest;

class TaskAssigneeController extends Controller
{
    public function index(Event $event, Task $task)
    {
        return view('task-assignees.index', [
            'taskAssignees' => $taskAssignees,
        ]);
    }

    public function create(Event $event, Task $task)
    {
        return view('task-assignees.create', [

        ]);
    }

    public function confirmDestroy(Event $event, Task $task, 
        TaskAssignee $taskAssignee)
    {
        return view('task-assignees.delete', [
            'taskAssignee' => $taskAssignee,
        ]);
    }

    public function store(StoreTaskAssigneeRequest $request, Event $event, 
        Task $task)
    {
        self::storeOrUpdate($request, $task);
        return redirect()->route('task.assignee.index');
    }

    public function destroy(Event $event, Task $task, 
        TaskAssignee $taskAssignee)
    {
        $taskAssignee->destroy();
        return redirect()->route('task.assignee.index');
    }

    private static function storeOrUpdate(Request $request, Task $task,
        TaskAssignee $taskAssignee = null)
    {
        if (!$taskAssignee) {
            $taskAssignee = new TaskAssignee;
            $taskAssignee->task()->associate($task);
        }
        $user = User::findByPublic($request->assignee);
        $taskAssignee->assignee()->associate($user);
        $taskAssignee->role = $request->role;
        $taskAssignee->save();
    }
}

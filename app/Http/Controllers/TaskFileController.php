<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Task;
use App\Models\TaskFile;
use App\Http\Requests\StoreTaskFileRequest;

class TaskFileController extends Controller
{
    public function index(Event $event, Task $task)
    {
        return view('task-files.index', [
            'taskFiles' => $taskFiles,
        ]);
    }

    public function create(Event $event, Task $task)
    {
        return view('task-files.create', [

        ]);
    }

    public function confirmDestroy(Event $event, Task $task, TaskFile $taskFile)
    {
        return view('task-files.delete', [
            'taskFile' => $taskFile,
        ]);
    }

    public function store(StoreTaskFileRequest $request, Event $event, 
        Task $task)
    {
        self::storeOrUpdate($request, $task);
        return redirect()->route('task.file.index');
    }

    public function destroy(Event $event, Task $task, TaskFile $taskFile)
    {
        $taskFile->destroy();
        return redirect()->route('task.file.index');
    }

    private static function storeOrUpdate(Request $request, Task $task,
        TaskFile $taskFile = null)
    {
        if (!$taskFile) {
            $taskFile = new TaskFile;
            $taskFile->task()->associate($task);
        }
        $taskFile->file_name = $request->name;
        $taskFile->url = $request->url;
        $taskFile->save();
    }
}

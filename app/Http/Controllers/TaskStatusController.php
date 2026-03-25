<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskStatus;
use App\Http\Requests\StoreTaskStatusRequest;


class TaskStatusController extends Controller
{
    public function index()
    {
        return view('task-statuses.index', [
            'taskStatuses' => $taskStatuses,
        ]);
    }

    public function create()
    {
        return view('task-statuses.create', [

        ]);
    }

    public function confirmDestroy(TaskStatus $taskStatus)
    {
        return view('task-statuses.delete', [
            'taskStatus' => $taskStatus,
        ]);
    }

    public function store(StoreTaskStatusRequest $request)
    {
        self::storeOrUpdate($request);
        return redirect()->route('task.status.index');
    }

    public function destroy(TaskStatus $taskStatus)
    {
        $taskStatus->destroy();
        return redirect()->route('task.status.index');
    }

    private static function storeOrUpdate(Request $request, 
        TaskStatus $taskStatus = null)
    {
        if (!$taskStatus) {
            $taskStatus = new TaskStatus;
        }
        $taskStatus->status_name = $request->name;
        $taskStatus->save();
    }
}

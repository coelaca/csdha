<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskType;
use App\Http\Requests\StoreTaskTypeRequest;

class TaskTypeController extends Controller
{
    public function index()
    {
        return view('task-types.index', [
            'taskTypes' => $taskTypes,
        ]);
    }

    public function create()
    {
        return view('task-types.create', [

        ]);
    }

    public function confirmDestroy(TaskType $taskType)
    {
        return view('task-types.delete', [
            'taskType' => $taskType,
        ]);
    }

    public function store(StoreTaskTypeRequest $request)
    {
        self::storeOrUpdate($request);
        return redirect()->route('task.type.index');
    }

    public function destroy(TaskType $taskType)
    {
        $taskType->destroy();
        return redirect()->route('task.type.index');
    }

    private static function storeOrUpdate(Request $request, 
        TaskType $taskType = null)
    {
        if (!$taskType) {
            $taskType = new TaskType;
        }
        $taskType->type_name = $request->name;
        $taskType->save();
    }
}
